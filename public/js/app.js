// Meedocentrix UI Flow and Rendering Logic

// State Management
let currentUserRole = null;
let currentUserName = null;
let currentPage = null;
let sidebarOpen = true;
let liveSyncTimer = null;

// Role Configurations
const ROLES = {
    administrator: {
        name: 'Administrator',
        user: 'System Admin',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'users', icon: 'fas fa-users-gear', label: 'User Management' },
            { id: 'roles', icon: 'fas fa-shield-halved', label: 'Roles & Permissions' },
            { id: 'rates', icon: 'fas fa-tags', label: 'Rates & Fees' },
            { id: 'transactions', icon: 'fas fa-money-check-dollar', label: 'All Transactions' },
            { id: 'reports', icon: 'fas fa-file-invoice', label: 'Reports & Analytics' }
        ]
    },
    fishport: {
        name: 'Fishport Personnel',
        user: 'Juan Dela Cruz',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'vessels', icon: 'fas fa-ship', label: 'Vessel Logs' },
            { id: 'vessel_registry', icon: 'fas fa-clipboard-list', label: 'Vessel Registry' },
            { id: 'fishport_records', icon: 'fas fa-fish', label: 'Fishport Transactions' },
            { id: 'send_payment', icon: 'fas fa-file-invoice-dollar', label: 'Send for Payment' },
            { id: 'reports', icon: 'fas fa-file-lines', label: 'Reports' }
        ]
    },
    market: {
        name: 'Public Market Personnel',
        user: 'Maria Santos',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'vendors', icon: 'fas fa-users', label: 'Tenant Directory' },
            { id: 'stalls', icon: 'fas fa-store', label: 'Stall Management' },
            { id: 'market_records', icon: 'fas fa-receipt', label: 'Market Transactions' },
            { id: 'send_payment', icon: 'fas fa-file-invoice-dollar', label: 'Send for Payment' },
            { id: 'market_reports', icon: 'fas fa-file-lines', label: 'Reports' }
        ]
    },
    cemetery: {
        name: 'Cemetery Personnel',
        user: 'Pedro Penduko',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'cemetery_records', icon: 'fas fa-users', label: 'Occupant Records' },
            { id: 'cemetery_services', icon: 'fas fa-book-journal-whills', label: 'Service Logs' },
            { id: 'cemetery_transactions', icon: 'fas fa-receipt', label: 'Cemetery Transactions' },
            { id: 'cemetery_payments', icon: 'fas fa-cash-register', label: 'Payment Collection' },
            { id: 'cemetery_reports', icon: 'fas fa-file-lines', label: 'Reports' }
        ]
    },
    terminal: {
        name: 'Terminal Personnel',
        user: 'Mario Lopez',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'vehicles', icon: 'fas fa-bus', label: 'Vehicle Logs' },
            { id: 'terminal_records', icon: 'fas fa-ticket', label: 'Terminal Transactions' },
            { id: 'send_payment', icon: 'fas fa-file-invoice-dollar', label: 'Payment History' }
        ]
    },
    atrium: {
        name: 'Atrium Hall Personnel',
        user: 'Clara Recto',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'atrium_bookings', icon: 'fas fa-calendar-check', label: 'Bookings' },
            { id: 'atrium_payments', icon: 'fas fa-money-check-dollar', label: 'Payments' },
            { id: 'atrium_supplies', icon: 'fas fa-boxes-stacked', label: 'Supplies' },
            { id: 'atrium_reports', icon: 'fas fa-chart-pie', label: 'Reports' }
        ]
    },
    collector: {
        name: 'Assigned Collector',
        user: 'Roberto Gomez',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'pending_collections', icon: 'fas fa-clock', label: 'Pending Collections' },
            { id: 'collector_payments', icon: 'fas fa-hand-holding-dollar', label: 'Received Payments' },
            { id: 'collector_reports', icon: 'fas fa-file-lines', label: 'Reports' }
        ]
    },
    cashier: {
        name: 'Main Cashier',
        user: 'Elena Marquez',
        nav: [
            { id: 'dashboard', icon: 'fas fa-chart-pie', label: 'Dashboard' },
            { id: 'cashier_remittance', icon: 'fas fa-inbox', label: 'Verify Remittances' },
            { id: 'official_collections', icon: 'fas fa-vault', label: 'Official Collections' },
            { id: 'daily_summary', icon: 'fas fa-file-contract', label: 'Daily Summary' }
        ]
    }
};

const SESSION_ROLE_KEY = 'meedocentrixRole';

const SHARED_PAGE_ROUTES = {
    settings: '/settings',
    notifications: '/notifications',
    direct_payment: '/direct-payment'
};

const ROLE_PAGE_ROUTES = {
    administrator: {
        dashboard: '/admin/dashboard',
        users: '/admin/users',
        roles: '/admin/roles',
        rates: '/admin/rates',
        transactions: '/admin/transactions',
        reports: '/admin/reports'
    },
    fishport: {
        dashboard: '/fishport/dashboard',
        vessels: '/fishport/vessel-logs',
        vessel_registry: '/fishport/vessel-registry',
        fishport_records: '/fishport/records',
        profile: '/fishport/profile',
        send_payment: '/fishport/send-payment',
        reports: '/fishport/reports'
    },
    market: {
        dashboard: '/market/dashboard',
        vendors: '/market/vendors',
        stalls: '/market/stalls',
        market_records: '/market/records',
        send_payment: '/market/send-payment',
        market_reports: '/market/reports',
        profile: '/market/profile'
    },
    cemetery: {
        dashboard: '/cemetery/dashboard',
        cemetery_records: '/cemetery/records',
        cemetery_services: '/cemetery/services',
        cemetery_transactions: '/cemetery/transactions',
        cemetery_payments: '/cemetery/payments',
        cemetery_reports: '/cemetery/reports',
        profile: '/cemetery/profile'
    },
    terminal: {
        dashboard: '/terminal/dashboard',
        vehicles: '/terminal/vehicles',
        terminal_records: '/terminal/records',
        send_payment: '/terminal/send-payment'
    },
    atrium: {
        dashboard: '/atrium/dashboard',
        atrium_records: '/atrium/records',
        atrium_bookings: '/atrium/bookings',
        atrium_payments: '/atrium/payments',
        atrium_supplies: '/atrium/supplies',
        atrium_reports: '/atrium/reports',
        profile: '/atrium/profile'
    },
    collector: {
        dashboard: '/collector/dashboard',
        pending_collections: '/collector/pending-collections',
        collector_payments: '/collector/payments',
        collector_reports: '/collector/reports',
        profile: '/collector/profile'
    },
    cashier: {
        dashboard: '/cashier/dashboard',
        cashier_remittance: '/cashier/remittance',
        official_collections: '/cashier/collections',
        daily_summary: '/cashier/summary'
    }
};

let mockUsers = [
    { id: 1, name: 'System Admin', username: 'admin', role: 'Administrator', status: 'Active', lastLogin: 'Today, 08:30 AM' },
    { id: 2, name: 'Juan Dela Cruz', username: 'jdelacruz', role: 'Fishport Personnel', status: 'Active', lastLogin: 'Today, 09:15 AM', assignedCollectors: [6, 7] },
    { id: 3, name: 'Maria Santos', username: 'msantos', role: 'Public Market Personnel', status: 'Active', lastLogin: 'Yesterday, 04:20 PM', assignedCollectors: [] },
    { id: 4, name: 'Pedro Penduko', username: 'ppenduko', role: 'Cemetery Personnel', status: 'Inactive', lastLogin: 'Mar 12, 2026' },
    { id: 5, name: 'Elena Marquez', username: 'emarquez', role: 'Main Cashier', status: 'Active', lastLogin: 'Today, 07:45 AM' },
    { id: 6, name: 'Roberto Gomez', username: 'rgomez', role: 'Assigned Collector', status: 'Active', lastLogin: 'Today, 10:05 AM' },
    { id: 7, name: 'Luis Antonio', username: 'lantonio', role: 'Assigned Collector', status: 'Active', lastLogin: 'Today, 11:00 AM' },
    { id: 8, name: 'Maria Clara', username: 'mclara', role: 'Assigned Collector', status: 'Inactive', lastLogin: 'Mar 15, 2026' },
    { id: 9, name: 'Mario Lopez', username: 'mlopez', role: 'Terminal Personnel', status: 'Active', lastLogin: 'Today, 09:30 AM', assignedCollectors: [8] }
];

let mockRates = {
    fishport: [
        { id: 'fp1', name: 'Banyera - Small (Tuna)', basis: 'per banyera', rate: 5.00 },
        { id: 'fp2', name: 'Banyera - Medium (Galunggong)', basis: 'per banyera', rate: 10.00 },
        { id: 'fp3', name: 'Banyera - Large (Tamban)', basis: 'per banyera', rate: 15.00 },
        { id: 'fp4', name: 'Docking Fee - Small Vessel', basis: 'per arrival', rate: 500.00 },
        { id: 'fp5', name: 'Docking Fee - Large Vessel', basis: 'per arrival', rate: 1500.00 },
        { id: 'fp6', name: 'Ice & Water Supply', basis: 'per block/100L', rate: 25.00 },
    ],
    market: [
        { id: 'mkt1', name: 'Stall Rental - Dry Goods', basis: 'per sq. meter/month', rate: 150.00 },
        { id: 'mkt2', name: 'Stall Rental - Wet Market', basis: 'per sq. meter/month', rate: 200.00 },
        { id: 'mkt3', name: 'Arcabala (Daily Ticket)', basis: 'per day', rate: 20.00 },
    ],
    cemetery: [
        { id: 'cem1', name: 'Apartment Niche Rental', basis: 'per 5 years', rate: 2500.00 },
        { id: 'cem2', name: 'Burial Permit Fee', basis: 'per burial', rate: 300.00 },
        { id: 'cem3', name: 'Tomb Construction Permit', basis: 'per permit', rate: 500.00 },
    ],
    terminal: [
        { id: 'ter1', name: 'Terminal Fee - Bus', basis: 'per departure', rate: 50.00 },
        { id: 'ter2', name: 'Terminal Fee - Van', basis: 'per departure', rate: 30.00 },
        { id: 'ter3', name: 'Parking Fee (Overnight)', basis: 'per night', rate: 100.00 },
    ],
    atrium: [
        { id: 'atr1', name: 'Hall Rental (First 4 hours)', basis: 'per event', rate: 5000.00 },
        { id: 'atr2', name: 'Hall Rental (Succeeding hour)', basis: 'per hour', rate: 800.00 },
        { id: 'atr3', name: 'Sound System Package', basis: 'per event', rate: 1500.00 },
    ]
};

// ======================= DOM ELEMENTS =======================
const loginPage = document.getElementById('loginPage');
const appContainer = document.getElementById('appContainer');
const sidebarNav = document.getElementById('sidebarNav');
const contentArea = document.getElementById('contentArea');
const pageTitle = document.getElementById('pageTitle');
const sidebarUserName = document.getElementById('sidebarUserName');
const sidebarUserRole = document.getElementById('sidebarUserRole');
const topbarUserName = document.getElementById('topbarUserName');
let profileDropdownName = document.getElementById('profileDropdownName');
let profileDropdownRole = document.getElementById('profileDropdownRole');
const notifDropdown = document.getElementById('notifDropdown');
const profileDropdown = document.getElementById('profileDropdown');
const isServerRenderedApp = Boolean(appContainer && sidebarNav && contentArea && !loginPage);
const serverRenderedRoleKey = appContainer?.dataset.authRoleKey || null;
const serverRenderedUserName = appContainer?.dataset.authUserName || null;
const serverRenderedRoleLabel = appContainer?.dataset.authUserRoleLabel || null;

// ======================= AUTH & NAVIGATION =======================

function normalizePath(pathname) {
    if (!pathname) return '/';
    const normalized = pathname.replace(/\/+$/, '');
    return normalized || '/';
}

function getRoutesForRole(roleId) {
    return { ...SHARED_PAGE_ROUTES, ...(ROLE_PAGE_ROUTES[roleId] || {}) };
}

function getRoleFromPath(pathname) {
    const path = normalizePath(pathname);

    for (const [roleId, routeMap] of Object.entries(ROLE_PAGE_ROUTES)) {
        if (Object.values(routeMap).some(routePath => {
            const normalized = normalizePath(routePath);
            return path === normalized || path.startsWith(normalized + '/');
        })) {
            return roleId;
        }
    }

    return null;
}

function getPageIdFromPath(pathname, roleId = null) {
    const path = normalizePath(pathname);
    const searchMaps = [];

    if (roleId && ROLE_PAGE_ROUTES[roleId]) {
        searchMaps.push(ROLE_PAGE_ROUTES[roleId]);
    }

    searchMaps.push(SHARED_PAGE_ROUTES);

    for (const routeMap of searchMaps) {
        for (const [pageId, routePath] of Object.entries(routeMap)) {
            const normalized = normalizePath(routePath);
            if (path === normalized || path.startsWith(normalized + '/')) {
                return pageId;
            }
        }
    }

    for (const routeMap of Object.values(ROLE_PAGE_ROUTES)) {
        for (const [pageId, routePath] of Object.entries(routeMap)) {
            const normalized = normalizePath(routePath);
            if (path === normalized || path.startsWith(normalized + '/')) {
                return pageId;
            }
        }
    }

    return null;
}

function getCurrentContext(pathname = window.location.pathname) {
    const path = normalizePath(pathname);
    const pathRole = getRoleFromPath(path);
    const storedRole = sessionStorage.getItem(SESSION_ROLE_KEY);
    const roleId = pathRole || serverRenderedRoleKey || storedRole || 'administrator';
    
    // Explicit server-rendered override takes precedence
    const explicitlyStatedPage = document.querySelector('[data-server-rendered-page]');
    let pageId = explicitlyStatedPage ? explicitlyStatedPage.dataset.serverRenderedPage : null;
    
    if (!pageId) {
        pageId = getPageIdFromPath(path, roleId);
    }

    // Default to a fallback if we really can't find it to prevent sidebar crash
    if (!pageId && roleId && ROLE_PAGE_ROUTES[roleId]) {
        pageId = Object.keys(ROLE_PAGE_ROUTES[roleId])[0]; // default to first tab (usually dashboard)
    }

    return { path, roleId, pageId };
}

function applyCurrentUser(roleId) {
    const roleConfig = ROLES[roleId];
    if (!roleConfig) return;

    currentUserRole = roleId;
    currentUserName = serverRenderedUserName || roleConfig.user;
    sessionStorage.setItem(SESSION_ROLE_KEY, roleId);

    if (sidebarUserName) sidebarUserName.textContent = currentUserName;
    if (sidebarUserRole) sidebarUserRole.textContent = serverRenderedRoleLabel || roleConfig.name;
    if (topbarUserName) topbarUserName.textContent = currentUserName.split(' ')[0];
    if (profileDropdownName) profileDropdownName.textContent = currentUserName;
    if (profileDropdownRole) profileDropdownRole.textContent = serverRenderedRoleLabel || roleConfig.name;

    buildSidebar(roleConfig.nav);
}

function setActiveNavigation(pageId) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    const activeNav = document.getElementById(`nav-${pageId}`);
    if (activeNav) activeNav.classList.add('active');
}

function initializeServerRenderedApp(pathname = window.location.pathname) {
    const { roleId, pageId } = getCurrentContext(pathname);
    if (!roleId || !pageId) {
        stopServerLiveSync();
        return;
    }

    applyCurrentUser(roleId);
    currentPage = pageId;
    closeDropdowns();
    setActiveNavigation(pageId);

    const serverRenderedPage = contentArea.querySelector('[data-server-rendered-page]');
    if (serverRenderedPage && serverRenderedPage.dataset.serverRenderedPage === pageId) {
        if (pageTitle) {
            pageTitle.textContent = serverRenderedPage.dataset.pageTitle || pageTitle.textContent;
        }
        startServerLiveSync();
        return;
    }

    renderPage(pageId);
    startServerLiveSync();
}

function handleLogin(event) {
    event.preventDefault();
    quickLogin('administrator'); // Default to admin for demo
    return false;
}

function quickLogin(roleId) {
    const roleConfig = ROLES[roleId];
    if (!roleConfig) return;

    currentUserRole = roleId;
    currentUserName = roleConfig.user;

    // Update UI Elements
    if(sidebarUserName) sidebarUserName.textContent = currentUserName;
    if(sidebarUserRole) sidebarUserRole.textContent = roleConfig.name;
    if(topbarUserName) topbarUserName.textContent = currentUserName.split(' ')[0];
    if(profileDropdownName) profileDropdownName.textContent = currentUserName;
    if(profileDropdownRole) profileDropdownRole.textContent = roleConfig.name;

    // Build Sidebar
    buildSidebar(roleConfig.nav);

    // Hide Login, Show App
    if(loginPage) loginPage.style.display = 'none';
    if(appContainer) appContainer.style.display = 'flex';

    // Show Notification
    showToast(`Welcome back, ${currentUserName}!`, 'success');

    // Load initial page
    navigateTo('dashboard');
}

function performLogout() {
    if (isServerRenderedApp) {
        sessionStorage.removeItem(SESSION_ROLE_KEY);
        const logoutForm = document.getElementById('logoutForm');
        if (logoutForm) {
            logoutForm.submit();
            return;
        }

        window.location.href = '/login';
        return;
    }

    currentUserRole = null;
    currentUserName = null;
    currentPage = null;

    if(appContainer) appContainer.style.display = 'none';
    if(loginPage) loginPage.style.display = '';

    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.reset();

    // Hide any error state
    const loginError = document.getElementById('loginError');
    if (loginError) loginError.style.display = 'none';

    closeDropdowns();
}

function handleLogout() {
    const submitBtn = document.getElementById('modalSubmitBtn');
    const modalFooter = document.getElementById('modalFooter');

    if (!modalOverlay || !modalTitle || !modalBody || !submitBtn || !modalFooter) {
        const confirmed = window.confirm('Are you sure you want to log out?');
        if (!confirmed) return;
        performLogout();
        return;
    }

    modalTitle.textContent = 'Confirm Logout';
    modalBody.innerHTML = `
        <div style="display:flex; align-items:flex-start; gap:12px; padding-top:4px;">
            <div style="width:38px; height:38px; border-radius:999px; background:var(--danger-light); color:var(--danger); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-right-from-bracket"></i>
            </div>
            <div>
                <p style="margin:0; font-size:1rem; font-weight:600; color:var(--gray-800);">Are you sure you want to log out?</p>
                <p style="margin:6px 0 0; color:var(--gray-500); font-size:.92rem;">Your current session will end and you will return to login.</p>
            </div>
        </div>
    `;

    submitBtn.textContent = 'Log Out';
    submitBtn.className = 'btn btn-danger';
    submitBtn.onclick = () => {
        closeModal();
        performLogout();
    };

    modalOverlay.style.display = 'flex';
}

function buildSidebar(navItems) {
    if(!sidebarNav) return;
    if (!Array.isArray(navItems) || navItems.length === 0) return;
    sidebarNav.innerHTML = '';
    navItems.forEach(item => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nav-item';
        btn.id = `nav-${item.id}`;
        btn.onclick = () => navigateTo(item.id);
        
        btn.innerHTML = `
            <i class="${item.icon}"></i>
            <span>${item.label}</span>
        `;
        sidebarNav.appendChild(btn);
    });
}

function navigateTo(pageId) {
    const proceedNavigation = () => {
        if (isServerRenderedApp) {
            const targetPath = getRoutesForRole(currentUserRole)[pageId];
            if (!targetPath) return;

            currentPage = pageId;
            closeDropdowns();
            setActiveNavigation(pageId);

            const normalizedTargetPath = normalizePath(targetPath);
            if (normalizePath(window.location.pathname) !== normalizedTargetPath) {
                window.location.href = normalizedTargetPath;
            }
            return;
        }

        setActiveNavigation(pageId);
        currentPage = pageId;
        closeDropdowns();
        renderPage(pageId);
    };

    proceedNavigation();
}

// ======================= LIVE AUTO-SYNC (SERVER RENDERED) =======================

function hasLiveSyncModalOpen() {
    return Boolean(document.querySelector(
        '.is-open,' +
        '.cp-modal.is-open,' +
        '.cpm-modal.is-open,' +
        '.sp-modal.is-open,' +
        '.vl-modal.is-open,' +
        '.vr-modal.is-open,' +
        '.fp-modal.is-open,' +
        '.um-modal.is-open,' +
        '.modal-overlay[style*="display: flex"]'
    ));
}

function hasLiveSyncActiveInputFocus() {
    const active = document.activeElement;
    if (!active) return false;
    return active.matches(
        'input:not([type="hidden"]):not([readonly]), textarea:not([readonly]), select:not([disabled]), [contenteditable="true"]'
    );
}

function hasLiveSyncUnsavedChanges() {
    const fields = document.querySelectorAll('form input, form textarea, form select');

    for (const field of fields) {
        if (!(field instanceof HTMLElement)) continue;

        if (field.matches('[type="hidden"], [type="submit"], [type="button"], [type="reset"]')) {
            continue;
        }

        if (field instanceof HTMLInputElement) {
            const type = (field.type || '').toLowerCase();

            if (type === 'checkbox' || type === 'radio') {
                if (field.checked !== field.defaultChecked) return true;
                continue;
            }

            if (type === 'file') {
                if ((field.files?.length || 0) > 0) return true;
                continue;
            }

            if (field.value !== field.defaultValue) return true;
            continue;
        }

        if (field instanceof HTMLTextAreaElement) {
            if (field.value !== field.defaultValue) return true;
            continue;
        }

        if (field instanceof HTMLSelectElement) {
            const changed = Array.from(field.options).some(option => option.selected !== option.defaultSelected);
            if (changed) return true;
        }
    }

    return false;
}

function canRunLiveSyncRefresh() {
    if (document.hidden) return false;
    if (hasLiveSyncModalOpen()) return false;
    if (hasLiveSyncActiveInputFocus()) return false;
    if (hasLiveSyncUnsavedChanges()) return false;
    return true;
}

function stopServerLiveSync() {
    if (liveSyncTimer) {
        window.clearInterval(liveSyncTimer);
        liveSyncTimer = null;
    }
}

function startServerLiveSync() {
    stopServerLiveSync();

    if (!isServerRenderedApp) return;

    const pageRoot = contentArea?.querySelector('[data-server-rendered-page]');
    if (!pageRoot) return;
    if (pageRoot.hasAttribute('data-live-refresh-disabled')) return;

    const intervalMs = Number(pageRoot.dataset.liveRefreshMs || 12000);
    if (!Number.isFinite(intervalMs) || intervalMs < 5000) return;

    liveSyncTimer = window.setInterval(() => {
        const currentRoot = contentArea?.querySelector('[data-server-rendered-page]');
        if (!currentRoot) return;
        if (currentRoot.hasAttribute('data-live-refresh-disabled')) return;
        if (!canRunLiveSyncRefresh()) return;

        window.location.reload();
    }, intervalMs);
}

// ======================= PAGE RENDERING =======================

function renderPage(pageId) {
    if(!contentArea) return;

    // ── Server-rendered page guard ──────────────────────────────────────────
    // For pages that are rendered by Laravel (not JS), we must NOT clear the
    // contentArea and NOT trigger a redirect loop.
    const serverRenderedPaths = {
        'fishport': {
            'dashboard': '/fishport/dashboard',
            'profile':   '/fishport/profile',
            'vessels': '/fishport/vessel-logs',
            'vessel_registry': '/fishport/vessel-registry',
            'fishport_records': '/fishport/records',
            'send_payment': '/fishport/send-payment',
            'reports': '/fishport/reports',
        },
        'market': {
            'dashboard': '/market/dashboard',
            'vendors': '/market/vendors',
            'stalls': '/market/stalls',
            'market_records': '/market/records',
            'send_payment': '/market/send-payment',
            'market_reports': '/market/reports',
            'profile': '/market/profile',
        },
        'collector': {
            'dashboard': '/collector/dashboard',
            'pending_collections': '/collector/pending-collections',
            'collector_payments': '/collector/payments',
            'collector_reports': '/collector/reports',
            'profile': '/collector/profile',
        },
    };

    // Check role-specific pages
    if (currentUserRole && serverRenderedPaths[currentUserRole] && serverRenderedPaths[currentUserRole][pageId]) {
        const targetPath = serverRenderedPaths[currentUserRole][pageId];
        if (window.location.pathname === targetPath) {
            const pageTitlesByRole = {
                fishport: {
                    dashboard: 'Fishport Dashboard',
                    vessels: 'Vessel Logs',
                    vessel_registry: 'Vessel Registry',
                    fishport_records: 'Fishport Transactions',
                    send_payment: 'Send for Payment',
                    reports: 'Fishport Reports',
                    profile: 'My Profile',
                },
                market: {
                    dashboard: 'Market Dashboard',
                    vendors: 'Tenant Directory',
                    stalls: 'Stall Management',
                    market_records: 'Market Transactions',
                    send_payment: 'Send for Payment',
                    market_reports: 'Market Reports',
                    profile: 'My Profile',
                },
                collector: {
                    dashboard: 'Collector Dashboard',
                    pending_collections: 'Pending Collections',
                    collector_payments: 'Collector Payments',
                    collector_reports: 'Collector Reports',
                    profile: 'My Profile',
                },
            };

            if (pageTitle) {
                const roleTitles = pageTitlesByRole[currentUserRole] || {};
                pageTitle.textContent = roleTitles[pageId] || pageTitle.textContent;
            }
            return;
        } else {
            window.location.href = targetPath;
            return;
        }
    }

    // Check shared pages (profile visible from any role via sidebar)
    if (pageId === 'profile' && (currentUserRole === 'fishport' || currentUserRole === 'collector')) {
        const profilePath = currentUserRole === 'collector' ? '/collector/profile' : '/fishport/profile';
        if (window.location.pathname === profilePath) {
            if (pageTitle) pageTitle.textContent = 'My Profile';
            return;
        } else {
            window.location.href = profilePath;
            return;
        }
    }

    // Clear content (JS-rendered pages only)
    contentArea.innerHTML = '';
    
    // Set Title
    let title = 'Dashboard';

    switch(pageId) {
        case 'dashboard':
            title = 'Overview Dashboard';
            renderDashboard();
            break;
        case 'users':
            title = 'User Management';
            renderUserManagementPage();
            break;
        case 'roles':
            title = 'Roles & Permissions';
            renderRolesPage();
            break;
        case 'rates':
            title = 'Rates & Fees Management';
            renderRatesPage();
            break;
        case 'transactions':
            title = 'Master Transaction Ledger';
            renderAllTransactionsPage();
            break;
        case 'fishport_records':
            title = 'Fishport Transactions';
            renderTablePage('Fishport Records', ['Transaction ID', 'Vessel', 'Owner', 'Amount', 'Status', 'Date']);
            break;
        case 'vessels':
            title = 'Vessel Arrival Logs';
            renderVesselLogsPage();
            break;
        case 'vessel_registry':
            title = 'Vessel Registry';
            renderVesselRegistryPage();
            break;
        case 'send_payment':
            title = currentUserRole === 'terminal' ? 'Payment History' : 'Send for Payment';
            renderSendPaymentPage();
            break;
        case 'market_reports':
            title = 'Market Reports';
            window.location.href = '/market/reports';
            return;
        case 'market_records':
            title = 'Public Market Transactions';
            renderTablePage('Market Collections', ['Stall No.', 'Vendor', 'Type', 'Amount', 'Status', 'Date']);
            break;
        case 'vendors':
            title = 'Tenant Directory';
            renderVendorDirectoryPage();
            break;
        case 'stalls':
            title = 'Stall Management';
            renderStallManagementPage();
            break;
        case 'cemetery_records':
            title = 'Cemetery Occupant Records';
            renderCemeteryRecordsPage();
            break;
        case 'cemetery_services':
            title = 'Cemetery Service Logs';
            renderCemeteryServicesPage();
            break;
        case 'cemetery_transactions':
            title = 'Cemetery Transactions';
            renderTablePage('Cemetery Transactions', ['Ref ID', 'Description', 'Amount', 'Date', 'Status', 'Actions']);
            break;
        case 'cemetery_payments':
            title = 'Cemetery Payment Collection';
            renderDirectPaymentPage();
            break;
        case 'cemetery_reports':
            title = 'Cemetery Reports';
            renderReportsPage();
            break;
        case 'direct_payment':
            title = 'Direct Payment Collection';
            renderDirectPaymentPage();
            break;
        case 'vehicles':
            title = 'Vehicle Logs';
            renderTerminalVehiclesPage();
            break;
        case 'terminal_records':
            title = 'Terminal Transactions';
            renderTablePage('Terminal Logs', ['Plate No.', 'Driver', 'Type', 'Amount', 'Time In', 'Time Out']);
            break;
        case 'calendar':
            title = 'Booking Calendar';
            renderBookingCalendarPage();
            break;
        case 'booking':
            title = 'Atrium Hall Booking';
            renderBookingPage();
            break;
        case 'atrium_records':
            title = 'Reservation Records';
            renderAtriumRecordsPage();
            break;
        case 'pending_collections':
            title = 'Pending Collections';
            // Server-rendered — redirect to the real Laravel page
            window.location.href = '/collector/pending-collections';
            return;
        case 'collector_payments':
            title = 'Received Payments';
            window.location.href = '/collector/payments';
            return;
        case 'collector_reports':
            title = 'Collector Reports';
            window.location.href = '/collector/reports';
            return;
        case 'remit':
            title = 'Remit to Cashier';
            window.location.href = '/collector/remit';
            return;
        case 'cashier_remittance':
            title = 'Verify Remittances';
            renderRemittancePage();
            break;
        case 'official_collections':
            title = 'Official Collections';
            renderOfficialCollectionsPage();
            break;
        case 'daily_summary':
            title = 'Daily Summary & Reports';
            renderDailySummaryPage();
            break;
        case 'reports':
            title = 'Reports & Analytics';
            renderReportsPage();
            break;
        case 'profile':
            title = 'User Profile';
            renderProfilePage();
            break;
        case 'settings':
            title = 'System Settings';
            renderSettingsPage();
            break;
        case 'notifications':
            title = 'All Notifications';
            renderNotificationsPage();
            break;
        default:
            title = pageId.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase());
            renderPlaceholder(title);
    }

    if(pageTitle) pageTitle.textContent = title;
}

// --- Specific Page Renderers ---

function renderDashboard() {
    let statsHtml = '';
    let chartsHtml = '';
    let tablesHtml = '';

    if (currentUserRole === 'administrator') {
        const filterHtml = `
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
                <div class="filter-group" style="display: inline-flex; background: var(--gray-100); padding: 4px; border-radius: var(--radius-md);">
                    <button class="btn btn-sm active" data-filter="today" style="background: white; box-shadow: var(--shadow-sm); border: none; color: var(--gray-800); font-weight: 600;">Today</button>
                    <button class="btn btn-sm" data-filter="week" style="background: transparent; border: none; color: var(--gray-600);">This Week</button>
                    <button class="btn btn-sm" data-filter="month" style="background: transparent; border: none; color: var(--gray-600);">This Month</button>
                </div>
            </div>
        `;

        statsHtml = `
            ${filterHtml}
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-coins"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Total Revenue</h3>
                        <h2 id="statRevenue" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">₱124,500.00</h2>
                        <span id="statRevenueChange" class="text-success" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;"><i class="fas fa-arrow-up"></i> 12% vs yesterday</span>
                    </div>
                </div>
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-clock"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending Remittances</h3>
                        <h2 id="statPending" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">15</h2>
                        <span id="statPendingDesc" class="text-warning" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;">Needs review</span>
                    </div>
                </div>
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Total Transactions</h3>
                        <h2 id="statTransactions" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">1,284</h2>
                        <span id="statTransactionsChange" class="text-success" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;"><i class="fas fa-arrow-up"></i> 5% vs yesterday</span>
                    </div>
                </div>
            </div>
        `;
        
        chartsHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2">
                    <div class="card-header">
                        <h3>Revenue by Department</h3>
                        <button class="btn btn-icon"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="280"></canvas>
                    </div>
                </div>
                <div class="card" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header border-bottom flex-between">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Activity</h3>
                        <button class="btn btn-icon btn-sm text-muted"><i class="fas fa-filter"></i></button>
                    </div>
                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="activity-timeline" style="position: relative; padding-left: 24px;">
                            <!-- Line connecting items -->
                            <div style="position: absolute; top: 10px; bottom: 10px; left: 11px; width: 2px; background: var(--gray-200); z-index: 0;"></div>
                            
                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--success-light); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--success); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--success-light);">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Elena Marquez</strong> verified remittance</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">10m ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);"><span class="badge bg-success-light text-success" style="font-size: 0.7rem; padding: 2px 6px; margin-right: 6px;">Cashier</span> Amount: <strong>₱45,000.00</strong></div>
                                </div>
                            </div>
                            
                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--primary-100); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--primary-600); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--primary-100);">
                                    <i class="fas fa-ship"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Juan Dela Cruz</strong> logged arrival</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">25m ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Vessel <em>MV San Juan</em> docked at Pier 3.</div>
                                </div>
                            </div>

                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--warning-light); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--warning); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--warning-light);">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Maria Santos</strong> flagged stall</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">1h ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Stall #142 marked for maintenance.</div>
                                </div>
                            </div>
                            
                            <div class="timeline-item" style="position: relative; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--gray-200); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--gray-600); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--gray-200);">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>System</strong> auto-backup</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">2h ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Daily database backup completed successfully.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        tablesHtml = `
            <div class="card mt-4" style="border: none; box-shadow: var(--shadow-md);">
                <div class="card-header flex-between border-bottom">
                    <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Transactions</h3>
                    <button class="btn btn-outline btn-sm" onclick="navigateTo('transactions')">View All Ledger</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Department</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>#TRX-9982</strong></td>
                                    <td><span class="badge bg-primary-100 text-primary-700">Fishport</span></td>
                                    <td>Vessel Docking Fee</td>
                                    <td><strong>₱1,500.00</strong></td>
                                    <td><span class="badge bg-success-light text-success">Completed</span></td>
                                    <td>Today, 10:23 AM</td>
                                </tr>
                                <tr>
                                    <td><strong>#TRX-9981</strong></td>
                                    <td><span class="badge bg-success-light text-success">Public Market</span></td>
                                    <td>Stall Rental - Sec A</td>
                                    <td><strong>₱5,000.00</strong></td>
                                    <td><span class="badge bg-warning-light text-warning">Pending</span></td>
                                    <td>Today, 09:45 AM</td>
                                </tr>
                                <tr>
                                    <td><strong>#TRX-9980</strong></td>
                                    <td><span class="badge bg-warning-light text-warning">Terminal</span></td>
                                    <td>Bus Terminal Fee</td>
                                    <td><strong>₱250.00</strong></td>
                                    <td><span class="badge bg-success-light text-success">Completed</span></td>
                                    <td>Today, 09:12 AM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'fishport') {
        // Fishport uses a server-rendered Laravel dashboard (handled by renderPage guard above)
        // This block is a safety fallback only.
    } else if (currentUserRole === 'market') {
        // Public Market Personnel Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--success-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-store"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Active Stalls</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">142</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;"><i class="fas fa-check"></i> 95% Occupancy</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Registered Vendors</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">168</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">+3 new this month</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--warning); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending for Payment</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">15</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Requires sending to collector</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Market Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('market_records')">View All Ledger</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#MKT-2042</strong></td>
                                        <td>Stall Rental - Sec A (Jose Rizal)</td>
                                        <td><strong>₱5,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 11:45 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#MKT-2041</strong></td>
                                        <td>Arcabala Ticket - Elena Marquez</td>
                                        <td><strong>₱20.00</strong></td>
                                        <td><span class="badge bg-warning-light text-warning">Pending</span></td>
                                        <td>Today, 09:30 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#MKT-2040</strong></td>
                                        <td>Vendor Permit Fee - Maria Santos</td>
                                        <td><strong>₱1,200.00</strong></td>
                                        <td><span class="badge bg-info-light text-info">Sent</span></td>
                                        <td>Today, 08:15 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'cemetery') {
        // Cemetery Personnel Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--success-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-cross"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Available Niches</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">85</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Ready for occupation</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-book-journal-whills"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Services Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">4</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Scheduled burials</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-cash-register"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Direct Payments</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱8,500</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;">Collected today</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Cemetery Transactions</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('cemetery_transactions')">View All Ledger</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#CEM-3042</strong></td>
                                        <td>Burial Permit Fee - De La Cruz Family</td>
                                        <td><strong>₱300.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 09:15 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#CEM-3041</strong></td>
                                        <td>Apartment Niche Renewal - Santos Family</td>
                                        <td><strong>₱2,500.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 10:30 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'terminal') {
        // Terminal Personnel Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-bus"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Vehicles Logged Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">48</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;"><i class="fas fa-arrow-up"></i> 12% vs yesterday</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Terminal Fees Collected</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱12,450</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Estimated total</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--warning); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending for Payment</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">22</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Requires sending to collector</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Terminal Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('terminal_records')">View All Ledger</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#TRM-4042</strong></td>
                                        <td>Bus Terminal Fee - ABC-1234</td>
                                        <td><strong>₱250.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 11:15 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#TRM-4041</strong></td>
                                        <td>Van Terminal Fee - XYZ-9876</td>
                                        <td><strong>₱150.00</strong></td>
                                        <td><span class="badge bg-warning-light text-warning">Pending</span></td>
                                        <td>Today, 10:30 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#TRM-4040</strong></td>
                                        <td>Tricycle Fee - TR-001</td>
                                        <td><strong>₱20.00</strong></td>
                                        <td><span class="badge bg-info-light text-info">Sent</span></td>
                                        <td>Today, 09:45 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'atrium') {
        // Atrium Hall Personnel Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Upcoming Bookings</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">14</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;">Next 30 days</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Active Clients</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">120</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Registered profiles</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-hand-holding-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Collections Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱15,000</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Direct payments</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Atrium Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('atrium_records')">View All Ledger</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#ATR-5042</strong></td>
                                        <td>Hall Rental (Full) - DEPEd Seminar</td>
                                        <td><strong>₱10,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 10:15 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#ATR-5041</strong></td>
                                        <td>Sound System Rental - Wedding Reception</td>
                                        <td><strong>₱1,500.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 09:30 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'collector') {
        // Assigned Collector Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--warning-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning-600);"><i class="fas fa-clock"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending Collections</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">12</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Assigned to you</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-hand-holding-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Amount Collected Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱8,450</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Ready to remit</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-arrow-right-arrow-left"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Total Remitted Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱15,000</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Acknowledged by cashier</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Collections</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('collector_payments')">View History</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Source Area</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#COL-8842</strong></td>
                                        <td>Stall Rental - Sec A (Jose Rizal)</td>
                                        <td><strong>₱5,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Public Market</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#COL-8841</strong></td>
                                        <td>Bus Terminal Fee - ABC-1234</td>
                                        <td><strong>₱250.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Terminal</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (currentUserRole === 'cashier') {
        // Main Cashier Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--warning-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning-600);"><i class="fas fa-inbox"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending Remittances</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">8</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Awaiting verification</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-vault"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Verified Collections</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱45,200</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Official daily total</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Generated Reports</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">2</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;">Daily summaries</span>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Cashier Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('cashier_remittance')">View Pending</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Source / Collector</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#REM-9942</strong></td>
                                        <td>Roberto Gomez (Collector)</td>
                                        <td><strong>₱15,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Verified</span></td>
                                        <td>Today, 11:45 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#REM-9941</strong></td>
                                        <td>Terminal Personnel</td>
                                        <td><strong>₱8,200.00</strong></td>
                                        <td><span class="badge bg-warning-light text-warning">Pending Review</span></td>
                                        <td>Today, 10:30 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        // Generic Role Dashboard
        statsHtml = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-details">
                        <h3>My Records Today</h3>
                        <h2>24</h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-details">
                        <h3>Processed Transactions</h3>
                        <h2>18</h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-clock"></i></div>
                    <div class="stat-details">
                        <h3>Pending Tasks</h3>
                        <h2>6</h2>
                    </div>
                </div>
            </div>
        `;
        
        tablesHtml = `
            <div class="dashboard-grid mt-4">
                <div class="card col-span-2">
                    <div class="card-header flex-between">
                        <h3>My Recent Activities</h3>
                        <button class="btn btn-primary btn-sm" onclick="openAddRecordModal()"><i class="fas fa-plus"></i> New Entry</button>
                    </div>
                    <div class="card-body p-0">
                         <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h4>No recent activities</h4>
                            <p>You haven't added any records today.</p>
                            <button class="btn btn-primary mt-3" onclick="openAddRecordModal()">Add Record</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    contentArea.innerHTML = `
        ${statsHtml}
        ${chartsHtml}
        ${tablesHtml}
    `;

    // Initialize Chart if admin
    if (currentUserRole === 'administrator') {
        setTimeout(initDashboardChart, 100);
        
        // Add interactivity to the filter group
        setTimeout(() => {
            const filterBtns = document.querySelectorAll('.filter-group .btn');
            const statRev = document.getElementById('statRevenue');
            const statRevChange = document.getElementById('statRevenueChange');
            const statPend = document.getElementById('statPending');
            const statPendDesc = document.getElementById('statPendingDesc');
            const statTrans = document.getElementById('statTransactions');
            const statTransChange = document.getElementById('statTransactionsChange');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    filterBtns.forEach(b => {
                        b.classList.remove('active');
                        b.style.background = 'transparent';
                        b.style.boxShadow = 'none';
                        b.style.color = 'var(--gray-600)';
                        b.style.fontWeight = 'normal';
                    });
                    const target = e.target;
                    target.classList.add('active');
                    target.style.background = 'white';
                    target.style.boxShadow = 'var(--shadow-sm)';
                    target.style.color = 'var(--gray-800)';
                    target.style.fontWeight = '600';
                    
                    // Fade out
                    if(statRev) statRev.style.opacity = '0';
                    if(statRevChange) statRevChange.style.opacity = '0';
                    if(statPend) statPend.style.opacity = '0';
                    if(statPendDesc) statPendDesc.style.opacity = '0';
                    if(statTrans) statTrans.style.opacity = '0';
                    if(statTransChange) statTransChange.style.opacity = '0';

                    setTimeout(() => {
                        const filterType = target.getAttribute('data-filter');
                        
                        if(filterType === 'week') {
                            if(statRev) statRev.textContent = '₱864,250.00';
                            if(statRevChange) statRevChange.innerHTML = '<i class="fas fa-arrow-up"></i> 8% vs last week';
                            if(statPend) statPend.textContent = '42';
                            if(statPendDesc) statPendDesc.textContent = 'Overdue by 3 days';
                            if(statTrans) statTrans.textContent = '8,542';
                            if(statTransChange) statTransChange.innerHTML = '<i class="fas fa-arrow-up"></i> 4% vs last week';
                        } else if (filterType === 'month') {
                            if(statRev) statRev.textContent = '₱3,450,120.00';
                            if(statRevChange) statRevChange.innerHTML = '<i class="fas fa-arrow-up"></i> 15% vs last month';
                            if(statPend) statPend.textContent = '8';
                            if(statPendDesc) statPendDesc.textContent = 'In reconciliation';
                            if(statTrans) statTrans.textContent = '34,218';
                            if(statTransChange) statTransChange.innerHTML = '<i class="fas fa-arrow-up"></i> 11% vs last month';
                        } else {
                            if(statRev) statRev.textContent = '₱124,500.00';
                            if(statRevChange) statRevChange.innerHTML = '<i class="fas fa-arrow-up"></i> 12% vs yesterday';
                            if(statPend) statPend.textContent = '15';
                            if(statPendDesc) statPendDesc.textContent = 'Needs review';
                            if(statTrans) statTrans.textContent = '1,284';
                            if(statTransChange) statTransChange.innerHTML = '<i class="fas fa-arrow-up"></i> 5% vs yesterday';
                        }

                        // Fade in
                        if(statRev) statRev.style.opacity = '1';
                        if(statRevChange) statRevChange.style.opacity = '1';
                        if(statPend) statPend.style.opacity = '1';
                        if(statPendDesc) statPendDesc.style.opacity = '1';
                        if(statTrans) statTrans.style.opacity = '1';
                        if(statTransChange) statTransChange.style.opacity = '1';

                    }, 300);
                    
                    // Simulate chart update
                    initDashboardChart();
                });
            });
        }, 200);
    }
}

function getRecords() {
    return JSON.parse(localStorage.getItem('meedocentrix_records')) || [];
}

function saveRecords(records) {
    localStorage.setItem('meedocentrix_records', JSON.stringify(records));
}

window.sendToCollector = function(id) {
    let records = getRecords();
    let record = records.find(r => r.id == id);
    if(record) {
        record.sentToCollector = true;
        saveRecords(records);
        showToast('Sent to Collector successfully!', 'success');
        renderPage(currentPage);
    }
}

window.sendAllToCollector = function(collectorId) {
    if(!collectorId) {
        showToast('Please select a collector first.', 'error');
        return;
    }

    const collector = mockUsers.find(u => u.id == collectorId);
    if (!collector) return;

    if(!confirm(`Are you sure you want to send all pending transactions to ${collector.name} for collection?`)) {
        return;
    }

    let records = getRecords();
    let updated = false;
    records.forEach(r => {
        if(r.department === currentUserRole && !r.sentToCollector && r.status !== 'Collected') {
            r.sentToCollector = true;
            r.assignedCollectorId = collectorId;
            r.assignedCollectorName = collector.name;
            updated = true;
        }
    });
    if (updated) {
        saveRecords(records);
        showToast(`All records successfully forwarded to ${collector.name}!`, 'success');
        renderPage(currentPage);
    } else {
        showToast('No pending records to send.', 'info');
    }
}

window.markCollected = function(id) {
    let records = getRecords();
    let record = records.find(r => r.id == id);
    if(record) {
        record.status = 'Collected';
        saveRecords(records);
        showToast('Record collected successfully!', 'success');
        renderPage(currentPage);
    }
}

// ======================= COLLECTOR MODULES =======================

function renderPendingCollectionsPage() {
    let allRecords = getRecords();
    // Assuming currentUserRole === 'collector' and username is 'rgomez' based on ROLES config
    const currentUserId = '4'; // Roberto Gomez's ID
    
    let pendingRecords = allRecords.filter(r => r.sentToCollector && r.status !== 'Collected');

    let trHtml = '';
    if (pendingRecords.length === 0) {
        trHtml = `<tr><td colspan="6" style="text-align: center; padding: 3rem;"><i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>No pending collections assigned to you.</td></tr>`;
    } else {
        trHtml = pendingRecords.map(r => `
            <tr>
                <td><strong>#${r.id}</strong></td>
                <td><span class="badge bg-gray-100 text-gray-700" style="text-transform: capitalize;">${r.department}</span></td>
                <td>${r.title}</td>
                <td><strong>₱${parseFloat(r.amount).toFixed(2)}</strong></td>
                <td><span class="badge bg-warning-light text-warning">Pending Collection</span></td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="markCollected('${r.id}')"><i class="fas fa-hand-holding-dollar"></i> Collect</button>
                </td>
            </tr>
        `).join('');
    }

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--warning-600), var(--warning-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Pending Collections</h2>
                        <p style="opacity: 0.9; margin: 0;">Transactions forwarded to you by department personnel that require physical collection.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Assigned to You</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="pendingSearch" placeholder="Search ID or description..." onkeyup="filterPendingCollections()">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Source Dept.</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingCollectionsTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterPendingCollections = function() {
    const q = document.getElementById('pendingSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#pendingCollectionsTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const id = row.children[0].textContent.toLowerCase();
        const desc = row.children[2].textContent.toLowerCase();
        if (id.includes(q) || desc.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function renderCollectorPaymentsPage() {
    let allRecords = getRecords();
    const currentUserId = '4';
    
    // Payments that the collector has collected but not yet remitted
    let collectedRecords = allRecords.filter(r => r.status === 'Collected' && !r.remitted);
    
    let totalAmount = collectedRecords.reduce((sum, r) => sum + parseFloat(r.amount), 0);

    let trHtml = '';
    if (collectedRecords.length === 0) {
        trHtml = `<tr><td colspan="6" style="text-align: center; padding: 3rem;"><i class="fas fa-box-open text-muted fa-2x mb-2"></i><br>No collections to show.</td></tr>`;
    } else {
        trHtml = collectedRecords.map(r => `
            <tr>
                <td><strong>#${r.id}</strong></td>
                <td><span class="badge bg-gray-100 text-gray-700" style="text-transform: capitalize;">${r.department}</span></td>
                <td>${r.title}</td>
                <td>${r.dateCollected || r.date}</td>
                <td><strong>₱${parseFloat(r.amount).toFixed(2)}</strong></td>
                <td><span class="badge bg-success-light text-success"><i class="fas fa-check"></i> Received</span></td>
            </tr>
        `).join('');
    }

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--success-700), var(--success-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Received Payments</h2>
                        <p style="opacity: 0.9; margin: 0;">Log of physical cash collected from assigned area transactions.</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: var(--radius-md); text-align: center; border: 1px solid rgba(255,255,255,0.3);">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">Cash on Hand</div>
                        <div style="font-size: 1.5rem; font-weight: bold;">₱${totalAmount.toFixed(2)}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Collection History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Source Dept.</th>
                                <th>Description</th>
                                <th>Date Collected</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function renderRemitPage() {
    let allRecords = getRecords();
    const currentUserId = '4';
    
    // Get records ready to be remitted
    let unremittedRecords = allRecords.filter(r => r.status === 'Collected' && !r.remitted && r.sentToCollector);
    let remittedRecords = allRecords.filter(r => r.remitted);
    
    let totalUnremitted = unremittedRecords.reduce((sum, r) => sum + parseFloat(r.amount), 0);

    let recentRemittancesHtml = remittedRecords.length === 0 ? `<tr><td colspan="4" style="text-align: center; padding: 2rem;" class="text-muted">No past remittances found.</td></tr>` : 
        remittedRecords.slice(0, 5).map(r => `
            <tr>
                <td><strong>#REM-${r.id}</strong></td>
                <td>₱${parseFloat(r.amount).toFixed(2)}</td>
                <td>${r.remittedDate || 'Recently'}</td>
                <td><span class="badge bg-primary-100 text-primary-700">Verified by Cashier</span></td>
            </tr>
        `).join('');

    contentArea.innerHTML = `
        <div class="grid-1-2" style="grid-template-columns: 1fr 2fr;">
            <!-- Remittance Form Card -->
            <div class="card" style="align-self: start; border-top: 4px solid var(--primary-500);">
                <div class="card-header border-bottom">
                    <h3><i class="fas fa-arrow-right-arrow-left text-primary" style="margin-right: 8px;"></i> Remit to Cashier</h3>
                </div>
                <div class="card-body" style="padding: 2rem; text-align: center;">
                    <div style="background: var(--gray-50); border: 1px dashed var(--gray-200); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="color: var(--gray-500); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Total Ready to Remit</h4>
                        <h2 style="font-size: 2.5rem; color: var(--gray-800); margin: 0;">₱${totalUnremitted.toFixed(2)}</h2>
                        <p class="text-muted text-sm mt-2">${unremittedRecords.length} transactions included</p>
                    </div>

                    <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;" onclick="processRemittance()" ${unremittedRecords.length === 0 ? 'disabled' : ''}>
                        <i class="fas fa-file-invoice" style="margin-right: 8px;"></i> Submit Remittance Report
                    </button>
                    <p class="text-muted text-sm" style="margin-top: 1rem; line-height: 1.4;">Submitting will forward these collections to the Main Cashier for verification.</p>
                </div>
            </div>

            <!-- Past Remittances -->
            <div class="card">
                <div class="card-header flex-between border-bottom">
                    <div>
                        <h3>Recent Remittances</h3>
                        <p class="text-muted text-sm" style="margin-top: 4px;">History of your transfers to the Main Cashier.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Report ID</th>
                                    <th>Total Amount</th>
                                    <th>Date Remitted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="remittanceFeedBody">
                                ${recentRemittancesHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
}

window.processRemittance = function() {
    if(confirm('Are you sure you want to remit all currently collected funds to the Main Cashier?')) {
        let allRecords = getRecords();
        const currentUserId = '4';
        const now = new Date().toLocaleString();
        
        let updated = false;
        allRecords.forEach(r => {
            if (r.assignedCollectorId === currentUserId && r.status === 'Collected' && !r.remitted) {
                r.remitted = true;
                r.remittedDate = now;
                r.status = 'Remitted to Cashier';
                updated = true;
            }
        });

        if (updated) {
            saveRecords(allRecords);
            showToast('Remittance report submitted successfully!', 'success');
            renderPage('remit');
        }
    }
}

// Override markCollected specifically for collector flow
window.markCollected = function(id) {
    let records = getRecords();
    let record = records.find(r => r.id == id);
    if(record) {
        if(confirm(`Confirm physical cash collection of ₱${record.amount} for #${record.id}?`)) {
            record.status = 'Collected';
            record.dateCollected = new Date().toLocaleString();
            saveRecords(records);
            showToast('Payment collected successfully!', 'success');
            renderPage(currentPage);
        }
    }
}

function renderTablePage(title, headers) {
    let thHtml = `<th>ID</th><th>Title/Details</th><th>Amount (₱)</th><th>Date</th><th>Status</th><th>Actions</th>`;
    
    let allRecords = getRecords();
    let myRecords = [];
    
    if (currentUserRole === 'collector') {
        myRecords = allRecords.filter(r => r.sentToCollector === true && r.status !== 'Collected');
    } else if (currentUserRole === 'administrator') {
        myRecords = allRecords;
    } else {
        myRecords = allRecords.filter(r => r.department === currentUserRole);
    }

    let trHtml = '';
    if (myRecords.length === 0) {
        trHtml = `<tr><td colspan="6" style="text-align: center; padding: 2rem;">No records found</td></tr>`;
    } else {
        trHtml = myRecords.map(r => `
            <tr>
                <td>#${r.id}</td>
                <td>${r.title}</td>
                <td><strong>₱${parseFloat(r.amount).toFixed(2)}</strong></td>
                <td>${r.date}</td>
                <td><span class="badge bg-${r.status === 'Collected' ? 'success' : 'warning'}-light text-${r.status === 'Collected' ? 'success' : 'warning'}">${r.status}</span></td>
                <td>
                    ${currentUserRole === 'collector'
                        ? `<button class="btn btn-sm btn-success" onclick="markCollected('${r.id}')">Collect</button>`
                        : (currentUserRole === 'cemetery' 
                            ? `<span class="badge bg-gray-200 text-gray-700"><i class="fas fa-lock" style="margin-right: 4px;"></i> Recorded</span>`
                            : (r.sentToCollector ? `<span class="badge bg-info-light text-info">Sent</span>` : `<button class="btn btn-sm btn-primary" onclick="openSelectCollectorModal('${r.id}')">Send to Collector</button>`))}
                </td>
            </tr>
        `).join('');
    }

    const sendAllBtn = (currentUserRole !== 'collector' && currentUserRole !== 'administrator' && currentUserRole !== 'cemetery') ?
        `<button class="btn btn-success" onclick="openSelectCollectorModal('all')" style="margin-right: 8px;"><i class="fas fa-paper-plane"></i> Send All to Collector</button>` : '';
    const addBtn = (currentUserRole !== 'collector' && currentUserRole !== 'cemetery') ?
        `<button class="btn btn-primary" onclick="openAddRecordModal()"><i class="fas fa-plus"></i> Add New</button>` : '';
    contentArea.innerHTML = `
        <div class="card">
            <div class="card-header flex-between">
                <h3>${title}</h3>
                <div class="header-actions" style="display: flex;">
                    ${sendAllBtn}
                    ${addBtn}
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>${thHtml}</tr>
                        </thead>
                        <tbody>
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.openAddRecordModal = function() {
    modalTitle.textContent = 'Add New Record';
    modalBody.innerHTML = `
        <form id="addRecordForm" onsubmit="return handleAddRecord(event)">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Title / Identifier</label>
                <input type="text" id="recordTitle" class="form-control" placeholder="Enter details..." required style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: inherit;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Amount / Value (₱)</label>
                <input type="number" id="recordAmount" class="form-control" placeholder="0.00" required step="0.01" style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: inherit;">
            </div>
            <button type="submit" id="hiddenSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.onclick = () => {
        document.getElementById('hiddenSubmitBtn').click();
    };
    
    modalOverlay.style.display = 'flex';
}

window.handleAddRecord = function(event) {
    event.preventDefault();
    const title = document.getElementById('recordTitle').value;
    const amount = document.getElementById('recordAmount').value;
    
    let records = getRecords();
    let newRecord = {
        id: Date.now().toString().slice(-6),
        title: title,
        amount: amount,
        department: currentUserRole,
        date: new Date().toLocaleDateString(),
        status: 'Active',
        sentToCollector: false
    };
    
    records.push(newRecord);
    saveRecords(records);
    
    closeModal();
    showToast('Record added successfully!', 'success');
    renderPage(currentPage);
    return false;
}

window.renderAllTransactionsPage = function(activeDept = 'all') {
    let allRecords = getRecords();
    
    // Generate 20 mock records per department for a total of 100
    let hasPersonnel = allRecords.some(r => r.personnelName);
    if (allRecords.length < 100 || !hasPersonnel) {
        allRecords = [];
        let baseId = 1000;
        const depts = ['fishport', 'market', 'terminal', 'cemetery', 'atrium'];
        const titles = {
            fishport: ['Vessel Docking Fee', 'Banyera (Tuna)', 'Banyera (Galunggong)', 'Ice & Water Supply', 'Vessel Unloading'],
            market: ['Stall Rental - Dry Goods', 'Stall Rental - Wet Market', 'Arcabala Ticket', 'Vendor Permit Fee', 'Scale Calibration'],
            terminal: ['Bus Terminal Fee', 'Van Terminal Fee', 'Overnight Parking', 'Tricycle Fee', 'Baggage Fee'],
            cemetery: ['Apartment Niche', 'Burial Permit', 'Tomb Construction', 'Exhumation Permit', 'Niche Renewal'],
            atrium: ['Hall Rental (Full)', 'Hall Rental (Half)', 'Sound System', 'Tables & Chairs', 'Event Deposit']
        };
        const names = {
            fishport: ['Juan Dela Cruz', 'Luis Antonio', 'Andres Bonifacio', 'Miguel Malvar'],
            market: ['Maria Santos', 'Elena Marquez', 'Jose Rizal', 'Diego Silang'],
            terminal: ['Mario Lopez', 'Maria Clara', 'Emilio Aguinaldo', 'Juan Luna'],
            cemetery: ['Pedro Penduko', 'Apolinario Mabini', 'Gabriela Silang', 'Lapu-Lapu'],
            atrium: ['Clara Recto', 'Melchora Aquino', 'Antonio Luna', 'Marcelo H. del Pilar']
        };
        const statuses = ['Completed', 'Active', 'Pending', 'Collected'];
        
        for (let d of depts) {
            for (let i = 0; i < 20; i++) {
                baseId++;
                let title = titles[d][i % titles[d].length];
                let amount = (Math.floor(Math.random() * 50) + 1) * 50; 
                let status = statuses[Math.floor(Math.random() * statuses.length)];
                let sent = status === 'Collected' || status === 'Completed' || Math.random() > 0.5;
                let day = (Math.floor(Math.random() * 28) + 1).toString().padStart(2, '0');
                let personnelName = names[d][i % names[d].length];
                
                allRecords.push({
                    id: baseId.toString(),
                    department: d,
                    title: `${title} #${i + 1}`,
                    amount: amount,
                    date: `2026-03-${day}`,
                    status: status,
                    sentToCollector: sent,
                    personnelName: personnelName
                });
            }
        }
        
        // Sort descending so the highest IDs show at the top
        allRecords.sort((a, b) => b.id - a.id);
        saveRecords(allRecords);
    }

    const departments = [
        { id: 'all', name: 'All Departments', icon: 'fa-globe' },
        { id: 'fishport', name: 'Fishport', icon: 'fa-fish' },
        { id: 'market', name: 'Public Market', icon: 'fa-store' },
        { id: 'cemetery', name: 'Cemetery', icon: 'fa-cross' },
        { id: 'terminal', name: 'Terminal', icon: 'fa-bus' },
        { id: 'atrium', name: 'Atrium Hall', icon: 'fa-building' }
    ];

    const tabsHtml = departments.map(dept => `
        <button class="tab ${dept.id === activeDept ? 'active' : ''}" onclick="renderAllTransactionsPage('${dept.id}')">
            <i class="fas ${dept.icon}"></i> ${dept.name}
        </button>
    `).join('');

    let displayRecords = allRecords;
    if (activeDept !== 'all') {
        displayRecords = allRecords.filter(r => r.department === activeDept);
    }

    contentArea.innerHTML = `
        <div class="card">
            <div class="card-header flex-between">
                <div>
                    <h3>Master Transaction Ledger</h3>
                    <p class="text-muted text-sm" style="margin-top: 4px;">Centralized view of all records across departments.</p>
                </div>
                <button class="btn btn-primary" onclick="showToast('Exporting Ledger...', 'info')"><i class="fas fa-download"></i> Export Ledger</button>
            </div>
            <div class="tabs" style="margin-bottom: 0;">
                ${tabsHtml}
            </div>
            <div class="card-body p-0">
                <div class="table-controls">
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="ledgerSearch" onkeyup="applyLedgerFilters('${activeDept}')" placeholder="Search ID, details, personnel, or department...">
                    </div>
                    <div class="table-actions">
                        <select id="ledgerStatusFilter" class="form-control" style="padding: 8px 12px; height: 36px; font-size: 0.82rem;" onchange="applyLedgerFilters('${activeDept}')">
                            <option>All Status</option>
                            <option>Completed / Collected</option>
                            <option>Active / Pending</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Department</th>
                                <th>Description / Title</th>
                                <th>Personnel</th>
                                <th>Amount (₱)</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerTableBody">
                            ${window.generateLedgerRows(displayRecords)}
                        </tbody>
                    </table>
                </div>
                <div class="table-pagination">
                    <span id="ledgerPaginationText">Showing ${displayRecords.length > 0 ? '1' : '0'} to ${displayRecords.length} of ${displayRecords.length} entries</span>
                    <div class="pagination-btns">
                        <button disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="active">1</button>
                        <button disabled><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    `;
};

window.generateLedgerRows = function(records) {
    if(records.length === 0) {
         return `<tr><td colspan="8" style="text-align: center; padding: 2rem;">No transaction records found.</td></tr>`;
    }
    return records.map(r => {
        let deptName = r.department.charAt(0).toUpperCase() + r.department.slice(1);
        if (r.department === 'market') deptName = 'Public Market';
        if (r.department === 'atrium') deptName = 'Atrium Hall';
        
        let statusBadge = '';
        if(r.status === 'Completed' || r.status === 'Collected') statusBadge = `<span class="badge bg-success-light text-success" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">${r.status}</span>`;
        else if(r.status === 'Pending' || r.status === 'Active') statusBadge = `<span class="badge bg-warning-light text-warning" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">${r.status}</span>`;
        else statusBadge = `<span class="badge bg-gray-200 text-gray-700" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">${r.status}</span>`;

        let deptBadgeClass = 'bg-gray-100 text-gray-700';
        if(r.department === 'fishport') deptBadgeClass = 'bg-primary-100 text-primary-700';
        if(r.department === 'market') deptBadgeClass = 'bg-success-light text-success';
        if(r.department === 'terminal') deptBadgeClass = 'bg-warning-light text-warning';
        if(r.department === 'cemetery') deptBadgeClass = 'bg-gray-200 text-gray-700';
        if(r.department === 'atrium') deptBadgeClass = 'bg-info-light text-info';

        return `
            <tr>
                <td><strong>#TRX-${r.id}</strong></td>
                <td><span class="badge ${deptBadgeClass}" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;"><i class="fas fa-building" style="margin-right:4px;"></i>${deptName}</span></td>
                <td>${r.title}</td>
                <td>${r.personnelName || 'N/A'}</td>
                <td><strong>₱${parseFloat(r.amount).toFixed(2)}</strong></td>
                <td>${r.date}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon" title="Edit Record" onclick="openEditTransactionModal('${r.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" title="Delete Record" onclick="deleteTransaction('${r.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');
};

window.applyLedgerFilters = function(activeDept = 'all') {
    const searchVal = document.getElementById('ledgerSearch').value.toLowerCase();
    const statusVal = document.getElementById('ledgerStatusFilter').value;

    let allRecords = getRecords();
    
    let filtered = allRecords.filter(r => {
        let matchesSearch = r.id.toLowerCase().includes(searchVal) || 
                              r.title.toLowerCase().includes(searchVal) || 
                              r.department.toLowerCase().includes(searchVal) ||
                              r.amount.toString().includes(searchVal) ||
                              (r.personnelName && r.personnelName.toLowerCase().includes(searchVal));
        
        let matchesDept = activeDept === 'all' || r.department === activeDept;

        let matchesStatus = true;
        if (statusVal === 'Completed / Collected') {
            matchesStatus = (r.status === 'Completed' || r.status === 'Collected');
        } else if (statusVal === 'Active / Pending') {
            matchesStatus = (r.status === 'Active' || r.status === 'Pending');
        }

        return matchesSearch && matchesDept && matchesStatus;
    });

    document.getElementById('ledgerTableBody').innerHTML = window.generateLedgerRows(filtered);
    
    const paginationText = document.getElementById('ledgerPaginationText');
    if (paginationText) {
        paginationText.textContent = `Showing ${filtered.length > 0 ? '1' : '0'} to ${filtered.length} of ${filtered.length} entries (filtered from ${allRecords.length})`;
    }
};

window.openEditTransactionModal = function(id) {
    let records = getRecords();
    let record = records.find(r => r.id == id);
    if(!record) return;

    modalTitle.textContent = 'Edit Transaction Record';
    modalBody.innerHTML = `
        <form id="editRecordForm" onsubmit="event.preventDefault(); saveEditedTransaction('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group full-width">
                    <label>Description / Title</label>
                    <input type="text" id="editRecordTitle" class="form-control" value="${record.title}" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select id="editRecordDept" class="form-control" required>
                        <option value="fishport" ${record.department === 'fishport' ? 'selected' : ''}>Fishport</option>
                        <option value="market" ${record.department === 'market' ? 'selected' : ''}>Public Market</option>
                        <option value="terminal" ${record.department === 'terminal' ? 'selected' : ''}>Terminal</option>
                        <option value="cemetery" ${record.department === 'cemetery' ? 'selected' : ''}>Cemetery</option>
                        <option value="atrium" ${record.department === 'atrium' ? 'selected' : ''}>Atrium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (₱)</label>
                    <input type="number" id="editRecordAmount" class="form-control" value="${record.amount}" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="text" id="editRecordDate" class="form-control" value="${record.date}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editRecordStatus" class="form-control" required>
                        <option value="Active" ${record.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Pending" ${record.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Collected" ${record.status === 'Collected' ? 'selected' : ''}>Collected</option>
                        <option value="Completed" ${record.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditRecordSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditRecordSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedTransaction = function(id) {
    let records = getRecords();
    let index = records.findIndex(r => r.id == id);
    if(index > -1) {
        records[index].title = document.getElementById('editRecordTitle').value;
        records[index].department = document.getElementById('editRecordDept').value;
        records[index].amount = document.getElementById('editRecordAmount').value;
        records[index].date = document.getElementById('editRecordDate').value;
        records[index].status = document.getElementById('editRecordStatus').value;
        
        saveRecords(records);
        closeModal();
        showToast('Transaction successfully updated!', 'success');
        
        if(currentPage === 'transactions') {
            renderAllTransactionsPage();
        } else {
            renderPage(currentPage);
        }
    }
}

window.deleteTransaction = function(id) {
    if(confirm('Are you sure you want to delete this transaction record? This action cannot be undone.')) {
        let records = getRecords();
        records = records.filter(r => r.id != id);
        saveRecords(records);
        showToast('Transaction deleted successfully.', 'success');
        
        if(currentPage === 'transactions') {
            renderAllTransactionsPage();
        } else {
            renderPage(currentPage);
        }
    }
}

function renderUserManagementPage() {
    let trHtml = mockUsers.map(u => `
        <tr>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-100); color: var(--primary-700); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                        ${u.name.split(' ').map(n => n[0]).join('')}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--gray-800);">${u.name}</div>
                        <div style="font-size: 0.75rem; color: var(--gray-500);">@${u.username}</div>
                    </div>
                </div>
            </td>
            <td><span class="badge bg-gray-100 text-gray-700" style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;"><i class="fas fa-shield-halved" style="margin-right: 4px;"></i>${u.role}</span></td>
            <td><span class="status-badge ${u.status.toLowerCase()}">${u.status}</span></td>
            <td>${u.lastLogin}</td>
            <td>
                ${['Fishport Personnel', 'Public Market Personnel', 'Terminal Personnel'].includes(u.role) ? `<button class="btn btn-icon" title="Assign Collectors" onclick="openEditUserModal(${u.id})"><i class="fas fa-user-tag text-primary"></i></button>` : ''}
                <button class="btn btn-icon" title="Edit User" onclick="openEditUserModal(${u.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-icon" title="Reset Password"><i class="fas fa-key"></i></button>
                <button class="btn btn-icon text-danger" title="Delete User"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `).join('');

    contentArea.innerHTML = `
        <div class="card">
            <div class="card-header flex-between">
                <div>
                    <h3>System Users</h3>
                    <p class="text-muted text-sm" style="margin-top: 4px;">Manage system access, roles, and user accounts.</p>
                </div>
                <button class="btn btn-primary" onclick="openAddUserModal()"><i class="fas fa-user-plus"></i> Add New User</button>
            </div>
            <div class="card-body p-0">
                <div class="table-controls">
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search by name or username...">
                    </div>
                    <div class="table-actions">
                        <select class="form-control" style="padding: 8px 12px; height: 36px; font-size: 0.82rem;">
                            <option>All Roles</option>
                            <option>Administrator</option>
                            <option>Fishport Personnel</option>
                            <option>Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User Info</th>
                                <th>Assigned Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.openEditUserModal = function(userId) {
    const user = mockUsers.find(u => u.id === userId);
    if (!user) return;

    const isAssignable = ['Fishport Personnel', 'Public Market Personnel', 'Terminal Personnel'].includes(user.role);
    let collectorSectionHtml = '';

    if (isAssignable) {
        const allCollectors = mockUsers.filter(u => u.role === 'Assigned Collector');
        const assignedCollectorIds = user.assignedCollectors || [];
        const assignedCollectors = allCollectors.filter(c => assignedCollectorIds.includes(c.id));
        const availableCollectors = allCollectors.filter(c => !assignedCollectorIds.includes(c.id) && c.status === 'Active');

        const assignedCollectorsHtml = assignedCollectors.map(c => `
            <div class="assigned-collector-item" data-collector-id="${c.id}">
                <span><i class="fas fa-user-check" style="margin-right: 8px; color: var(--success);"></i>${c.name}</span>
                <button type="button" class="btn-remove-collector" onclick="removeCollectorFromAssignmentList(this)">&times;</button>
            </div>
        `).join('');

        const availableCollectorsOptions = availableCollectors.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

        collectorSectionHtml = `
            <hr style="margin: 24px 0; border-color: var(--gray-100);">
            <div class="form-group full-width">
                <label style="font-weight: 700; color: var(--gray-800); margin-bottom: 12px;">Assigned Collectors (Max 4)</label>
                <div id="assignedCollectorsList">
                    ${assignedCollectorsHtml.length > 0 ? assignedCollectorsHtml : '<p class="text-muted text-sm" style="padding: 8px 0;">No collectors assigned yet.</p>'}
                </div>
                <div class="input-group mt-3" id="addCollectorGroup">
                    <select id="collectorSelect" class="form-control">
                        <option value="">Select a collector to add...</option>
                        ${availableCollectorsOptions}
                    </select>
                    <button type="button" class="btn btn-outline" id="addCollectorBtn">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <small class="text-muted" id="collectorLimitWarning" style="display: none; color: var(--danger); margin-top: 8px; font-weight: 500;">Maximum of 4 collectors reached.</small>
            </div>
        `;
    }

    modalTitle.textContent = 'Edit User Details';
    modalBody.innerHTML = `
        <form id="editUserForm" onsubmit="event.preventDefault(); saveUserChanges(${userId});">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="editUserName" class="form-control" value="${user.name}" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="editUserUsername" class="form-control" value="${user.username}" required>
                </div>
            </div>
            <div class="form-group">
                <label>System Role</label>
                <input type="text" class="form-control" value="${user.role}" disabled style="background: var(--gray-100); cursor: not-allowed;">
            </div>
            
            ${collectorSectionHtml}

            <button type="submit" id="hiddenEditUserSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditUserSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';

    if (isAssignable) {
        // Attach event listener programmatically to avoid potential issues with innerHTML
        const addBtn = document.getElementById('addCollectorBtn');
        if (addBtn) {
            addBtn.addEventListener('click', addCollectorToAssignmentList);
        }
        updateCollectorAssignmentUI();
    }
}

window.addCollectorToAssignmentList = function() {
    const select = document.getElementById('collectorSelect');
    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const collectorId = selectedOption.value;
    const collectorName = selectedOption.text;

    const placeholder = document.querySelector('#assignedCollectorsList .text-muted');
    if (placeholder) placeholder.remove();

    const newItem = document.createElement('div');
    newItem.className = 'assigned-collector-item';
    newItem.dataset.collectorId = collectorId;
    newItem.innerHTML = `
        <span><i class="fas fa-user-check" style="margin-right: 8px; color: var(--success);"></i>${collectorName}</span>
        <button type="button" class="btn-remove-collector" onclick="removeCollectorFromAssignmentList(this)">&times;</button>
    `;
    document.getElementById('assignedCollectorsList').appendChild(newItem);
    
    selectedOption.remove();
    select.selectedIndex = 0;
    updateCollectorAssignmentUI();
}

window.removeCollectorFromAssignmentList = function(button) {
    const item = button.parentElement;
    const collectorId = parseInt(item.dataset.collectorId);
    const collector = mockUsers.find(u => u.id === collectorId);
    
    // Only add back to the dropdown if the collector is currently Active
    if (collector && collector.status === 'Active') {
        const select = document.getElementById('collectorSelect');
        const newOption = new Option(collector.name, collector.id);
        select.add(newOption);
    }
    
    item.remove();
    
    const list = document.getElementById('assignedCollectorsList');
    if (list.children.length === 0) {
        list.innerHTML = '<p class="text-muted text-sm" style="padding: 8px 0;">No collectors assigned yet.</p>';
    }
    updateCollectorAssignmentUI();
}

window.updateCollectorAssignmentUI = function() {
    const list = document.getElementById('assignedCollectorsList');
    const addGroup = document.getElementById('addCollectorGroup');
    const warning = document.getElementById('collectorLimitWarning');
    if (!list || !addGroup || !warning) return;

    const assignedCount = list.getElementsByClassName('assigned-collector-item').length;

    if (assignedCount >= 4) {
        addGroup.style.display = 'none';
        warning.style.display = 'block';
    } else {
        addGroup.style.display = 'flex';
        warning.style.display = 'none';
    }
}

window.saveUserChanges = function(userId) {
    const user = mockUsers.find(u => u.id === userId);
    if (!user) return;

    user.name = document.getElementById('editUserName').value;
    user.username = document.getElementById('editUserUsername').value;

    const isAssignable = ['Fishport Personnel', 'Public Market Personnel', 'Terminal Personnel'].includes(user.role);
    if (isAssignable) {
        const collectorItems = document.querySelectorAll('#assignedCollectorsList .assigned-collector-item');
        user.assignedCollectors = Array.from(collectorItems).map(item => parseInt(item.dataset.collectorId));
    }

    closeModal();
    showToast('User details updated successfully!', 'success');
    renderUserManagementPage();
}

window.openAddUserModal = function() {
    modalTitle.textContent = 'Register New User';
    
    modalBody.innerHTML = `
        <form id="addUserForm" onsubmit="event.preventDefault(); showToast('User account successfully created!', 'success'); closeModal();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" placeholder="e.g., Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" placeholder="e.g., jdelacruz" required>
                </div>
                <div class="form-group full-width">
                    <label>Email Address (Optional)</label>
                    <input type="email" class="form-control" placeholder="juan@example.com">
                </div>
                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="password" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" placeholder="Confirm password" required>
                </div>
                <div class="form-group full-width">
                    <label>System Role & Access Level</label>
                    <select class="form-control" required>
                        <option value="" disabled selected>Select a role...</option>
                        <option value="fishport">Fishport Personnel</option>
                        <option value="market">Public Market Personnel</option>
                        <option value="cemetery">Cemetery Personnel</option>
                        <option value="terminal">Terminal Personnel</option>
                        <option value="atrium">Atrium Hall Personnel</option>
                        <option value="collector">Assigned Collector</option>
                        <option value="cashier">Main Cashier</option>
                    </select>
                    <small class="text-muted" style="display: block; margin-top: 6px; font-size: 0.75rem;"><i class="fas fa-info-circle"></i> This dictates which department the user has access to upon login.</small>
                </div>
            </div>
            <button type="submit" id="hiddenUserSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Create User';
    submitBtn.onclick = () => document.getElementById('hiddenUserSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

function renderRatesPage(activeDept = 'fishport') {
    const departments = [
        { id: 'fishport', name: 'Fishport', icon: 'fa-fish' },
        { id: 'market', name: 'Public Market', icon: 'fa-store' },
        { id: 'cemetery', name: 'Cemetery', icon: 'fa-cross' },
        { id: 'terminal', name: 'Terminal', icon: 'fa-bus' },
        { id: 'atrium', name: 'Atrium Hall', icon: 'fa-building' },
    ];

    const tabsHtml = departments.map(dept => `
        <button class="tab ${dept.id === activeDept ? 'active' : ''}" onclick="renderRatesPage('${dept.id}')">
            <i class="fas ${dept.icon}"></i> ${dept.name}
        </button>
    `).join('');

    const ratesForDept = mockRates[activeDept] || [];
    const tableRowsHtml = ratesForDept.map(rate => `
        <tr>
            <td>${rate.name}</td>
            <td>${rate.basis}</td>
            <td><strong>₱${parseFloat(rate.rate).toFixed(2)}</strong></td>
            <td>
                <button class="btn btn-icon" title="Edit Rate" onclick="openRateModal('${activeDept}', '${rate.id}')"><i class="fas fa-edit"></i></button>
                <button class="btn btn-icon text-danger" title="Delete Rate" onclick="deleteRate('${activeDept}', '${rate.id}')"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `).join('');

    const tableHtml = ratesForDept.length > 0 ? `
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item / Service Description</th>
                        <th>Basis of Rate</th>
                        <th>Amount (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRowsHtml}
                </tbody>
            </table>
        </div>
    ` : `
        <div class="empty-state">
            <i class="fas fa-tags"></i>
            <h4>No rates defined</h4>
            <p>There are no rates set for this department yet.</p>
        </div>
    `;

    contentArea.innerHTML = `
        <div class="card">
            <div class="card-header flex-between">
                <div>
                    <h3>Rates & Fees Matrix</h3>
                    <p class="text-muted text-sm" style="margin-top: 4px;">Set the formulas and rates for each economic enterprise.</p>
                </div>
                <button class="btn btn-primary" onclick="openRateModal('${activeDept}')">
                    <i class="fas fa-plus"></i> Add New Rate
                </button>
            </div>
            <div class="tabs">
                ${tabsHtml}
            </div>
            <div class="card-body p-0">
                ${tableHtml}
            </div>
        </div>
    `;
}

window.openRateModal = function(department, rateId = null) {
    const isEditing = rateId !== null;
    const rate = isEditing ? mockRates[department].find(r => r.id === rateId) : {};
    
    modalTitle.textContent = isEditing ? 'Edit Rate' : 'Add New Rate';
    
    modalBody.innerHTML = `
        <form id="rateForm" onsubmit="event.preventDefault(); saveRate('${department}', ${isEditing ? `'${rateId}'` : 'null'});">
            <div class="form-group">
                <label>Item / Service Description</label>
                <input type="text" id="rateName" class="form-control" placeholder="e.g., Banyera - Small" value="${rate.name || ''}" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Basis of Rate</label>
                    <input type="text" id="rateBasis" class="form-control" placeholder="e.g., per banyera" value="${rate.basis || ''}" required>
                </div>
                <div class="form-group">
                    <label>Amount (₱)</label>
                    <input type="number" id="rateAmount" class="form-control" placeholder="0.00" step="0.01" value="${rate.rate || ''}" required>
                </div>
            </div>
            <button type="submit" id="hiddenRateSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = isEditing ? 'Save Changes' : 'Add Rate';
    submitBtn.onclick = () => document.getElementById('hiddenRateSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveRate = function(department, rateId) {
    const isEditing = rateId !== null;
    const name = document.getElementById('rateName').value;
    const basis = document.getElementById('rateBasis').value;
    const rate = document.getElementById('rateAmount').value;

    if (!name || !basis || !rate) {
        showToast('Please fill all fields.', 'error');
        return;
    }

    if (isEditing) {
        // Update existing rate
        const rateIndex = mockRates[department].findIndex(r => r.id === rateId);
        if (rateIndex > -1) {
            mockRates[department][rateIndex] = { ...mockRates[department][rateIndex], name, basis, rate: parseFloat(rate) };
        }
    } else {
        // Add new rate
        const newRate = {
            id: department.substring(0, 3) + Date.now().toString().slice(-4),
            name,
            basis,
            rate: parseFloat(rate)
        };
        mockRates[department].push(newRate);
    }

    closeModal();
    showToast(`Rate successfully ${isEditing ? 'updated' : 'added'}!`, 'success');
    renderRatesPage(department);
}

window.deleteRate = function(department, rateId) {
    // A simple confirmation for demo purposes
    if (confirm('Are you sure you want to delete this rate? This action cannot be undone.')) {
        const rateIndex = mockRates[department].findIndex(r => r.id === rateId);
        if (rateIndex > -1) {
            mockRates[department].splice(rateIndex, 1);
            showToast('Rate deleted successfully.', 'success');
            renderRatesPage(department);
        }
    }
}

function renderRolesPage() {
    contentArea.innerHTML = `
        <div class="grid-1-2">
            <div class="card">
                <div class="card-header flex-between">
                    <h3>System Roles</h3>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div class="card-body p-0">
                    <div class="role-list">
                        <div class="role-item active">
                            <div class="role-info">
                                <span class="role-name">Administrator</span>
                                <span class="badge bg-primary-100 text-primary-700" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Full Access</span>
                            </div>
                            <i class="fas fa-chevron-right text-primary-400"></i>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Fishport Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Public Market Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Cemetery Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Terminal Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Assigned Collector</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Main Cashier</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header flex-between">
                    <div>
                        <h3>Permissions: Administrator</h3>
                        <p class="text-muted text-sm mt-1">Configure access levels for this role.</p>
                    </div>
                    <span class="status-badge active" style="font-size: 0.75rem;">System Role</span>
                </div>
                <div class="card-body bg-gray-50" style="background: var(--gray-50);">
                    <div class="permissions-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        
                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--primary-50); color: var(--primary-400); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-chart-pie"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">Dashboard & Analytics</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Main Dashboard</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Revenue Analytics</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Export Reports</span></label>
                        </div>

                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--success-light); color: var(--success); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-users-gear"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">User Management</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Users Directory</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Add New Users</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Edit User Details</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Delete/Suspend Users</span></label>
                        </div>

                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--warning-light); color: var(--warning); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-tags"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">System Configuration</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Manage Roles & Permissions</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Manage Rates & Fees Matrix</span></label>
                        </div>

                    </div>
                </div>
                <div class="card-footer" style="padding: 16px 22px; border-top: 1px solid var(--gray-100); display: flex; justify-content: flex-end; gap: 10px; background: white;">
                    <button class="btn btn-secondary">Discard Changes</button>
                    <button class="btn btn-primary" onclick="showToast('Permissions saved successfully!', 'success')"><i class="fas fa-save"></i> Save Permissions</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderRemittancePage() {
    contentArea.innerHTML = `
        <div class="stats-grid mb-4">
             <div class="stat-card" style="background: var(--primary-900); color: white;">
                <div class="stat-icon text-white" style="background: rgba(255,255,255,0.2);"><i class="fas fa-vault"></i></div>
                <div class="stat-details">
                    <h3 style="color: var(--primary-100);">Total Verified Today</h3>
                    <h2 style="color: white;">₱84,200.00</h2>
                </div>
            </div>
            <div class="stat-card" style="border: 1px solid var(--warning);">
                <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-clock"></i></div>
                <div class="stat-details">
                    <h3>Pending Remittances</h3>
                    <h2>₱12,500.00</h2>
                    <span class="text-warning">3 collectors waiting</span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-bottom">
                <h3>Incoming Remittances</h3>
                <p class="text-muted text-sm mt-1">Review and verify funds submitted by collectors and department personnel.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Collector / Personnel</th>
                                <th>Source Area</th>
                                <th>Declared Amount</th>
                                <th>Time Submitted</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Roberto Gomez</strong></td>
                                <td>Public Market</td>
                                <td><strong>₱5,500.00</strong></td>
                                <td>10:45 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Roberto Gomez', '5500.00', 'Public Market')">Verify Funds</button></td>
                            </tr>
                            <tr>
                                <td><strong>Luis Antonio</strong></td>
                                <td>Fishport</td>
                                <td><strong>₱4,000.00</strong></td>
                                <td>11:15 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Luis Antonio', '4000.00', 'Fishport')">Verify Funds</button></td>
                            </tr>
                            <tr>
                                <td><strong>Mario Lopez</strong></td>
                                <td>Terminal</td>
                                <td><strong>₱3,000.00</strong></td>
                                <td>11:30 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Mario Lopez', '3000.00', 'Terminal')">Verify Funds</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.showVerificationModal = function(name, amount, area) {
    modalTitle.textContent = 'Verify Remittance Funds';
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 1rem 0;">
            <div style="width: 60px; height: 60px; background: var(--primary-50); color: var(--primary-600); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.5rem auto;">
                <i class="fas fa-handshake"></i>
            </div>
            <h4 style="margin-bottom: 0.5rem; color: var(--gray-800);">Confirming Receipt from ${name}</h4>
            <p class="text-muted">Please confirm that the physical cash matches the declared amount for the ${area} collection.</p>
            
            <div style="background: var(--gray-50); border-radius: 8px; padding: 1.5rem; margin: 1.5rem 0; border: 1px dashed var(--gray-300);">
                <div style="font-size: 0.85rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem;">Declared Total</div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-700);">₱${parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
            </div>

            <div class="form-group" style="text-align: left;">
                <label style="font-weight: 600; font-size: 0.85rem;">Official Receipt / Reference No.</label>
                <input type="text" id="officialRef" class="form-control" placeholder="Enter OR number..." required>
            </div>
        </div>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Verify & Log to Vault';
    submitBtn.onclick = () => {
        const ref = document.getElementById('officialRef').value;
        if(!ref) {
            showToast('Please enter an Official Receipt number.', 'error');
            return;
        }
        showToast(`Remittance from ${name} verified and logged!`, 'success');
        closeModal();
        renderRemittancePage();
    };
    
    modalOverlay.style.display = 'flex';
}

function renderOfficialCollectionsPage() {
    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-800), var(--primary-600)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Official Collection Vault</h2>
                        <p style="opacity: 0.9; margin: 0;">Master record of all verified and confirmed remittances for the current fiscal period.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);" onclick="showToast('Exporting collection logs...', 'info')"><i class="fas fa-file-export"></i> Export Logs</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between border-bottom">
                <h3>Verified Transaction History</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search reference or personnel...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>OR Number</th>
                                <th>Source / Collector</th>
                                <th>Department</th>
                                <th>Amount (₱)</th>
                                <th>Date Verified</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#OR-2026-0042</strong></td>
                                <td>Roberto Gomez</td>
                                <td>Public Market</td>
                                <td><strong>₱15,000.00</strong></td>
                                <td>Today, 09:15 AM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                            <tr>
                                <td><strong>#OR-2026-0041</strong></td>
                                <td>Clara Recto</td>
                                <td>Atrium Hall</td>
                                <td><strong>₱10,000.00</strong></td>
                                <td>Today, 08:45 AM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                            <tr>
                                <td><strong>#OR-2026-0040</strong></td>
                                <td>Luis Antonio</td>
                                <td>Fishport</td>
                                <td><strong>₱20,200.00</strong></td>
                                <td>Yesterday, 04:30 PM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function renderDailySummaryPage() {
    contentArea.innerHTML = `
        <div class="grid-1-2" style="grid-template-columns: 1fr 2fr;">
            <!-- Summary Generator Card -->
            <div class="card" style="align-self: start; border-top: 4px solid var(--primary-500);">
                <div class="card-header border-bottom">
                    <h3><i class="fas fa-file-contract text-primary" style="margin-right: 8px;"></i> Collection Summary</h3>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <p class="text-muted text-sm mb-4">Consolidate all verified collections for today and generate an official summary report.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-weight: 600; font-size: 0.85rem;">Select Report Date</label>
                        <input type="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    </div>

                    <div style="background: var(--gray-50); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="text-muted text-sm">Total Remittances</span>
                            <span style="font-weight: 600;">18 Reports</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="text-muted text-sm">Direct Collections</span>
                            <span style="font-weight: 600;">₱18,500.00</span>
                        </div>
                        <hr style="margin: 8px 0; border-color: var(--gray-200);">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 700; color: var(--gray-800);">Consolidated Total</span>
                            <span style="font-weight: 700; color: var(--primary-700);">₱56,700.00</span>
                        </div>
                    </div>

                    <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;" onclick="simulateReportGeneration('Daily Collection Summary', 'collection')">
                        <i class="fas fa-magic" style="margin-right: 8px;"></i> Generate Summary
                    </button>
                </div>
            </div>

            <!-- Breakdown by Department -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3>Daily Revenue Breakdown</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; gap: 1rem;">
                        <!-- Dept 1: Fishport -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--primary-100); color: var(--primary-600); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-fish"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Fishport</div>
                                <div class="text-muted text-sm">4 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱20,200.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">35.6% of total</div>
                            </div>
                        </div>
                        <!-- Dept 2: Public Market -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--success-light); color: var(--success); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-store"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Public Market</div>
                                <div class="text-muted text-sm">5 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱15,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">26.5% of total</div>
                            </div>
                        </div>
                        <!-- Dept 3: Terminal -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--warning-light); color: var(--warning); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-bus"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Public Terminal</div>
                                <div class="text-muted text-sm">6 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱8,500.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">15.0% of total</div>
                            </div>
                        </div>
                        <!-- Dept 4: Cemetery -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--gray-200); color: var(--gray-700); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-cross"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Cemetery</div>
                                <div class="text-muted text-sm">Direct collections</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱3,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">5.3% of total</div>
                            </div>
                        </div>
                        <!-- Dept 5: Atrium Hall -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--info-light); color: var(--info); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-building"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Atrium Hall</div>
                                <div class="text-muted text-sm">Direct collections</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱10,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">17.6% of total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderBookingPage() {
    contentArea.innerHTML = `
        <div class="card">
            <div class="card-header flex-between">
                <h3>Atrium Hall Booking Calendar</h3>
                <button class="btn btn-primary" onclick="openModal('New Booking', 'Enter client details and schedule.')"><i class="fas fa-calendar-plus"></i> New Booking</button>
            </div>
            <div class="card-body">
                <div class="calendar-wrapper" style="min-height: 400px; display: flex; align-items: center; justify-content: center; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <div style="text-align: center;">
                        <i class="fas fa-calendar-alt fa-3x mb-3" style="color: var(--gray-300);"></i>
                        <h4 style="color: var(--gray-600);">Calendar View Component</h4>
                        <p class="text-muted text-sm">Interactive calendar will be rendered here.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Upcoming Reservations</h3>
            </div>
            <div class="card-body p-0">
                 <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Event Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>San Juan High School</td>
                            <td>Graduation Ball</td>
                            <td>Mar 25, 2026</td>
                            <td><span class="badge bg-success-light text-success">Confirmed</span></td>
                            <td><span class="badge bg-success-light text-success">Paid</span></td>
                        </tr>
                        <tr>
                            <td>Reyes Family</td>
                            <td>Wedding Reception</td>
                            <td>Apr 02, 2026</td>
                            <td><span class="badge bg-warning-light text-warning">Tentative</span></td>
                            <td><span class="badge bg-warning-light text-warning">Downpayment</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderReportsPage() {
    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-800), var(--primary-600)); color: white; border: none;">
            <div class="card-body" style="padding: 2.5rem 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.8rem;">Reports & Analytics Studio</h2>
                        <p style="opacity: 0.9; max-width: 600px; line-height: 1.5;">Generate comprehensive insights, export department records, and audit system activities. Select a report type below to configure and download your data.</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: var(--radius-md); backdrop-filter: blur(10px);">
                        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem;">Total Records Available</div>
                        <div style="font-size: 1.5rem; font-weight: 700;"><i class="fas fa-database" style="margin-right: 8px; font-size: 1.2rem;"></i>1,284</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            
            <!-- Report Card 1 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--primary-50); color: var(--primary-600); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">Collection Summary</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Consolidated summary of all financial collections within a specific date range.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="form-control" style="flex: 1;" title="Start Date">
                            <input type="date" class="form-control" style="flex: 1;" title="End Date">
                        </div>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="simulateReportGeneration('Collection Summary Report', 'collection')">
                        <i class="fas fa-magic" style="margin-right: 8px;"></i> Generate Report
                    </button>
                </div>
            </div>

            <!-- Report Card 2 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--success-light); color: var(--success); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">Department Records</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Export detailed transaction ledgers and logs specific to a department.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Select Target</label>
                        <select class="form-control" style="width: 100%;">
                            <option>All Departments</option>
                            <option>Fishport</option>
                            <option>Public Market</option>
                            <option>Cemetery</option>
                            <option>Terminal</option>
                            <option>Atrium Hall</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; justify-content: center; background: var(--success); border-color: var(--success);" onclick="simulateReportGeneration('Department Ledger (CSV)', 'department')">
                        <i class="fas fa-file-csv" style="margin-right: 8px;"></i> Export to CSV
                    </button>
                </div>
            </div>

            <!-- Report Card 3 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--warning-light); color: var(--warning); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">System Audit Trail</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Review system access logs, role changes, and record modifications for security.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Filter by Action Type</label>
                        <select class="form-control" style="width: 100%;">
                            <option>All Actions</option>
                            <option>User Logins</option>
                            <option>Record Additions</option>
                            <option>Updates & Deletions</option>
                        </select>
                    </div>
                    <button class="btn btn-outline" style="width: 100%; justify-content: center; color: var(--gray-700); border-color: var(--gray-300);" onclick="simulateReportGeneration('System Audit Trail', 'audit')">
                        <i class="fas fa-search" style="margin-right: 8px;"></i> Query Logs
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Generation Overlay (Hidden by default) -->
        <div id="reportGenerationOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255, 255, 255, 0.9); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            
            <div style="background: white; padding: 3rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); text-align: center; max-width: 400px; width: 90%;">
                
                <!-- Animated Icon Container -->
                <div style="position: relative; width: 80px; height: 80px; margin: 0 auto 2rem auto;">
                    <!-- Outer spinning ring -->
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 4px solid var(--primary-100); border-top-color: var(--primary-600); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <!-- Inner static icon -->
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: var(--primary-600); font-size: 2rem;">
                        <i class="fas fa-file-invoice" id="reportGenIcon"></i>
                    </div>
                </div>

                <h3 id="reportGenTitle" style="color: var(--gray-800); margin-bottom: 0.5rem; font-size: 1.25rem;">Compiling Data...</h3>
                <p id="reportGenDesc" style="color: var(--gray-500); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5;">Please wait while we gather the necessary records for your report.</p>
                
                <!-- Progress Bar -->
                <div style="width: 100%; height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden; margin-bottom: 1rem;">
                    <div id="reportGenProgress" style="height: 100%; width: 0%; background: linear-gradient(90deg, var(--primary-400), var(--primary-600)); transition: width 0.3s ease;"></div>
                </div>
                
                <div id="reportGenStatusText" style="font-size: 0.75rem; color: var(--gray-400); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">0%</div>
            </div>

            <style>
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                @keyframes pulse-success { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
                .report-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
            </style>
        </div>
    `;
}

window.simulateReportGeneration = function(reportName, type) {
    const overlay = document.getElementById('reportGenerationOverlay');
    const title = document.getElementById('reportGenTitle');
    const desc = document.getElementById('reportGenDesc');
    const progress = document.getElementById('reportGenProgress');
    const statusText = document.getElementById('reportGenStatusText');
    const icon = document.getElementById('reportGenIcon');

    // Reset UI
    overlay.style.display = 'flex';
    title.textContent = `Generating ${reportName}`;
    desc.textContent = 'Initializing database connection...';
    progress.style.width = '0%';
    statusText.textContent = '0%';
    
    // Set icon based on type
    if(type === 'collection') icon.className = 'fas fa-chart-pie';
    else if(type === 'department') icon.className = 'fas fa-file-csv';
    else icon.className = 'fas fa-list-check';

    icon.style.color = 'var(--primary-600)';

    let percent = 0;
    
    const interval = setInterval(() => {
        // Randomly increment between 5 and 15
        percent += Math.floor(Math.random() * 10) + 5;
        
        if (percent >= 100) {
            percent = 100;
            clearInterval(interval);
            
            // Final success state
            progress.style.width = '100%';
            progress.style.background = 'var(--success)';
            statusText.textContent = 'COMPLETE';
            statusText.style.color = 'var(--success)';
            title.textContent = 'Report Ready!';
            desc.textContent = 'Your data has been successfully compiled and exported.';
            
            icon.className = 'fas fa-check-circle';
            icon.style.color = 'var(--success)';
            icon.parentElement.previousElementSibling.style.borderColor = 'var(--success)';
            icon.parentElement.previousElementSibling.style.animation = 'none';
            icon.parentElement.parentElement.style.animation = 'pulse-success 2s infinite';

            // Hide overlay after delay and show toast
            setTimeout(() => {
                overlay.style.display = 'none';
                showToast(`${reportName} has been downloaded.`, 'success');
            }, 1500);
            
        } else {
            // Update progress
            progress.style.width = `${percent}%`;
            statusText.textContent = `${percent}%`;
            
            // Update descriptive text based on progress
            if (percent > 20 && percent < 50) desc.textContent = 'Querying transaction ledgers...';
            else if (percent >= 50 && percent < 80) desc.textContent = 'Aggregating financial data...';
            else if (percent >= 80) desc.textContent = 'Formatting document structure...';
        }
    }, 400); // Update every 400ms
}

function renderProfilePage() {
    renderPlaceholder("My Profile");
}

function renderSettingsPage() {
    renderPlaceholder("System Settings");
}

function renderNotificationsPage() {
    renderPlaceholder("All Notifications");
}

function renderPlaceholder(title) {
    contentArea.innerHTML = `
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 5rem 2rem;">
                <div style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;">
                    <i class="fas fa-hammer"></i>
                </div>
                <h2>${title}</h2>
                <p class="text-muted mt-2">This module is currently under development.</p>
                <button class="btn btn-outline mt-4" onclick="navigateTo('dashboard')">Return to Dashboard</button>
            </div>
        </div>
    `;
}

// ======================= MARKET PERSONNEL MODULES =======================

function getVendors() {
    let vendors = JSON.parse(localStorage.getItem('meedocentrix_vendors'));
    if (!vendors || vendors.length === 0) {
        vendors = [
            { id: 'VND-001', name: 'Jose Rizal', business: 'Rizal Dry Goods', type: 'Dry Goods', contact: '09123456789', status: 'Active' },
            { id: 'VND-002', name: 'Elena Marquez', business: 'Elena Fresh Catch', type: 'Wet Market', contact: '09198765432', status: 'Active' },
            { id: 'VND-003', name: 'Maria Santos', business: 'Santos Vegetables', type: 'Vegetables', contact: '09223334444', status: 'Inactive' }
        ];
        localStorage.setItem('meedocentrix_vendors', JSON.stringify(vendors));
    }
    return vendors;
}

function saveVendors(vendors) {
    localStorage.setItem('meedocentrix_vendors', JSON.stringify(vendors));
}

function renderVendorDirectoryPage() {
    let vendors = getVendors();

    let trHtml = vendors.length === 0 ? `<tr><td colspan="6" style="text-align: center; padding: 2rem;">No registered vendors found.</td></tr>` : vendors.map(vendor => {
        let statusBadge = vendor.status === 'Active' 
            ? `<span class="badge bg-success-light text-success">Active</span>` 
            : `<span class="badge bg-gray-200 text-gray-700">Inactive</span>`;

        return `
            <tr>
                <td><strong>#${vendor.id}</strong></td>
                <td>${vendor.name}</td>
                <td>${vendor.business}</td>
                <td>${vendor.type}</td>
                <td>${vendor.contact}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditVendorModal('${vendor.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteVendor('${vendor.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--success-700), var(--success-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Vendor Directory</h2>
                        <p style="opacity: 0.9; margin: 0;">Manage and edit the directory of all registered vendors operating in the Public Market.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddVendorModal()"><i class="fas fa-plus"></i> Register Vendor</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Registered Vendors</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search vendor name or business..." onkeyup="filterVendors(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Vendor ID</th>
                                <th>Vendor Name</th>
                                <th>Business Name</th>
                                <th>Category / Type</th>
                                <th>Contact No.</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vendorTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterVendors = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#vendorTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const name = row.children[1].textContent.toLowerCase();
        const business = row.children[2].textContent.toLowerCase();
        if (name.includes(q) || business.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddVendorModal = function() {
    modalTitle.textContent = 'Register New Vendor';
    
    modalBody.innerHTML = `
        <form id="addVendorForm" onsubmit="event.preventDefault(); saveNewVendor();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" id="vendorName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" id="vendorBusiness" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category / Type</label>
                    <select id="vendorType" class="form-control" required>
                        <option value="Dry Goods">Dry Goods</option>
                        <option value="Wet Market">Wet Market</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Meat Section">Meat Section</option>
                        <option value="Fish Section">Fish Section</option>
                        <option value="Eatery/Carenderia">Eatery/Carenderia</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" id="vendorContact" class="form-control" required>
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select id="vendorStatus" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenVendorSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Register Vendor';
    submitBtn.onclick = () => document.getElementById('hiddenVendorSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewVendor = function() {
    let vendors = getVendors();
    let newVendor = {
        id: 'VND-' + Date.now().toString().slice(-4),
        name: document.getElementById('vendorName').value,
        business: document.getElementById('vendorBusiness').value,
        type: document.getElementById('vendorType').value,
        contact: document.getElementById('vendorContact').value,
        status: document.getElementById('vendorStatus').value
    };
    
    vendors.unshift(newVendor);
    saveVendors(vendors);
    
    closeModal();
    showToast('Vendor successfully registered!', 'success');
    renderVendorDirectoryPage();
}

window.openEditVendorModal = function(id) {
    let vendors = getVendors();
    let vendor = vendors.find(v => v.id === id);
    if (!vendor) return;

    modalTitle.textContent = 'Edit Vendor Record';
    
    modalBody.innerHTML = `
        <form id="editVendorForm" onsubmit="event.preventDefault(); saveEditedVendor('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" id="editVendorName" class="form-control" value="${vendor.name}" required>
                </div>
                <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" id="editVendorBusiness" class="form-control" value="${vendor.business}" required>
                </div>
                <div class="form-group">
                    <label>Category / Type</label>
                    <select id="editVendorType" class="form-control" required>
                        <option value="Dry Goods" ${vendor.type === 'Dry Goods' ? 'selected' : ''}>Dry Goods</option>
                        <option value="Wet Market" ${vendor.type === 'Wet Market' ? 'selected' : ''}>Wet Market</option>
                        <option value="Vegetables" ${vendor.type === 'Vegetables' ? 'selected' : ''}>Vegetables</option>
                        <option value="Fruits" ${vendor.type === 'Fruits' ? 'selected' : ''}>Fruits</option>
                        <option value="Meat Section" ${vendor.type === 'Meat Section' ? 'selected' : ''}>Meat Section</option>
                        <option value="Fish Section" ${vendor.type === 'Fish Section' ? 'selected' : ''}>Fish Section</option>
                        <option value="Eatery/Carenderia" ${vendor.type === 'Eatery/Carenderia' ? 'selected' : ''}>Eatery/Carenderia</option>
                        <option value="Others" ${vendor.type === 'Others' ? 'selected' : ''}>Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" id="editVendorContact" class="form-control" value="${vendor.contact}" required>
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select id="editVendorStatus" class="form-control" required>
                        <option value="Active" ${vendor.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${vendor.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditVendorSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditVendorSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedVendor = function(id) {
    let vendors = getVendors();
    let index = vendors.findIndex(v => v.id === id);
    if (index > -1) {
        vendors[index].name = document.getElementById('editVendorName').value;
        vendors[index].business = document.getElementById('editVendorBusiness').value;
        vendors[index].type = document.getElementById('editVendorType').value;
        vendors[index].contact = document.getElementById('editVendorContact').value;
        vendors[index].status = document.getElementById('editVendorStatus').value;
        
        saveVendors(vendors);
        closeModal();
        showToast('Vendor record updated!', 'success');
        renderVendorDirectoryPage();
    }
}

window.deleteVendor = function(id) {
    if(confirm('Are you sure you want to remove this vendor?')) {
        let vendors = getVendors();
        vendors = vendors.filter(v => v.id !== id);
        saveVendors(vendors);
        showToast('Vendor removed from directory.', 'success');
        renderVendorDirectoryPage();
    }
}

// STALL MANAGEMENT

function getStalls() {
    let stalls = JSON.parse(localStorage.getItem('meedocentrix_stalls'));
    if (!stalls || stalls.length === 0) {
        stalls = [
            { id: 'STL-A01', section: 'Section A (Dry Goods)', rate: '150.00', occupant: 'Jose Rizal', status: 'Occupied' },
            { id: 'STL-A02', section: 'Section A (Dry Goods)', rate: '150.00', occupant: '', status: 'Available' },
            { id: 'STL-B01', section: 'Section B (Wet Market)', rate: '200.00', occupant: 'Elena Marquez', status: 'Occupied' },
            { id: 'STL-B02', section: 'Section B (Wet Market)', rate: '200.00', occupant: '', status: 'Under Maintenance' }
        ];
        localStorage.setItem('meedocentrix_stalls', JSON.stringify(stalls));
    }
    return stalls;
}

function saveStalls(stalls) {
    localStorage.setItem('meedocentrix_stalls', JSON.stringify(stalls));
}

function renderStallManagementPage() {
    let stalls = getStalls();

    let trHtml = stalls.length === 0 ? `<tr><td colspan="6" style="text-align: center; padding: 2rem;">No stalls found.</td></tr>` : stalls.map(stall => {
        let statusBadge = '';
        if (stall.status === 'Occupied') statusBadge = `<span class="badge bg-success-light text-success">Occupied</span>`;
        else if (stall.status === 'Available') statusBadge = `<span class="badge bg-info-light text-info">Available</span>`;
        else statusBadge = `<span class="badge bg-warning-light text-warning">${stall.status}</span>`;

        return `
            <tr>
                <td><strong>${stall.id}</strong></td>
                <td>${stall.section}</td>
                <td><strong>₱${parseFloat(stall.rate).toFixed(2)} / sqm</strong></td>
                <td>${stall.occupant || '<span class="text-muted"><i>Vacant</i></span>'}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditStallModal('${stall.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteStall('${stall.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--warning-600), var(--warning-400)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Stall Management & Rentals</h2>
                        <p style="opacity: 0.9; margin: 0;">Monitor stall availability, update rental rates, and track stall occupancy details.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddStallModal()"><i class="fas fa-plus"></i> Add Stall</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Stall Registry</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search stall ID or section..." onkeyup="filterStalls(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Stall ID</th>
                                <th>Section / Area</th>
                                <th>Rental Rate</th>
                                <th>Current Occupant</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stallTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterStalls = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#stallTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const id = row.children[0].textContent.toLowerCase();
        const section = row.children[1].textContent.toLowerCase();
        if (id.includes(q) || section.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddStallModal = function() {
    modalTitle.textContent = 'Add New Stall';
    
    // Create options for vendors (to select occupant if occupied)
    let vendors = getVendors();
    let vendorOptions = '<option value="">-- None (Vacant) --</option>';
    vendors.filter(v => v.status === 'Active').forEach(v => {
        vendorOptions += `<option value="${v.name}">${v.name} (${v.business})</option>`;
    });

    modalBody.innerHTML = `
        <form id="addStallForm" onsubmit="event.preventDefault(); saveNewStall();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Stall ID / Number</label>
                    <input type="text" id="stallId" class="form-control" placeholder="e.g., STL-C01" required>
                </div>
                <div class="form-group">
                    <label>Section / Area</label>
                    <select id="stallSection" class="form-control" required>
                        <option value="Section A (Dry Goods)">Section A (Dry Goods)</option>
                        <option value="Section B (Wet Market)">Section B (Wet Market)</option>
                        <option value="Section C (Vegetables)">Section C (Vegetables)</option>
                        <option value="Section D (Meat)">Section D (Meat)</option>
                        <option value="Section E (Fish)">Section E (Fish)</option>
                        <option value="Food Court">Food Court</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rental Rate (per sqm)</label>
                    <input type="number" id="stallRate" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="stallStatus" class="form-control" required onchange="toggleStallOccupantField(this.value, 'add')">
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group full-width" id="addOccupantGroup" style="display: none;">
                    <label>Assign Occupant (Vendor)</label>
                    <select id="stallOccupant" class="form-control">
                        ${vendorOptions}
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenStallSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Add Stall';
    submitBtn.onclick = () => document.getElementById('hiddenStallSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.toggleStallOccupantField = function(status, mode) {
    const occupantGroup = document.getElementById(mode === 'add' ? 'addOccupantGroup' : 'editOccupantGroup');
    if (status === 'Occupied') {
        occupantGroup.style.display = 'block';
    } else {
        occupantGroup.style.display = 'none';
        const select = document.getElementById(mode === 'add' ? 'stallOccupant' : 'editStallOccupant');
        select.value = '';
    }
}

window.saveNewStall = function() {
    let stalls = getStalls();
    
    // Check for duplicate ID
    let newId = document.getElementById('stallId').value;
    if (stalls.some(s => s.id === newId)) {
        showToast('A stall with this ID already exists!', 'error');
        return;
    }

    let status = document.getElementById('stallStatus').value;
    let occupant = status === 'Occupied' ? document.getElementById('stallOccupant').value : '';

    let newStall = {
        id: newId,
        section: document.getElementById('stallSection').value,
        rate: document.getElementById('stallRate').value,
        status: status,
        occupant: occupant
    };
    
    stalls.unshift(newStall);
    saveStalls(stalls);
    
    closeModal();
    showToast('Stall successfully added!', 'success');
    renderStallManagementPage();
}

window.openEditStallModal = function(id) {
    let stalls = getStalls();
    let stall = stalls.find(s => s.id === id);
    if (!stall) return;

    let vendors = getVendors();
    let vendorOptions = '<option value="">-- None (Vacant) --</option>';
    vendors.filter(v => v.status === 'Active').forEach(v => {
        vendorOptions += `<option value="${v.name}" ${stall.occupant === v.name ? 'selected' : ''}>${v.name} (${v.business})</option>`;
    });

    modalTitle.textContent = 'Edit Stall Details';
    
    modalBody.innerHTML = `
        <form id="editStallForm" onsubmit="event.preventDefault(); saveEditedStall('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Stall ID / Number</label>
                    <input type="text" class="form-control" value="${stall.id}" disabled style="background: var(--gray-100);">
                </div>
                <div class="form-group">
                    <label>Section / Area</label>
                    <select id="editStallSection" class="form-control" required>
                        <option value="Section A (Dry Goods)" ${stall.section === 'Section A (Dry Goods)' ? 'selected' : ''}>Section A (Dry Goods)</option>
                        <option value="Section B (Wet Market)" ${stall.section === 'Section B (Wet Market)' ? 'selected' : ''}>Section B (Wet Market)</option>
                        <option value="Section C (Vegetables)" ${stall.section === 'Section C (Vegetables)' ? 'selected' : ''}>Section C (Vegetables)</option>
                        <option value="Section D (Meat)" ${stall.section === 'Section D (Meat)' ? 'selected' : ''}>Section D (Meat)</option>
                        <option value="Section E (Fish)" ${stall.section === 'Section E (Fish)' ? 'selected' : ''}>Section E (Fish)</option>
                        <option value="Food Court" ${stall.section === 'Food Court' ? 'selected' : ''}>Food Court</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rental Rate (per sqm)</label>
                    <input type="number" id="editStallRate" class="form-control" step="0.01" value="${stall.rate}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editStallStatus" class="form-control" required onchange="toggleStallOccupantField(this.value, 'edit')">
                        <option value="Available" ${stall.status === 'Available' ? 'selected' : ''}>Available</option>
                        <option value="Occupied" ${stall.status === 'Occupied' ? 'selected' : ''}>Occupied</option>
                        <option value="Under Maintenance" ${stall.status === 'Under Maintenance' ? 'selected' : ''}>Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group full-width" id="editOccupantGroup" style="${stall.status === 'Occupied' ? 'display: block;' : 'display: none;'}">
                    <label>Assign Occupant (Vendor)</label>
                    <select id="editStallOccupant" class="form-control">
                        ${vendorOptions}
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditStallSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditStallSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedStall = function(id) {
    let stalls = getStalls();
    let index = stalls.findIndex(s => s.id === id);
    if (index > -1) {
        let status = document.getElementById('editStallStatus').value;
        let occupant = status === 'Occupied' ? document.getElementById('editStallOccupant').value : '';

        stalls[index].section = document.getElementById('editStallSection').value;
        stalls[index].rate = document.getElementById('editStallRate').value;
        stalls[index].status = status;
        stalls[index].occupant = occupant;
        
        saveStalls(stalls);
        closeModal();
        showToast('Stall details updated!', 'success');
        renderStallManagementPage();
    }
}

window.deleteStall = function(id) {
    if(confirm('Are you sure you want to remove this stall?')) {
        let stalls = getStalls();
        stalls = stalls.filter(s => s.id !== id);
        saveStalls(stalls);
        showToast('Stall removed.', 'success');
        renderStallManagementPage();
    }
}

function getVesselRegistry() {
    let registry = JSON.parse(localStorage.getItem('meedocentrix_vessel_registry'));
    if (!registry || registry.length === 0) {
        registry = [
            { id: 'VR-001', name: 'MV San Juan', owner: 'Reynaldo Santos', type: 'Medium Commercial', registrationDate: '2026-01-15', status: 'Active' },
            { id: 'VR-002', name: 'F/B Santa Maria', owner: 'Luisito Antonio', type: 'Small Municipal', registrationDate: '2026-02-10', status: 'Active' },
            { id: 'VR-003', name: 'MV Santo Niño', owner: 'Andres Bonifacio', type: 'Large Commercial', registrationDate: '2025-11-20', status: 'Inactive' }
        ];
        localStorage.setItem('meedocentrix_vessel_registry', JSON.stringify(registry));
    }
    return registry;
}

function saveVesselRegistry(registry) {
    localStorage.setItem('meedocentrix_vessel_registry', JSON.stringify(registry));
}

function renderVesselRegistryPage() {
    let registry = getVesselRegistry();

    let trHtml = registry.length === 0 ? `<tr><td colspan="7" style="text-align: center; padding: 2rem;">No registered vessels found.</td></tr>` : registry.map(vessel => {
        let statusBadge = vessel.status === 'Active' 
            ? `<span class="badge bg-success-light text-success">Active</span>` 
            : `<span class="badge bg-gray-200 text-gray-700">Inactive</span>`;

        return `
            <tr>
                <td><strong>#${vessel.id}</strong></td>
                <td>${vessel.name}</td>
                <td>${vessel.owner}</td>
                <td>${vessel.type}</td>
                <td>${vessel.registrationDate}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditRegistryModal('${vessel.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteRegistryVessel('${vessel.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-700), var(--primary-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Master Vessel Registry</h2>
                        <p style="opacity: 0.9; margin: 0;">Manage and edit the official directory of all registered vessels operating in the fishport.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddRegistryModal()"><i class="fas fa-plus"></i> Register Vessel</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Registered Vessels</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search vessel name or owner..." onkeyup="filterVesselRegistry(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Registry ID</th>
                                <th>Vessel Name</th>
                                <th>Owner / Operator</th>
                                <th>Classification</th>
                                <th>Date Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vesselRegistryTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterVesselRegistry = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#vesselRegistryTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const name = row.children[1].textContent.toLowerCase();
        const owner = row.children[2].textContent.toLowerCase();
        if (name.includes(q) || owner.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddRegistryModal = function() {
    modalTitle.textContent = 'Register New Vessel';
    
    modalBody.innerHTML = `
        <form id="addRegistryForm" onsubmit="event.preventDefault(); saveNewRegistryVessel();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vessel Name</label>
                    <input type="text" id="regVesselName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Owner / Operator</label>
                    <input type="text" id="regVesselOwner" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Classification / Type</label>
                    <select id="regVesselType" class="form-control" required>
                        <option value="Small Municipal">Small Municipal</option>
                        <option value="Medium Commercial">Medium Commercial</option>
                        <option value="Large Commercial">Large Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Registration Date</label>
                    <input type="date" id="regVesselDate" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="regVesselStatus" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenRegistrySubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Register Vessel';
    submitBtn.onclick = () => document.getElementById('hiddenRegistrySubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewRegistryVessel = function() {
    let registry = getVesselRegistry();
    let newVessel = {
        id: 'VR-' + Date.now().toString().slice(-4),
        name: document.getElementById('regVesselName').value,
        owner: document.getElementById('regVesselOwner').value,
        type: document.getElementById('regVesselType').value,
        registrationDate: document.getElementById('regVesselDate').value,
        status: document.getElementById('regVesselStatus').value
    };
    
    registry.unshift(newVessel);
    saveVesselRegistry(registry);
    
    closeModal();
    showToast('Vessel successfully registered!', 'success');
    renderVesselRegistryPage();
}

window.openEditRegistryModal = function(id) {
    let registry = getVesselRegistry();
    let vessel = registry.find(v => v.id === id);
    if (!vessel) return;

    modalTitle.textContent = 'Edit Vessel Registry';
    
    modalBody.innerHTML = `
        <form id="editRegistryForm" onsubmit="event.preventDefault(); saveEditedRegistryVessel('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vessel Name</label>
                    <input type="text" id="editRegVesselName" class="form-control" value="${vessel.name}" required>
                </div>
                <div class="form-group">
                    <label>Owner / Operator</label>
                    <input type="text" id="editRegVesselOwner" class="form-control" value="${vessel.owner}" required>
                </div>
                <div class="form-group">
                    <label>Classification / Type</label>
                    <select id="editRegVesselType" class="form-control" required>
                        <option value="Small Municipal" ${vessel.type === 'Small Municipal' ? 'selected' : ''}>Small Municipal</option>
                        <option value="Medium Commercial" ${vessel.type === 'Medium Commercial' ? 'selected' : ''}>Medium Commercial</option>
                        <option value="Large Commercial" ${vessel.type === 'Large Commercial' ? 'selected' : ''}>Large Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Registration Date</label>
                    <input type="date" id="editRegVesselDate" class="form-control" value="${vessel.registrationDate}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editRegVesselStatus" class="form-control" required>
                        <option value="Active" ${vessel.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${vessel.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditRegistrySubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditRegistrySubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedRegistryVessel = function(id) {
    let registry = getVesselRegistry();
    let index = registry.findIndex(v => v.id === id);
    if (index > -1) {
        registry[index].name = document.getElementById('editRegVesselName').value;
        registry[index].owner = document.getElementById('editRegVesselOwner').value;
        registry[index].type = document.getElementById('editRegVesselType').value;
        registry[index].registrationDate = document.getElementById('editRegVesselDate').value;
        registry[index].status = document.getElementById('editRegVesselStatus').value;
        
        saveVesselRegistry(registry);
        closeModal();
        showToast('Vessel registry updated!', 'success');
        renderVesselRegistryPage();
    }
}

window.deleteRegistryVessel = function(id) {
    if(confirm('Are you sure you want to remove this vessel from the registry?')) {
        let registry = getVesselRegistry();
        registry = registry.filter(v => v.id !== id);
        saveVesselRegistry(registry);
        showToast('Vessel removed from registry.', 'success');
        renderVesselRegistryPage();
    }
}

function getVesselLogs() {
    let logs = JSON.parse(localStorage.getItem('meedocentrix_vessels'));
    if (!logs || logs.length === 0) {
        logs = [
            { id: 'VL-8829', name: 'MV San Juan', captain: 'Reynaldo Santos', type: 'Medium Commercial', status: 'Unloading', timeIn: '06:30', timeOut: '' },
            { id: 'VL-8830', name: 'F/B Santa Maria', captain: 'Luisito Antonio', type: 'Small Municipal', status: 'Docked', timeIn: '07:15', timeOut: '' },
            { id: 'VL-8831', name: 'MV Santo Niño', captain: 'Andres Bonifacio', type: 'Large Commercial', status: 'Departed', timeIn: '08:45', timeOut: '14:00' }
        ];
        localStorage.setItem('meedocentrix_vessels', JSON.stringify(logs));
    }
    return logs;
}

function saveVesselLogs(logs) {
    localStorage.setItem('meedocentrix_vessels', JSON.stringify(logs));
}

function formatTime(time24) {
    if (!time24) return '--:--';
    let [hours, minutes] = time24.split(':');
    let h = parseInt(hours);
    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12; 
    return `${h.toString().padStart(2, '0')}:${minutes} ${ampm}`;
}

function renderVesselLogsPage() {
    let logs = getVesselLogs();

    let trHtml = logs.length === 0 ? `<tr><td colspan="8" style="text-align: center; padding: 2rem;">No vessel logs found.</td></tr>` : logs.map(log => {
        let statusBadge = '';
        if (log.status === 'Departed') statusBadge = `<span class="badge bg-gray-200 text-gray-700">Departed</span>`;
        else if (log.status === 'Unloading') statusBadge = `<span class="badge bg-success-light text-success">Unloading</span>`;
        else if (log.status === 'Docked') statusBadge = `<span class="badge bg-info-light text-info">Docked</span>`;
        else statusBadge = `<span class="badge bg-warning-light text-warning">${log.status}</span>`;

        return `
            <tr>
                <td><strong>#${log.id}</strong></td>
                <td>${log.name}</td>
                <td>${log.captain}</td>
                <td>${log.type}</td>
                <td>${statusBadge}</td>
                <td>${formatTime(log.timeIn)}</td>
                <td>${formatTime(log.timeOut)}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditVesselModal('${log.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteVesselLog('${log.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-700), var(--primary-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Vessel Arrival Logs</h2>
                        <p style="opacity: 0.9; margin: 0;">Monitor and record arriving fishing vessels, docking fees, and catch unloads.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddVesselModal()"><i class="fas fa-ship"></i> Log Arrival</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Today's Arrivals</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search vessel name or captain..." onkeyup="filterVesselLogs(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Vessel Name</th>
                                <th>Captain / Owner</th>
                                <th>Type / Tonnage</th>
                                <th>Status</th>
                                <th>Arrival Time</th>
                                <th>Departure Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="vesselLogsTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterVesselLogs = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#vesselLogsTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const name = row.children[1].textContent.toLowerCase();
        const captain = row.children[2].textContent.toLowerCase();
        if (name.includes(q) || captain.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddVesselModal = function() {
    modalTitle.textContent = 'Log New Vessel Arrival';
    
    modalBody.innerHTML = `
        <form id="addVesselForm" onsubmit="event.preventDefault(); saveNewVessel();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vessel Name</label>
                    <input type="text" id="vesselName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Captain / Owner</label>
                    <input type="text" id="vesselCaptain" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Type / Tonnage</label>
                    <select id="vesselType" class="form-control" required>
                        <option value="Small Municipal">Small Municipal</option>
                        <option value="Medium Commercial">Medium Commercial</option>
                        <option value="Large Commercial">Large Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="vesselStatus" class="form-control" required>
                        <option value="Docked">Docked</option>
                        <option value="Unloading">Unloading</option>
                        <option value="Awaiting Inspection">Awaiting Inspection</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time In</label>
                    <input type="time" id="vesselTimeIn" class="form-control" value="${new Date().toTimeString().slice(0, 5)}" required>
                </div>
                <div class="form-group">
                    <label>Time Out</label>
                    <input type="time" id="vesselTimeOut" class="form-control">
                </div>
            </div>
            <button type="submit" id="hiddenVesselSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Log';
    submitBtn.onclick = () => document.getElementById('hiddenVesselSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewVessel = function() {
    let logs = getVesselLogs();
    let newLog = {
        id: 'VL-' + Date.now().toString().slice(-4),
        name: document.getElementById('vesselName').value,
        captain: document.getElementById('vesselCaptain').value,
        type: document.getElementById('vesselType').value,
        status: document.getElementById('vesselStatus').value,
        timeIn: document.getElementById('vesselTimeIn').value,
        timeOut: document.getElementById('vesselTimeOut').value
    };
    
    logs.unshift(newLog); // Add to top
    saveVesselLogs(logs);
    
    closeModal();
    showToast('Vessel arrival logged successfully!', 'success');
    renderVesselLogsPage();
}

window.openEditVesselModal = function(id) {
    let logs = getVesselLogs();
    let log = logs.find(l => l.id === id);
    if (!log) return;

    modalTitle.textContent = 'Edit Vessel Log';
    
    modalBody.innerHTML = `
        <form id="editVesselForm" onsubmit="event.preventDefault(); saveEditedVessel('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Vessel Name</label>
                    <input type="text" id="editVesselName" class="form-control" value="${log.name}" required>
                </div>
                <div class="form-group">
                    <label>Captain / Owner</label>
                    <input type="text" id="editVesselCaptain" class="form-control" value="${log.captain}" required>
                </div>
                <div class="form-group">
                    <label>Type / Tonnage</label>
                    <select id="editVesselType" class="form-control" required>
                        <option value="Small Municipal" ${log.type === 'Small Municipal' ? 'selected' : ''}>Small Municipal</option>
                        <option value="Medium Commercial" ${log.type === 'Medium Commercial' ? 'selected' : ''}>Medium Commercial</option>
                        <option value="Large Commercial" ${log.type === 'Large Commercial' ? 'selected' : ''}>Large Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editVesselStatus" class="form-control" required>
                        <option value="Docked" ${log.status === 'Docked' ? 'selected' : ''}>Docked</option>
                        <option value="Unloading" ${log.status === 'Unloading' ? 'selected' : ''}>Unloading</option>
                        <option value="Awaiting Inspection" ${log.status === 'Awaiting Inspection' ? 'selected' : ''}>Awaiting Inspection</option>
                        <option value="Departed" ${log.status === 'Departed' ? 'selected' : ''}>Departed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time In</label>
                    <input type="time" id="editVesselTimeIn" class="form-control" value="${log.timeIn}" required>
                </div>
                <div class="form-group">
                    <label>Time Out</label>
                    <input type="time" id="editVesselTimeOut" class="form-control" value="${log.timeOut || ''}">
                </div>
            </div>
            <button type="submit" id="hiddenEditVesselSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditVesselSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedVessel = function(id) {
    let logs = getVesselLogs();
    let index = logs.findIndex(l => l.id === id);
    if (index > -1) {
        logs[index].name = document.getElementById('editVesselName').value;
        logs[index].captain = document.getElementById('editVesselCaptain').value;
        logs[index].type = document.getElementById('editVesselType').value;
        logs[index].status = document.getElementById('editVesselStatus').value;
        logs[index].timeIn = document.getElementById('editVesselTimeIn').value;
        logs[index].timeOut = document.getElementById('editVesselTimeOut').value;
        
        saveVesselLogs(logs);
        closeModal();
        showToast('Vessel log updated successfully!', 'success');
        renderVesselLogsPage();
    }
}

window.deleteVesselLog = function(id) {
    if(confirm('Are you sure you want to delete this vessel log?')) {
        let logs = getVesselLogs();
        logs = logs.filter(l => l.id !== id);
        saveVesselLogs(logs);
        showToast('Vessel log deleted.', 'success');
        renderVesselLogsPage();
    }
}

function renderSendPaymentPage() {
    let allRecords = getRecords();
    let pendingRecords = allRecords.filter(r => r.department === currentUserRole && !r.sentToCollector && r.status !== 'Collected');
    let totalAmount = pendingRecords.reduce((sum, r) => sum + parseFloat(r.amount), 0);

    let trHtml = '';
    if (pendingRecords.length === 0) {
        trHtml = `<tr><td colspan="6" style="text-align: center; padding: 3rem;"><i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>All caught up! No pending transactions to send.</td></tr>`;
    } else {
        trHtml = pendingRecords.map(r => `
            <tr>
                <td><strong>#${r.id}</strong></td>
                <td>${r.title}</td>
                <td>${r.date}</td>
                <td><strong>₱${parseFloat(r.amount).toFixed(2)}</strong></td>
                <td><span class="badge bg-warning-light text-warning">Pending Review</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="openSelectCollectorModal('${r.id}')"><i class="fas fa-paper-plane"></i> Send</button>
                </td>
            </tr>
        `).join('');
    }

    contentArea.innerHTML = `
        <div class="grid-1-2" style="grid-template-columns: 1fr 3fr;">
            <!-- Summary Card -->
            <div class="card" style="align-self: start;">
                <div class="card-header">
                    <h3>Summary</h3>
                </div>
                <div class="card-body" style="text-align: center; padding: 2rem;">
                    <div style="width: 64px; height: 64px; background: var(--warning-light); color: var(--warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem auto;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h4 style="color: var(--gray-500); font-weight: 500; font-size: 0.9rem;">Total Pending Amount</h4>
                    <h2 style="font-size: 2.2rem; color: var(--gray-800); margin: 0.5rem 0;">₱${totalAmount.toFixed(2)}</h2>
                    <p class="text-muted text-sm mb-4">${pendingRecords.length} transaction(s) waiting</p>

                    <button class="btn btn-success" style="width: 100%; justify-content: center;" onclick="openSelectCollectorModal('all')" ${pendingRecords.length === 0 ? 'disabled' : ''}>
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send All to Collector
                    </button>
                </div>
            </div>

            <!-- List Card -->
            <div class="card">
                <div class="card-header flex-between">
                    <div>
                        <h3>Pending Transactions</h3>
                        <p class="text-muted text-sm" style="margin-top: 4px;">Transactions that need to be forwarded to assigned collectors.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Description</th>
                                    <th>Date Recorded</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${trHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
}

window.openSelectCollectorModal = function(targetId) {
    const currentUserProfile = mockUsers.find(u => u.username === (currentUserRole === 'fishport' ? 'jdelacruz' : currentUserRole === 'terminal' ? 'mlopez' : 'msantos'));
    
    let assignedCollectors = [];
    if (currentUserProfile && currentUserProfile.assignedCollectors) {
        assignedCollectors = mockUsers.filter(u => currentUserProfile.assignedCollectors.includes(u.id));
    }
    
    if (assignedCollectors.length === 0) {
        assignedCollectors = mockUsers.filter(u => u.role === 'Assigned Collector' && u.status === 'Active');
    }

    if (assignedCollectors.length === 0) {
        showToast('No active collectors available to assign.', 'error');
        return;
    }

    const collectorOptionsHtml = assignedCollectors.map(c => `
        <div class="collector-option" onclick="selectCollectorOption(this, '${c.id}')" style="padding: 12px 16px; border: 1px solid var(--gray-200); border-radius: var(--radius-md); margin-bottom: 8px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; background: var(--primary-50); color: var(--primary-600); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">
                    ${c.name.split(' ').map(n=>n[0]).join('')}
                </div>
                <div>
                    <div style="font-weight: 600; color: var(--gray-800);">${c.name}</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500);">Assigned Collector</div>
                </div>
            </div>
            <div class="check-icon" style="color: var(--success); display: none;"><i class="fas fa-check-circle"></i></div>
        </div>
    `).join('');

    modalTitle.textContent = targetId === 'all' ? 'Send All to Collector' : 'Forward Transaction';
    
    modalBody.innerHTML = `
        <p class="text-muted" style="margin-bottom: 1rem;">Please select the designated collector who will receive ${targetId === 'all' ? 'these transactions' : 'this transaction'}.</p>
        <div id="collectorSelectionContainer">
            ${collectorOptionsHtml}
        </div>
        <input type="hidden" id="selectedModalCollectorId" value="">
        <button type="button" id="hiddenCollectorConfirmBtn" style="display:none;" onclick="confirmCollectorSelection('${targetId}')"></button>

        <style>
            .collector-option:hover { border-color: var(--primary-300); background: var(--primary-50); }
            .collector-option.selected { border-color: var(--primary-500); background: var(--primary-50); box-shadow: 0 0 0 1px var(--primary-500); }
            .collector-option.selected .check-icon { display: block !important; }
        </style>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Confirm & Send';
    submitBtn.onclick = () => document.getElementById('hiddenCollectorConfirmBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.selectCollectorOption = function(element, collectorId) {
    document.querySelectorAll('.collector-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('selectedModalCollectorId').value = collectorId;
}

window.confirmCollectorSelection = function(targetId) {
    const selectedId = document.getElementById('selectedModalCollectorId').value;
    if (!selectedId) {
        showToast('Please select a collector from the list.', 'error');
        return;
    }
    
    if (targetId === 'all') {
        const collector = mockUsers.find(u => u.id == selectedId);
        if (!collector) return;

        let records = getRecords();
        let updated = false;
        records.forEach(r => {
            if(r.department === currentUserRole && !r.sentToCollector && r.status !== 'Collected') {
                r.sentToCollector = true;
                r.assignedCollectorId = selectedId;
                r.assignedCollectorName = collector.name;
                updated = true;
            }
        });
        if (updated) {
            saveRecords(records);
            showToast(`All records successfully forwarded to ${collector.name}!`, 'success');
            closeModal();
            renderPage(currentPage);
        } else {
            showToast('No pending records to send.', 'info');
        }
    } else {
        const collector = mockUsers.find(u => u.id == selectedId);
        if (!collector) return;
        
        let records = getRecords();
        let record = records.find(r => r.id == targetId);
        if(record) {
            record.sentToCollector = true;
            record.assignedCollectorId = selectedId;
            record.assignedCollectorName = collector.name;
            saveRecords(records);
            showToast(`Transaction sent to ${collector.name} successfully!`, 'success');
            closeModal();
            renderPage(currentPage);
        }
    }
}

// ======================= CEMETERY PERSONNEL MODULES =======================

function getCemeteryRecords() {
    let records = JSON.parse(localStorage.getItem('meedocentrix_cemetery_records'));
    if (!records || records.length === 0) {
        records = [
            { id: 'CR-001', occupant: 'Juan Dela Cruz Sr.', representative: 'Juan Dela Cruz Jr.', location: 'Apartment Niche Block A, Level 2', dateOfInterment: '2023-05-12', status: 'Active' },
            { id: 'CR-002', occupant: 'Maria Clara Santos', representative: 'Elena Marquez', location: 'Ground Tomb Section B, Lot 14', dateOfInterment: '2021-11-04', status: 'Active' },
            { id: 'CR-003', occupant: 'Antonio Luna', representative: 'Jose Luna', location: 'Apartment Niche Block C, Level 4', dateOfInterment: '2015-08-22', status: 'Expired/Due for Renewal' }
        ];
        localStorage.setItem('meedocentrix_cemetery_records', JSON.stringify(records));
    }
    return records;
}

function saveCemeteryRecords(records) {
    localStorage.setItem('meedocentrix_cemetery_records', JSON.stringify(records));
}

function renderCemeteryRecordsPage() {
    let records = getCemeteryRecords();

    let trHtml = records.length === 0 ? `<tr><td colspan="7" style="text-align: center; padding: 2rem;">No cemetery occupant records found.</td></tr>` : records.map(record => {
        let statusBadge = record.status === 'Active' 
            ? `<span class="badge bg-success-light text-success">Active</span>` 
            : `<span class="badge bg-warning-light text-warning">${record.status}</span>`;

        return `
            <tr>
                <td><strong>#${record.id}</strong></td>
                <td>${record.occupant}</td>
                <td>${record.representative}</td>
                <td>${record.location}</td>
                <td>${record.dateOfInterment}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditCemeteryRecordModal('${record.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteCemeteryRecord('${record.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--gray-700), var(--gray-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Cemetery Occupant Records</h2>
                        <p style="opacity: 0.9; margin: 0;">Manage and maintain the directory of all interred individuals and lease locations.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddCemeteryRecordModal()"><i class="fas fa-plus"></i> Add Occupant Record</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Occupant Directory</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search occupant or representative..." onkeyup="filterCemeteryRecords(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Record ID</th>
                                <th>Name of Deceased</th>
                                <th>Representative / Family</th>
                                <th>Location / Niche</th>
                                <th>Date of Interment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cemeteryRecordsTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterCemeteryRecords = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#cemeteryRecordsTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const occupant = row.children[1].textContent.toLowerCase();
        const rep = row.children[2].textContent.toLowerCase();
        if (occupant.includes(q) || rep.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddCemeteryRecordModal = function() {
    modalTitle.textContent = 'Add Occupant Record';
    
    modalBody.innerHTML = `
        <form id="addCemeteryRecordForm" onsubmit="event.preventDefault(); saveNewCemeteryRecord();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Name of Deceased</label>
                    <input type="text" id="cemOccupant" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Family Representative</label>
                    <input type="text" id="cemRepresentative" class="form-control" required>
                </div>
                <div class="form-group full-width">
                    <label>Location / Niche Designation</label>
                    <input type="text" id="cemLocation" class="form-control" placeholder="e.g. Apartment Niche Block A, Level 2" required>
                </div>
                <div class="form-group">
                    <label>Date of Interment</label>
                    <input type="date" id="cemDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="cemStatus" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Expired/Due for Renewal">Expired/Due for Renewal</option>
                        <option value="Exhumed">Exhumed</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenCemRecSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Record';
    submitBtn.onclick = () => document.getElementById('hiddenCemRecSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewCemeteryRecord = function() {
    let records = getCemeteryRecords();
    let newRecord = {
        id: 'CR-' + Date.now().toString().slice(-4),
        occupant: document.getElementById('cemOccupant').value,
        representative: document.getElementById('cemRepresentative').value,
        location: document.getElementById('cemLocation').value,
        dateOfInterment: document.getElementById('cemDate').value,
        status: document.getElementById('cemStatus').value
    };
    
    records.unshift(newRecord);
    saveCemeteryRecords(records);
    
    closeModal();
    showToast('Occupant successfully added!', 'success');
    renderCemeteryRecordsPage();
}

window.openEditCemeteryRecordModal = function(id) {
    let records = getCemeteryRecords();
    let record = records.find(r => r.id === id);
    if (!record) return;

    modalTitle.textContent = 'Edit Occupant Record';
    
    modalBody.innerHTML = `
        <form id="editCemeteryRecordForm" onsubmit="event.preventDefault(); saveEditedCemeteryRecord('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Name of Deceased</label>
                    <input type="text" id="editCemOccupant" class="form-control" value="${record.occupant}" required>
                </div>
                <div class="form-group">
                    <label>Family Representative</label>
                    <input type="text" id="editCemRepresentative" class="form-control" value="${record.representative}" required>
                </div>
                <div class="form-group full-width">
                    <label>Location / Niche Designation</label>
                    <input type="text" id="editCemLocation" class="form-control" value="${record.location}" required>
                </div>
                <div class="form-group">
                    <label>Date of Interment</label>
                    <input type="date" id="editCemDate" class="form-control" value="${record.dateOfInterment}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editCemStatus" class="form-control" required>
                        <option value="Active" ${record.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Expired/Due for Renewal" ${record.status === 'Expired/Due for Renewal' ? 'selected' : ''}>Expired/Due for Renewal</option>
                        <option value="Exhumed" ${record.status === 'Exhumed' ? 'selected' : ''}>Exhumed</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditCemRecSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditCemRecSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedCemeteryRecord = function(id) {
    let records = getCemeteryRecords();
    let index = records.findIndex(r => r.id === id);
    if (index > -1) {
        records[index].occupant = document.getElementById('editCemOccupant').value;
        records[index].representative = document.getElementById('editCemRepresentative').value;
        records[index].location = document.getElementById('editCemLocation').value;
        records[index].dateOfInterment = document.getElementById('editCemDate').value;
        records[index].status = document.getElementById('editCemStatus').value;
        
        saveCemeteryRecords(records);
        closeModal();
        showToast('Occupant record updated!', 'success');
        renderCemeteryRecordsPage();
    }
}

window.deleteCemeteryRecord = function(id) {
    if(confirm('Are you sure you want to remove this cemetery record?')) {
        let records = getCemeteryRecords();
        records = records.filter(r => r.id !== id);
        saveCemeteryRecords(records);
        showToast('Occupant record removed.', 'success');
        renderCemeteryRecordsPage();
    }
}

function getCemeteryServices() {
    let services = JSON.parse(localStorage.getItem('meedocentrix_cemetery_services'));
    if (!services || services.length === 0) {
        services = [
            { id: 'CS-101', type: 'Burial Permit', client: 'Jose Rizal', date: '2026-03-17', status: 'Scheduled', staff: 'Pedro Penduko' },
            { id: 'CS-102', type: 'Tomb Construction', client: 'Elena Marquez', date: '2026-03-15', status: 'In Progress', staff: 'Construction Team A' },
            { id: 'CS-103', type: 'Exhumation Permit', client: 'Andres Bonifacio', date: '2026-03-10', status: 'Completed', staff: 'Pedro Penduko' }
        ];
        localStorage.setItem('meedocentrix_cemetery_services', JSON.stringify(services));
    }
    return services;
}

function saveCemeteryServices(services) {
    localStorage.setItem('meedocentrix_cemetery_services', JSON.stringify(services));
}

function renderCemeteryServicesPage() {
    let services = getCemeteryServices();

    let trHtml = services.length === 0 ? `<tr><td colspan="7" style="text-align: center; padding: 2rem;">No cemetery services found.</td></tr>` : services.map(service => {
        let statusBadge = '';
        if (service.status === 'Completed') statusBadge = `<span class="badge bg-success-light text-success">Completed</span>`;
        else if (service.status === 'Scheduled') statusBadge = `<span class="badge bg-primary-100 text-primary-700">Scheduled</span>`;
        else if (service.status === 'In Progress') statusBadge = `<span class="badge bg-warning-light text-warning">In Progress</span>`;
        else statusBadge = `<span class="badge bg-gray-200 text-gray-700">${service.status}</span>`;

        return `
            <tr>
                <td><strong>#${service.id}</strong></td>
                <td>${service.type}</td>
                <td>${service.client}</td>
                <td>${service.date}</td>
                <td>${service.staff}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditCemServiceModal('${service.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteCemService('${service.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--info-700), var(--info-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Cemetery Service Logs</h2>
                        <p style="opacity: 0.9; margin: 0;">Schedule and track daily cemetery operations like burials, constructions, and exhumations.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddCemServiceModal()"><i class="fas fa-plus"></i> Schedule Service</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Service Schedule</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search service or client..." onkeyup="filterCemServices(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Service ID</th>
                                <th>Service Type</th>
                                <th>Client Name</th>
                                <th>Date</th>
                                <th>Assigned Staff</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cemServicesTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterCemServices = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#cemServicesTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const type = row.children[1].textContent.toLowerCase();
        const client = row.children[2].textContent.toLowerCase();
        if (type.includes(q) || client.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddCemServiceModal = function() {
    modalTitle.textContent = 'Schedule Cemetery Service';
    
    modalBody.innerHTML = `
        <form id="addCemServiceForm" onsubmit="event.preventDefault(); saveNewCemService();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Service Type</label>
                    <select id="cemServiceType" class="form-control" required>
                        <option value="Burial Permit">Burial Permit</option>
                        <option value="Tomb Construction">Tomb Construction</option>
                        <option value="Exhumation Permit">Exhumation Permit</option>
                        <option value="Niche Renewal">Niche Renewal</option>
                        <option value="Maintenance/Cleaning">Maintenance/Cleaning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" id="cemServiceClient" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Scheduled Date</label>
                    <input type="date" id="cemServiceDate" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="form-group">
                    <label>Assigned Staff/Team</label>
                    <input type="text" id="cemServiceStaff" class="form-control" value="Pedro Penduko" required>
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select id="cemServiceStatus" class="form-control" required>
                        <option value="Scheduled">Scheduled</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenCemSvcSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Schedule Service';
    submitBtn.onclick = () => document.getElementById('hiddenCemSvcSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewCemService = function() {
    let services = getCemeteryServices();
    let newService = {
        id: 'CS-' + Date.now().toString().slice(-3),
        type: document.getElementById('cemServiceType').value,
        client: document.getElementById('cemServiceClient').value,
        date: document.getElementById('cemServiceDate').value,
        staff: document.getElementById('cemServiceStaff').value,
        status: document.getElementById('cemServiceStatus').value
    };
    
    services.unshift(newService);
    saveCemeteryServices(services);
    
    closeModal();
    showToast('Service successfully scheduled!', 'success');
    renderCemeteryServicesPage();
}

window.openEditCemServiceModal = function(id) {
    let services = getCemeteryServices();
    let service = services.find(s => s.id === id);
    if (!service) return;

    modalTitle.textContent = 'Edit Service Details';
    
    modalBody.innerHTML = `
        <form id="editCemServiceForm" onsubmit="event.preventDefault(); saveEditedCemService('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Service Type</label>
                    <select id="editCemServiceType" class="form-control" required>
                        <option value="Burial Permit" ${service.type === 'Burial Permit' ? 'selected' : ''}>Burial Permit</option>
                        <option value="Tomb Construction" ${service.type === 'Tomb Construction' ? 'selected' : ''}>Tomb Construction</option>
                        <option value="Exhumation Permit" ${service.type === 'Exhumation Permit' ? 'selected' : ''}>Exhumation Permit</option>
                        <option value="Niche Renewal" ${service.type === 'Niche Renewal' ? 'selected' : ''}>Niche Renewal</option>
                        <option value="Maintenance/Cleaning" ${service.type === 'Maintenance/Cleaning' ? 'selected' : ''}>Maintenance/Cleaning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" id="editCemServiceClient" class="form-control" value="${service.client}" required>
                </div>
                <div class="form-group">
                    <label>Scheduled Date</label>
                    <input type="date" id="editCemServiceDate" class="form-control" value="${service.date}" required>
                </div>
                <div class="form-group">
                    <label>Assigned Staff/Team</label>
                    <input type="text" id="editCemServiceStaff" class="form-control" value="${service.staff}" required>
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select id="editCemServiceStatus" class="form-control" required>
                        <option value="Scheduled" ${service.status === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                        <option value="In Progress" ${service.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Completed" ${service.status === 'Completed' ? 'selected' : ''}>Completed</option>
                        <option value="Cancelled" ${service.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditCemSvcSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditCemSvcSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedCemService = function(id) {
    let services = getCemeteryServices();
    let index = services.findIndex(s => s.id === id);
    if (index > -1) {
        services[index].type = document.getElementById('editCemServiceType').value;
        services[index].client = document.getElementById('editCemServiceClient').value;
        services[index].date = document.getElementById('editCemServiceDate').value;
        services[index].staff = document.getElementById('editCemServiceStaff').value;
        services[index].status = document.getElementById('editCemServiceStatus').value;
        
        saveCemeteryServices(services);
        closeModal();
        showToast('Service details updated!', 'success');
        renderCemeteryServicesPage();
    }
}

window.deleteCemService = function(id) {
    if(confirm('Are you sure you want to remove this service log?')) {
        let services = getCemeteryServices();
        services = services.filter(s => s.id !== id);
        saveCemeteryServices(services);
        showToast('Service log removed.', 'success');
        renderCemeteryServicesPage();
    }
}

function renderDirectPaymentPage() {
    let optionsHtml = '';
    let roleName = '';
    let iconPrefix = '';
    let headerGradient = '';
    let themeColor = '';
    
    if (currentUserRole === 'atrium') {
        roleName = 'Atrium Hall personnel';
        iconPrefix = 'ATR';
        headerGradient = 'linear-gradient(135deg, var(--info-700), var(--info-500))';
        themeColor = 'var(--info)';
        optionsHtml = `
            <option value="Hall Rental (Full Day)">Hall Rental (Full Day)</option>
            <option value="Hall Rental (Half Day)">Hall Rental (Half Day)</option>
            <option value="Event Deposit">Event Deposit</option>
            <option value="Sound System & Lights">Sound System & Lights</option>
            <option value="Tables & Chairs Rental">Tables & Chairs Rental</option>
            <option value="Catering Corkage">Catering Corkage</option>
            <option value="Others">Others</option>
        `;
    } else {
        roleName = 'Cemetery personnel';
        iconPrefix = 'CEM';
        headerGradient = 'linear-gradient(135deg, var(--success-700), var(--success-500))';
        themeColor = 'var(--success)';
        optionsHtml = `
            <option value="Apartment Niche Rental (5 Years)">Apartment Niche Rental (5 Years)</option>
            <option value="Burial Permit Fee">Burial Permit Fee</option>
            <option value="Tomb Construction Permit">Tomb Construction Permit</option>
            <option value="Exhumation Permit Fee">Exhumation Permit Fee</option>
            <option value="Others">Others</option>
        `;
    }

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: ${headerGradient}; color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Direct Payment Collection</h2>
                        <p style="opacity: 0.9; margin: 0;">Officially receive and log walk-in payments. Transactions are instantly verified.</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: var(--radius-md); text-align: center; border: 1px solid rgba(255,255,255,0.3);">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">Total Collected Today</div>
                        <div style="font-size: 1.5rem; font-weight: bold;">₱15,000.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-1-2" style="grid-template-columns: 1fr 2fr;">
            <!-- Direct Collection Form -->
            <div class="card" style="align-self: start; background: white; box-shadow: var(--shadow-md); border-top: 4px solid ${themeColor};">
                <div class="card-header border-bottom">
                    <h3 style="color: var(--gray-800);"><i class="fas fa-file-invoice-dollar" style="margin-right: 8px; color: ${themeColor};"></i> New Transaction</h3>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <p class="text-muted text-sm mb-4">Record payments collected directly by ${roleName}. This logs the transaction immediately as 'Collected' without requiring an assigned collector.</p>
                    
                    <form id="directPaymentForm" onsubmit="event.preventDefault(); processDirectPayment('${iconPrefix}');">
                        <div class="form-group mb-3">
                            <label style="font-weight: 600; color: var(--gray-700);">Client Name / Payee</label>
                            <input type="text" id="dpClient" class="form-control" placeholder="Enter name..." required>
                        </div>
                        <div class="form-group mb-3">
                            <label style="font-weight: 600; color: var(--gray-700);">Payment For (Description)</label>
                            <select id="dpDesc" class="form-control" required>
                                <option value="" disabled selected>Select service or fee...</option>
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label style="font-weight: 600; color: var(--gray-700);">Amount Received (₱)</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; color: var(--gray-500);">₱</span>
                                <input type="number" id="dpAmount" class="form-control" placeholder="0.00" step="0.01" required style="font-size: 1.2rem; font-weight: 600; padding-left: 30px;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; background: ${themeColor}; border-color: ${themeColor};">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Process Payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Collections Feed -->
            <div class="card" style="border: none; box-shadow: var(--shadow-md);">
                <div class="card-header flex-between border-bottom">
                    <div>
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Collections Feed</h3>
                        <p class="text-muted text-sm" style="margin-top: 4px;">Payments recorded directly by you today.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Payee</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="directPaymentFeed">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem;">
                                        <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                        <p class="text-muted m-0">No direct payments processed yet today.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
}

window.processDirectPayment = function(iconPrefix) {
    const client = document.getElementById('dpClient').value;
    const desc = document.getElementById('dpDesc').value;
    const amount = document.getElementById('dpAmount').value;

    if(confirm(`Confirm direct payment collection of ₱${amount} from ${client} for ${desc}?`)) {
        let records = getRecords();
        let newRecord = {
            id: 'DP' + Date.now().toString().slice(-4),
            title: `${desc} - ${client}`,
            amount: amount,
            department: currentUserRole,
            date: new Date().toLocaleDateString(),
            status: 'Collected',
            sentToCollector: false
        };
        records.push(newRecord);
        saveRecords(records);
        
        showToast('Payment successfully collected and recorded!', 'success');
        
        // Reset form
        document.getElementById('directPaymentForm').reset();
        
        // Quick UI update for demo
        const tbody = document.getElementById('directPaymentFeed');
        if (tbody.children[0].textContent.includes('Load to view history')) {
            tbody.innerHTML = '';
        }
        
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><strong>#${iconPrefix}-${newRecord.id}</strong></td>
            <td>${client}</td>
            <td>${desc}</td>
            <td><strong>₱${parseFloat(amount).toFixed(2)}</strong></td>
            <td><span class="text-success" style="font-weight: 500;">Just now</span></td>
        `;
        tbody.insertBefore(newRow, tbody.firstChild);
    }
}

// ======================= TERMINAL PERSONNEL MODULES =======================

function getTerminalVehicles() {
    let logs = JSON.parse(localStorage.getItem('meedocentrix_terminal_logs'));
    if (!logs || logs.length === 0) {
        logs = [
            { id: 'TV-001', plate: 'ABC-1234', driver: 'Mario Lopez', type: 'Bus', status: 'Logged In', timeIn: '08:15', timeOut: '' },
            { id: 'TV-002', plate: 'XYZ-9876', driver: 'Juan Luna', type: 'Van', status: 'Departed', timeIn: '09:00', timeOut: '10:30' },
            { id: 'TV-003', plate: 'TR-001', driver: 'Emilio Aguinaldo', type: 'Tricycle', status: 'Logged In', timeIn: '10:45', timeOut: '' }
        ];
        localStorage.setItem('meedocentrix_terminal_logs', JSON.stringify(logs));
    }
    return logs;
}

function saveTerminalVehicles(logs) {
    localStorage.setItem('meedocentrix_terminal_logs', JSON.stringify(logs));
}

function renderTerminalVehiclesPage() {
    let logs = getTerminalVehicles();

    let trHtml = logs.length === 0 ? `<tr><td colspan="8" style="text-align: center; padding: 2rem;">No vehicle logs found.</td></tr>` : logs.map(log => {
        let statusBadge = '';
        if (log.status === 'Departed') statusBadge = `<span class="badge bg-gray-200 text-gray-700">Departed</span>`;
        else if (log.status === 'Logged In') statusBadge = `<span class="badge bg-success-light text-success">Logged In</span>`;
        else statusBadge = `<span class="badge bg-warning-light text-warning">${log.status}</span>`;

        return `
            <tr>
                <td><strong>#${log.id}</strong></td>
                <td><span class="badge bg-primary-50 text-primary-700 border border-primary-200" style="font-family: monospace; font-size: 0.85rem;">${log.plate}</span></td>
                <td>${log.driver}</td>
                <td>${log.type}</td>
                <td>${statusBadge}</td>
                <td>${formatTime(log.timeIn)}</td>
                <td>${formatTime(log.timeOut)}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditTerminalVehicleModal('${log.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteTerminalVehicle('${log.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--info-700), var(--info-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Terminal Vehicle Logs</h2>
                        <p style="opacity: 0.9; margin: 0;">Monitor and record arriving and departing buses, vans, and tricycles in the public terminal.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddTerminalVehicleModal()"><i class="fas fa-plus"></i> Log Vehicle Entry</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Today's Activity Log</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search plate no. or driver..." onkeyup="filterTerminalVehicles(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Plate Number</th>
                                <th>Driver / Operator</th>
                                <th>Vehicle Type</th>
                                <th>Status</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="terminalVehiclesTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterTerminalVehicles = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#terminalVehiclesTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const plate = row.children[1].textContent.toLowerCase();
        const driver = row.children[2].textContent.toLowerCase();
        if (plate.includes(q) || driver.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddTerminalVehicleModal = function() {
    modalTitle.textContent = 'Log New Vehicle Entry';
    
    modalBody.innerHTML = `
        <form id="addTermVehForm" onsubmit="event.preventDefault(); saveNewTerminalVehicle();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Plate Number</label>
                    <input type="text" id="tvPlate" class="form-control" placeholder="e.g. ABC-1234" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Driver / Operator Name</label>
                    <input type="text" id="tvDriver" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select id="tvType" class="form-control" required>
                        <option value="Bus">Bus</option>
                        <option value="Van">Van / UV Express</option>
                        <option value="Tricycle">Tricycle</option>
                        <option value="Jeepney">Jeepney</option>
                        <option value="Private/Others">Private/Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="tvStatus" class="form-control" required>
                        <option value="Logged In">Logged In</option>
                        <option value="Departed">Departed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time In</label>
                    <input type="time" id="tvTimeIn" class="form-control" value="${new Date().toTimeString().slice(0, 5)}" required>
                </div>
                <div class="form-group">
                    <label>Time Out</label>
                    <input type="time" id="tvTimeOut" class="form-control">
                </div>
            </div>
            <button type="submit" id="hiddenTvSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Log';
    submitBtn.onclick = () => document.getElementById('hiddenTvSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewTerminalVehicle = function() {
    let logs = getTerminalVehicles();
    let newLog = {
        id: 'TV-' + Date.now().toString().slice(-4),
        plate: document.getElementById('tvPlate').value.toUpperCase(),
        driver: document.getElementById('tvDriver').value,
        type: document.getElementById('tvType').value,
        status: document.getElementById('tvStatus').value,
        timeIn: document.getElementById('tvTimeIn').value,
        timeOut: document.getElementById('tvTimeOut').value
    };
    
    logs.unshift(newLog);
    saveTerminalVehicles(logs);
    
    closeModal();
    showToast('Vehicle logged successfully!', 'success');
    renderTerminalVehiclesPage();
}

window.openEditTerminalVehicleModal = function(id) {
    let logs = getTerminalVehicles();
    let log = logs.find(l => l.id === id);
    if (!log) return;

    modalTitle.textContent = 'Edit Vehicle Log';
    
    modalBody.innerHTML = `
        <form id="editTermVehForm" onsubmit="event.preventDefault(); saveEditedTerminalVehicle('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Plate Number</label>
                    <input type="text" id="editTvPlate" class="form-control" value="${log.plate}" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Driver / Operator Name</label>
                    <input type="text" id="editTvDriver" class="form-control" value="${log.driver}" required>
                </div>
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select id="editTvType" class="form-control" required>
                        <option value="Bus" ${log.type === 'Bus' ? 'selected' : ''}>Bus</option>
                        <option value="Van" ${log.type === 'Van' ? 'selected' : ''}>Van / UV Express</option>
                        <option value="Tricycle" ${log.type === 'Tricycle' ? 'selected' : ''}>Tricycle</option>
                        <option value="Jeepney" ${log.type === 'Jeepney' ? 'selected' : ''}>Jeepney</option>
                        <option value="Private/Others" ${log.type === 'Private/Others' ? 'selected' : ''}>Private/Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editTvStatus" class="form-control" required>
                        <option value="Logged In" ${log.status === 'Logged In' ? 'selected' : ''}>Logged In</option>
                        <option value="Departed" ${log.status === 'Departed' ? 'selected' : ''}>Departed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time In</label>
                    <input type="time" id="editTvTimeIn" class="form-control" value="${log.timeIn}" required>
                </div>
                <div class="form-group">
                    <label>Time Out</label>
                    <input type="time" id="editTvTimeOut" class="form-control" value="${log.timeOut || ''}">
                </div>
            </div>
            <button type="submit" id="hiddenEditTvSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditTvSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedTerminalVehicle = function(id) {
    let logs = getTerminalVehicles();
    let index = logs.findIndex(l => l.id === id);
    if (index > -1) {
        logs[index].plate = document.getElementById('editTvPlate').value.toUpperCase();
        logs[index].driver = document.getElementById('editTvDriver').value;
        logs[index].type = document.getElementById('editTvType').value;
        logs[index].status = document.getElementById('editTvStatus').value;
        logs[index].timeIn = document.getElementById('editTvTimeIn').value;
        logs[index].timeOut = document.getElementById('editTvTimeOut').value;
        
        saveTerminalVehicles(logs);
        closeModal();
        showToast('Vehicle log updated!', 'success');
        renderTerminalVehiclesPage();
    }
}

window.deleteTerminalVehicle = function(id) {
    if(confirm('Are you sure you want to delete this vehicle log?')) {
        let logs = getTerminalVehicles();
        logs = logs.filter(l => l.id !== id);
        saveTerminalVehicles(logs);
        showToast('Vehicle log deleted.', 'success');
        renderTerminalVehiclesPage();
    }
}

// ======================= ATRIUM PERSONNEL MODULES =======================

function getAtriumRecords() {
    let records = JSON.parse(localStorage.getItem('meedocentrix_atrium_records'));
    if (!records || records.length === 0) {
        records = [
            { id: 'ATR-001', event: 'Wedding Reception', client: 'Jose Rizal', date: '2026-04-15', status: 'Confirmed' },
            { id: 'ATR-002', event: 'Corporate Seminar', client: 'DEPEd Region IV', date: '2026-03-20', status: 'Pending Deposit' },
            { id: 'ATR-003', event: 'Birthday Party', client: 'Elena Marquez', date: '2026-05-01', status: 'Confirmed' }
        ];
        localStorage.setItem('meedocentrix_atrium_records', JSON.stringify(records));
    }
    return records;
}

function saveAtriumRecords(records) {
    localStorage.setItem('meedocentrix_atrium_records', JSON.stringify(records));
}

function renderAtriumRecordsPage() {
    let records = getAtriumRecords();

    let trHtml = records.length === 0 ? `<tr><td colspan="6" style="text-align: center; padding: 2rem;">No reservation records found.</td></tr>` : records.map(record => {
        let statusBadge = record.status === 'Confirmed' 
            ? `<span class="badge bg-success-light text-success">Confirmed</span>` 
            : `<span class="badge bg-warning-light text-warning">${record.status}</span>`;

        return `
            <tr>
                <td><strong>#${record.id}</strong></td>
                <td>${record.event}</td>
                <td>${record.client}</td>
                <td>${record.date}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-icon text-primary" onclick="openEditAtriumModal('${record.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-icon text-danger" onclick="deleteAtriumRecord('${record.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-700), var(--primary-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Reservation Records</h2>
                        <p style="opacity: 0.9; margin: 0;">Manage hall bookings, client details, and update reservation statuses.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddAtriumModal()"><i class="fas fa-plus"></i> New Booking</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Hall Bookings Registry</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search event or client..." onkeyup="filterAtriumRecords(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event Name</th>
                                <th>Client Details</th>
                                <th>Schedule Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="atriumRecordsTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

window.filterAtriumRecords = function(query) {
    const q = query.toLowerCase();
    const rows = document.querySelectorAll('#atriumRecordsTableBody tr');
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state
        const event = row.children[1].textContent.toLowerCase();
        const client = row.children[2].textContent.toLowerCase();
        if (event.includes(q) || client.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

window.openAddAtriumModal = function() {
    modalTitle.textContent = 'Add New Booking';
    
    modalBody.innerHTML = `
        <form id="addAtriumForm" onsubmit="event.preventDefault(); saveNewAtriumRecord();">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" id="atriumEvent" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Client Name / Contact</label>
                    <input type="text" id="atriumClient" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Schedule Date</label>
                    <input type="date" id="atriumDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="atriumStatus" class="form-control" required>
                        <option value="Pending Deposit">Pending Deposit</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenAtriumSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Booking';
    submitBtn.onclick = () => document.getElementById('hiddenAtriumSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveNewAtriumRecord = function() {
    let records = getAtriumRecords();
    let newRecord = {
        id: 'ATR-' + Date.now().toString().slice(-4),
        event: document.getElementById('atriumEvent').value,
        client: document.getElementById('atriumClient').value,
        date: document.getElementById('atriumDate').value,
        status: document.getElementById('atriumStatus').value
    };
    
    records.unshift(newRecord);
    saveAtriumRecords(records);
    
    closeModal();
    showToast('Booking successfully added!', 'success');
    renderAtriumRecordsPage();
}

window.openEditAtriumModal = function(id) {
    let records = getAtriumRecords();
    let record = records.find(r => r.id === id);
    if (!record) return;

    modalTitle.textContent = 'Edit Booking Record';
    
    modalBody.innerHTML = `
        <form id="editAtriumForm" onsubmit="event.preventDefault(); saveEditedAtriumRecord('${id}');">
            <div class="form-grid" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" id="editAtriumEvent" class="form-control" value="${record.event}" required>
                </div>
                <div class="form-group">
                    <label>Client Name / Contact</label>
                    <input type="text" id="editAtriumClient" class="form-control" value="${record.client}" required>
                </div>
                <div class="form-group">
                    <label>Schedule Date</label>
                    <input type="date" id="editAtriumDate" class="form-control" value="${record.date}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editAtriumStatus" class="form-control" required>
                        <option value="Pending Deposit" ${record.status === 'Pending Deposit' ? 'selected' : ''}>Pending Deposit</option>
                        <option value="Confirmed" ${record.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                        <option value="Cancelled" ${record.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                        <option value="Completed" ${record.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="hiddenEditAtriumSubmitBtn" style="display:none;"></button>
        </form>
    `;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.onclick = () => document.getElementById('hiddenEditAtriumSubmitBtn').click();
    
    modalOverlay.style.display = 'flex';
}

window.saveEditedAtriumRecord = function(id) {
    let records = getAtriumRecords();
    let index = records.findIndex(r => r.id === id);
    if (index > -1) {
        records[index].event = document.getElementById('editAtriumEvent').value;
        records[index].client = document.getElementById('editAtriumClient').value;
        records[index].date = document.getElementById('editAtriumDate').value;
        records[index].status = document.getElementById('editAtriumStatus').value;
        
        saveAtriumRecords(records);
        closeModal();
        showToast('Booking record updated!', 'success');
        renderAtriumRecordsPage();
    }
}

window.deleteAtriumRecord = function(id) {
    if(confirm('Are you sure you want to remove this booking?')) {
        let records = getAtriumRecords();
        records = records.filter(r => r.id !== id);
        saveAtriumRecords(records);
        showToast('Booking record removed.', 'success');
        renderAtriumRecordsPage();
    }
}

function renderBookingCalendarPage() {
    let records = getAtriumRecords();
    // Sort records by date for simple calendar feed
    records.sort((a, b) => new Date(a.date) - new Date(b.date));

    let feedHtml = records.length === 0 ? `<div style="padding: 2rem; text-align: center; color: var(--gray-500);">No upcoming bookings scheduled.</div>` : records.map(r => {
        let color = r.status === 'Confirmed' ? 'var(--success)' : r.status === 'Pending Deposit' ? 'var(--warning)' : 'var(--gray-400)';
        let dateObj = new Date(r.date);
        let month = dateObj.toLocaleString('default', { month: 'short' });
        let day = dateObj.getDate();
        
        return `
            <div style="display: flex; gap: 1rem; align-items: center; padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                <div style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 8px; width: 60px; height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary-600); font-weight: bold;">${month}</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: var(--gray-800); line-height: 1;">${day}</span>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; color: var(--gray-800);">${r.event}</h4>
                        <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; border: 1px solid ${color}; color: ${color};">${r.status}</span>
                    </div>
                    <p style="margin: 4px 0 0 0; color: var(--gray-500); font-size: 0.85rem;"><i class="fas fa-user" style="margin-right: 4px;"></i> ${r.client}</p>
                </div>
            </div>
        `;
    }).join('');

    contentArea.innerHTML = `
        <div class="card mb-4">
            <div class="card-header flex-between">
                <div>
                    <h3 style="margin-bottom: 0.5rem;"><i class="fas fa-calendar-alt text-primary-500" style="margin-right: 8px;"></i> Booking Calendar Schedule</h3>
                    <p style="opacity: 0.9; margin: 0; color: var(--gray-500); font-size: 0.85rem;">Chronological feed of all hall reservations.</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="navigateTo('atrium_records')">Manage Bookings</button>
            </div>
            <div class="card-body p-0">
                ${feedHtml}
            </div>
        </div>
    `;
}

// ======================= CHARTS =======================

function initDashboardChart() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;

    if(window.revenueChartInstance) {
        window.revenueChartInstance.destroy();
    }

    // Adding dynamic randomness for the filter simulation
    const randomMultiplier = () => (Math.random() * 0.5) + 0.8;

    window.revenueChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Public Market',
                    data: [12000, 19000, 15000, 22000, 18000, 25000, 28000].map(v => v * randomMultiplier()),
                    backgroundColor: '#2563eb'
                },
                {
                    label: 'Fishport',
                    data: [8000, 12000, 10000, 14000, 11000, 16000, 18000].map(v => v * randomMultiplier()),
                    backgroundColor: '#60a5fa'
                },
                {
                    label: 'Terminal',
                    data: [5000, 6000, 5500, 7000, 6500, 8000, 9000].map(v => v * randomMultiplier()),
                    backgroundColor: '#93bbfd'
                },
                {
                    label: 'Cemetery',
                    data: [2000, 3000, 2500, 1500, 4000, 3500, 5000].map(v => v * randomMultiplier()),
                    backgroundColor: '#bfdbfe'
                },
                {
                    label: 'Atrium Hall',
                    data: [0, 5000, 0, 0, 10000, 15000, 8000].map(v => v * randomMultiplier()),
                    backgroundColor: '#eff6ff'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1200,
                easing: 'easeOutQuart',
                delay: (context) => {
                    let delay = 0;
                    if (context.type === 'data' && context.mode === 'default') {
                        delay = context.dataIndex * 100 + context.datasetIndex * 50;
                    }
                    return delay;
                }
            },
            scales: {
                y: { beginAtZero: true, stacked: true },
                x: { stacked: true }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// ======================= UI INTERACTIONS =======================

window.togglePassword = function() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function ensureSidebarOverlay() {
    let overlay = document.querySelector('.sidebar-overlay');
    if (overlay) return overlay;

    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', () => {
        window.toggleSidebar(false);
    });
    document.body.appendChild(overlay);
    return overlay;
}

window.toggleSidebar = function(forceOpen = null) {
    const sidebar = document.getElementById('sidebar');
    const wrapper = document.querySelector('.main-wrapper');
    if (!sidebar) return;

    const isMobile = window.matchMedia('(max-width: 1024px)').matches;

    if (isMobile) {
        const overlay = ensureSidebarOverlay();
        const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !sidebar.classList.contains('open');

        sidebar.classList.toggle('open', shouldOpen);
        overlay.classList.toggle('show', shouldOpen);
        document.body.classList.toggle('sidebar-open', shouldOpen);
        sidebarOpen = shouldOpen;
        return;
    }

    const shouldExpand = typeof forceOpen === 'boolean' ? forceOpen : !sidebar.classList.contains('collapsed');
    sidebar.classList.toggle('collapsed', !shouldExpand);
    if (wrapper) wrapper.classList.toggle('expanded', !shouldExpand);
    sidebarOpen = shouldExpand;
}

window.toggleNotifications = function() {
    if(profileDropdown) profileDropdown.style.display = 'none';
    if(notifDropdown) notifDropdown.style.display = notifDropdown.style.display === 'none' ? 'block' : 'none';
}

window.toggleProfileMenu = function() {
    if(notifDropdown) notifDropdown.style.display = 'none';
    if(profileDropdown) profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
}

function closeDropdowns() {
    if(notifDropdown) notifDropdown.style.display = 'none';
    if(profileDropdown) profileDropdown.style.display = 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.topbar-right')) {
        closeDropdowns();
    }
});

// ======================= MODAL SYSTEM =======================

const modalOverlay = document.getElementById('modalOverlay');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const modalSubmitBtn = document.getElementById('modalSubmitBtn');

function resetModalSubmitButton() {
    if (!modalSubmitBtn) return;
    modalSubmitBtn.textContent = 'Save Changes';
    modalSubmitBtn.className = 'btn btn-primary';
    modalSubmitBtn.onclick = null;
}

window.openModal = function(title, content) {
    if(!modalOverlay || !modalTitle || !modalBody) return;
    
    modalTitle.textContent = title;
    
    // Generate a generic form structure for demo purposes
    modalBody.innerHTML = `
        <div class="form-group" style="margin-bottom: 1rem;">
            <p class="text-muted" style="margin-bottom: 1rem;">${content}</p>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Title / Identifier</label>
            <input type="text" class="form-control" placeholder="Enter details..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: inherit;">
        </div>
        <div class="form-group" style="margin-bottom: 1rem;">
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Amount / Value (₱)</label>
            <input type="number" class="form-control" placeholder="0.00" style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: inherit;">
        </div>
        <div class="form-group" style="margin-bottom: 1rem;">
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Notes</label>
            <textarea class="form-control" rows="3" placeholder="Optional notes..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: inherit; resize: vertical;"></textarea>
        </div>
    `;
    
    modalOverlay.style.display = 'flex';
}

window.closeModal = function(e) {
    if (e && e.target !== modalOverlay && !e.target.closest('.modal-close') && !e.target.closest('.btn-secondary')) return;
    if(modalOverlay) modalOverlay.style.display = 'none';
    resetModalSubmitButton();
}

// ======================= TOAST NOTIFICATIONS =======================

window.showToast = function(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if(!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem;
        background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-lg);
        margin-top: 0.75rem; min-width: 300px;
        transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-left: 4px solid ${type === 'success' ? 'var(--success)' : type === 'warning' ? 'var(--warning)' : type === 'error' ? 'var(--danger)' : 'var(--info)'};
    `;
    
    let icon = 'fa-info-circle';
    let iconColor = 'var(--info)';
    if(type === 'success') { icon = 'fa-check-circle'; iconColor = 'var(--success)'; }
    if(type === 'warning') { icon = 'fa-exclamation-triangle'; iconColor = 'var(--warning)'; }
    if(type === 'error') { icon = 'fa-times-circle'; iconColor = 'var(--danger)'; }

    toast.innerHTML = `
        <i class="fas ${icon}" style="color: ${iconColor}; font-size: 1.25rem;"></i>
        <span style="font-weight: 500;">${message}</span>
    `;
    
    container.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove after 3s
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    const syncSidebarOnResize = () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        if (!sidebar) return;

        if (!window.matchMedia('(max-width: 1024px)').matches) {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
            if (overlay) overlay.classList.remove('show');
        }
    };

    syncSidebarOnResize();
    window.addEventListener('resize', syncSidebarOnResize);

    if (isServerRenderedApp) {
        initializeServerRenderedApp();
        startServerLiveSync();
        return;
    }

    // Check if URL has a hash for quick login demo
    if(window.location.hash) {
        const role = window.location.hash.substring(1);
        if(ROLES[role]) {
            quickLogin(role);
        }
    }
});

window.addEventListener('popstate', () => {
    if (isServerRenderedApp) {
        initializeServerRenderedApp();
        startServerLiveSync();
    }
});
