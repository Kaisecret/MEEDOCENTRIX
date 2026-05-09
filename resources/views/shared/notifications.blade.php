@extends('layouts.app')
@section('content')
@php
    $roleKey = auth()->user()?->uiRoleKey() ?? 'shared';
    $sessionScope = $roleKey === 'administrator' ? 'admin' : $roleKey;
    $scopeParams = ['session_scope' => $sessionScope];
    if ($sessionScope === 'collector') {
        $collectorToken = preg_replace('/[^a-zA-Z0-9]/', '', (string) request('s', ''));
        if (is_string($collectorToken) && $collectorToken !== '') {
            $scopeParams['s'] = substr($collectorToken, 0, 16);
        }
    }
@endphp
<div
    data-server-rendered-page="notifications"
    data-page-title="All Notifications"
    class="content-card"
    style="padding: 16px; display: flex; flex-direction: column; gap: 10px;"
>
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px; border: 1px solid var(--gray-200); border-radius: 10px; background: #fff;">
        <div>
            <h2 style="margin: 0; font-size: 1.25rem;">Notifications</h2>
            <p class="text-muted" style="margin: 4px 0 0 0;">Unread: {{ $unreadCount }}</p>
        </div>
        <a href="{{ route('notifications.read_all_link', $scopeParams) }}" class="btn btn-outline">Mark All as Read</a>
    </div>

    <div class="card" style="margin: 0; border-radius: 10px;">
        @if(method_exists($notifications, 'count') && $notifications->count() === 0)
            <div style="padding: 24px; text-align: center; color: var(--gray-500);">
                <i class="fas fa-bell-slash" style="font-size: 28px; margin-bottom: 10px;"></i>
                <div style="font-weight: 700; color: var(--gray-700);">No notifications yet</div>
                <div style="margin-top: 4px;">New system activity will appear here.</div>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px; padding: 10px;">
                @foreach($notifications as $notification)
                    <div style="border: 1px solid var(--gray-200); border-radius: 10px; padding: 10px; background: {{ $notification->is_read ? '#fff' : '#f5f9ff' }};">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                            <div>
                                <div style="font-weight: 700; color: var(--gray-900); margin-bottom: 2px;">{{ $notification->title }}</div>
                                <div class="text-muted" style="line-height: 1.4;">{{ $notification->message }}</div>
                                <div style="margin-top: 6px; font-size: 12px; color: var(--gray-500);">
                                    {{ optional($notification->created_at)->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="status-badge {{ $notification->is_read ? 'inactive' : 'active' }}">
                                    {{ $notification->is_read ? 'Read' : 'Unread' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(method_exists($notifications, 'hasPages') && $notifications->hasPages())
        <div class="pagination-wrap">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
