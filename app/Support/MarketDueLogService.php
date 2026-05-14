<?php

namespace App\Support;

use App\Models\CollectionDispatchItem;
use App\Models\CollectionDispatch;
use App\Models\MarketDueLog;
use App\Models\MarketStallLease;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketDueLogService
{
    /**
     * Sync due logs for active billable leases and resolve each due status.
     */
    public static function sync(?Carbon $today = null, int $lookbackDays = 90): void
    {
        $today ??= now();
        $dayStart = $today->copy()->startOfDay();
        $windowStart = $dayStart->copy()->subDays(max(7, $lookbackDays));

        $leases = MarketStallLease::query()
            ->with('stall')
            ->where('lease_status', 'active')
            ->whereHas('stall', static function ($stallQuery): void {
                $stallQuery->where('stall_status', 'occupied')
                    ->where('is_billable', true);
            })
            ->get();

        if ($leases->isEmpty()) {
            return;
        }

        self::ensureDueRows($leases, $dayStart, $windowStart);
        self::resolveStatuses($leases, $dayStart, $windowStart);
    }

    /**
     * @return array{
     *   rows: \Illuminate\Support\Collection<int, array{date:string,due:int,sent:int,awaiting:int,paid:int,missed:int}>,
     *   today: array{due:int,sent:int,awaiting:int,paid:int,missed:int}
     * }
     */
    public static function dailySummary(int $days = 14): array
    {
        $days = max(1, $days);
        $end = now()->startOfDay();
        $start = $end->copy()->subDays($days - 1);

        $raw = MarketDueLog::query()
            ->selectRaw('due_date, status, COUNT(*) as aggregate')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('due_date', 'status')
            ->get()
            ->groupBy(static fn ($row): string => Carbon::parse((string) $row->due_date)->toDateString());

        $rows = collect();
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $dayRows = collect($raw->get($key, collect()));
            $rows->push([
                'date' => $day->format('M d, Y'),
                'due' => (int) $dayRows->where('status', 'due')->sum('aggregate'),
                'sent' => (int) $dayRows->where('status', 'sent')->sum('aggregate'),
                'awaiting' => (int) $dayRows->where('status', 'awaiting_confirmation')->sum('aggregate'),
                'paid' => (int) $dayRows->where('status', 'paid')->sum('aggregate'),
                'missed' => (int) $dayRows->where('status', 'missed')->sum('aggregate'),
            ]);
        }

        $todayRow = $rows->last() ?: [
            'date' => $end->format('M d, Y'),
            'due' => 0,
            'sent' => 0,
            'awaiting' => 0,
            'paid' => 0,
            'missed' => 0,
        ];

        return [
            'rows' => $rows,
            'today' => [
                'due' => (int) $todayRow['due'],
                'sent' => (int) $todayRow['sent'],
                'awaiting' => (int) $todayRow['awaiting'],
                'paid' => (int) $todayRow['paid'],
                'missed' => (int) $todayRow['missed'],
            ],
        ];
    }

    public static function enqueueAssignedDueItems(?Carbon $today = null, ?int $actorUserId = null, ?string $actorName = null): int
    {
        if (! Schema::hasColumn('market_stall_leases', 'collector_user_id')) {
            return 0;
        }

        $today ??= now();
        $dayStart = $today->copy()->startOfDay();
        $todayDate = $dayStart->toDateString();

        $dueLogs = MarketDueLog::query()
            ->with([
                'lease:id,collector_user_id,computed_rate_amount,lease_status,market_stall_id,market_tenant_id,billing_period,billing_cycles',
                'lease.stall:id,stall_status,is_billable',
            ])
            ->whereDate('due_date', $todayDate)
            ->where('status', 'due')
            ->whereNull('collection_dispatch_item_id')
            ->whereHas('lease', static function ($leaseQuery): void {
                $leaseQuery
                    ->whereNotNull('collector_user_id')
                    ->where('lease_status', 'active')
                    ->whereHas('stall', static function ($stallQuery): void {
                        $stallQuery->where('stall_status', 'occupied')
                            ->where('is_billable', true);
                    });
            })
            ->orderBy('id')
            ->get();

        if ($dueLogs->isEmpty()) {
            return 0;
        }

        $openLeaseIds = CollectionDispatchItem::query()
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->whereNotNull('market_stall_lease_id')
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'))
            ->pluck('market_stall_lease_id')
            ->filter(static fn ($id): bool => $id !== null)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $candidateLogs = $dueLogs
            ->filter(static function (MarketDueLog $log) use ($openLeaseIds): bool {
                $leaseId = (int) $log->market_stall_lease_id;
                if ($leaseId <= 0) {
                    return false;
                }

                return ! $openLeaseIds->contains($leaseId);
            })
            ->values();

        if ($candidateLogs->isEmpty()) {
            return 0;
        }

        $createdItems = 0;
        $createdDispatchMeta = [];
        $now = now();

        DB::transaction(function () use (
            $candidateLogs,
            $actorUserId,
            $todayDate,
            $now,
            &$createdItems,
            &$createdDispatchMeta
        ): void {
            $dispatchByCollector = [];

            foreach ($candidateLogs as $log) {
                $lease = $log->lease;
                $collectorUserId = (int) ($lease?->collector_user_id ?? 0);
                if (! $lease || $collectorUserId <= 0) {
                    continue;
                }

                $existingOpen = CollectionDispatchItem::query()
                    ->where('market_stall_lease_id', (int) $lease->id)
                    ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
                    ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'))
                    ->exists();

                if ($existingOpen) {
                    continue;
                }

                if (! isset($dispatchByCollector[$collectorUserId])) {
                    $dispatch = CollectionDispatch::query()->create([
                        'department_code' => 'market',
                        'collector_user_id' => $collectorUserId,
                        'sent_by_user_id' => $actorUserId,
                        'period_type' => 'auto',
                        'from_date' => $todayDate,
                        'to_date' => $todayDate,
                        'notes' => 'Auto-generated recurring market due queue.',
                        'status' => 'sent',
                        'sent_at' => $now,
                    ]);

                    $dispatchByCollector[$collectorUserId] = $dispatch;
                    $createdDispatchMeta[$collectorUserId] = [
                        'dispatch_id' => (int) $dispatch->id,
                        'item_count' => 0,
                    ];
                }

                /** @var CollectionDispatch $dispatch */
                $dispatch = $dispatchByCollector[$collectorUserId];
                $amount = round((float) ($log->expected_amount ?? $lease->computed_rate_amount ?? 0), 2);

                $item = CollectionDispatchItem::query()->create([
                    'collection_dispatch_id' => (int) $dispatch->id,
                    'fishport_log_id' => null,
                    'market_stall_lease_id' => (int) $lease->id,
                    'payment_record_id' => null,
                    'market_payment_collection_id' => null,
                    'amount_snapshot' => $amount,
                    'status' => 'sent',
                ]);

                $log->update([
                    'status' => 'sent',
                    'collection_dispatch_item_id' => (int) $item->id,
                    'sent_at' => $now,
                    'notes' => null,
                ]);

                $createdItems++;
                $createdDispatchMeta[$collectorUserId]['item_count']++;
            }
        });

        if ($createdItems > 0) {
            $senderName = trim((string) $actorName) !== '' ? trim((string) $actorName) : 'Market recurring assignment';
            foreach ($createdDispatchMeta as $collectorUserId => $meta) {
                $itemCount = (int) ($meta['item_count'] ?? 0);
                $dispatchId = (int) ($meta['dispatch_id'] ?? 0);
                if ($itemCount <= 0 || $dispatchId <= 0) {
                    continue;
                }

                AppNotificationService::notifyDispatchSent(
                    departmentCode: 'market',
                    dispatchId: $dispatchId,
                    collectorUserId: (int) $collectorUserId,
                    itemCount: $itemCount,
                    actorName: $senderName,
                    createdByUserId: $actorUserId
                );
            }
        }

        return $createdItems;
    }

    /**
     * @param Collection<int, MarketStallLease> $leases
     */
    private static function ensureDueRows(Collection $leases, Carbon $dayStart, Carbon $windowStart): void
    {
        foreach ($leases as $lease) {
            $firstDue = self::firstDueDate($lease);
            if (! $firstDue) {
                continue;
            }

            $cursor = $firstDue->copy();
            while ($cursor->lt($windowStart)) {
                $cursor = self::incrementDueCursor($cursor, $lease);
            }

            while ($cursor->lte($dayStart)) {
                MarketDueLog::query()->firstOrCreate(
                    [
                        'market_stall_lease_id' => (int) $lease->id,
                        'due_date' => $cursor->toDateString(),
                    ],
                    [
                        'billing_period' => (string) ($lease->billing_period ?: 'monthly'),
                        'billing_cycles' => max(1, (int) ($lease->billing_cycles ?? 1)),
                        'expected_amount' => round((float) ($lease->computed_rate_amount ?? 0), 2),
                        'status' => 'due',
                    ]
                );

                $cursor = self::incrementDueCursor($cursor, $lease);
            }
        }
    }

    /**
     * @param Collection<int, MarketStallLease> $leases
     */
    private static function resolveStatuses(Collection $leases, Carbon $dayStart, Carbon $windowStart): void
    {
        $leaseIds = $leases->pluck('id')->map(static fn ($id): int => (int) $id)->values();
        if ($leaseIds->isEmpty()) {
            return;
        }

        $logsByLease = MarketDueLog::query()
            ->whereIn('market_stall_lease_id', $leaseIds->all())
            ->whereBetween('due_date', [$windowStart->toDateString(), $dayStart->toDateString()])
            ->orderBy('market_stall_lease_id')
            ->orderBy('due_date')
            ->get()
            ->groupBy('market_stall_lease_id');

        $dispatchItemsByLease = CollectionDispatchItem::query()
            ->whereIn('market_stall_lease_id', $leaseIds->all())
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'market');
            })
            ->whereDate('created_at', '>=', $windowStart->toDateString())
            ->get()
            ->groupBy('market_stall_lease_id');

        foreach ($logsByLease as $leaseId => $logs) {
            $leaseDispatchItems = collect($dispatchItemsByLease->get($leaseId, collect()))
                ->sortBy('created_at')
                ->values();
            $logs = collect($logs)->values();

            for ($i = 0; $i < $logs->count(); $i++) {
                /** @var MarketDueLog $log */
                $log = $logs[$i];
                $previousStatus = (string) ($log->status ?? 'due');
                $dueStart = Carbon::parse((string) $log->due_date)->startOfDay();
                $nextDueStart = $i + 1 < $logs->count()
                    ? Carbon::parse((string) $logs[$i + 1]->due_date)->startOfDay()
                    : self::fallbackNextDueDate($dueStart, (string) $log->billing_period, (int) $log->billing_cycles);

                $cycleItem = $leaseDispatchItems
                    ->first(static function (CollectionDispatchItem $item) use ($dueStart, $nextDueStart): bool {
                        if (! $item->created_at) {
                            return false;
                        }
                        return $item->created_at->gte($dueStart) && $item->created_at->lt($nextDueStart);
                    });

                $status = 'due';
                $notes = null;
                $dispatchItemId = null;
                $paymentId = null;
                $sentAt = null;
                $paidAt = null;
                $closedAt = null;

                if ($cycleItem) {
                    $dispatchItemId = (int) $cycleItem->id;
                    $sentAt = $cycleItem->created_at;

                    if ((string) $cycleItem->status === 'accepted') {
                        $status = 'paid';
                        $paymentId = $cycleItem->market_payment_collection_id ? (int) $cycleItem->market_payment_collection_id : null;
                        $paidAt = $cycleItem->reviewed_at ?: $cycleItem->updated_at;
                        $closedAt = $paidAt;
                    } elseif ((string) $cycleItem->status === 'collected_pending_confirmation') {
                        $status = 'awaiting_confirmation';
                    } elseif (in_array((string) $cycleItem->status, ['sent', 'rejected'], true)) {
                        $status = 'sent';
                    } elseif ((string) $cycleItem->status === 'cancelled') {
                        $status = $dueStart->lt($dayStart) ? 'missed' : 'due';
                        $notes = $cycleItem->review_note ?: 'Queue entry cancelled.';
                        $closedAt = $cycleItem->reviewed_at ?: $cycleItem->updated_at;
                    }
                } elseif ($dueStart->lt($dayStart)) {
                    $status = 'missed';
                    $notes = 'No dispatch item was sent on due cycle.';
                    $closedAt = $dayStart->copy();
                }

                $log->update([
                    'status' => $status,
                    'collection_dispatch_item_id' => $dispatchItemId,
                    'market_payment_collection_id' => $paymentId,
                    'sent_at' => $sentAt,
                    'paid_at' => $paidAt,
                    'closed_at' => $closedAt,
                    'notes' => $notes,
                    'expected_amount' => round((float) ($log->expected_amount ?? 0), 2),
                ]);

                if ($previousStatus !== 'missed' && $status === 'missed') {
                    /** @var MarketStallLease|null $lease */
                    $lease = $leases->first(static fn (MarketStallLease $entry): bool => (int) $entry->id === (int) $log->market_stall_lease_id);
                    if ($lease) {
                        AppNotificationService::notifyMarketDueMissed($log, $lease);
                    }
                }
            }
        }
    }

    private static function firstDueDate(MarketStallLease $lease): ?Carbon
    {
        $start = $lease->start_date instanceof Carbon
            ? $lease->start_date->copy()->startOfDay()
            : ($lease->created_at ? $lease->created_at->copy()->startOfDay() : null);

        if (! $start) {
            return null;
        }

        return self::incrementDueCursor($start, $lease);
    }

    private static function incrementDueCursor(Carbon $cursor, MarketStallLease $lease): Carbon
    {
        $period = strtolower((string) ($lease->billing_period ?: 'monthly'));
        $cycles = max(1, (int) ($lease->billing_cycles ?? 1));

        return self::fallbackNextDueDate($cursor, $period, $cycles);
    }

    private static function fallbackNextDueDate(Carbon $from, string $period, int $cycles): Carbon
    {
        $period = strtolower(trim($period));
        $cycles = max(1, $cycles);
        $next = $from->copy();

        return match ($period) {
            'daily' => $next->addDays($cycles),
            'weekly' => $next->addDays(7 * $cycles),
            default => $next->addDays(30 * $cycles),
        };
    }
}
