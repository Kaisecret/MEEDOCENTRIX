<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\FishportLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FishportSendPaymentController extends Controller
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

        $query = FishportLog::query()
            ->with([
                'vessel:id,name',
                'origin:id,name',
                'paymentRecord:id,fishport_log_id,payment_number,total_amount,payer_name',
            ])
            ->where('is_paid', false);

        if ($search !== '') {
            $searchLike = '%' . $search . '%';
            $query->where(function ($innerQuery) use ($searchLike): void {
                $innerQuery->where('log_number', 'like', $searchLike)
                    ->orWhereHas('vessel', static fn ($vesselQuery) => $vesselQuery->where('name', 'like', $searchLike))
                    ->orWhereHas('origin', static fn ($originQuery) => $originQuery->where('name', 'like', $searchLike))
                    ->orWhereHas('paymentRecord', static fn ($paymentRecordQuery) => $paymentRecordQuery->where('payment_number', 'like', $searchLike));
            });
        }

        if ($period === 'today') {
            $today = Carbon::today()->toDateString();
            $query->whereDate('log_date', $today);
        } elseif ($period === 'week') {
            $query->whereBetween('log_date', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString(),
            ]);
        } elseif ($period === 'custom') {
            if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
                $query->whereDate('log_date', '>=', $from);
            }

            if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
                $query->whereDate('log_date', '<=', $to);
            }
        }

        $logs = $query
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->get();

        $openItems = CollectionDispatchItem::query()
            ->select(['id', 'fishport_log_id', 'status'])
            ->whereIn('status', ['sent', 'collected_pending_confirmation'])
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'fishport');
            })
            ->get();

        $openDispatchByLogId = $openItems
            ->mapWithKeys(static fn (CollectionDispatchItem $item) => [
                (int) $item->fishport_log_id => [
                    'item_id' => (int) $item->id,
                    'status' => (string) $item->status,
                ],
            ])
            ->all();

        $collectors = CollectorDepartmentAssignment::query()
            ->with(['collector:id,name,is_active', 'department:id,code,name'])
            ->whereHas('department', static fn ($departmentQuery) => $departmentQuery->where('code', 'fishport'))
            ->whereHas('collector', static fn ($collectorQuery) => $collectorQuery->where('is_active', true))
            ->orderByDesc('updated_at')
            ->get()
            ->map(static function (CollectorDepartmentAssignment $assignment): array {
                return [
                    'user_id' => (int) $assignment->collector_user_id,
                    'name' => (string) ($assignment->collector?->name ?? 'Collector'),
                    'department' => (string) ($assignment->department?->name ?? 'Fishport'),
                ];
            })
            ->values();

        $awaitingConfirmationItems = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'fishportLog:id,log_number,log_date,log_time,arr_dep,fishport_vessel_id,fishport_origin_id',
                'fishportLog.vessel:id,name',
                'fishportLog.origin:id,name',
            ])
            ->where('status', 'collected_pending_confirmation')
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'fishport'))
            ->orderByDesc('collected_at')
            ->limit(50)
            ->get();

        return view('fishport.send_payment', [
            'logs' => $logs,
            'collectors' => $collectors,
            'openDispatchByLogId' => $openDispatchByLogId,
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
            'log_ids' => ['required', 'array', 'min:1'],
            'log_ids.*' => ['required', 'integer', Rule::exists('fishport_logs', 'id')],
            'period_type' => ['nullable', 'string', 'max:20'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment = CollectorDepartmentAssignment::query()
            ->where('collector_user_id', (int) $validated['collector_user_id'])
            ->whereHas('department', static fn ($departmentQuery) => $departmentQuery->where('code', 'fishport'))
            ->first();

        if (! $assignment) {
            return redirect()
                ->back()
                ->with('error', 'Selected collector is not assigned to Fishport.');
        }

        $selectedLogIds = collect($validated['log_ids'])
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $logs = FishportLog::query()
            ->with('paymentRecord:id,fishport_log_id,total_amount')
            ->whereIn('id', $selectedLogIds)
            ->where('is_paid', false)
            ->get();

        if ($logs->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No unpaid logs selected for sending.');
        }

        $openLogIds = CollectionDispatchItem::query()
            ->whereIn('fishport_log_id', $logs->pluck('id'))
            ->whereIn('status', ['sent', 'collected_pending_confirmation'])
            ->pluck('fishport_log_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $eligibleLogs = $logs->reject(static fn (FishportLog $log): bool => in_array((int) $log->id, $openLogIds, true))->values();

        if ($eligibleLogs->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Selected logs are already in an active collector queue.');
        }

        DB::transaction(function () use ($request, $validated, $eligibleLogs): void {
            $dispatch = CollectionDispatch::query()->create([
                'department_code' => 'fishport',
                'collector_user_id' => (int) $validated['collector_user_id'],
                'sent_by_user_id' => $request->user()?->id,
                'period_type' => $validated['period_type'] ?? null,
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $itemRows = $eligibleLogs->map(static function (FishportLog $log) use ($dispatch): array {
                $totalAmount = (float) ($log->paymentRecord?->total_amount ?? $log->payments()->sum('total'));

                return [
                    'collection_dispatch_id' => $dispatch->id,
                    'fishport_log_id' => $log->id,
                    'payment_record_id' => $log->paymentRecord?->id,
                    'amount_snapshot' => round($totalAmount, 2),
                    'status' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            CollectionDispatchItem::query()->insert($itemRows);
        });

        return redirect()
            ->back()
            ->with('status', count($eligibleLogs) . ' transaction(s) sent to collector queue.');
    }

    public function cancel(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        $dispatchItem->loadMissing('dispatch', 'fishportLog');

        if (! $dispatchItem->dispatch || (string) $dispatchItem->dispatch->department_code !== 'fishport') {
            return redirect()
                ->back()
                ->with('error', 'This queue item does not belong to Fishport.');
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
                'review_note' => 'Cancelled by fishport personnel before collection.',
            ]);

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
        });

        return redirect()
            ->back()
            ->with('status', 'Sent transaction cancelled and returned to local queue.');
    }

    public function approve(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        if ((string) $dispatchItem->status !== 'collected_pending_confirmation') {
            return redirect()->back()->with('error', 'This collection item is not waiting for approval.');
        }

        DB::transaction(function () use ($request, $dispatchItem): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with(['dispatch', 'fishportLog.paymentRecord'])
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if ((string) $item->status !== 'collected_pending_confirmation') {
                return;
            }

            $log = $item->fishportLog;
            if (! $log) {
                return;
            }

            $log->update([
                'is_paid' => true,
                'paid_at' => now(),
                'paid_by_user_id' => $request->user()?->id,
            ]);

            $log->paymentRecord()->updateOrCreate(
                [],
                [
                    'payment_number' => $log->paymentRecord?->payment_number ?: ('FP-PAY-' . str_pad((string) $log->id, 6, '0', STR_PAD_LEFT)),
                    'total_amount' => round((float) ($item->amount_snapshot ?? 0), 2),
                    'payer_name' => $item->payer_name ?: ($log->paymentRecord?->payer_name ?: null),
                    'generated_by_user_id' => $log->paymentRecord?->generated_by_user_id ?: $request->user()?->id,
                    'generated_at' => $log->paymentRecord?->generated_at ?: now(),
                ]
            );

            $item->update([
                'status' => 'accepted',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $request->user()?->id,
                'review_note' => trim((string) $request->input('review_note', '')),
            ]);

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
        });

        return redirect()
            ->back()
            ->with('status', 'Collection proof approved. Transaction is now marked as paid.');
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
