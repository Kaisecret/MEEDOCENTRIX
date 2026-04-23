<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Meedocentrix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-primary: #2563eb;  
            --accent-hover: #1e40af;
            --text-main: #0f172a;
            --text-muted: #475569;
        }

        body {
            background-color: #f1f5f9; 
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .login-wrapper {
            max-width: 1100px; 
            min-height: 650px; 
            border-radius: 24px; 
            position: relative; 
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15); 
            border: none;
            background-color: #ffffff;
            display: flex;
            flex-direction: column; /* Force column layout for wrapper */
            overflow: hidden;
        }

        /* NEW FIX: Tell the inner row to grow and fill the entire wrapper */
        .login-wrapper > .row {
            flex-grow: 1;
        }

        /* create a directory  NAME Regala_WS101Act3 */
        /* =========================================================
           ANIMATION & COLUMN LAYOUT SETUP
           ========================================================= */
        .transition-col {
            transition: width 0.9s cubic-bezier(0.16, 1, 0.3, 1), 
                        opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden; 
            /* Removed height: 100% to let flex stretch it naturally */
            display: flex; 
            flex-direction: column;
        }

        #leftCol {
            width: 41.666667%; 
        }

        #rightCol {
            width: 58.333333%; 
        }

        /* --- STATE WHEN ANIMATION IS ACTIVE --- */
        .login-wrapper.centered-mode #leftCol {
            width: 0%;
            opacity: 0;
        }

        .login-wrapper.centered-mode #rightCol {
            width: 100%;
        }

        .login-wrapper.centered-mode .right-panel {
            border-radius: 24px !important; 
        }

        /* LEFT PANEL */
        .left-panel {
            background-color: #2563eb;
            color: white;
            border-top-left-radius: 24px;
            border-bottom-left-radius: 24px;
            padding: 3.5rem !important;
            min-width: 400px;
            /* Removed height: 100% */
            flex-grow: 1; /* This guarantees it stretches to the absolute bottom */
            width: 100%;
        }

        .premium-logo-badge {
            width: 260px;
            height: auto;
            max-width: 80%;
            border-radius: 0;
            border: none;
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.25));
            margin-bottom: 1.5rem;
            background-color: transparent;
            object-fit: contain;
            object-position: center;
            padding: 0;
            position: relative;
            z-index: 0;
            display: block;
            transform: scale(1.35);
            transform-origin: center;
        }

        .btn-learn-more {
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            color: white;
            border-radius: 50px;
            padding: 10px 36px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-learn-more:hover {
            background-color: white;
            color: var(--accent-primary);
            transform: translateY(-2px);
        }

        /* =========================================================
           THE TOGGLE BUTTON
           ========================================================= */
        .overlap-btn {
            width: 54px;
            height: 54px;
            background: var(--accent-primary);
            border: 5px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: absolute;
            z-index: 20;
            font-size: 1.2rem;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
            cursor: pointer;
            
            top: 50%;
            left: 41.666667%;
            transform: translate(-50%, -50%) rotate(0deg);
            transition: left 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                        background-color 0.3s ease;
        }

        .overlap-btn:hover {
            background-color: var(--accent-hover);
        }

        .login-wrapper.centered-mode .overlap-btn {
            left: 27px; 
            transform: translate(-50%, -50%) rotate(180deg); 
        }

        /* RIGHT PANEL */
        .right-panel {
            background-color: #ffffff;
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
            padding: 4.5rem 5.5rem !important; 
            /* Removed height: 100% */
            flex-grow: 1;
            width: 100%; 
            transition: border-radius 0.9s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- INPUT FIELD ICONS & PASSWORD TOGGLE --- */
        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .form-control-custom {
            padding-left: 48px !important;
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--accent-primary);
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.5rem;
            line-height: 1;
            z-index: 2;
        }
        .password-toggle:hover { color: var(--accent-primary); }
        #passwordInput.form-control-custom { padding-right: 50px !important; }

        /* UX IMPROVEMENTS FOR INPUTS */
        .form-label-custom {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            display: block;
            text-align: left;
        }

        .form-control-custom {
            border-radius: 8px; 
            padding: 14px 16px;
            border: 2px solid #cbd5e1; 
            font-size: 1rem; 
            background-color: #ffffff; 
            color: var(--text-main);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: var(--accent-primary); 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .btn-login {
            background-color: var(--accent-primary);
            color: white;
            border-radius: 8px;
            padding: 14px;
            font-weight: 700;
            font-size: 1.1rem; 
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-login:hover {
            background-color: var(--accent-hover);
            color: white;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }

        .btn-login:disabled {
            cursor: not-allowed;
            opacity: 0.9;
            transform: none;
            box-shadow: none;
        }

        .btn-login.is-loading {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .form-check-label {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 600;
        }
        
        .forgot-link {
            font-size: 0.95rem;
            color: var(--accent-primary); 
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            text-decoration: underline; 
            color: var(--accent-hover);
        }

        .register-link {
            color: var(--accent-primary);
            font-weight: 700;
            text-decoration: underline; 
        }

        .auth-form-shell {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }

        .auth-feedback {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.8rem 0.95rem;
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        .auth-feedback i {
            margin-top: 0.1rem;
        }

        /* Mobile & Tablet Responsiveness */
        @media (max-width: 991px) {
            .login-wrapper {
                min-height: auto; 
                height: auto;
            }

            .premium-logo-badge {
                width: 180px;
                height: auto;
                margin-bottom: 1rem !important;
            }

            .left-panel .mb-5 { margin-bottom: 2rem !important; }
            .right-panel .mb-5 { margin-bottom: 2.5rem !important; }
            .right-panel .mb-4 { margin-bottom: 1.25rem !important; }
            #leftCol { display: none !important; }
            #rightCol { width: 100% !important; }
            .right-panel { border-radius: 24px !important; padding: 3rem 1.5rem !important; }
            .overlap-btn { display: none !important; }
        }
    </style>
</head>
<body class="d-flex align-items-start align-items-md-center justify-content-center min-vh-100 p-3">

    <div class="container login-wrapper p-0" id="mainWrapper">
        
        <div class="overlap-btn" id="togglePanelBtn">
            <i class="fas fa-chevron-left" id="chevronIcon"></i>
        </div>

        <div class="row g-0 w-100 h-100 align-items-stretch">
            
            <div class="transition-col" id="leftCol">
                <div class="left-panel d-flex flex-column align-items-center justify-content-center text-center">
                    
                    <img src="{{ asset('images/meedologo.png') }}" alt="Meedocentrix Logo" class="premium-logo-badge">
                    
                    <div class="mb-5 mt-2">
                        <p class="text-white opacity-90 px-2" style="line-height: 1.6; font-size: 1.15rem; font-weight: 500;">
                            Comprehensive Economic Enterprise<br>Management System
                        </p>
                    </div>

                    <button class="btn btn-learn-more">Learn More</button>
                </div>
            </div>

            <div class="transition-col" id="rightCol">
                <div class="right-panel d-flex flex-column justify-content-center">

                    <div class="d-block d-lg-none text-center mb-4">
                        <img src="{{ asset('images/meedologo.png') }}" alt="Meedocentrix Logo" style="width: 220px; max-width: 72vw; height: auto; margin-bottom: 0.8rem; object-fit: contain; object-position: center; position: relative; z-index: 0; filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.18));">
                    </div>

                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2" style="color: var(--text-main);">Welcome Back!</h2>
                        <p class="mb-3" style="color: var(--text-muted); font-size: 1.05rem;">Sign in to your account to continue</p>
                    </div>

                    <div class="auth-form-shell">
                        <form id="personnelLoginForm" action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="emailInput" class="form-label-custom">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="email" id="emailInput" name="email" value="{{ old('email') }}" class="form-control form-control-custom" placeholder="Please enter your email address" required autocomplete="email">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="passwordInput" class="form-label-custom">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="passwordInput" name="password" class="form-control form-control-custom" placeholder="Please enter your password" required autocomplete="current-password">
                                <button type="button" class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="auth-feedback" role="alert" aria-live="polite">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        @elseif ($errors->any())
                            <div class="auth-feedback" role="alert" aria-live="polite">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <div class="form-check">
                                <input class="form-check-input shadow-none" type="checkbox" id="rememberMe" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;">
                                <label class="form-check-label ms-1" for="rememberMe" style="cursor: pointer;">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="forgot-link" onclick="event.preventDefault();">Forgot Password</a>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" id="personnelLoginBtn" class="btn btn-login py-3" data-loading-label="Signing in...">Login</button>
                        </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Panel toggle animation (only for desktop)
        const toggleBtn = document.getElementById('togglePanelBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                document.getElementById('mainWrapper').classList.toggle('centered-mode');
            });
        }

        // Password visibility toggle
        const togglePasswordBtn = document.getElementById('togglePassword');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('passwordInput');
                const icon = this.querySelector('i');

                // Toggle the type
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the icon
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        }

        // Submit loading feedback
        const personnelLoginForm = document.getElementById('personnelLoginForm');
        const personnelLoginBtn = document.getElementById('personnelLoginBtn');
        if (personnelLoginForm && personnelLoginBtn) {
            const defaultLabel = personnelLoginBtn.textContent.trim();
            const loadingLabel = personnelLoginBtn.dataset.loadingLabel || 'Signing in...';

            personnelLoginForm.addEventListener('submit', function(event) {
                if (personnelLoginForm.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                personnelLoginForm.dataset.submitting = '1';
                personnelLoginBtn.disabled = true;
                personnelLoginBtn.classList.add('is-loading');
                personnelLoginBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i><span>${loadingLabel}</span>`;

                // Fallback: if submission is blocked by browser/network interruption, restore button.
                window.setTimeout(() => {
                    if (document.visibilityState === 'visible') {
                        personnelLoginForm.dataset.submitting = '0';
                        personnelLoginBtn.disabled = false;
                        personnelLoginBtn.classList.remove('is-loading');
                        personnelLoginBtn.textContent = defaultLabel;
                    }
                }, 12000);
            });
        }
    </script>
</body> 
</html>
