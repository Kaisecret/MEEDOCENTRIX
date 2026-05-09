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
        $markAllUrl = $this->scopedRoute($request, 'notifications.read_all');
        $viewAllUrl = $this->scopedRoute($request, 'notifications');

        if (! Schema::hasTable('app_notifications')) {
            return response()->json([
                'unread_count' => 0,
                'items' => [],
                'mark_all_url' => $markAllUrl,
                'view_all_url' => $viewAllUrl,
            ]);
        }
        $data = AppNotificationService::feedForUser($user, 10);

        $items = collect($data['items'])->map(function (AppNotification $notification) use ($request): array {
            return [
                'id' => (int) $notification->id,
                'title' => (string) $notification->title,
                'message' => (string) $notification->message,
                'type' => (string) $notification->type,
                'action_url' => $this->scopedUrl($request, (string) ($notification->action_url ?? '')),
                'is_read' => (bool) $notification->is_read,
                'created_at_human' => optional($notification->created_at)->diffForHumans(),
                'mark_read_url' => $this->scopedRoute($request, 'notifications.mark_read', ['notification' => $notification->id]),
            ];
        })->values()->all();

        return response()->json([
            'unread_count' => (int) ($data['unread_count'] ?? 0),
            'items' => $items,
            'mark_all_url' => $markAllUrl,
            'view_all_url' => $viewAllUrl,
        ]);
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            if ($request->expectsJson()) {
                return response()->json(['updated' => 0]);
            }

            return redirect($this->scopedRoute($request, 'notifications'));
        }

        $updated = AppNotificationService::markAllAsRead($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'updated' => (int) $updated,
            ]);
        }

        return redirect($this->scopedRoute($request, 'notifications'))->with('status', 'All notifications marked as read.');
    }

    public function readAllLink(Request $request): RedirectResponse
    {
        if (Schema::hasTable('app_notifications')) {
            AppNotificationService::markAllAsRead($request->user());
        }

        return redirect($this->scopedRoute($request, 'notifications'))->with('status', 'All notifications marked as read.');
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse|RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false], 404);
            }

            return redirect($this->scopedRoute($request, 'notifications'));
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

        $redirectTo = $this->scopedUrl($request, (string) ($notification->action_url ?: $this->scopedRoute($request, 'notifications')));

        return redirect($redirectTo);
    }

    public function markReadLink(Request $request, AppNotification $notification): RedirectResponse
    {
        if (! Schema::hasTable('app_notifications')) {
            return redirect($this->scopedRoute($request, 'notifications'));
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

        $redirectTo = $this->scopedUrl($request, (string) ($notification->action_url ?: $this->scopedRoute($request, 'notifications')));

        return redirect($redirectTo);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function scopedRoute(Request $request, string $routeName, array $params = []): string
    {
        return route($routeName, array_merge($params, $this->scopeQuery($request)));
    }

    /**
     * @return array{session_scope:string,s?:string}
     */
    private function scopeQuery(Request $request): array
    {
        $roleKey = (string) ($request->user()?->uiRoleKey() ?? 'shared');
        $sessionScope = $roleKey === 'administrator' ? 'admin' : $roleKey;

        $query = ['session_scope' => $sessionScope];
        if ($sessionScope === 'collector') {
            $token = preg_replace('/[^a-zA-Z0-9]/', '', (string) $request->query('s', ''));
            if (is_string($token) && $token !== '') {
                $query['s'] = substr($token, 0, 16);
            }
        }

        return $query;
    }

    private function scopedUrl(Request $request, string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $this->scopedRoute($request, 'notifications');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $isRelative = ! isset($parts['host']) && ! isset($parts['scheme']);
        $isSameHost = isset($parts['host']) && strcasecmp((string) $parts['host'], (string) $request->getHost()) === 0;
        if (! $isRelative && ! $isSameHost) {
            return $url;
        }

        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $query = array_merge($query, $this->scopeQuery($request));
        $queryString = http_build_query($query);

        $path = (string) ($parts['path'] ?? '/');
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

        if ($isRelative) {
            return $path . ($queryString !== '' ? ('?' . $queryString) : '') . $fragment;
        }

        $scheme = (string) ($parts['scheme'] ?? $request->getScheme());
        $host = (string) $parts['host'];
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $scheme . '://' . $host . $port . $path . ($queryString !== '' ? ('?' . $queryString) : '') . $fragment;
    }
}
