<?php

namespace App\Support;

use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use Illuminate\Support\Carbon;

class MarketQueueLifecycle
{
    public const SENT_TIMEOUT_HOURS = 24;

    public static function autoCancelStaleSentItems(?Carbon $now = null, int $timeoutHours = self::SENT_TIMEOUT_HOURS): int
    {
        $now ??= now();
        $timeout = max(1, $timeoutHours);
        $cutoff = $now->copy()->subHours($timeout);

        $staleItems = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'marketStallLease.stall:id,stall_no',
                'marketStallLease.tenant:id,first_name,last_name,middle_name,business_name',
            ])
            ->where('status', 'sent')
            ->whereNotNull('market_stall_lease_id')
            ->where('created_at', '<=', $cutoff)
            ->whereHas('dispatch', static function ($dispatchQuery): void {
                $dispatchQuery->where('department_code', 'market');
            })
            ->get(['id', 'collection_dispatch_id']);

        if ($staleItems->isEmpty()) {
            return 0;
        }

        $itemIds = $staleItems->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        $dispatchIds = $staleItems->pluck('collection_dispatch_id')
            ->filter(static fn ($id): bool => $id !== null)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        CollectionDispatchItem::query()
            ->whereIn('id', $itemIds)
            ->update([
                'status' => 'cancelled',
                'reviewed_at' => $now,
                'reviewed_by_user_id' => null,
                'review_note' => 'Auto-cancelled: not collected within 24 hours.',
                'updated_at' => $now,
            ]);

        foreach ($dispatchIds as $dispatchId) {
            self::refreshDispatchStatus($dispatchId, $now);
        }

        foreach ($staleItems as $staleItem) {
            AppNotificationService::notifyDispatchItemReviewed($staleItem, 'cancelled');
        }

        return count($itemIds);
    }

    public static function refreshDispatchStatus(int $dispatchId, ?Carbon $now = null): void
    {
        $dispatch = CollectionDispatch::query()->find($dispatchId);
        if (! $dispatch) {
            return;
        }

        $now ??= now();

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
                'completed_at' => $now,
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
