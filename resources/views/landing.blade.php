<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meedocentrix | Enterprise System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text-main: #0f172a;
            --text-muted: #475569;
            --bg-body: #ffffff;
            --bg-alt: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* 
         * ==========================================
         * NAVBAR
         * ==========================================
         */
        .navbar {
            padding: 1.5rem 0;
            position: relative;
            z-index: 50;
            background-color: transparent;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--text-main) !important;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .brand-logo {
            height: 70px; /* Pushed height to maximum for strong visibility */
            width: auto; 
            object-fit: contain; 
        }

        /* Highly specialized CSS filter to instantly turn pure white PNGs into our var(--primary) #2563eb blue */
        .blue-tint-logo {
            filter: brightness(0) saturate(100%) invert(29%) sepia(96%) saturate(2206%) hue-rotate(214deg) brightness(101%) contrast(94%);
        }

        .nav-link {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0 1rem;
            color: var(--text-main) !important;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        .btn-contact {
            border: 2px solid var(--primary);
            background: transparent;
            color: var(--primary) !important;
            padding: 0.5rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-contact:hover {
            background: var(--primary);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        }

        /* 
         * ==========================================
         * HERO SECTION
         * ==========================================
         */
        .hero-section {
            position: relative;
            z-index: 10;
            min-height: calc(100vh - 90px);
            display: flex;
            align-items: center;
            padding-bottom: 2rem;
        }

        @media (min-width: 992px) {
            .hero-section {
                margin-top: -34px;
            }
        }

        .hero-content {
            padding-right: 3rem;
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .performance-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #EFF6FF;
            color: var(--primary);
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 2rem;
            border: 1px solid rgba(37, 99, 235, 0.15);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.05);
        }

        .title-main {
            font-size: clamp(3rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -1.5px;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .title-sub {
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
        }

        .hero-desc {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2.5rem;
            max-width: 90%;
            font-weight: 400;
        }

        .btn-get-started {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1rem 2.8rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .btn-get-started:hover {
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
        }

        .btn-get-started i {
            transition: transform 0.3s ease;
        }

        .btn-get-started:hover i {
            transform: translateX(5px);
        }

        /* 
         * ==========================================
         * MOCKUPS (Flawless Scaling CSS)
         * ==========================================
         */
        .mockups-grid {
            position: relative;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center; /* Horizontally center the mockups */
            margin-top: -40px; /* Push the whole group upwards slightly */
            animation: fadeLeft 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: 0.2s;
            opacity: 0;
            perspective: 1000px;
        }

        @keyframes fadeLeft {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .mockup-scaler {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-origin: center center; /* Scale from center now that it's centered */
            transform: scale(0.9);
            /* scale it slightly down universally to fit perfectly */
        }

        /* --- LAPTOP --- front and center */
        .macbook-wrapper {
            position: relative;
            width: 620px;
            z-index: 10;
            margin: 0 auto;
            transform: translateY(-10px);
        }

        .macbook-lid {
            background-color: #1e293b;
            border-radius: 16px 16px 0 0;
            padding: 18px 18px 24px 18px;
            box-shadow: 0 35px 60px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .macbook-lid::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background: #334155;
            border-radius: 50%;
        }

        .macbook-screen {
            background-color: var(--primary);
            /* SOLID BLUE BACKGROUND */
            height: 350px;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .macbook-screen img {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 2;
        }

        .macbook-screen .logo-text {
            width: 60%; /* Boosted from 50% to ensure it is very readable */
            height: auto;
            object-fit: contain;
            animation: fadeLogoText 12s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }

        .macbook-screen .logo-icon {
            width: 140px;
            /* Nice large icon size */
            height: 140px;
            object-fit: contain;
            border-radius: 28px;
            background-color: transparent;
            /* Assuming white logo */
            animation: fadeLogoIcon 12s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* 12 second loop guarantees exact 3-second transitions */
        @keyframes fadeLogoText {

            0%,
            25% {
                opacity: 1;
                filter: blur(0px);
                transform: translate(-50%, -50%) scale(1);
            }

            50%,
            75% {
                opacity: 0;
                filter: blur(5px);
                transform: translate(-50%, -50%) scale(0.95);
            }

            100% {
                opacity: 1;
                filter: blur(0px);
                transform: translate(-50%, -50%) scale(1);
            }
        }

        @keyframes fadeLogoIcon {

            0%,
            25% {
                opacity: 0;
                filter: blur(5px);
                transform: translate(-50%, -50%) scale(1.05);
            }

            50%,
            75% {
                opacity: 1;
                filter: blur(0px);
                transform: translate(-50%, -50%) scale(1);
            }

            100% {
                opacity: 0;
                filter: blur(5px);
                transform: translate(-50%, -50%) scale(1.05);
            }
        }

        .macbook-base {
            background-color: #cbd5e1;
            height: 20px;
            border-radius: 0 0 20px 20px;
            position: relative;
            width: 114%;
            left: -7%;
            box-shadow: inset 0px 4px 6px rgba(255, 255, 255, 0.5), 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .macbook-base::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 12%;
            height: 6px;
            background: #94a3b8;
            border-radius: 0 0 8px 8px;
        }

        /* --- PHONES --- */
        .iphone-wrapper {
            position: absolute;
            bottom: -20px;
            width: 210px;
            height: 430px;
            background-color: #0f172a;
            border-radius: 36px;
            padding: 10px;
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.35), inset 0 0 6px rgba(255, 255, 255, 0.2);
            z-index: 20;
            /* PLACE PHONES ON TOP OF THE LAPTOP SO THEY ARE FULLY VISIBLE */
        }

        .phone-left {
            left: -20px;
            transform: scale(0.75) translateY(40px) rotate(-4deg);
            transform-origin: center bottom;
        }

        .phone-right {
            right: -20px;
            transform: scale(0.75) translateY(40px) rotate(4deg);
            transform-origin: center bottom;
        }

        .iphone-screen {
            background-color: var(--bg-alt);
            border-radius: 26px;
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .iphone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 45%;
            height: 20px;
            background-color: #0f172a;
            border-radius: 0 0 12px 12px;
            z-index: 20;
        }

        /* Collector App UI Inside Phone */
        .col-ui-header {
            background-color: var(--primary);
            padding: 35px 15px 45px;
            color: white;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .col-ui-header h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .col-ui-header p {
            font-size: 10px;
            opacity: 0.8;
            margin: 2px 0 0 0;
        }

        .col-ui-body {
            position: relative;
            top: -30px;
            height: calc(100% - 60px);
            padding: 0 14px 20px 14px;
            overflow-y: auto;
            /* ENABLE SCROLLING */
            overflow-x: hidden;
            scrollbar-width: none;
            /* Firefox hide scrollbar */
        }

        .col-ui-body::-webkit-scrollbar {
            width: 0;
            background: transparent;
            /* Chrome/Safari hide scrollbar */
        }

        .c-card {
            background: white;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .c-card .icn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .icn-blue {
            background: #EFF6FF;
            color: var(--primary);
        }

        .icn-yellow {
            background: #FEFCE8;
            color: #ca8a04;
        }

        .c-card h4 {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }

        .c-card .val {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        .c-sec-title {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin: 10px 0 8px 4px;
            letter-spacing: 0.5px;
        }

        .c-item {
            background: white;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            border-left: 3px solid transparent;
            margin-bottom: 6px;
        }

        .c-item.paid {
            border-left-color: #22c55e;
        }

        .c-item.pend {
            border-left-color: #eab308;
        }

        .c-item .n {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 2px 0;
        }

        .c-item .d {
            font-size: 9px;
            color: var(--text-muted);
            margin: 0;
        }

        .c-badge {
            font-size: 8px;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .c-badge.paid {
            background: #dcfce7;
            color: #15803d;
        }

        .c-badge.pend {
            background: #fef08a;
            color: #a16207;
        }


        /* 
         * ==========================================
         * RESPONSIVENESS
         * ==========================================
         */
        @media (max-width: 1400px) {
            .mockup-scaler {
                transform: scale(0.85);
            }
        }

        @media (max-width: 1199px) {
            .mockup-scaler {
                transform: scale(0.7);
            }

            /* Pull phones tighter on smaller screens */
            .phone-left {
                left: -50px;
            }

            .phone-right {
                right: -50px;
            }
        }

        @media (max-width: 991px) {
            .navbar-toggler {
                border: none;
            }

            .navbar-toggler:focus {
                box-shadow: none;
            }

            .hero-section {
                align-items: flex-start;
                padding-top: 2rem;
                min-height: auto;
            }

            .hero-content {
                padding-right: 0;
                text-align: center;
                margin-bottom: 5rem;
            }

            .hero-desc {
                margin-left: auto;
                margin-right: auto;
            }

            /* Reposition Mockups for Stacked Layout */
            .mockups-grid {
                justify-content: center;
                height: 450px;
                margin-top: 1rem; /* Restore space on stacked mobile layout */
                margin-bottom: 2rem;
            }

            .mockup-scaler {
                transform-origin: center center;
                right: auto;
                left: 0;
                transform: scale(0.75);
            }
        }

        @media (max-width: 767px) {
            .mockups-grid {
                height: 350px;
            }

            .mockup-scaler {
                transform: scale(0.6);
            }

            /* phones very tight */
            .phone-left {
                left: -30px;
            }

            .phone-right {
                right: -30px;
            }
        }

        @media (max-width: 576px) {
            .title-main {
                font-size: 2.8rem;
            }

            .title-sub {
                font-size: 1.8rem;
            }

            .mockups-grid {
                height: 280px;
            }

            .mockup-scaler {
                transform: scale(0.45);
            }

            .hero-content {
                margin-bottom: 2rem;
            }

            /* Hide the extra phone on extremely tiny mobile if desired, or keep it */
            .phone-right {
                display: none;
            }

            .phone-left {
                left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid px-0 h-100 d-flex flex-column">

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logoforsidebar-cropped.png') }}" class="brand-logo blue-tint-logo" alt="Meedocentrix Logo">
            </a>
        </nav>

        <!-- Hero Content Area -->
        <div class="container flex-grow-1 d-flex hero-section">
            <div class="row w-100 align-items-center m-0">

                <!-- Left Text Column -->
                <div class="col-lg-5 col-md-12 hero-content">
                    <div class="performance-badge">
                        <i class="fa-solid fa-bolt text-warning"></i> Lightning Fast Performance
                    </div>

                    <h1 class="title-main">Meedocentrix</h1>
                    <h2 class="title-sub">Enterprise System</h2>
                    <p class="hero-desc">
                        A robust economic enterprise management system designed to streamline, manage, and securely
                        scale your business operations in one beautiful platform.
                    </p>

                    <a href="{{ route('login') }}" class="btn-get-started">
                        Get Started <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Right Mockups Column -->
                <div class="col-lg-7 col-md-12 mockups-grid">

                    <!-- Scalable Master Container -->
                    <div class="mockup-scaler">

                        <!-- Left Phone (Behind Laptop, Tilted) -->
                        <div class="iphone-wrapper phone-left">
                            <div class="iphone-notch"></div>
                            <div class="iphone-screen">
                                <!-- Collector Dashboard Code -->
                                <div class="col-ui-header">
                                    <h3>Collector App</h3>
                                    <p>Active Session</p>
                                </div>
                                <div class="col-ui-body" id="phoneScrollLeft">
                                    <div class="c-card">
                                        <div>
                                            <h4>Today's Total</h4>
                                            <p class="val">₱ 12,500.00</p>
                                        </div>
                                        <div class="icn icn-blue"><i class="fa-solid fa-wallet"></i></div>
                                    </div>

                                    <div class="c-card mb-3">
                                        <div>
                                            <h4>Pending Tickets</h4>
                                            <p class="val" style="font-size: 16px;">14 Pending</p>
                                        </div>
                                        <div class="icn icn-yellow"><i class="fa-solid fa-file-invoice"></i></div>
                                    </div>

                                    <div class="c-sec-title">Recent Transactions</div>
                                    <div class="c-list">
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Fish Port Stall A</p>
                                                <p class="d">Today, 09:30 AM</p>
                                            </div>
                                            <span class="c-badge paid">Paid</span>
                                        </div>
                                        <div class="c-item pend">
                                            <div>
                                                <p class="n">Market Section C</p>
                                                <p class="d">Today, 10:15 AM</p>
                                            </div>
                                            <span class="c-badge pend">Pending</span>
                                        </div>
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Slaughterhouse 1</p>
                                                <p class="d">Yesterday</p>
                                            </div>
                                            <span class="c-badge paid">Paid</span>
                                        </div>
                                    </div>
                                    <!-- Extra content for scrolling demo -->
                                    <div class="c-list mt-1">
                                        <div class="c-item pend">
                                            <div>
                                                <p class="n">Transport Fee</p>
                                                <p class="d">Yesterday</p>
                                            </div>
                                            <span class="c-badge pend">Pending</span>
                                        </div>
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Fish Port Stall B</p>
                                                <p class="d">Older</p>
                                            </div>
                                            <span class="c-badge paid">Paid</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Laptop (Front and Center) -->
                        <div class="macbook-wrapper">
                            <div class="macbook-lid">
                                <div class="macbook-screen">
                                    <img src="{{ asset('images/logoforsidebar-cropped.png') }}" class="logo-text blue-tint-logo"
                                        alt="Dashboard Sidebar Logo">
                                    <img src="{{ asset('images/meedologo.png') }}" class="logo-icon"
                                        alt="Meedocentrix Icon">
                                </div>
                            </div>
                            <div class="macbook-base"></div>
                        </div>

                        <!-- Right Phone (Behind Laptop, Tilted) -->
                        <div class="iphone-wrapper phone-right">
                            <div class="iphone-notch"></div>
                            <div class="iphone-screen">
                                <!-- Collector Dashboard Code -->
                                <div class="col-ui-header" style="background-color: var(--text-main);">
                                    <h3>Collector App</h3>
                                    <p>Performance View</p>
                                </div>
                                <div class="col-ui-body" id="phoneScrollRight">
                                    <div class="c-card">
                                        <div>
                                            <h4>Weekly Total</h4>
                                            <p class="val">₱ 58,200.00</p>
                                        </div>
                                        <div class="icn icn-blue"><i class="fa-solid fa-chart-line"></i></div>
                                    </div>

                                    <div class="c-card mb-3">
                                        <div>
                                            <h4>Compliance</h4>
                                            <p class="val" style="font-size: 16px;">94% Rate</p>
                                        </div>
                                        <div class="icn icn-yellow" style="color:#15803d; background:#dcfce7;"><i
                                                class="fa-solid fa-check-circle"></i></div>
                                    </div>

                                    <div class="c-sec-title">System Logs</div>
                                    <div class="c-list">
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Sync Success</p>
                                                <p class="d">Today, 09:00 AM</p>
                                            </div>
                                            <span class="c-badge paid">OK</span>
                                        </div>
                                        <div class="c-item pend">
                                            <div>
                                                <p class="n">Network Delay</p>
                                                <p class="d">Today, 08:15 AM</p>
                                            </div>
                                            <span class="c-badge pend">WARN</span>
                                        </div>
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Batch Upload</p>
                                                <p class="d">Yesterday</p>
                                            </div>
                                            <span class="c-badge paid">OK</span>
                                        </div>
                                    </div>
                                    <!-- Extra content for scrolling -->
                                    <div class="c-list mt-1">
                                        <div class="c-item paid">
                                            <div>
                                                <p class="n">Device Paired</p>
                                                <p class="d">Yesterday</p>
                                            </div>
                                            <span class="c-badge paid">OK</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /Scalable Master Container -->

                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

            const easeInOutQuad = (t) => (t < 0.5)
                ? 2 * t * t
                : 1 - Math.pow(-2 * t + 2, 2) / 2;

            const animateScroll = (element, target, duration) => new Promise((resolve) => {
                const startTop = element.scrollTop;
                const delta = target - startTop;

                if (Math.abs(delta) < 1 || duration <= 0) {
                    element.scrollTop = target;
                    resolve();
                    return;
                }

                const startTime = performance.now();

                const step = (now) => {
                    const progress = Math.min((now - startTime) / duration, 1);
                    const eased = easeInOutQuad(progress);
                    element.scrollTop = startTop + (delta * eased);

                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        resolve();
                    }
                };

                requestAnimationFrame(step);
            });

            const runPhoneScrollLoop = async (element, config) => {
                while (true) {
                    const maxScroll = element.scrollHeight - element.clientHeight;
                    if (maxScroll < 12) {
                        await wait(2000);
                        continue;
                    }

                    await wait(config.pauseTopMs);
                    await animateScroll(element, maxScroll * config.downTargetRatio, config.downDurationMs);
                    await wait(config.pauseBottomMs);
                    await animateScroll(element, 0, config.upDurationMs);
                }
            };

            const leftPhone = document.getElementById('phoneScrollLeft');
            const rightPhone = document.getElementById('phoneScrollRight');

            if (leftPhone) {
                leftPhone.scrollTop = 0;
                runPhoneScrollLoop(leftPhone, {
                    downDurationMs: 3200,
                    upDurationMs: 2200,
                    pauseTopMs: 1200,
                    pauseBottomMs: 1400,
                    downTargetRatio: 0.95,
                });
            }

            if (rightPhone) {
                rightPhone.scrollTop = 0;
                wait(450).then(() => runPhoneScrollLoop(rightPhone, {
                    downDurationMs: 3600,
                    upDurationMs: 2400,
                    pauseTopMs: 1400,
                    pauseBottomMs: 1600,
                    downTargetRatio: 0.92,
                }));
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
