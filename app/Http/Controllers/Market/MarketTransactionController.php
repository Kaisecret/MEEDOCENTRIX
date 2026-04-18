<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatchItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $validStatuses = ['all', 'sent', 'collected_pending_confirmation', 'accepted', 'rejected', 'cancelled'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $itemsQuery = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'marketStallLease.stall.location',
                'marketStallLease.tenant',
                'marketPaymentCollection',
            ])
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'));

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
            ->paginate(15)
            ->withQueryString();

        $baseSummaryQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static fn ($dispatchQuery) => $dispatchQuery->where('department_code', 'market'));

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
        ]);
    }
}

