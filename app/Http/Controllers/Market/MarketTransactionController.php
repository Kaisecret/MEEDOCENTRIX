<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatchItem;
use App\Support\MarketDueLogService;
use App\Support\MarketQueueLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MarketTransactionController extends Controller
{
    public function index(Request $request): View
    {
        MarketQueueLifecycle::autoCancelStaleSentItems();
        MarketDueLogService::sync();

        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $validStatuses = ['all', 'sent', 'collected_pending_confirmation', 'accepted', 'rejected', 'cancelled'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $range = trim((string) $request->query('range', 'all'));
        $validRanges = ['all', 'today', 'week', 'month', 'custom'];
        if (! in_array($range, $validRanges, true)) {
            $range = 'all';
        }

        $fromInput = trim((string) $request->query('from', ''));
        $toInput = trim((string) $request->query('to', ''));

        [$rangeStart, $rangeEnd, $rangeLabel] = $this->resolveRange($range, $fromInput, $toInput);

        $itemsQuery = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'marketStallLease.stall.location',
                'marketStallLease.tenant',
                'marketPaymentCollection',
            ])
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'));

        if ($rangeStart && $rangeEnd) {
            $itemsQuery->whereBetween('updated_at', [$rangeStart, $rangeEnd]);
        }

        if ($status !== 'all') {
            $itemsQuery->where('status', $status);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $itemsQuery->where(function ($query) use ($like): void {
                $query->where('payer_name', 'like', $like)
                    ->orWhere('review_note', 'like', $like)
                    ->orWhereHas('marketStallLease', function ($leaseQuery) use ($like): void {
                        $leaseQuery->where('contract_number', 'like', $like)
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
                                    ->orWhere('business_name', 'like', $like);
                            });
                    })
                    ->orWhereHas('marketPaymentCollection', function ($paymentQuery) use ($like): void {
                        $paymentQuery->where('payment_number', 'like', $like);
                    });
            });
        }

        $items = $itemsQuery
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $baseSummaryQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'));

        if ($rangeStart && $rangeEnd) {
            $baseSummaryQuery->whereBetween('updated_at', [$rangeStart, $rangeEnd]);
        }

        $summary = [
            'all_count' => (int) (clone $baseSummaryQuery)->count(),
            'all_amount' => (float) (clone $baseSummaryQuery)->sum('amount_snapshot'),
            'accepted_count' => (int) (clone $baseSummaryQuery)->where('status', 'accepted')->count(),
            'accepted_amount' => (float) (clone $baseSummaryQuery)->where('status', 'accepted')->sum('amount_snapshot'),
            'awaiting_count' => (int) (clone $baseSummaryQuery)->where('status', 'collected_pending_confirmation')->count(),
            'awaiting_amount' => (float) (clone $baseSummaryQuery)->where('status', 'collected_pending_confirmation')->sum('amount_snapshot'),
            'pending_count' => (int) (clone $baseSummaryQuery)->whereIn('status', ['sent', 'rejected'])->count(),
            'pending_amount' => (float) (clone $baseSummaryQuery)->whereIn('status', ['sent', 'rejected'])->sum('amount_snapshot'),
        ];

        return view('market.transactions', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'summary' => $summary,
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'from' => $fromInput,
            'to' => $toInput,
        ]);
    }

    /**
     * @return array{0: ?\Illuminate\Support\Carbon, 1: ?\Illuminate\Support\Carbon, 2: string}
     */
    private function resolveRange(string $range, string $fromInput, string $toInput): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today (' . $now->format('M d, Y') . ')'];
            case 'week':
                $start = $now->copy()->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                return [$start, $end, 'This Week (' . $start->format('M d') . ' – ' . $end->format('M d, Y') . ')'];
            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                return [$start, $end, 'This Month (' . $now->format('F Y') . ')'];
            case 'custom':
                $start = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : null;
                $end = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : null;
                if ($start && $end && $start->greaterThan($end)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
                $label = 'Custom';
                if ($start && $end) {
                    $label = $start->format('M d, Y') . ' – ' . $end->format('M d, Y');
                } elseif ($start) {
                    $label = 'From ' . $start->format('M d, Y');
                } elseif ($end) {
                    $label = 'Until ' . $end->format('M d, Y');
                }
                return [$start, $end, $label];
            default:
                return [null, null, 'All Dates'];
        }
    }
}
