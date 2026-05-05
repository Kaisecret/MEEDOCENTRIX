<?php

namespace App\Support;

use App\Models\AppNotification;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\MarketDueLog;
use App\Models\MarketStallLease;
use App\Models\User;
use Illuminate\Support\Collection;

class AppNotificationService
{
    public static function notifyRateAndFeeUpdated(
        string $changeSummary,
        ?int $createdByUserId = null,
        ?string $actorName = null
    ): void {
        $sender = trim((string) $actorName) !== '' ? trim((string) $actorName) : 'An administrator';
        $message = $sender . ' updated rate and fee settings. ' . trim($changeSummary);

        self::createForUsers(
            self::activeUsers()->pluck('id'),
            [
                'type' => 'info',
                'title' => 'Rates & Fees Updated',
                'message' => $message,
                'action_url' => route('notifications'),
                'created_by_user_id' => $createdByUserId,
            ]
        );
    }

    public static function notifyDispatchSent(
        string $departmentCode,
        int $dispatchId,
        int $collectorUserId,
        int $itemCount,
        ?string $actorName = null,
        ?int $createdByUserId = null
    ): void {
        $departmentLabel = self::departmentLabel($departmentCode);
        $sender = $actorName ?: 'A personnel';

        self::createForUsers(
            collect([$collectorUserId]),
            [
                'type' => 'info',
                'title' => $departmentLabel . ' Queue Sent',
                'message' => $sender . ' sent ' . $itemCount . ' item(s) to your collector queue.',
                'action_url' => route('collector.pending_collections'),
                'event_key' => 'dispatch_sent_collector_' . $dispatchId,
                'created_by_user_id' => $createdByUserId,
            ]
        );

        $audience = self::departmentUserIds($departmentCode)
            ->merge(self::adminUserIds())
            ->unique()
            ->filter(static fn ($id): bool => (int) $id !== $collectorUserId);

        self::createForUsers(
            $audience,
            [
                'type' => 'info',
                'title' => $departmentLabel . ' Sent for Collection',
                'message' => $itemCount . ' item(s) were sent to collector queue by ' . $sender . '.',
                'action_url' => route('notifications'),
                'event_key' => 'dispatch_sent_department_' . $dispatchId,
                'created_by_user_id' => $createdByUserId,
            ]
        );
    }

    public static function notifyCollectorSubmitted(CollectionDispatchItem $item, ?int $createdByUserId = null): void
    {
        $item->loadMissing('dispatch.collector');
        $departmentCode = (string) ($item->dispatch?->department_code ?? '');
        if ($departmentCode === '') {
            return;
        }

        $departmentLabel = self::departmentLabel($departmentCode);
        $amount = number_format((float) ($item->amount_snapshot ?? 0), 2);
        $collectorName = (string) ($item->dispatch?->collector?->name ?: 'Collector');

        $audience = self::departmentUserIds($departmentCode)
            ->merge(self::adminUserIds())
            ->unique();

        self::createForUsers(
            $audience,
            [
                'type' => 'warning',
                'title' => $departmentLabel . ' Collection Awaiting Approval',
                'message' => $collectorName . ' submitted payment proof (PHP ' . $amount . '). Please review.',
                'action_url' => route('notifications'),
                'event_key' => 'dispatch_item_awaiting_' . (int) $item->id,
                'created_by_user_id' => $createdByUserId,
            ]
        );
    }

    public static function notifyDispatchItemReviewed(CollectionDispatchItem $item, string $result, ?int $createdByUserId = null): void
    {
        $item->loadMissing('dispatch.collector');
        $departmentCode = (string) ($item->dispatch?->department_code ?? '');
        if ($departmentCode === '') {
            return;
        }

        $normalizedResult = $result === 'accepted' ? 'accepted' : ($result === 'rejected' ? 'rejected' : 'cancelled');
        $departmentLabel = self::departmentLabel($departmentCode);
        $amount = number_format((float) ($item->amount_snapshot ?? 0), 2);
        $title = match ($normalizedResult) {
            'accepted' => $departmentLabel . ' Collection Approved',
            'rejected' => $departmentLabel . ' Collection Rejected',
            default => $departmentLabel . ' Queue Item Cancelled',
        };

        $message = match ($normalizedResult) {
            'accepted' => 'Payment proof was approved (PHP ' . $amount . ').',
            'rejected' => 'Payment proof was rejected and returned to queue for correction.',
            default => 'Queue item was cancelled/returned and needs re-processing.',
        };

        $collectorId = (int) ($item->dispatch?->collector_user_id ?? 0);
        if ($collectorId > 0) {
            self::createForUsers(
                collect([$collectorId]),
                [
                    'type' => $normalizedResult === 'accepted' ? 'success' : 'warning',
                    'title' => $title,
                    'message' => $message,
                    'action_url' => route('collector.payments'),
                    'event_key' => 'dispatch_item_' . $normalizedResult . '_collector_' . (int) $item->id,
                    'created_by_user_id' => $createdByUserId,
                ]
            );
        }

        $audience = self::departmentUserIds($departmentCode)
            ->merge(self::adminUserIds())
            ->unique();

        self::createForUsers(
            $audience,
            [
                'type' => $normalizedResult === 'accepted' ? 'success' : 'warning',
                'title' => $title,
                'message' => $message,
                'action_url' => route('notifications'),
                'event_key' => 'dispatch_item_' . $normalizedResult . '_department_' . (int) $item->id,
                'created_by_user_id' => $createdByUserId,
            ]
        );
    }

    public static function notifyMarketDueMissed(MarketDueLog $log, ?MarketStallLease $lease = null): void
    {
        $lease ??= $log->relationLoaded('lease') ? $log->lease : null;
        if (! $lease) {
            return;
        }

        $lease->loadMissing('stall:id,stall_no', 'tenant:id,first_name,last_name,middle_name,business_name');

        $stallNo = (string) ($lease->stall?->stall_no ?? 'Unknown Stall');
        $tenantName = trim((string) ($lease->tenant?->fullName() ?: $lease->tenant?->business_name ?: 'Tenant'));
        $dueDate = optional($log->due_date)->format('M d, Y') ?? 'Today';

        $audience = self::departmentUserIds('market')
            ->merge(self::adminUserIds())
            ->merge(self::collectorUserIdsForDepartment('market'))
            ->unique();

        self::createForUsers(
            $audience,
            [
                'type' => 'danger',
                'title' => 'Missed Market Payment Due',
                'message' => $tenantName . ' (' . $stallNo . ') was missed on ' . $dueDate . '.',
                'action_url' => route('notifications'),
                'event_key' => 'market_due_missed_' . (int) $log->market_stall_lease_id . '_' . $log->due_date?->toDateString(),
                'payload' => [
                    'lease_id' => (int) $log->market_stall_lease_id,
                    'due_date' => $log->due_date?->toDateString(),
                ],
            ]
        );
    }

    public static function feedForUser(User $user, int $limit = 8): array
    {
        $notifications = AppNotification::query()
            ->where('user_id', (int) $user->id)
            ->latest('created_at')
            ->limit(max(1, min($limit, 30)))
            ->get();

        $unreadCount = AppNotification::query()
            ->where('user_id', (int) $user->id)
            ->where('is_read', false)
            ->count();

        return [
            'unread_count' => (int) $unreadCount,
            'items' => $notifications,
        ];
    }

    public static function markAllAsRead(User $user): int
    {
        return AppNotification::query()
            ->where('user_id', (int) $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    private static function createForUsers(Collection $userIds, array $payload): void
    {
        $ids = $userIds
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn ($id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $eventKey = isset($payload['event_key']) ? (string) $payload['event_key'] : null;

        foreach ($ids as $userId) {
            if ($eventKey !== null && $eventKey !== '') {
                AppNotification::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'event_key' => $eventKey,
                    ],
                    [
                        'type' => (string) ($payload['type'] ?? 'info'),
                        'title' => (string) ($payload['title'] ?? 'System Notification'),
                        'message' => (string) ($payload['message'] ?? ''),
                        'action_url' => $payload['action_url'] ?? null,
                        'payload' => $payload['payload'] ?? null,
                        'created_by_user_id' => $payload['created_by_user_id'] ?? null,
                        'is_read' => false,
                        'read_at' => null,
                    ]
                );
                continue;
            }

            AppNotification::query()->create([
                'user_id' => $userId,
                'type' => (string) ($payload['type'] ?? 'info'),
                'title' => (string) ($payload['title'] ?? 'System Notification'),
                'message' => (string) ($payload['message'] ?? ''),
                'action_url' => $payload['action_url'] ?? null,
                'event_key' => null,
                'payload' => $payload['payload'] ?? null,
                'created_by_user_id' => $payload['created_by_user_id'] ?? null,
                'is_read' => false,
                'read_at' => null,
            ]);
        }
    }

    private static function departmentUserIds(string $departmentCode): Collection
    {
        $departmentCode = strtolower(trim($departmentCode));

        return self::activeUsers()
            ->filter(static fn (User $user): bool => strtolower($user->uiRoleKey()) === $departmentCode)
            ->pluck('id');
    }

    private static function adminUserIds(): Collection
    {
        return self::activeUsers()
            ->filter(static fn (User $user): bool => strtolower($user->uiRoleKey()) === 'administrator')
            ->pluck('id');
    }

    private static function collectorUserIdsForDepartment(string $departmentCode): Collection
    {
        $departmentCode = strtolower(trim($departmentCode));

        return CollectorDepartmentAssignment::query()
            ->whereHas('department', static fn ($query) => $query->whereRaw('LOWER(code) = ?', [$departmentCode]))
            ->pluck('collector_user_id')
            ->map(static fn ($id): int => (int) $id)
            ->values();
    }

    private static function activeUsers(): Collection
    {
        static $users = null;

        if ($users instanceof Collection) {
            return $users;
        }

        $users = User::query()
            ->where(static function ($query): void {
                $query->whereNull('is_active')
                    ->orWhere('is_active', true);
            })
            ->get(['id', 'name', 'role', 'department', 'is_active']);

        return $users;
    }

    private static function departmentLabel(string $departmentCode): string
    {
        return match (strtolower(trim($departmentCode))) {
            'market' => 'Public Market',
            'fishport' => 'Fishport',
            'cemetery' => 'Cemetery',
            'terminal' => 'Terminal',
            'atrium' => 'Atrium Hall',
            default => ucfirst(strtolower(trim($departmentCode))),
        };
    }
}
