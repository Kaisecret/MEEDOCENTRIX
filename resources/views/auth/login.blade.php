@extends('layouts.guest')
@section('content')

<div id="loginPage" class="login-page">
    <div class="login-wrapper">

        <!-- ===== LEFT: Blue Panel with Bubbles ===== -->
        <div class="login-left">
            <!-- Blue floating bubbles -->
            <div class="bubble bubble-1"></div>
            <div class="bubble bubble-2"></div>
            <div class="bubble bubble-3"></div>
            <div class="bubble bubble-4"></div>

            <div class="login-left-content">
                <!-- Shining Logo at top -->
                <div class="logo-showcase">
                    <div class="logo-glow"></div>
                    <div class="logo-shine"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" class="logo-main-img">
                        <circle cx="250" cy="250" r="250" fill="#294c7b" />
                        <rect x="130" y="260" width="60" height="110" fill="#cbd5e1" rx="6" />
                        <rect x="220" y="190" width="60" height="180" fill="#94a3b8" rx="6" />
                        <rect x="310" y="120" width="60" height="250" fill="#e2e8f0" rx="6" />
                        <path d="M 120 250 L 210 160 L 270 190 L 360 90" fill="none" stroke="#fbbf24" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" />
                        <polygon points="330,80 380,70 370,120" fill="#fbbf24" />
                    </svg>
                </div>
                <h1 class="welcome-text">WELCOME</h1>
                <h2 class="welcome-sub">Meedocentrix</h2>
                <p class="welcome-desc">Comprehensive Economic Enterprise Management System</p>
            </div>
        </div>

        <!-- ===== RIGHT: White Form Panel ===== -->
        <div class="login-right">
            <div class="login-right-inner">

                <!-- Mobile-only branding -->
                <div class="mobile-logo-area">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" class="mobile-logo">
                        <circle cx="250" cy="250" r="250" fill="#294c7b" />
                        <rect x="130" y="260" width="60" height="110" fill="#cbd5e1" rx="6" />
                        <rect x="220" y="190" width="60" height="180" fill="#94a3b8" rx="6" />
                        <rect x="310" y="120" width="60" height="250" fill="#e2e8f0" rx="6" />
                        <path d="M 120 250 L 210 160 L 270 190 L 360 90" fill="none" stroke="#fbbf24" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" />
                        <polygon points="330,80 380,70 370,120" fill="#fbbf24" />
                    </svg>
                    <h2>Meedocentrix</h2>
                </div>

                <div class="form-header">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to access the system</p>
                </div>

                <!-- Error -->
                <div id="loginError" class="login-error" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Access Denied</strong>
                        <p>Invalid username or password. Please try again.</p>
                    </div>
                    <button class="error-dismiss" onclick="document.getElementById('loginError').style.display='none'"><i class="fas fa-times"></i></button>
                </div>

                <!-- Form -->
                <form id="loginForm" onsubmit="return handleLogin(event)" autocomplete="off">
                    <div class="login-field">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <div class="field-input-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" id="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    <div class="login-field">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="field-input-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="password" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="login-options">
                        <label class="login-checkbox">
                            <input type="checkbox" id="rememberMe">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-login" id="loginBtn">
                        <span>Sign In</span>
                    </button>
                </form>

                <!-- Role Quick Select -->
                <div class="roles-section">
                    <div class="roles-divider"><span>or select a role</span></div>
                    <div class="role-grid">
                        <button onclick="quickLogin('administrator')" class="role-btn"><i class="fas fa-shield-halved"></i><span>Admin</span></button>
                        <button onclick="quickLogin('fishport')" class="role-btn"><i class="fas fa-fish"></i><span>Fishport</span></button>
                        <button onclick="quickLogin('market')" class="role-btn"><i class="fas fa-store"></i><span>Market</span></button>
                        <button onclick="quickLogin('cemetery')" class="role-btn"><i class="fas fa-cross"></i><span>Cemetery</span></button>
                        <button onclick="quickLogin('terminal')" class="role-btn"><i class="fas fa-bus"></i><span>Terminal</span></button>
                        <button onclick="quickLogin('atrium')" class="role-btn"><i class="fas fa-building"></i><span>Atrium</span></button>
                        <button onclick="quickLogin('collector')" class="role-btn"><i class="fas fa-hand-holding-dollar"></i><span>Collector</span></button>
                        <button onclick="quickLogin('cashier')" class="role-btn"><i class="fas fa-cash-register"></i><span>Cashier</span></button>
                    </div>
                </div>

                <div class="login-footer-text">
                    <p>&copy; 2026 Meedocentrix &middot; Municipal Government</p>
                </div>
            </div>
        </div>

    </div>
</div>


@endsection