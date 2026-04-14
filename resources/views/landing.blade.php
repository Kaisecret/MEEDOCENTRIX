<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meedocentrix Landing Page</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-blue: #2563eb;
            --light-cyan: #60a5fa;
            --mid-blue: #3b82f6;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0b1e5c 0%, #1a3fad 40%, #2b6aeb 75%, #4a9afc 100%);
            overflow-x: hidden;
            color: white;
            position: relative;
        }

        /* Background Liquid Shapes (Pure CSS 3D approximation) */
        .shape {
            position: absolute;
            border-radius: 50%;
            z-index: 0;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 45vw;
            height: 40vw;
            top: 10%;
            right: -5%;
            background: radial-gradient(circle at 30% 30%, #60a5fa, #1d4ed8);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            box-shadow: -10px 20px 50px rgba(0,0,0,0.3);
            animation-duration: 8s;
        }

        .shape-2 {
            width: 15vw;
            height: 15vw;
            bottom: -5%;
            right: 5%;
            background: radial-gradient(circle at 30% 30%, #93bbfd, #2563eb);
            border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%;
            box-shadow: -5px 15px 30px rgba(0,0,0,0.2);
            animation-duration: 7s;
            animation-delay: 1s;
        }

        .shape-3 {
            width: 8vw;
            height: 8vw;
            top: 20%;
            left: 55%;
            background: radial-gradient(circle at 30% 30%, #3b82f6, #1e3a8a);
            border-radius: 50% 50% 40% 60% / 60% 40% 50% 50%;
            box-shadow: -5px 10px 20px rgba(0,0,0,0.2);
            animation-duration: 5s;
            animation-delay: 0.5s;
        }
        
        .shape-4 {
            width: 10vw;
            height: 10vw;
            bottom: 10%;
            left: 50%;
            background: radial-gradient(circle at 30% 30%, #2563eb, #0f2240);
            border-radius: 30% 70% 50% 50% / 40% 40% 60% 60%;
            box-shadow: -5px 10px 20px rgba(0,0,0,0.2);
            animation-duration: 9s;
            animation-delay: 2s;
        }

        .shape-5 {
            width: 12vw;
            height: 12vw;
            bottom: -5%;
            left: 20%;
            background: radial-gradient(circle at 30% 30%, #60a5fa, #152e56);
            border-radius: 50%;
            opacity: 0.6;
            animation-duration: 6s;
        }

        /* Elongated top right shape */
        .shape-6 {
            width: 30vw;
            height: 10vw;
            top: 5%;
            right: -10%;
            background: radial-gradient(ellipse at 30% 30%, #3b82f6, #0a1628);
            border-radius: 50px;
            transform: rotate(-20deg);
            box-shadow: -10px 20px 40px rgba(0,0,0,0.3);
            animation-duration: 10s;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        /* Decorative dots and squiggles */
        .dot {
            position: absolute;
            background-color: rgba(255,255,255,0.6);
            border-radius: 50%;
        }

        /* Content Wrapper to sit above background */
        .content-wrapper {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar {
            padding-top: 2rem;
            padding-bottom: 1rem;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.25rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: 1px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-size: 0.95rem;
            margin: 0 15px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            background-color: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover {
            color: white !important;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-outline-custom {
            border: 1.5px solid rgba(255,255,255,0.4);
            border-radius: 50px;
            padding: 0.5rem 1.8rem;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(5px);
            display: inline-block;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: var(--primary-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Hero Section Styling */
        .hero-section {
            flex-grow: 1;
            display: flex;
            align-items: center;
            padding-top: 2rem;
            padding-bottom: 4rem; /* Provides safe space so icons won't hit bottom */
        }

        .hero-content {
            animation: fadeUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .badge-pill-custom {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        h1.display-title {
            font-size: 4.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.5rem;
            letter-spacing: -1.5px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        h2.display-subtitle {
            font-size: 2.6rem;
            font-weight: 300;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            opacity: 0.95;
            letter-spacing: -0.5px;
        }

        .hero-text {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.85);
            max-width: 500px;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            font-weight: 400;
        }

        /* Enhanced Start Button */
        .btn-start {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            color: var(--primary-blue);
            font-weight: 700;
            padding: 0.9rem 2.5rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border: 2px solid rgba(255,255,255,0.9);
        }

        .btn-start:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
            color: #1e3a8a;
            background: #ffffff;
            border-color: #ffffff;
        }
        
        .btn-start i {
            transition: transform 0.3s ease;
            font-size: 1rem;
        }

        .btn-start:hover i {
            transform: translateX(6px);
        }

        /* Fixed Social Icons - Changed from absolute to margin-top to avoid overlapping */
        .social-icons {
            margin-top: 4.5rem;
            display: flex;
            gap: 1.2rem;
            animation: fadeIn 1.5s ease-out forwards;
            animation-delay: 0.6s;
            opacity: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .social-icons a {
            color: white;
            font-size: 1.1rem;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .social-icons a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            h1.display-title { font-size: 4rem; }
            h2.display-subtitle { font-size: 2.2rem; }
            .hero-section { padding-top: 1rem; }
            .social-icons { margin-top: 3.5rem; }
        }

        @media (max-width: 768px) {
            h1.display-title { font-size: 3.2rem; }
            h2.display-subtitle { font-size: 1.8rem; }
            .shape-1 { width: 80vw; height: 70vw; }
            .social-icons { justify-content: center; margin-top: 3rem; }
            .hero-content { text-align: center; display: flex; flex-direction: column; align-items: center; }
            .hero-text { text-align: center; }
        }
    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
    <div class="shape shape-6"></div>

    <div class="dot" style="width: 5px; height: 5px; top: 30%; left: 50%;"></div>
    <div class="dot" style="width: 8px; height: 8px; top: 35%; left: 48%;"></div>
    <div class="dot" style="width: 4px; height: 4px; top: 40%; left: 52%;"></div>
    <div class="dot" style="width: 6px; height: 6px; bottom: 30%; right: 30%;"></div>
    
    <div class="container content-wrapper">
        
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid px-0">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" style="width: 38px; height: 38px; border-radius: 50%; border: 2.5px solid rgba(255,255,255,0.9); background-color: #1e3a8a; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <circle cx="250" cy="250" r="250" fill="#294c7b" />
                        <rect x="130" y="260" width="60" height="110" fill="#cbd5e1" rx="6" />
                        <rect x="220" y="190" width="60" height="180" fill="#94a3b8" rx="6" />
                        <rect x="310" y="120" width="60" height="250" fill="#e2e8f0" rx="6" />
                        <path d="M 120 250 L 210 160 L 270 190 L 360 90" fill="none" stroke="#fbbf24" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" />
                        <polygon points="330,80 380,70 370,120" fill="#fbbf24" />
                    </svg>
                    MEEDOCENTRIX
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border-color: rgba(255,255,255,0.5);">
                    <i class="fa-solid fa-bars text-white"></i>
                </button>
                
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">HOME</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">TEAM</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">LOGIN</a>
                        </li>
                        <li class="nav-item ms-3 mt-3 mt-lg-0">
                            <a href="#" class="btn-outline-custom">Contact Sales</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="hero-section row">
            <div class="col-lg-7 col-md-9 position-relative hero-content">
                <div class="badge-pill-custom">
                    <i class="fa-solid fa-bolt me-2 text-warning"></i>Lightning Fast Performance
                </div>
                
                <h1 class="display-title">Meedocentrix</h1>
                <h2 class="display-subtitle">Enterprise System</h2>
                <p class="hero-text">
                    Comprehensive Economic Enterprise Management System. A powerful platform to manage, streamline, and scale your business operations efficiently.
                </p>
                
                <a href="{{ route('login') }}" class="btn btn-start text-decoration-none">
                    Get Started <i class="fa-solid fa-arrow-right"></i>
                </a>

                <!-- Changed to position relative using margin instead of position:absolute; bottom:0 -->
                <div class="social-icons">
                    <a href="#" aria-label="Website"><i class="fa-solid fa-globe"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
