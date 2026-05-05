<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Support\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! Schema::hasTable('app_notifications')) {
            return view('shared.notifications', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        $notifications = AppNotification::query()
            ->where('user_id', (int) $user->id)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = AppNotification::query()
            ->where('user_id', (int) $user->id)
            ->where('is_read', false)
            ->count();

        return view('shared.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! Schema::hasTable('app_notifications')) {
            return response()->json([
                'unread_count' => 0,
                'items' => [],
                'mark_all_url' => route('notifications.read_all'),
                'view_all_url' => route('notifications'),
            ]);
        }
        $data = AppNotificationService::feedForUser($user, 10);

        $items = collect($data['items'])->map(static function (AppNotification $notification): array {
            return [
                'id' => (int) $notification->id,
                'title' => (string) $notification->title,
                'message' => (string) $notification->message,
                'type' => (string) $notification->type,
                'action_url' => $notification->action_url,
                'is_read' => (bool) $notification->is_read,
                'created_at_human' => optional($notification->created_at)->diffForHumans(),
                'mark_read_url' => route('notifications.mark_read', ['notification' => $notification->id]),
            ];
        })->values()->all();

        return response()->json([
            'unread_count' => (int) ($data['unread_count'] ?? 0),
            'items' => $items,
            'mark_all_url' => route('notifications.read_all'),
            'view_all_url' => route('notifications'),
        ]);
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            if ($request->expectsJson()) {
                return response()->json(['updated' => 0]);
            }

            return redirect()->route('notifications');
        }

        $updated = AppNotificationService::markAllAsRead($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'updated' => (int) $updated,
            ]);
        }

        return redirect()->route('notifications')->with('status', 'All notifications marked as read.');
    }

    public function readAllLink(Request $request): RedirectResponse
    {
        if (Schema::hasTable('app_notifications')) {
            AppNotificationService::markAllAsRead($request->user());
        }

        return redirect()->route('notifications')->with('status', 'All notifications marked as read.');
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse|RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false], 404);
            }

            return redirect()->route('notifications');
        }

        if ((int) $notification->user_id !== (int) $request->user()?->id) {
            abort(403);
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
            ]);
        }

        $redirectTo = $notification->action_url ?: route('notifications');

        return redirect($redirectTo);
    }

    public function markReadLink(Request $request, AppNotification $notification): RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            return redirect()->route('notifications');
        }

        if ((int) $notification->user_id !== (int) $request->user()?->id) {
            abort(403);
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $redirectTo = $notification->action_url ?: route('notifications');

        return redirect($redirectTo);
    }
}
