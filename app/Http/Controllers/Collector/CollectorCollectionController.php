<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Support\AppNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CollectorCollectionController extends Controller
{
    public function dashboard(Request $request): View
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;
        if (! $departmentCode) {
            return view('collector.dashboard', [
                'assignment' => $assignment,
                'period' => 'month',
                'from' => '',
                'to' => '',
                'rangeLabel' => 'No Assignment',
                'pendingCount' => 0,
                'pendingTotal' => 0,
                'awaitingCount' => 0,
                'awaitingTotal' => 0,
                'acceptedCount' => 0,
                'acceptedTotal' => 0,
                'rejectedCount' => 0,
                'rejectedTotal' => 0,
                'allCount' => 0,
                'allTotal' => 0,
                'chartLabels' => [],
                'chartPending' => [],
                'chartAccepted' => [],
                'chartRejected' => [],
                'recentTransactions' => collect(),
            ]);
        }
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['today', 'week', 'month', 'custom', 'all'], true)) {
            $period = 'month';
        }

        $fromInput = trim((string) $request->query('from', ''));
        $toInput = trim((string) $request->query('to', ''));

        $hasFromInput = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromInput) === 1;
        $hasToInput = preg_match('/^\d{4}-\d{2}-\d{2}$/', $toInput) === 1;
        if ($period !== 'custom' && ($hasFromInput || $hasToInput)) {
            $period = 'custom';
        }

        [$fromDate, $toDate, $rangeLabel] = $this->resolveDashboardDateRange($period, $fromInput, $toInput);

        $baseQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static function ($query) use ($departmentCode, $request): void {
                $query->where('collector_user_id', (int) $request->user()?->id);
                if ($departmentCode) {
                    $query->where('department_code', $departmentCode);
                }
            });
        $this->applyDepartmentVisibilityFilter($baseQuery, $departmentCode);

        $this->applyQueueDateFilter($baseQuery, $fromDate, $toDate);

        $pendingCount = (clone $baseQuery)->whereIn('status', ['sent', 'rejected'])->count();
        $pendingTotal = (float) (clone $baseQuery)->whereIn('status', ['sent', 'rejected'])->sum('amount_snapshot');
        $awaitingCount = (clone $baseQuery)->where('status', 'collected_pending_confirmation')->count();
        $awaitingTotal = (float) (clone $baseQuery)->where('status', 'collected_pending_confirmation')->sum('amount_snapshot');
        $acceptedCount = (clone $baseQuery)->where('status', 'accepted')->count();
        $acceptedTotal = (float) (clone $baseQuery)->where('status', 'accepted')->sum('amount_snapshot');
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();
        $rejectedTotal = (float) (clone $baseQuery)->where('status', 'rejected')->sum('amount_snapshot');
        $allCount = (clone $baseQuery)
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->count();
        $allTotal = (float) (clone $baseQuery)
            ->whereIn('status', ['sent', 'rejected', 'collected_pending_confirmation'])
            ->sum('amount_snapshot');

        $chartRows = (clone $baseQuery)
            ->get(['created_at', 'status', 'amount_snapshot']);

        $groupedByDay = $chartRows
            ->groupBy(static fn (CollectionDispatchItem $item): string => optional($item->created_at)?->format('Y-m-d') ?? '')
            ->reject(static fn ($_, string $date): bool => $date === '');

        $chartDateKeys = $groupedByDay->keys()->sort()->values();
        if ($chartDateKeys->count() > 31) {
            $chartDateKeys = $chartDateKeys->slice(-31)->values();
        }

        $chartLabels = [];
        $chartPending = [];
        $chartAccepted = [];
        $chartRejected = [];

        foreach ($chartDateKeys as $dateKey) {
            $rows = $groupedByDay->get($dateKey, collect());
            $chartLabels[] = Carbon::parse($dateKey)->format('m/d');
            $chartPending[] = (float) $rows
                ->where('status', 'sent')
                ->sum('amount_snapshot');
            $chartAccepted[] = (float) $rows
                ->where('status', 'accepted')
                ->sum('amount_snapshot');
            $chartRejected[] = (float) $rows
                ->where('status', 'rejected')
                ->sum('amount_snapshot');
        }

        $recentItemsQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static function ($query) use ($departmentCode, $request): void {
                $query->where('collector_user_id', (int) $request->user()?->id);
                if ($departmentCode) {
                    $query->where('department_code', $departmentCode);
                }
            });
        $this->applyDepartmentVisibilityFilter($recentItemsQuery, $departmentCode);

        $this->applyQueueDateFilter($recentItemsQuery, $fromDate, $toDate);

        if ($departmentCode === 'market') {
            $recentItemsQuery->with([
                'marketStallLease:id,market_stall_id,market_tenant_id,contract_number',
                'marketStallLease.stall:id,stall_no',
                'marketStallLease.tenant:id,first_name,last_name,middle_name',
                'marketPaymentCollection:id,payment_number',
            ]);
        } else {
            $recentItemsQuery->with([
                'fishportLog:id,log_number',
                'paymentRecord:id,payment_number',
            ]);
        }

        $recentTransactions = $recentItemsQuery
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (CollectionDispatchItem $item) use ($departmentCode): array {
                $statusLabel = match ((string) $item->status) {
                    'sent' => 'Pending',
                    'collected_pending_confirmation' => 'Awaiting',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                    default => ucfirst(str_replace('_', ' ', (string) $item->status)),
                };

                $statusClass = match ((string) $item->status) {
                    'accepted' => 'accepted',
                    'rejected' => 'rejected',
                    'collected_pending_confirmation' => 'awaiting',
                    default => 'pending',
                };

                if ($departmentCode === 'market') {
                    $reference = (string) (
                        $item->marketPaymentCollection?->payment_number
                        ?? $item->marketStallLease?->contract_number
                        ?? '-'
                    );
                    $source = (string) (
                        $item->marketStallLease?->stall?->stall_no
                        ?? $item->marketStallLease?->tenant?->fullName()
                        ?? '-'
                    );
                } else {
                    $reference = (string) (
                        $item->paymentRecord?->payment_number
                        ?? $item->fishportLog?->log_number
                        ?? '-'
                    );
                    $source = (string) ($item->fishportLog?->log_number ?? '-');
                }

                return [
                    'date' => optional($item->updated_at)->format('m/d/Y h:i A') ?? '-',
                    'reference' => $reference,
                    'source' => $source,
                    'status' => $statusLabel,
                    'status_class' => $statusClass,
                    'amount' => (float) $item->amount_snapshot,
                    'payer' => (string) ($item->payer_name ?: '-'),
                ];
            });

        return view('collector.dashboard', [
            'assignment' => $assignment,
            'period' => $period,
            'from' => $fromDate?->toDateString() ?? '',
            'to' => $toDate?->toDateString() ?? '',
            'rangeLabel' => $rangeLabel,
            'pendingCount' => $pendingCount,
            'pendingTotal' => $pendingTotal,
            'awaitingCount' => $awaitingCount,
            'awaitingTotal' => $awaitingTotal,
            'acceptedCount' => $acceptedCount,
            'acceptedTotal' => $acceptedTotal,
            'rejectedCount' => $rejectedCount,
            'rejectedTotal' => $rejectedTotal,
            'allCount' => $allCount,
            'allTotal' => $allTotal,
            'chartLabels' => $chartLabels,
            'chartPending' => $chartPending,
            'chartAccepted' => $chartAccepted,
            'chartRejected' => $chartRejected,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function pendingCollections(Request $request): View
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;
        if (! $departmentCode) {
            return view('collector.pending_collections', [
                'assignment' => $assignment,
                'items' => CollectionDispatchItem::query()->whereRaw('1 = 0')->paginate(12),
                'search' => trim((string) $request->query('q', '')),
            ]);
        }
        $search = trim((string) $request->query('q', ''));

        if ($departmentCode === 'market') {
            $itemsQuery = CollectionDispatchItem::query()
                ->with([
                    'dispatch:id,collector_user_id,department_code',
                    'marketStallLease:id,market_stall_id,market_tenant_id,contract_number,billing_period,billing_cycles,start_date,end_date',
                    'marketStallLease.stall:id,stall_no,market_stall_location_id,stall_status',
                    'marketStallLease.stall.location:id,location_code,location_name',
                    'marketStallLease.tenant:id,first_name,last_name,middle_name,business_name,contact_number',
                    'marketPaymentCollection:id,payment_number',
                ])
                ->whereIn('status', ['sent', 'rejected'])
                ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                    $query->where('collector_user_id', (int) $request->user()?->id);
                    if ($departmentCode) {
                        $query->where('department_code', $departmentCode);
                    }
                });

            if ($search !== '') {
                $likeSearch = '%' . $search . '%';
                $itemsQuery->where(function ($query) use ($likeSearch): void {
                    $query->where('payer_name', 'like', $likeSearch)
                        ->orWhereHas('marketStallLease', function ($leaseQuery) use ($likeSearch): void {
                            $leaseQuery->where('contract_number', 'like', $likeSearch)
                                ->orWhereHas('stall', function ($stallQuery) use ($likeSearch): void {
                                    $stallQuery->where('stall_no', 'like', $likeSearch)
                                        ->orWhereHas('location', function ($locationQuery) use ($likeSearch): void {
                                            $locationQuery->where('location_code', 'like', $likeSearch)
                                                ->orWhere('location_name', 'like', $likeSearch);
                                        });
                                })
                                ->orWhereHas('tenant', function ($tenantQuery) use ($likeSearch): void {
                                    $tenantQuery->where('first_name', 'like', $likeSearch)
                                        ->orWhere('last_name', 'like', $likeSearch)
                                        ->orWhere('business_name', 'like', $likeSearch);
                                });
                        })
                        ->orWhereHas('marketPaymentCollection', static fn ($paymentQuery) => $paymentQuery->where('payment_number', 'like', $likeSearch));
                });
            }

            $items = $itemsQuery
                ->orderByDesc('created_at')
                ->paginate(12)
                ->withQueryString();

            return view('collector.pending_collections_market', [
                'assignment' => $assignment,
                'items' => $items,
                'search' => $search,
            ]);
        }

        $itemsQuery = CollectionDispatchItem::query()
            ->with([
                'dispatch:id,collector_user_id,department_code',
                'fishportLog:id,log_number,log_date,log_time,arr_dep,fishport_vessel_id,fishport_origin_id',
                'fishportLog.vessel:id,name',
                'fishportLog.origin:id,name',
                'fishportLog.payments:id,fishport_log_id,fishport_payment_type_id,quantity,total',
                'fishportLog.payments.paymentType:id,name',
                'paymentRecord:id,fishport_log_id,payment_number',
            ])
            ->whereIn('status', ['sent', 'rejected'])
            ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                $query->where('collector_user_id', (int) $request->user()?->id);
                if ($departmentCode) {
                    $query->where('department_code', $departmentCode);
                }
            });
        $this->applyDepartmentVisibilityFilter($itemsQuery, $departmentCode);

        if ($search !== '') {
            $likeSearch = '%' . $search . '%';
            $itemsQuery->where(function ($query) use ($likeSearch): void {
                $query->where('payer_name', 'like', $likeSearch)
                    ->orWhereHas('fishportLog', static fn ($logQuery) => $logQuery->where('log_number', 'like', $likeSearch))
                    ->orWhereHas('fishportLog.vessel', static fn ($vesselQuery) => $vesselQuery->where('name', 'like', $likeSearch))
                    ->orWhereHas('fishportLog.origin', static fn ($originQuery) => $originQuery->where('name', 'like', $likeSearch))
                    ->orWhereHas('paymentRecord', static fn ($paymentRecordQuery) => $paymentRecordQuery->where('payment_number', 'like', $likeSearch));
            });
        }

        $items = $itemsQuery
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('collector.pending_collections', [
            'assignment' => $assignment,
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function collect(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;
        if (! $departmentCode) {
            return redirect()->back()->with('error', 'No collector department assignment found. Contact admin.');
        }
        $dispatchItem->loadMissing('dispatch', 'fishportLog', 'marketStallLease.stall', 'marketStallLease.tenant');

        if (! $dispatchItem->dispatch || (int) $dispatchItem->dispatch->collector_user_id !== (int) $request->user()?->id) {
            return redirect()->back()->with('error', 'You are not assigned to this collection item.');
        }

        if ($departmentCode && $dispatchItem->dispatch->department_code !== $departmentCode) {
            return redirect()->back()->with('error', 'This transaction belongs to a different department assignment.');
        }

        if (! in_array((string) $dispatchItem->status, ['sent', 'rejected'], true)) {
            return redirect()->back()->with('error', 'This transaction is not open for collection.');
        }
        if ($departmentCode === 'fishport' && (bool) ($dispatchItem->fishportLog?->is_paid ?? false)) {
            return redirect()->back()->with('error', 'This transaction is already paid in Fishport and was removed from collector queue.');
        }

        $validated = $request->validate([
            'payer_name' => ['required', 'string', 'max:150'],
            'collector_note' => ['nullable', 'string', 'max:1000'],
            'proof_image' => ['nullable', 'required_without:proof_camera', 'image', 'max:5120'],
            'proof_camera' => ['nullable', 'required_without:proof_image', 'image', 'max:5120'],
        ], [
            'proof_image.required_without' => 'Upload a proof photo or capture one using camera.',
            'proof_camera.required_without' => 'Upload a proof photo or capture one using camera.',
        ]);

        $proofFile = $request->file('proof_camera') ?: $request->file('proof_image');
        $proofPath = null;

        if ($proofFile) {
            $proofPath = $proofFile->store('collection-proofs', 'public');
            if (! $proofPath) {
                return redirect()->back()->with('error', 'Unable to upload proof image. Please try again.');
            }
        }

        DB::transaction(function () use ($request, $dispatchItem, $validated, $proofPath): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with('dispatch')
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if (! in_array((string) $item->status, ['sent', 'rejected'], true)) {
                return;
            }

            $item->update([
                'status' => 'collected_pending_confirmation',
                'payer_name' => trim((string) $validated['payer_name']),
                'collected_at' => now(),
                'collected_by_user_id' => $request->user()?->id,
                'proof_image_path' => $proofPath,
                'collector_note' => trim((string) ($validated['collector_note'] ?? '')),
                'reviewed_at' => null,
                'reviewed_by_user_id' => null,
                'review_note' => null,
            ]);

            $item->dispatch?->update([
                'status' => 'awaiting_confirmation',
            ]);

            AppNotificationService::notifyCollectorSubmitted($item, $request->user()?->id);
        });

        return redirect()
            ->back()
            ->with('status', 'Collection submitted with proof. Waiting for department confirmation.');
    }

    public function cancelAwaiting(Request $request, CollectionDispatchItem $dispatchItem): RedirectResponse
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;
        if (! $departmentCode) {
            return redirect()->back()->with('error', 'No collector department assignment found. Contact admin.');
        }

        $dispatchItem->loadMissing('dispatch');

        if (! $dispatchItem->dispatch || (int) $dispatchItem->dispatch->collector_user_id !== (int) $request->user()?->id) {
            return redirect()->back()->with('error', 'You are not assigned to this collection item.');
        }

        if ($dispatchItem->dispatch->department_code !== $departmentCode) {
            return redirect()->back()->with('error', 'This transaction belongs to a different department assignment.');
        }

        if ((string) $dispatchItem->status !== 'collected_pending_confirmation') {
            return redirect()->back()->with('error', 'Only awaiting submissions can be cancelled.');
        }

        DB::transaction(function () use ($request, $dispatchItem): void {
            /** @var CollectionDispatchItem $item */
            $item = CollectionDispatchItem::query()
                ->with('dispatch')
                ->lockForUpdate()
                ->findOrFail($dispatchItem->id);

            if ((string) $item->status !== 'collected_pending_confirmation') {
                return;
            }

            $previousProof = (string) $item->proof_image_path;

            $item->update([
                'status' => 'cancelled',
                'collected_at' => null,
                'collected_by_user_id' => null,
                'proof_image_path' => null,
                'collector_note' => null,
                'payer_name' => null,
                'reviewed_at' => null,
                'reviewed_by_user_id' => null,
                'review_note' => null,
            ]);

            if ($previousProof !== '' && Storage::disk('public')->exists($previousProof)) {
                Storage::disk('public')->delete($previousProof);
            }

            $this->refreshDispatchStatus((int) $item->collection_dispatch_id);
            AppNotificationService::notifyDispatchItemReviewed($item, 'cancelled', $request->user()?->id);
        });

        return redirect()
            ->route('collector.payments')
            ->with('status', 'Awaiting submission cancelled. The record was returned to Market Send for Payment queue.');
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

        $pendingCount = (int) ($statusCounts['sent'] ?? 0) + (int) ($statusCounts['rejected'] ?? 0);
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

    public function payments(Request $request): View
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;
        if (! $departmentCode) {
            return view('collector.payments', [
                'assignment' => $assignment,
                'items' => CollectionDispatchItem::query()->whereRaw('1 = 0')->paginate(12),
                'statusFilter' => 'all',
                'counts' => [
                    'all' => 0,
                    'awaiting' => 0,
                    'accepted' => 0,
                    'rejected' => 0,
                ],
            ]);
        }
        $statusFilter = (string) $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'awaiting', 'accepted', 'rejected'], true)) {
            $statusFilter = 'all';
        }

        if ($departmentCode === 'market') {
            $itemsQuery = CollectionDispatchItem::query()
                ->with([
                    'dispatch:id,collector_user_id,department_code',
                    'marketStallLease:id,market_stall_id,market_tenant_id,contract_number,billing_period,billing_cycles,start_date,end_date',
                    'marketStallLease.stall:id,stall_no,market_stall_location_id,stall_status',
                    'marketStallLease.stall.location:id,location_code,location_name',
                    'marketStallLease.tenant:id,first_name,last_name,middle_name,business_name,contact_number',
                    'marketPaymentCollection:id,payment_number',
                ])
                ->whereIn('status', ['collected_pending_confirmation', 'accepted', 'rejected'])
                ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                    $query->where('collector_user_id', (int) $request->user()?->id);
                    if ($departmentCode) {
                        $query->where('department_code', $departmentCode);
                    }
                });

            if ($statusFilter === 'awaiting') {
                $itemsQuery->where('status', 'collected_pending_confirmation');
            } elseif ($statusFilter === 'accepted') {
                $itemsQuery->where('status', 'accepted');
            } elseif ($statusFilter === 'rejected') {
                $itemsQuery->where('status', 'rejected');
            }

            $items = $itemsQuery
                ->orderByDesc('updated_at')
                ->paginate(12)
                ->withQueryString();

            $baseCountQuery = CollectionDispatchItem::query()
                ->whereIn('status', ['collected_pending_confirmation', 'accepted', 'rejected'])
                ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                    $query->where('collector_user_id', (int) $request->user()?->id);
                    if ($departmentCode) {
                        $query->where('department_code', $departmentCode);
                    }
                });

            $counts = [
                'all' => (clone $baseCountQuery)->count(),
                'awaiting' => (clone $baseCountQuery)->where('status', 'collected_pending_confirmation')->count(),
                'accepted' => (clone $baseCountQuery)->where('status', 'accepted')->count(),
                'rejected' => (clone $baseCountQuery)->where('status', 'rejected')->count(),
            ];

            return view('collector.payments_market', [
                'assignment' => $assignment,
                'items' => $items,
                'statusFilter' => $statusFilter,
                'counts' => $counts,
            ]);
        }

        $itemsQuery = CollectionDispatchItem::query()
            ->with([
                'dispatch:id,collector_user_id,department_code',
                'fishportLog:id,log_number,log_date,log_time,fishport_vessel_id,fishport_origin_id',
                'fishportLog.vessel:id,name',
                'fishportLog.origin:id,name',
                'paymentRecord:id,fishport_log_id,payment_number',
            ])
            ->whereIn('status', ['collected_pending_confirmation', 'accepted', 'rejected'])
            ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                $query->where('collector_user_id', (int) $request->user()?->id);
                if ($departmentCode) {
                    $query->where('department_code', $departmentCode);
                }
            });

        if ($statusFilter === 'awaiting') {
            $itemsQuery->where('status', 'collected_pending_confirmation');
        } elseif ($statusFilter === 'accepted') {
            $itemsQuery->where('status', 'accepted');
        } elseif ($statusFilter === 'rejected') {
            $itemsQuery->where('status', 'rejected');
        }

        $items = $itemsQuery
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $baseCountQuery = CollectionDispatchItem::query()
            ->whereIn('status', ['collected_pending_confirmation', 'accepted', 'rejected'])
            ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                $query->where('collector_user_id', (int) $request->user()?->id);
                if ($departmentCode) {
                    $query->where('department_code', $departmentCode);
                }
            });

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'awaiting' => (clone $baseCountQuery)->where('status', 'collected_pending_confirmation')->count(),
            'accepted' => (clone $baseCountQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $baseCountQuery)->where('status', 'rejected')->count(),
        ];

        return view('collector.payments', [
            'assignment' => $assignment,
            'items' => $items,
            'statusFilter' => $statusFilter,
            'counts' => $counts,
        ]);
    }

    public function proofImage(Request $request, CollectionDispatchItem $dispatchItem): BinaryFileResponse
    {
        $dispatchItem->loadMissing('dispatch');
        $user = $request->user();

        if (! $dispatchItem->dispatch || ! $user) {
            abort(404);
        }

        $roleKey = $user->uiRoleKey();
        $canView = false;

        if ($roleKey === 'administrator') {
            $canView = true;
        } elseif ($roleKey === 'collector') {
            $assignment = $this->collectorAssignment($request);
            $departmentCode = $assignment?->department?->code;
            $canView = $departmentCode !== null
                && (int) $dispatchItem->dispatch->collector_user_id === (int) $user->id
                && (string) $dispatchItem->dispatch->department_code === (string) $departmentCode;
        } elseif ($roleKey === 'fishport') {
            $canView = (string) $dispatchItem->dispatch->department_code === 'fishport';
        } elseif ($roleKey === 'market') {
            $canView = (string) $dispatchItem->dispatch->department_code === 'market';
        }

        if (! $canView) {
            abort(403);
        }

        $proofPath = trim((string) $dispatchItem->proof_image_path);
        if ($proofPath === '' || ! Storage::disk('public')->exists($proofPath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($proofPath);
        $mimeType = Storage::disk('public')->mimeType($proofPath) ?: 'image/jpeg';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function applyQueueDateFilter($query, ?Carbon $fromDate, ?Carbon $toDate): void
    {
        if (! $fromDate || ! $toDate) {
            return;
        }

        $query
            ->where('created_at', '>=', $fromDate->copy()->startOfDay()->toDateTimeString())
            ->where('created_at', '<=', $toDate->copy()->endOfDay()->toDateTimeString());
    }

    private function applyDepartmentVisibilityFilter($query, ?string $departmentCode): void
    {
        if ($departmentCode !== 'fishport') {
            return;
        }

        $query->whereHas('fishportLog', static function ($logQuery): void {
            $logQuery->where('is_paid', false);
        });
    }

    /**
     * @return array{0:Carbon|null,1:Carbon|null,2:string}
     */
    private function resolveDashboardDateRange(string $period, string $fromInput, string $toInput): array
    {
        $today = Carbon::today();

        return match ($period) {
            'today' => [$today->copy(), $today->copy(), 'Today'],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), 'This Week'],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'This Month'],
            'custom' => (function () use ($fromInput, $toInput): array {
                $hasFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromInput) === 1;
                $hasTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', $toInput) === 1;

                if (! $hasFrom && ! $hasTo) {
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                    return [$start, $end, 'This Month'];
                }

                $normalizedFrom = $hasFrom ? $fromInput : $toInput;
                $normalizedTo = $hasTo ? $toInput : $fromInput;

                $start = Carbon::parse($normalizedFrom)->startOfDay();
                $end = Carbon::parse($normalizedTo)->endOfDay();
                if ($end->lt($start)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }

                $label = $start->isSameDay($end) ? 'Custom Date' : 'Custom Range';
                return [$start, $end, $label];
            })(),
            default => [null, null, 'All Dates'],
        };
    }

    private function collectorAssignment(Request $request): ?CollectorDepartmentAssignment
    {
        return CollectorDepartmentAssignment::query()
            ->with('department:id,code,name')
            ->where('collector_user_id', (int) $request->user()?->id)
            ->first();
    }
}
