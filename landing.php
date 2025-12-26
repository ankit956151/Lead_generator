<?php
// Include configuration
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LeadGen - The Ultimate Lead Generation Platform. Capture, manage, and convert leads with powerful tools and analytics.">
    <meta name="keywords" content="lead generation, leads, marketing, CRM, conversion, analytics">
    <meta name="author" content="LeadGen">
    <title>LeadGen - Powerful Lead Generation Platform</title>
    
    <!-- Open Graph -->
    <meta property="og:title" content="LeadGen - Powerful Lead Generation Platform">
    <meta property="og:description" content="Capture, manage, and convert leads with powerful tools and analytics.">
    <meta property="og:type" content="website">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* CSS Variables */
        :root {
            --color-primary: #6366f1;
            --color-primary-dark: #4f46e5;
            --color-secondary: #06b6d4;
            --color-accent: #8b5cf6;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            
            --bg-dark: #0f0f1a;
            --bg-darker: #080810;
            --bg-card: rgba(26, 26, 46, 0.6);
            --bg-glass: rgba(255, 255, 255, 0.03);
            
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            
            --border-color: rgba(255, 255, 255, 0.08);
            
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            --gradient-glow: radial-gradient(ellipse at 50% 0%, rgba(99, 102, 241, 0.3) 0%, transparent 50%);
            
            --font-primary: 'Inter', sans-serif;
            --font-display: 'Outfit', sans-serif;
        }

        /* Reset */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: var(--font-primary);
            background: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-darker);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 4px;
        }

        /* Selection */
        ::selection {
            background: var(--color-primary);
            color: white;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 20px 0;
            background: transparent;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(15, 15, 26, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 0;
        }

        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
        }

        .logo-icon i {
            font-size: 20px;
            color: white;
        }

        .logo-text {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
        }

        .logo-text span {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-primary);
        }

        .btn-ghost:hover {
            background: var(--bg-glass);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 16px;
        }

        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 18px;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 120px 0 80px;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: var(--gradient-glow);
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease-out;
        }

        .hero-badge i {
            color: var(--color-warning);
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease-out 0.1s both;
        }

        .hero-title .gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 40px;
            max-width: 500px;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
            animation: fadeInUp 0.6s ease-out 0.3s both;
        }

        .hero-stats {
            display: flex;
            gap: 48px;
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .stat {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
        }

        .hero-visual {
            position: relative;
            animation: fadeInRight 0.8s ease-out 0.3s both;
        }

        .hero-image {
            position: relative;
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 24px;
            backdrop-filter: blur(20px);
        }

        .dashboard-preview {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.8), rgba(15, 15, 26, 0.9));
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid var(--border-color);
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green { background: #28c840; }

        .dashboard-body {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .mini-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
        }

        .mini-card-value {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .mini-card-label {
            font-size: 12px;
            color: var(--text-muted);
        }

        .chart-placeholder {
            grid-column: span 3;
            height: 120px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            display: flex;
            align-items: flex-end;
            padding: 16px;
            gap: 8px;
        }

        .bar {
            flex: 1;
            background: var(--gradient-primary);
            border-radius: 4px;
            opacity: 0.6;
        }

        .floating-card {
            position: absolute;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .floating-card.card-1 {
            top: -20px;
            left: -40px;
            animation: float 3s ease-in-out infinite;
        }

        .floating-card.card-2 {
            bottom: 40px;
            right: -40px;
            animation: float 3s ease-in-out infinite 1.5s;
        }

        .floating-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .floating-icon.purple {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
        }

        .floating-icon.green {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .floating-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .floating-value {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Features Section */
        .features {
            padding: 120px 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 64px;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            font-size: 13px;
            color: var(--color-primary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .section-desc {
            font-size: 18px;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--color-primary);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.15);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--color-primary);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .feature-desc {
            font-size: 15px;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* Pricing Section */
        .pricing {
            padding: 120px 0;
            background: linear-gradient(180deg, transparent 0%, rgba(99, 102, 241, 0.05) 50%, transparent 100%);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            align-items: start;
        }

        .pricing-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .pricing-card.featured {
            background: linear-gradient(180deg, rgba(99, 102, 241, 0.1) 0%, var(--bg-card) 100%);
            border-color: var(--color-primary);
            transform: scale(1.05);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.2);
        }

        .pricing-badge {
            display: inline-block;
            padding: 4px 12px;
            background: var(--gradient-primary);
            color: white;
            font-size: 12px;
            font-weight: 600;
            border-radius: 50px;
            margin-bottom: 16px;
        }

        .pricing-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .pricing-price {
            font-family: var(--font-display);
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .pricing-price span {
            font-size: 16px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .pricing-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .pricing-features {
            text-align: left;
            margin-bottom: 32px;
        }

        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-secondary);
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features i {
            color: var(--color-success);
        }

        /* CTA Section */
        .cta {
            padding: 120px 0;
        }

        .cta-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            padding: 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: var(--gradient-glow);
            opacity: 0.3;
        }

        .cta-title {
            font-family: var(--font-display);
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 16px;
            position: relative;
        }

        .cta-desc {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
        }

        .cta-form {
            display: flex;
            gap: 12px;
            justify-content: center;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }

        .cta-input {
            flex: 1;
            padding: 16px 24px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 16px;
        }

        .cta-input::placeholder {
            color: var(--text-muted);
        }

        .cta-input:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        /* Footer */
        .footer {
            padding: 80px 0 40px;
            border-top: 1px solid var(--border-color);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr);
            gap: 60px;
            margin-bottom: 60px;
        }

        .footer-brand p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 16px;
            max-width: 300px;
        }

        .footer-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            color: var(--text-muted);
        }

        .footer-links a {
            display: block;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 0;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--text-primary);
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 40px;
            border-top: 1px solid var(--border-color);
        }

        .footer-copyright {
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer-socials {
            display: flex;
            gap: 16px;
        }

        .footer-socials a {
            width: 40px;
            height: 40px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .footer-socials a:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-title {
                font-size: 48px;
            }

            .hero-visual {
                order: -1;
            }

            .floating-card {
                display: none;
            }

            .features-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .pricing-card.featured {
                transform: none;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .hero-title {
                font-size: 36px;
            }

            .hero-stats {
                gap: 32px;
            }

            .stat-value {
                font-size: 28px;
            }

            .section-title {
                font-size: 32px;
            }

            .pricing-card {
                padding: 32px 24px;
            }

            .cta-card {
                padding: 48px 24px;
            }

            .cta-title {
                font-size: 28px;
            }

            .cta-form {
                flex-direction: column;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 100px 0 60px;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#" class="nav-logo">
                <div class="logo-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <span class="logo-text">Lead<span>Gen</span></span>
            </a>
            
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
            
            <div class="nav-actions">
                <a href="index.html" class="btn btn-ghost">Login</a>
                <a href="index.html" class="btn btn-primary">Get Started</a>
            </div>

            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-star"></i>
                        <span>Trusted by 10,000+ businesses worldwide</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Capture & Convert <br>
                        <span class="gradient">More Leads</span> Effortlessly
                    </h1>
                    
                    <p class="hero-desc">
                        The all-in-one lead generation platform that helps you capture, manage, 
                        and convert leads with powerful automation and analytics.
                    </p>
                    
                    <div class="hero-actions">
                        <a href="index.html" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i>
                            Start Free Trial
                        </a>
                        <a href="#demo" class="btn btn-ghost btn-lg">
                            <i class="fas fa-play-circle"></i>
                            Watch Demo
                        </a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-value">12M+</span>
                            <span class="stat-label">Leads Captured</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">45%</span>
                            <span class="stat-label">Avg. Conversion</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">99.9%</span>
                            <span class="stat-label">Uptime</span>
                        </div>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-image">
                        <div class="dashboard-preview">
                            <div class="dashboard-header">
                                <span class="dot dot-red"></span>
                                <span class="dot dot-yellow"></span>
                                <span class="dot dot-green"></span>
                            </div>
                            <div class="dashboard-body">
                                <div class="mini-card">
                                    <div class="mini-card-value" style="color: var(--color-primary)">12,847</div>
                                    <div class="mini-card-label">Total Leads</div>
                                </div>
                                <div class="mini-card">
                                    <div class="mini-card-value" style="color: var(--color-success)">3,420</div>
                                    <div class="mini-card-label">Converted</div>
                                </div>
                                <div class="mini-card">
                                    <div class="mini-card-value" style="color: var(--color-secondary)">26.6%</div>
                                    <div class="mini-card-label">Conv. Rate</div>
                                </div>
                                <div class="chart-placeholder">
                                    <div class="bar" style="height: 40%"></div>
                                    <div class="bar" style="height: 60%"></div>
                                    <div class="bar" style="height: 45%"></div>
                                    <div class="bar" style="height: 80%"></div>
                                    <div class="bar" style="height: 65%"></div>
                                    <div class="bar" style="height: 90%"></div>
                                    <div class="bar" style="height: 75%"></div>
                                    <div class="bar" style="height: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="floating-card card-1">
                        <div class="floating-icon purple">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="floating-title">New Lead</div>
                        <div class="floating-value">Sarah Johnson</div>
                    </div>
                    
                    <div class="floating-card card-2">
                        <div class="floating-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="floating-title">Converted</div>
                        <div class="floating-value">+$2,400 Revenue</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2 class="section-title">Everything you need to grow</h2>
                <p class="section-desc">
                    Powerful tools designed to help you capture, manage, and convert 
                    more leads than ever before.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <h3 class="feature-title">Smart Lead Forms</h3>
                    <p class="feature-desc">
                        Create beautiful, high-converting forms with our drag-and-drop 
                        builder. No coding required.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="feature-title">AI Lead Scoring</h3>
                    <p class="feature-desc">
                        Automatically score and prioritize leads based on behavior, 
                        demographics, and engagement.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3 class="feature-title">Email Automation</h3>
                    <p class="feature-desc">
                        Nurture leads with personalized email sequences that convert 
                        prospects into customers.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Advanced Analytics</h3>
                    <p class="feature-desc">
                        Get deep insights into your lead generation performance with 
                        real-time dashboards and reports.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <h3 class="feature-title">Integrations</h3>
                    <p class="feature-desc">
                        Connect with your favorite tools including CRMs, email platforms, 
                        and marketing automation systems.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h3 class="feature-title">Enterprise Security</h3>
                    <p class="feature-desc">
                        Your data is protected with bank-level encryption, SSO support, 
                        and compliance certifications.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Pricing</span>
                <h2 class="section-title">Simple, transparent pricing</h2>
                <p class="section-desc">
                    Choose the plan that fits your needs. All plans include a 14-day free trial.
                </p>
            </div>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <h3 class="pricing-name">Starter</h3>
                    <div class="pricing-price">$29<span>/month</span></div>
                    <p class="pricing-desc">Perfect for small businesses getting started</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Up to 1,000 leads/month</li>
                        <li><i class="fas fa-check"></i> 5 lead capture forms</li>
                        <li><i class="fas fa-check"></i> Basic analytics</li>
                        <li><i class="fas fa-check"></i> Email support</li>
                        <li><i class="fas fa-check"></i> 1 team member</li>
                    </ul>
                    <a href="index.html" class="btn btn-ghost" style="width: 100%;">Get Started</a>
                </div>
                
                <div class="pricing-card featured">
                    <span class="pricing-badge">Most Popular</span>
                    <h3 class="pricing-name">Professional</h3>
                    <div class="pricing-price">$79<span>/month</span></div>
                    <p class="pricing-desc">For growing teams ready to scale</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Up to 10,000 leads/month</li>
                        <li><i class="fas fa-check"></i> Unlimited forms</li>
                        <li><i class="fas fa-check"></i> Advanced analytics</li>
                        <li><i class="fas fa-check"></i> Priority support</li>
                        <li><i class="fas fa-check"></i> 5 team members</li>
                        <li><i class="fas fa-check"></i> Email automation</li>
                        <li><i class="fas fa-check"></i> CRM integrations</li>
                    </ul>
                    <a href="index.html" class="btn btn-primary" style="width: 100%;">Get Started</a>
                </div>
                
                <div class="pricing-card">
                    <h3 class="pricing-name">Enterprise</h3>
                    <div class="pricing-price">$199<span>/month</span></div>
                    <p class="pricing-desc">For large organizations with custom needs</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Unlimited leads</li>
                        <li><i class="fas fa-check"></i> Unlimited everything</li>
                        <li><i class="fas fa-check"></i> Custom analytics</li>
                        <li><i class="fas fa-check"></i> 24/7 phone support</li>
                        <li><i class="fas fa-check"></i> Unlimited team members</li>
                        <li><i class="fas fa-check"></i> Custom integrations</li>
                        <li><i class="fas fa-check"></i> Dedicated manager</li>
                    </ul>
                    <a href="index.html" class="btn btn-ghost" style="width: 100%;">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">Ready to supercharge your lead generation?</h2>
                <p class="cta-desc">
                    Join thousands of businesses already using LeadGen to capture and convert more leads.
                </p>
                <form class="cta-form" onsubmit="event.preventDefault();">
                    <input type="email" class="cta-input" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Start Free Trial
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="nav-logo">
                        <div class="logo-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span class="logo-text">Lead<span>Gen</span></span>
                    </a>
                    <p>The all-in-one lead generation platform that helps you capture, manage, and convert leads effortlessly.</p>
                </div>
                
                <div class="footer-column">
                    <h4 class="footer-title">Product</h4>
                    <div class="footer-links">
                        <a href="#features">Features</a>
                        <a href="#pricing">Pricing</a>
                        <a href="#">Integrations</a>
                        <a href="#">Changelog</a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4 class="footer-title">Company</h4>
                    <div class="footer-links">
                        <a href="#">About</a>
                        <a href="#">Blog</a>
                        <a href="#">Careers</a>
                        <a href="#">Contact</a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4 class="footer-title">Legal</h4>
                    <div class="footer-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Cookie Policy</a>
                        <a href="#">GDPR</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">© 2025 LeadGen. All rights reserved.</p>
                <div class="footer-socials">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
