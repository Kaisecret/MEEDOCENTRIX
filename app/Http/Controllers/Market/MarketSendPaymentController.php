<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\MarketPaymentCollection;
use App\Models\MarketStallLease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketSendPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $period = (string) $request->query('period', 'today');
        if (! in_array($period, ['today', 'week', 'all', 'custom'], true)) {
            $period = 'today';
        }

        $search = trim((string) $request->query('q', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $query = MarketStallLease::query()
            ->with([
                'stall.location',
                'tenant',
            ])
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

        if ($period === 'today') {
            $query->whereDate('updated_at', now()->toDateString());
        } elseif ($period === 'week') {
            $query->whereBetween('updated_at', [
                now()->startOfWeek()->startOfDay()->toDateTimeString(),
                now()->endOfWeek()->endOfDay()->toDateTimeString(),
            ]);
        } elseif ($period === 'custom') {
            if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
                $query->whereDate('updated_at', '>=', $from);
            }
            if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
                $query->whereDate('updated_at', '<=', $to);
            }
        }

        $leases = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $openItems = CollectionDispatchItem::query()
            ->select(['id', 'market_stall_lease_id', 'status'])
            ->whereIn('status', ['sent', 'collected_pending_confirmation'])
            ->whereNotNull('market_stall_lease_id')
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'market');
            })
            ->get();

        $openDispatchByLeaseId = $openItems
            ->mapWithKeys(static fn (CollectionDispatchItem $item) => [
                (int) $item->market_stall_lease_id => [
                    'item_id' => (int) $item->id,
                    'status' => (string) $item->status,
                ],
            ])
            ->all();

        $collectors = CollectorDepartmentAssignment::query()
            ->with(['collector:id,name,is_active', 'department:id,code,name'])
            ->whereHas('department', static fn ($departmentQuery) => $departmentQuery->where('code', 'market'))
            ->whereHas('collector', static fn ($collectorQuery) => $collectorQuery->where('is_active', true))
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
            'openDispatchByLeaseId' => $openDispatchByLeaseId,
            'awaitingConfirmationItems' => $awaitingConfirmationItems,
            'period' => $period,
            'search' => $search,
            'from' => $from,
            'to' => $to,
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
            ->first();

        if (! $assignment) {
            return redirect()
                ->back()
                ->with('error', 'Selected collector is not assigned to Public Market.');
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
            ->whereIn('status', ['sent', 'collected_pending_confirmation'])
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

        DB::transaction(function () use ($request, $validated, $eligibleLeases): void {
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
        });

        return redirect()
            ->back()
            ->with('status', 'Collection proof rejected and returned to collector for correction.');
    }

    private function refreshDispatchStatus(int $dispatchId): void
    {
        $dispatch = CollectionDispatch::query()->find($dispatchId);
        if (! $dispatch) {
            return;
        }

        $statusCounts = CollectionDispatchItem::query()
            ->where('collection_dispatch_id', $dispatchId)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pendingCount = (int) ($statusCounts['sent'] ?? 0);
        $forApprovalCount = (int) ($statusCounts['collected_pending_confirmation'] ?? 0);

        if ($pendingCount === 0 && $forApprovalCount === 0) {
            $dispatch->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return;
        }

        if ($forApprovalCount > 0) {
            $dispatch->update([
                'status' => 'awaiting_confirmation',
                'completed_at' => null,
            ]);

            return;
        }

        $dispatch->update([
            'status' => 'sent',
            'completed_at' => null,
        ]);
    }
}

