<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\MarketPaymentCollection;
use App\Models\MarketStallLease;
use App\Support\AppNotificationService;
use App\Support\MarketDueLogService;
use App\Support\MarketQueueLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketSendPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        MarketDueLogService::sync($now);

        $query = MarketStallLease::query()
            ->with([
                'stall.location',
                'tenant',
            ])
            ->withMax('paymentCollections as last_payment_at', 'payment_date')
            ->where('lease_status', 'active')
            ->whereHas('stall', static function ($stallQuery): void {
                $stallQuery
                    ->where('stall_status', 'occupied')
                    ->where('is_billable', true);
            });

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($innerQuery) use ($like): void {
                $innerQuery->where('contract_number', 'like', $like)
                    ->orWhereHas('stall', function ($stallQuery) use ($like): void {
                        $stallQuery->where('stall_no', 'like', $like)
                            ->orWhereHas('location', function ($locationQuery) use ($like): void {
                                $locationQuery->where('location_code', 'like', $like)
                                    ->orWhere('location_name', 'like', $like);
                            });
                    })
                    ->orWhereHas('tenant', function ($tenantQuery) use ($like): void {
                        $tenantQuery->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('business_name', 'like', $like)
                            ->orWhere('contact_number', 'like', $like);
                    });
            });
        }

        $openLeaseIds = CollectionDispatchItem::query()
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->whereNotNull('market_stall_lease_id')
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'market');
            })
            ->pluck('market_stall_lease_id')
            ->filter(static fn ($id) => $id !== null)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($openLeaseIds->isNotEmpty()) {
            $query->whereNotIn('id', $openLeaseIds->all());
        }

        $leases = $query
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->filter(fn (MarketStallLease $lease): bool => $this->isLeaseDueToday($lease, $today))
            ->values();

        $collectors = CollectorDepartmentAssignment::query()
            ->with(['collector:id,name,is_active,is_absent', 'department:id,code,name'])
            ->whereHas('department', static fn ($departmentQuery) => $departmentQuery->where('code', 'market'))
            ->whereHas('collector', static function ($collectorQuery): void {
                $collectorQuery->where('is_active', true);

                if (Schema::hasColumn('users', 'is_absent')) {
                    $collectorQuery->where(function ($availabilityQuery): void {
                        $availabilityQuery->whereNull('is_absent')
                            ->orWhere('is_absent', false);
                    });
                }
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(static function (CollectorDepartmentAssignment $assignment): array {
                return [
                    'user_id' => (int) $assignment->collector_user_id,
                    'name' => (string) ($assignment->collector?->name ?? 'Collector'),
                    'department' => (string) ($assignment->department?->name ?? 'Public Market'),
                ];
            })
            ->values();

        $awaitingConfirmationItems = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'marketStallLease.stall.location',
                'marketStallLease.tenant',
            ])
            ->where('status', 'collected_pending_confirmation')
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'))
            ->orderByDesc('collected_at')
            ->limit(50)
            ->get();

        return view('market.send_payment', [
            'leases' => $leases,
            'collectors' => $collectors,
            'awaitingConfirmationItems' => $awaitingConfirmationItems,
            'search' => $search,
        ]);
    }

    public function dueTracker(Request $request): View
    {
        $now = Carbon::now();
        MarketDueLogService::sync($now);
        $dailyDueSummary = MarketDueLogService::dailySummary(30);

        return view('market.send_payment_due_tracker', [
            'dueTrackerRows' => $dailyDueSummary['rows'],
            'dueTrackerToday' => $dailyDueSummary['today'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'collector_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'lease_ids' => ['required', 'array', 'min:1'],
            'lease_ids.*' => ['required', 'integer', Rule::exists('market_stall_leases', 'id')],
            'period_type' => ['nullable', 'string', 'max:20'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment = CollectorDepartmentAssignment::query()
            ->where('collector_user_id', (int) $validated['collector_user_id'])
            ->whereHas('department', static fn ($departmentQuery) => $departmentQuery->where('code', 'market'))
            ->whereHas('collector', static function ($collectorQuery): void {
                $collectorQuery->where('is_active', true);

                if (Schema::hasColumn('users', 'is_absent')) {
                    $collectorQuery->where(function ($availabilityQuery): void {
                        $availabilityQuery->whereNull('is_absent')
                            ->orWhere('is_absent', false);
                    });
                }
            })
            ->first();

        if (! $assignment) {
            return redirect()
                ->back()
                ->with('error', 'Selected collector is not available for Public Market assignment.');
        }

        $selectedLeaseIds = collect($validated['lease_ids'])
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $leases = MarketStallLease::query()
            ->whereIn('id', $selectedLeaseIds)
            ->where('lease_status', 'active')
            ->whereHas('stall', static function ($stallQuery): void {
                $stallQuery->where('stall_status', 'occupied')
                    ->where('is_billable', true);
            })
            ->get();

        if ($leases->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No active market leases selected for sending.');
        }

        $openLeaseIds = CollectionDispatchItem::query()
            ->whereIn('market_stall_lease_id', $leases->pluck('id'))
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'market');
            })
            ->pluck('market_stall_lease_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $eligibleLeases = $leases
            ->reject(static fn (MarketStallLease $lease): bool => in_array((int) $lease->id, $openLeaseIds, true))
            ->values();

        if ($eligibleLeases->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Selected leases are already in an active collector queue.');
        }

        $dispatchId = null;
        DB::transaction(function () use ($request, $validated, $eligibleLeases, &$dispatchId): void {
            $dispatch = CollectionDispatch::query()->create([
                'department_code' => 'market',
                'collector_user_id' => (int) $validated['collector_user_id'],
                'sent_by_user_id' => $request->user()?->id,
                'period_type' => $validated['period_type'] ?? null,
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            $dispatchId = (int) $dispatch->id;

            $itemRows = $eligibleLeases->map(static function (MarketStallLease $lease) use ($dispatch): array {
                $amount = round((float) ($lease->computed_rate_amount ?? 0), 2);

                return [
                    'collection_dispatch_id' => $dispatch->id,
                    'fishport_log_id' => null,
                    'market_stall_lease_id' => $lease->id,
                    'payment_record_id' => null,
                    'market_payment_collection_id' => null,
                    'amount_snapshot' => $amount,
                    'status' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            CollectionDispatchItem::query()->insert($itemRows);
        });

        if ($dispatchId) {
            AppNotificationService::notifyDispatchSent(
                departmentCode: 'market',
                dispatchId: (int) $dispatchId,
                collectorUserId: (int) $validated['collector_user_id'],
                itemCount: $eligibleLeases->count(),
                actorName: (string) ($request->user()?->name ?? 'Market personnel'),
                createdByUserId: $request->user()?->id
            );
        }

        return redirect()
            ->back()
            ->with('status', count($eligibleLeases) . ' market transaction(s) sent to collector queue.');
    }

    public function cancel(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        $dispatchItem->loadMissing('dispatch', 'marketStallLease.stall');

        if (! $dispatchItem->dispatch || (string) $dispatchItem->dispatch->department_code !== 'market') {
            return redirect()
                ->back()
                ->with('error', 'This queue item does not belong to Public Market.');
        }

        if ((string) $dispatchItem->status !== 'sent') {
            return redirect()
                ->back()
                ->with('error', 'Only items in "Sent to collector" status can be cancelled.');
        }

        DB::transaction(function () use ($request, $dispatchItem): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with('dispatch')
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if ((string) $item->status !== 'sent') {
                return;
            }

            $item->update([
                'status' => 'cancelled',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $request->user()?->id,
                'review_note' => 'Cancelled by market personnel before collection.',
            ]);

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
            AppNotificationService::notifyDispatchItemReviewed($item, 'cancelled', $request->user()?->id);
        });

        return redirect()
            ->back()
            ->with('status', 'Sent market transaction cancelled and returned to local queue.');
    }

    public function approve(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        if ((string) $dispatchItem->status !== 'collected_pending_confirmation') {
            return redirect()->back()->with('error', 'This collection item is not waiting for approval.');
        }

        DB::transaction(function () use ($request, $dispatchItem): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with(['dispatch', 'marketStallLease'])
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if ((string) $item->status !== 'collected_pending_confirmation') {
                return;
            }

            $payment = MarketPaymentCollection::query()->create([
                'payment_number' => 'MKT-PAY-' . now()->format('Ymd') . '-' . str_pad((string) $item->id, 6, '0', STR_PAD_LEFT),
                'market_stall_lease_id' => $item->market_stall_lease_id,
                'collection_dispatch_item_id' => $item->id,
                'amount_paid' => round((float) ($item->amount_snapshot ?? 0), 2),
                'payer_name' => $item->payer_name ?: null,
                'payment_date' => now(),
                'collector_note' => $item->collector_note ?: null,
                'remarks' => trim((string) $request->input('review_note', '')),
                'generated_by_user_id' => $request->user()?->id,
            ]);

            $item->update([
                'status' => 'accepted',
                'market_payment_collection_id' => $payment->id,
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $request->user()?->id,
                'review_note' => trim((string) $request->input('review_note', '')),
            ]);

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
            AppNotificationService::notifyDispatchItemReviewed($item, 'accepted', $request->user()?->id);
        });

        return redirect()
            ->back()
            ->with('status', 'Collection proof approved. Market payment transaction was recorded.');
    }

    public function reject(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        if ((string) $dispatchItem->status !== 'collected_pending_confirmation') {
            return redirect()->back()->with('error', 'This collection item is not waiting for approval.');
        }

        DB::transaction(function () use ($request, $dispatchItem, $validated): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with('dispatch')
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if ((string) $item->status !== 'collected_pending_confirmation') {
                return;
            }

            $item->update([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $request->user()?->id,
                'review_note' => trim((string) $validated['review_note']),
            ]);

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
            AppNotificationService::notifyDispatchItemReviewed($item, 'rejected', $request->user()?->id);
        });

        return redirect()
            ->back()
            ->with('status', 'Collection proof rejected and returned to collector for correction.');
    }

    private function parseDateInput(string $value): ?Carbon
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

    private function resolveNextDueAt(MarketStallLease $lease): ?Carbon
    {
        $period = strtolower((string) ($lease->billing_period ?? 'monthly'));
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'monthly';
        }

        $cycles = max(1, (int) ($lease->billing_cycles ?? 1));

        $registrationAt = $lease->start_date instanceof Carbon
            ? $lease->start_date->copy()->setTime(7, 0, 0)
            : ($lease->created_at?->copy()->setTime(7, 0, 0));

        if (! $registrationAt) {
            return null;
        }

        $lastPaymentAt = null;
        if ($lease->last_payment_at !== null) {
            try {
                $lastPaymentAt = Carbon::parse((string) $lease->last_payment_at);
            } catch (\Throwable) {
                $lastPaymentAt = null;
            }
        }

        if ($period === 'daily') {
            $base = $lastPaymentAt
                ? $this->marketDayStart($lastPaymentAt)
                : $this->marketDayStart($registrationAt);

            return $base->addDays($cycles);
        }

        if ($period === 'weekly') {
            $base = $lastPaymentAt
                ? $this->marketWeekStart($lastPaymentAt)
                : $this->marketWeekStart($registrationAt);

            return $base->addWeeks($cycles);
        }

        if (! $lastPaymentAt) {
            return $registrationAt->copy()->addMonthsNoOverflow($cycles);
        }

        $base = $this->monthlyCycleStartFromRegistration($registrationAt, $lastPaymentAt);

        return $base->addMonthsNoOverflow($cycles);
    }

    private function isLeaseDueToday(MarketStallLease $lease, Carbon $today): bool
    {
        $period = strtolower((string) ($lease->billing_period ?? 'monthly'));
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'monthly';
        }

        $cycles = max(1, (int) ($lease->billing_cycles ?? 1));
        $start = $lease->start_date instanceof Carbon
            ? $lease->start_date->copy()->startOfDay()
            : ($lease->created_at?->copy()->startOfDay());

        if (! $start) {
            return false;
        }

        $intervalDays = match ($period) {
            'daily' => 1 * $cycles,
            'weekly' => 7 * $cycles,
            default => 30 * $cycles,
        };

        $firstDue = $start->copy()->addDays($intervalDays);
        if ($today->lt($firstDue)) {
            return false;
        }

        $elapsedDays = $firstDue->diffInDays($today);

        return $elapsedDays % $intervalDays === 0;
    }

    private function marketDayStart(Carbon $moment): Carbon
    {
        $start = $moment->copy()->setTime(7, 0, 0);
        if ($moment->lt($start)) {
            $start->subDay();
        }

        return $start;
    }

    private function marketWeekStart(Carbon $moment): Carbon
    {
        $start = $moment->copy()->startOfWeek(Carbon::MONDAY)->setTime(7, 0, 0);
        if ($moment->lt($start)) {
            $start->subWeek();
        }

        return $start;
    }

    private function monthlyCycleStartFromRegistration(Carbon $registrationAt, Carbon $moment): Carbon
    {
        $day = (int) $registrationAt->day;
        $hour = (int) $registrationAt->hour;
        $minute = (int) $registrationAt->minute;
        $second = (int) $registrationAt->second;

        $candidate = $moment->copy()->startOfMonth();
        $candidate->day(min($day, $candidate->daysInMonth))->setTime($hour, $minute, $second);

        if ($moment->lt($candidate)) {
            $candidate->subMonthNoOverflow()->startOfMonth();
            $candidate->day(min($day, $candidate->daysInMonth))->setTime($hour, $minute, $second);
        }

        return $candidate;
    }

    private function refreshDispatchStatus(int $dispatchId): void
    {
        MarketQueueLifecycle::refreshDispatchStatus($dispatchId);
    }
}
