<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meedocentrix</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
@php
    $authUser = auth()->user();
    $authRoleKey = $authUser?->uiRoleKey() ?? 'administrator';
    $authRoleLabel = $authUser?->roleLabel() ?? 'Administrator';
    $topbarName = $authUser ? explode(' ', trim($authUser->name))[0] : 'User';
    $userAvatar = $authUser?->avatar ?? 'boy';
    $topbarNotifications = collect();
    $topbarUnreadCount = 0;

    if ($authUser && \Illuminate\Support\Facades\Schema::hasTable('app_notifications')) {
        $topbarNotifications = \App\Models\AppNotification::query()
            ->where('user_id', (int) $authUser->id)
            ->latest('created_at')
            ->limit(8)
            ->get();

        $topbarUnreadCount = \App\Models\AppNotification::query()
            ->where('user_id', (int) $authUser->id)
            ->where('is_read', false)
            ->count();
    }

    $roleNavMap = [
        'administrator' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'users', 'icon' => 'fas fa-users-gear', 'label' => 'User Management'],
            ['id' => 'roles', 'icon' => 'fas fa-shield-halved', 'label' => 'Roles & Permissions'],
            ['id' => 'rates', 'icon' => 'fas fa-tags', 'label' => 'Rates & Fees'],
            ['id' => 'transactions', 'icon' => 'fas fa-money-check-dollar', 'label' => 'All Transactions'],
            ['id' => 'reports', 'icon' => 'fas fa-file-invoice', 'label' => 'Reports & Analytics'],
        ],
        'fishport' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'vessels', 'icon' => 'fas fa-ship', 'label' => 'Vessel Logs'],
            ['id' => 'vessel_registry', 'icon' => 'fas fa-clipboard-list', 'label' => 'Vessel Registry'],
            ['id' => 'fishport_records', 'icon' => 'fas fa-fish', 'label' => 'Fishport Transactions'],
            ['id' => 'send_payment', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Send for Payment'],
            ['id' => 'reports', 'icon' => 'fas fa-file-lines', 'label' => 'Reports'],
        ],
        'market' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'vendors', 'icon' => 'fas fa-users', 'label' => 'Tenant Directory'],
            ['id' => 'stalls', 'icon' => 'fas fa-store', 'label' => 'Stall Management'],
            ['id' => 'market_records', 'icon' => 'fas fa-receipt', 'label' => 'Market Transactions'],
            ['id' => 'send_payment', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Send for Payment'],
            ['id' => 'market_reports', 'icon' => 'fas fa-file-lines', 'label' => 'Reports'],
        ],
        'atrium' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'atrium_bookings', 'icon' => 'fas fa-calendar-check', 'label' => 'Bookings'],
            ['id' => 'atrium_payments', 'icon' => 'fas fa-money-check-dollar', 'label' => 'Payments'],
            ['id' => 'atrium_reports', 'icon' => 'fas fa-chart-pie', 'label' => 'Reports'],
        ],
        'collector' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'pending_collections', 'icon' => 'fas fa-clock', 'label' => 'Pending Collections'],
            ['id' => 'collector_payments', 'icon' => 'fas fa-hand-holding-dollar', 'label' => 'Received Payments'],
            ['id' => 'collector_reports', 'icon' => 'fas fa-file-lines', 'label' => 'Reports'],
        ],
        'cashier' => [
            ['id' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard'],
            ['id' => 'cashier_remittance', 'icon' => 'fas fa-inbox', 'label' => 'Verify Remittances'],
            ['id' => 'official_collections', 'icon' => 'fas fa-vault', 'label' => 'Official Collections'],
            ['id' => 'daily_summary', 'icon' => 'fas fa-file-contract', 'label' => 'Daily Summary'],
        ],
    ];

    $initialNavItems = $roleNavMap[$authRoleKey] ?? [];
@endphp

<div
    id="appContainer"
    class="app-container"
    data-auth-role-key="{{ $authRoleKey }}"
    data-auth-user-name="{{ $authUser?->name }}"
    data-auth-user-role-label="{{ $authRoleLabel }}"
    data-notif-feed-url="{{ route('notifications.feed') }}"
    data-notif-read-all-url="{{ route('notifications.read_all') }}"
    data-notif-view-all-url="{{ route('notifications') }}"
>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <button class="sidebar-close" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>

        <!-- Logo Section -->
        <div class="sidebar-brand">
            <img src="{{ asset('images/logoforsidebar-cropped.png') }}" alt="Meedocentrix" class="sidebar-brand-wordmark">
        </div>

        <!-- User Info -->
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="overflow:hidden;display:flex;align-items:center;justify-content:center;background:transparent;padding:0;">
                @if($userAvatar === 'girl')
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:42px;height:42px;border-radius:50%;">
                        <circle cx="50" cy="50" r="50" fill="#ffe4f0"/>
                        <ellipse cx="50" cy="70" rx="26" ry="20" fill="#f472b6"/>
                        <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                        <ellipse cx="50" cy="28" rx="20" ry="14" fill="#be185d"/>
                        <ellipse cx="35" cy="30" rx="10" ry="16" fill="#be185d"/>
                        <ellipse cx="65" cy="30" rx="10" ry="16" fill="#be185d"/>
                        <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                        <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                        <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    </svg>
                @else
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:42px;height:42px;border-radius:50%;">
                        <circle cx="50" cy="50" r="50" fill="#dbeafe"/>
                        <ellipse cx="50" cy="72" rx="26" ry="20" fill="#3b82f6"/>
                        <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                        <rect x="30" y="18" width="40" height="20" rx="10" fill="#1e3a6e"/>
                        <rect x="28" y="28" width="44" height="10" rx="5" fill="#2563eb"/>
                        <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                        <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                        <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    </svg>
                @endif
            </div>
            <div class="sidebar-user-info">
                <h4 id="sidebarUserName">{{ $authUser?->name }}</h4>
                <span id="sidebarUserRole" class="role-badge">{{ $authRoleLabel }}</span>
            </div>
            <div class="sidebar-user-status"></div>
        </div>

        <!-- Navigation Label -->
        <div class="sidebar-nav-label">
            <span>Main Menu</span>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav" id="sidebarNav">
            @foreach($initialNavItems as $navItem)
                @php
                    $navId = (string) ($navItem['id'] ?? '');
                    $navIcon = (string) ($navItem['icon'] ?? 'fas fa-circle');
                    $navLabel = (string) ($navItem['label'] ?? 'Menu');
                @endphp
                <button
                    type="button"
                    class="nav-item"
                    id="nav-{{ $navId }}"
                    data-nav-id="{{ $navId }}"
                    onclick="navigateTo(this.dataset.navId)"
                >
                    <i class="{{ $navIcon }}"></i>
                    <span>{{ $navLabel }}</span>
                </button>
            @endforeach
        </nav>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-version">v1.0.0</div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <h3 id="pageTitle">Dashboard</h3>
                </div>
            </div>
            <div class="topbar-center">
                <!-- Global search removed per user request -->
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" onclick="toggleNotifications()" id="notifBtn">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notifBadge" @if($topbarUnreadCount <= 0) style="display:none;" @endif>{{ $topbarUnreadCount }}</span>
                </button>
                <div class="topbar-profile" onclick="toggleProfileMenu()">
                    <div class="topbar-avatar" style="overflow:hidden;display:flex;align-items:center;justify-content:center;background:transparent;padding:0;">
                        @if($userAvatar === 'girl')
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:34px;height:34px;border-radius:50%;">
                                <circle cx="50" cy="50" r="50" fill="#ffe4f0"/>
                                <ellipse cx="50" cy="70" rx="26" ry="20" fill="#f472b6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <ellipse cx="50" cy="28" rx="20" ry="14" fill="#be185d"/>
                                <ellipse cx="35" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <ellipse cx="65" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:34px;height:34px;border-radius:50%;">
                                <circle cx="50" cy="50" r="50" fill="#dbeafe"/>
                                <ellipse cx="50" cy="72" rx="26" ry="20" fill="#3b82f6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <rect x="30" y="18" width="40" height="20" rx="10" fill="#1e3a6e"/>
                                <rect x="28" y="28" width="44" height="10" rx="5" fill="#2563eb"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </div>
                    <span id="topbarUserName">{{ $topbarName }}</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <!-- Notifications Dropdown -->
            <div id="notifDropdown" class="dropdown-panel notif-dropdown" style="display:none;">
                <div class="dropdown-header">
                    <h4>Notifications</h4>
                    <button class="btn-text" id="notifMarkAllBtn" type="button">Mark all as read</button>
                </div>
                <div class="dropdown-body" id="notifList">
                    @forelse($topbarNotifications as $notification)
                        @php
                            $notifTypeClass = match ((string) $notification->type) {
                                'success' => 'green',
                                'warning' => 'orange',
                                'danger' => 'orange',
                                'info' => 'blue',
                                default => 'purple',
                            };
                        @endphp
                        <a
                            href="{{ $notification->action_url ?: route('notifications') }}"
                            class="notif-item {{ $notification->is_read ? '' : 'unread' }}"
                            data-notif-id="{{ $notification->id }}"
                            data-mark-url="{{ route('notifications.mark_read', ['notification' => $notification->id]) }}"
                        >
                            <div class="notif-icon {{ $notifTypeClass }}"><i class="fas fa-bell"></i></div>
                            <div class="notif-content">
                                <p><strong>{{ $notification->title }}</strong><br>{{ $notification->message }}</p>
                                <span>{{ optional($notification->created_at)->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="notif-empty">No notifications yet.</div>
                    @endforelse
                </div>
                <div class="dropdown-footer">
                    <button onclick="navigateTo('notifications')" class="btn-text" type="button">View All Notifications</button>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div id="profileDropdown" class="dropdown-panel profile-dropdown" style="display:none;">
                <div class="profile-dropdown-header">
                    <div class="profile-avatar-lg" style="overflow:hidden;display:flex;align-items:center;justify-content:center;background:transparent;">
                        @if($userAvatar === 'girl')
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:52px;height:52px;border-radius:50%;">
                                <circle cx="50" cy="50" r="50" fill="#ffe4f0"/>
                                <ellipse cx="50" cy="70" rx="26" ry="20" fill="#f472b6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <ellipse cx="50" cy="28" rx="20" ry="14" fill="#be185d"/>
                                <ellipse cx="35" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <ellipse cx="65" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:52px;height:52px;border-radius:50%;">
                                <circle cx="50" cy="50" r="50" fill="#dbeafe"/>
                                <ellipse cx="50" cy="72" rx="26" ry="20" fill="#3b82f6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <rect x="30" y="18" width="40" height="20" rx="10" fill="#1e3a6e"/>
                                <rect x="28" y="28" width="44" height="10" rx="5" fill="#2563eb"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </div>
                    <h4 id="profileDropdownName">{{ $authUser?->name }}</h4>
                    <span id="profileDropdownRole">{{ $authRoleLabel }}</span>
                </div>
                <div class="profile-dropdown-body">
                    <button type="button" onclick="navigateTo('profile')"><i class="fas fa-user-gear"></i> My Profile</button>
                    <hr>
                    <button type="button" onclick="handleLogout()" class="text-danger"><i class="fas fa-right-from-bracket"></i> Log Out</button>
                </div>
            </div>
        </header>

        <!-- Page Content Area -->
        <main class="content-area" id="contentArea">
            @yield("content")
        </main>

        @if($authUser && $authRoleKey !== 'terminal')
            @include('admin.partials.global_toast')
        @endif
    </div>
</div>

<form
    id="logoutForm"
    action="{{ route('logout', ['session_scope' => $authRoleKey === 'administrator' ? 'admin' : $authRoleKey] + (request('s') ? ['s' => request('s')] : [])) }}"
    method="POST"
    style="display:none;"
>
    @csrf
    <input type="hidden" name="session_scope" value="{{ $authRoleKey === 'administrator' ? 'admin' : $authRoleKey }}">
    @if(request('s'))
        <input type="hidden" name="s" value="{{ request('s') }}">
    @endif
</form>

<div id="modalOverlay" class="modal-overlay" style="display:none;" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="modalTitle">Modal Title</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer" id="modalFooter">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" id="modalSubmitBtn">Save Changes</button>
        </div>
    </div>
</div>

<div id="toastContainer" class="toast-container"></div>

@include('shared.rate_limit_popup')


<!-- Modals -->
@yield('modals')
<script src="{{ asset('js/app.js') }}"></script>

@if($authRoleKey === 'collector')
<script>
(function () {
    var SS_KEY = 'collector_tab_token';
    function clean(t) { return (t || '').replace(/[^a-zA-Z0-9]/g, '').slice(0, 16); }

    var urlToken = clean(new URLSearchParams(window.location.search).get('s'));
    var storedToken = '';
    try { storedToken = clean(window.sessionStorage.getItem(SS_KEY) || ''); } catch (e) {}

    var tabToken = urlToken || storedToken;
    if (!tabToken) return;

    // Persist token in this tab's sessionStorage so future navigations can recover it.
    try { window.sessionStorage.setItem(SS_KEY, tabToken); } catch (e) {}

    // If the URL is missing the token but we recovered one from sessionStorage,
    // rewrite the URL in place without triggering a reload so subsequent requests
    // include it (via rewritten links/forms) and the referer carries it too.
    if (!urlToken && storedToken) {
        try {
            var u = new URL(window.location.href);
            u.searchParams.set('s', storedToken);
            window.history.replaceState(window.history.state, '', u.pathname + u.search + u.hash);
        } catch (e) {}
    }

    function shouldScope(url) {
        if (!url) return false;
        // Skip absolute externals, anchors, mailto, tel, javascript
        if (/^(https?:)?\/\//i.test(url)) {
            try {
                var u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) return false;
                return /^\/collector(\/|$)/i.test(u.pathname) || /^\/logout(\/|$|\?)/i.test(u.pathname);
            } catch (e) { return false; }
        }
        if (url.charAt(0) === '#' || /^(mailto:|tel:|javascript:)/i.test(url)) return false;
        return /^\/collector(\/|$)/i.test(url) || /^\/logout(\/|$|\?)/i.test(url);
    }

    function appendToken(url) {
        try {
            var u = new URL(url, window.location.origin);
            if (!u.searchParams.get('s')) {
                u.searchParams.set('s', tabToken);
            }
            // Preserve relative form when input was relative
            if (/^(https?:)?\/\//i.test(url)) return u.toString();
            return u.pathname + (u.search || '') + (u.hash || '');
        } catch (e) { return url; }
    }

    function rewriteAnchors(root) {
        var anchors = (root || document).querySelectorAll('a[href]');
        for (var i = 0; i < anchors.length; i++) {
            var a = anchors[i];
            var href = a.getAttribute('href');
            if (shouldScope(href)) a.setAttribute('href', appendToken(href));
        }
    }

    function rewriteForms(root) {
        var forms = (root || document).querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            var f = forms[i];
            var action = f.getAttribute('action') || window.location.pathname;
            if (!shouldScope(action)) continue;
            f.setAttribute('action', appendToken(action));
            if (!f.querySelector('input[name="s"]')) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 's';
                input.value = tabToken;
                f.appendChild(input);
            }
        }
    }

    function rewriteAll(root) {
        rewriteAnchors(root);
        rewriteForms(root);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { rewriteAll(document); });
    } else {
        rewriteAll(document);
    }

    // Catch clicks on anchors that may not have been rewritten yet (e.g. added right
    // before click, or rewritten by other scripts after us).
    document.addEventListener('click', function (e) {
        var el = e.target;
        while (el && el !== document && el.tagName !== 'A') el = el.parentNode;
        if (!el || el.tagName !== 'A') return;
        var href = el.getAttribute('href');
        if (shouldScope(href)) el.setAttribute('href', appendToken(href));
    }, true);

    // Catch form submits similarly.
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.tagName !== 'FORM') return;
        var action = f.getAttribute('action') || window.location.pathname;
        if (!shouldScope(action)) return;
        f.setAttribute('action', appendToken(action));
        if (!f.querySelector('input[name="s"]')) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 's';
            input.value = tabToken;
            f.appendChild(input);
        }
    }, true);

    // Patch fetch + XHR to carry the token automatically on same-origin collector calls.
    var origFetch = window.fetch;
    if (typeof origFetch === 'function') {
        window.fetch = function (input, init) {
            try {
                var url = typeof input === 'string' ? input : (input && input.url);
                if (shouldScope(url)) {
                    var scoped = appendToken(url);
                    if (typeof input === 'string') input = scoped;
                    else input = new Request(scoped, input);
                }
            } catch (e) {}
            return origFetch.call(this, input, init);
        };
    }

    var origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function (method, url) {
        if (shouldScope(url)) {
            arguments[1] = appendToken(url);
        }
        return origOpen.apply(this, arguments);
    };

    // Watch for dynamically added links/forms.
    var observer = new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var added = mutations[i].addedNodes;
            for (var j = 0; j < added.length; j++) {
                var node = added[j];
                if (node.nodeType !== 1) continue;
                rewriteAll(node);
            }
        }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
@endif
</body>
</html>

