<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatchItem;
use App\Models\MarketStall;
use App\Models\MarketStallLease;
use App\Models\MarketTenant;
use App\Support\MarketDueLogService;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MarketDashboardController extends Controller
{
    public function index(Request $request): View
    {
        MarketDueLogService::sync();

        $today = Carbon::today();
        $period = strtolower((string) $request->query('period', 'week'));
        $allowedPeriods = ['today', 'week', 'month', 'range'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'week';
        }

        $parsedFrom = $this->parseDate((string) $request->query('date_from', ''));
        $parsedTo = $this->parseDate((string) $request->query('date_to', ''));

        if ($period === 'today') {
            $rangeStart = $today->copy()->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $today->toDateString();
            $dateTo = $today->toDateString();
            $filterLabel = 'Today';
        } elseif ($period === 'week') {
            $rangeStart = $today->copy()->startOfWeek()->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Week';
        } elseif ($period === 'range' && $parsedFrom && $parsedTo && $parsedFrom->lte($parsedTo)) {
            $rangeStart = $parsedFrom->copy()->startOfDay();
            $rangeEnd = $parsedTo->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'Custom Range';
        } elseif ($period === 'month') {
            $rangeStart = $today->copy()->startOfMonth();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Month';
        } else {
            $period = 'week';
            $rangeStart = $today->copy()->startOfWeek()->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Week';
        }

        $displayRange = $rangeStart->isSameDay($rangeEnd)
            ? $rangeStart->format('F j, Y')
            : $rangeStart->format('F j, Y') . ' to ' . $rangeEnd->format('F j, Y');

        $daysInRange = max(1, $rangeStart->diffInDays($rangeEnd) + 1);
        $previousStart = $rangeStart->copy()->subDays($daysInRange)->startOfDay();
        $previousEnd = $rangeStart->copy()->subDay()->endOfDay();

        $dispatchBaseQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            });

        $currentDispatchQuery = (clone $dispatchBaseQuery)
            ->whereBetween('updated_at', [$rangeStart, $rangeEnd]);

        $previousDispatchQuery = (clone $dispatchBaseQuery)
            ->whereBetween('updated_at', [$previousStart, $previousEnd]);

        $totalDispatchCount = (clone $currentDispatchQuery)->count();
        $previousDispatchCount = (clone $previousDispatchQuery)->count();
        $acceptedDispatchCount = (clone $currentDispatchQuery)->where('status', 'accepted')->count();
        $pendingDispatchCount = (clone $currentDispatchQuery)->whereIn('status', ['sent', 'rejected'])->count();
        $awaitingDispatchCount = (clone $currentDispatchQuery)->where('status', 'collected_pending_confirmation')->count();

        $acceptedAmount = (float) (clone $currentDispatchQuery)->where('status', 'accepted')->sum('amount_snapshot');
        $pendingAmount = (float) (clone $currentDispatchQuery)->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])->sum('amount_snapshot');

        $activeStalls = (int) MarketStall::query()->where('stall_status', 'occupied')->count();
        $totalStalls = (int) MarketStall::query()->count();
        $activeLeases = (int) MarketStallLease::query()->where('lease_status', 'active')->count();
        $activeTenants = (int) MarketTenant::query()->whereHas('activeLease')->count();

        $occupancyPercent = $totalStalls > 0
            ? (int) round(($activeStalls / $totalStalls) * 100)
            : 0;

        $openQueueCount = (int) (clone $dispatchBaseQuery)
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->count();

        $openLeaseIds = CollectionDispatchItem::query()
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->whereNotNull('market_stall_lease_id')
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            })
            ->pluck('market_stall_lease_id')
            ->filter(static fn ($id): bool => $id !== null)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $readyLeaseQuery = MarketStallLease::query()
            ->where('lease_status', 'active')
            ->whereHas('stall', static function ($query): void {
                $query->where('stall_status', 'occupied')->where('is_billable', true);
            });

        if ($openLeaseIds->isNotEmpty()) {
            $readyLeaseQuery->whereNotIn('id', $openLeaseIds->all());
        }

        $readyLeaseCount = (int) $readyLeaseQuery->count();

        $rangeDays = $rangeStart->copy()->startOfDay()->diffInDays($rangeEnd->copy()->startOfDay()) + 1;
        if ($rangeDays <= 45) {
            $queueGranularity = 'day';
        } elseif ($rangeDays <= 180) {
            $queueGranularity = 'week';
        } else {
            $queueGranularity = 'month';
        }

        $bucketExpr = match ($queueGranularity) {
            'day' => "DATE(updated_at)",
            'week' => "YEARWEEK(updated_at, 3)",
            default => "DATE_FORMAT(updated_at, '%Y-%m')",
        };

        $rawDailyStatus = CollectionDispatchItem::query()
            ->selectRaw("$bucketExpr as bucket")
            ->selectRaw("SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count")
            ->selectRaw("SUM(CASE WHEN status IN ('sent','rejected') THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'collected_pending_confirmation' THEN 1 ELSE 0 END) as awaiting_count")
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            })
            ->whereBetween('updated_at', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $dailyStatusStats = collect();
        if ($queueGranularity === 'day') {
            foreach (CarbonPeriod::create($rangeStart->copy()->startOfDay(), $rangeEnd->copy()->startOfDay()) as $day) {
                $key = $day->toDateString();
                $item = $rawDailyStatus->get($key);
                $dailyStatusStats->push([
                    'label' => $day->format('D'),
                    'date' => $day->format('m/d'),
                    'accepted' => (int) ($item->accepted_count ?? 0),
                    'pending' => (int) ($item->pending_count ?? 0),
                    'awaiting' => (int) ($item->awaiting_count ?? 0),
                    'total' => (int) ($item->accepted_count ?? 0) + (int) ($item->pending_count ?? 0) + (int) ($item->awaiting_count ?? 0),
                ]);
            }
        } elseif ($queueGranularity === 'week') {
            $weekCursor = $rangeStart->copy()->startOfWeek(Carbon::MONDAY);
            $weekLimit = $rangeEnd->copy()->startOfWeek(Carbon::MONDAY);
            while ($weekCursor->lte($weekLimit)) {
                $key = (int) ($weekCursor->format('o') . str_pad((string) $weekCursor->isoWeek(), 2, '0', STR_PAD_LEFT));
                $item = $rawDailyStatus->get($key);
                $dailyStatusStats->push([
                    'label' => 'W' . $weekCursor->isoWeek(),
                    'date' => $weekCursor->format('M d'),
                    'accepted' => (int) ($item->accepted_count ?? 0),
                    'pending' => (int) ($item->pending_count ?? 0),
                    'awaiting' => (int) ($item->awaiting_count ?? 0),
                    'total' => (int) ($item->accepted_count ?? 0) + (int) ($item->pending_count ?? 0) + (int) ($item->awaiting_count ?? 0),
                ]);
                $weekCursor->addWeek();
            }
        } else {
            $monthCursor = $rangeStart->copy()->startOfMonth();
            $monthLimit = $rangeEnd->copy()->startOfMonth();
            while ($monthCursor->lte($monthLimit)) {
                $key = $monthCursor->format('Y-m');
                $item = $rawDailyStatus->get($key);
                $dailyStatusStats->push([
                    'label' => $monthCursor->format('M'),
                    'date' => $monthCursor->format('Y'),
                    'accepted' => (int) ($item->accepted_count ?? 0),
                    'pending' => (int) ($item->pending_count ?? 0),
                    'awaiting' => (int) ($item->awaiting_count ?? 0),
                    'total' => (int) ($item->accepted_count ?? 0) + (int) ($item->pending_count ?? 0) + (int) ($item->awaiting_count ?? 0),
                ]);
                $monthCursor->addMonth();
            }
        }

        $acceptedItemsInRange = CollectionDispatchItem::query()
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            })
            ->where('status', 'accepted')
            ->whereBetween('updated_at', [$rangeStart, $rangeEnd])
            ->get(['updated_at', 'amount_snapshot']);

        $monthlyAmount = collect();
        $monthCursor = $rangeStart->copy()->startOfMonth();
        $monthEnd = $rangeEnd->copy()->startOfMonth();
        $amountByMonth = $acceptedItemsInRange
            ->filter(static fn (CollectionDispatchItem $item): bool => $item->updated_at !== null)
            ->groupBy(static fn (CollectionDispatchItem $item): string => Carbon::parse((string) $item->updated_at)->format('Y-m'))
            ->map(static fn ($items): float => round((float) $items->sum('amount_snapshot'), 2));

        while ($monthCursor->lte($monthEnd)) {
            $ym = $monthCursor->format('Y-m');
            $monthlyAmount->push([
                'label' => $monthCursor->format('M Y'),
                'amount' => (float) ($amountByMonth->get($ym, 0)),
            ]);
            $monthCursor->addMonth();
        }

        $recentItems = CollectionDispatchItem::query()
            ->with([
                'marketStallLease.stall.location',
                'marketStallLease.tenant',
                'dispatch.collector:id,name',
                'marketPaymentCollection:id,payment_number',
            ])
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            })
            ->whereBetween('updated_at', [$rangeStart, $rangeEnd])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function (CollectionDispatchItem $item): array {
                $lease = $item->marketStallLease;
                $stall = $lease?->stall;
                $tenant = $lease?->tenant;
                $collectorName = (string) ($item->dispatch?->collector?->name ?? '-');

                return [
                    'stall_no' => (string) ($stall?->stall_no ?? '-'),
                    'location' => (string) ($stall?->location?->location_code ?? '-'),
                    'tenant_name' => (string) ($tenant ? $tenant->fullName() : '-'),
                    'payment_no' => (string) ($item->marketPaymentCollection?->payment_number ?? '-'),
                    'status' => (string) $item->status,
                    'status_label' => $this->statusLabel((string) $item->status),
                    'collector' => $collectorName,
                    'amount' => round((float) ($item->amount_snapshot ?? 0), 2),
                    'updated_at' => $item->updated_at?->format('m/d/Y h:i A') ?? '-',
                ];
            });

        $acceptedPercent = $totalDispatchCount > 0
            ? (int) round(($acceptedDispatchCount / $totalDispatchCount) * 100)
            : 0;

        return view('market.dashboard', [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterLabel' => $filterLabel,
            'displayRange' => $displayRange,
            'activeStalls' => $activeStalls,
            'totalStalls' => $totalStalls,
            'occupancyPercent' => $occupancyPercent,
            'activeLeases' => $activeLeases,
            'activeTenants' => $activeTenants,
            'readyLeaseCount' => $readyLeaseCount,
            'openQueueCount' => $openQueueCount,
            'totalDispatchCount' => $totalDispatchCount,
            'previousDispatchCount' => $previousDispatchCount,
            'acceptedDispatchCount' => $acceptedDispatchCount,
            'pendingDispatchCount' => $pendingDispatchCount,
            'awaitingDispatchCount' => $awaitingDispatchCount,
            'acceptedAmount' => $acceptedAmount,
            'pendingAmount' => $pendingAmount,
            'acceptedPercent' => $acceptedPercent,
            'dailyStatusStats' => $dailyStatusStats,
            'monthlyAmount' => $monthlyAmount,
            'recentItems' => $recentItems,
        ]);
    }

    private function parseDate(string $value): ?Carbon
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $trimmed)->startOfDay();
        } catch (\Throwable) {
            try {
                return Carbon::parse($trimmed)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'Pending',
            'rejected' => 'Rejected',
            'collected_pending_confirmation' => 'Awaiting',
            'accepted' => 'Accepted',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }
}
