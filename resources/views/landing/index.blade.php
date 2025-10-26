<!DOCTYPE html>
<html lang="fr" class="light-style" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>MOYOO Delivery - Révolutionnez votre logistique</title>
    <meta name="description" content="MOYOO Delivery est une plateforme complète de gestion de livraisons pour optimiser vos opérations logistiques." />
    <meta name="keywords" content="livraison, logistique, gestion, colis, livreurs, suivi GPS, Côte d'Ivoire, Abidjan" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <style>
        :root {
            --moyoo-primary: #ff6b35;
            --moyoo-secondary: #1e3a8a;
            --moyoo-accent: #f59e0b;
            --moyoo-success: #10b981;
            --moyoo-dark: #1f2937;
            --moyoo-light: #f8fafc;
            --moyoo-gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--moyoo-dark);
            background-color: #ffffff;
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--moyoo-primary);
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 40px;
            align-items: center;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--moyoo-dark);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--moyoo-primary);
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline {
            background: transparent;
            color: var(--moyoo-dark);
            border: 2px solid var(--moyoo-dark);
        }
        
        .btn-outline:hover {
            background: var(--moyoo-dark);
            color: white;
        }
        
        .btn-primary {
            background: var(--moyoo-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--moyoo-success);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--moyoo-primary) 0%, var(--moyoo-accent) 100%);
            color: white;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero .subtitle {
            font-size: 1.25rem;
            margin-bottom: 40px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-buttons .btn {
            padding: 16px 32px;
            font-size: 1.1rem;
        }
        
        .hero-buttons .btn-outline {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-color: white;
        }
        
        .hero-buttons .btn-outline:hover {
            background: white;
            color: var(--moyoo-primary);
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: var(--moyoo-light);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--moyoo-dark);
        }
        
        .section-title p {
            font-size: 1.2rem;
            color: var(--moyoo-gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--moyoo-primary), var(--moyoo-accent));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--moyoo-dark);
        }
        
        .feature-card p {
            color: var(--moyoo-gray);
            line-height: 1.6;
        }
        
        /* How it works */
        .how-it-works {
            padding: 100px 0;
            background: white;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .step {
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: var(--moyoo-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 20px;
        }
        
        .step h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--moyoo-dark);
        }
        
        .step p {
            color: var(--moyoo-gray);
            line-height: 1.6;
        }
        
        /* Pricing */
        .pricing {
            padding: 100px 0;
            background: var(--moyoo-light);
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        
        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid transparent;
        }
        
        .pricing-card.featured {
            border-color: var(--moyoo-primary);
            transform: scale(1.05);
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .pricing-card.featured:hover {
            transform: scale(1.05) translateY(-10px);
        }
        
        .pricing-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--moyoo-primary);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .pricing-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--moyoo-dark);
        }
        
        .pricing-card .price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--moyoo-primary);
            margin-bottom: 10px;
        }
        
        .pricing-card .period {
            color: var(--moyoo-gray);
            margin-bottom: 30px;
        }
        
        .pricing-features {
            list-style: none;
            margin-bottom: 30px;
        }
        
        .pricing-features li {
            padding: 8px 0;
            color: var(--moyoo-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .pricing-features li::before {
            content: '✓';
            color: var(--moyoo-success);
            font-weight: bold;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 100px 0;
            background: white;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        
        .testimonial-card {
            background: var(--moyoo-light);
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .testimonial-avatar {
            width: 60px;
            height: 60px;
            background: var(--moyoo-primary);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            color: var(--moyoo-gray);
            line-height: 1.6;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--moyoo-dark);
        }
        
        .testimonial-role {
            color: var(--moyoo-gray);
            font-size: 0.9rem;
        }
        
        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--moyoo-secondary) 0%, var(--moyoo-primary) 100%);
            color: white;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: var(--moyoo-dark);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section a:hover {
            color: var(--moyoo-primary);
        }
        
        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 30px;
            text-align: center;
            color: #9ca3af;
        }
        
        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--moyoo-dark);
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .steps {
                grid-template-columns: 1fr;
            }
            
            .pricing-grid {
                grid-template-columns: 1fr;
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
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
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Scroll to top */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--moyoo-primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }
        
        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-to-top:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        /* Mobile App Section */
        .mobile-app {
            padding: 100px 0;
            background: white;
        }
        
        .mobile-app-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }
        
        .mobile-app-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--moyoo-dark);
        }
        
        .mobile-subtitle {
            font-size: 1.2rem;
            color: var(--moyoo-gray);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .app-features {
            margin-bottom: 40px;
        }
        
        .app-feature {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .app-feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--moyoo-primary), var(--moyoo-accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .app-feature-text h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--moyoo-dark);
        }
        
        .app-feature-text p {
            color: var(--moyoo-gray);
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        .download-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .download-btn {
            transition: transform 0.3s ease;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
        }
        
        .download-btn img {
            height: 50px;
            border-radius: 8px;
        }
        
        /* Phone Mockup */
        .phone-mockup {
            position: relative;
            width: 280px;
            height: 560px;
            background: #1a1a1a;
            border-radius: 30px;
            padding: 20px;
            margin: 0 auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .phone-screen {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }
        
        .app-interface {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .app-header {
            background: var(--moyoo-primary);
            color: white;
            padding: 15px 20px 10px;
        }
        
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .app-title {
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
        }
        
        .app-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .mission-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .mission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .mission-header h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--moyoo-dark);
            margin: 0;
        }
        
        .mission-id {
            font-size: 0.8rem;
            color: var(--moyoo-primary);
            font-weight: 500;
        }
        
        .mission-details {
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--moyoo-gray);
        }
        
        .detail-item i {
            color: var(--moyoo-primary);
            width: 16px;
        }
        
        .mission-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-accept, .btn-navigate {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-accept {
            background: var(--moyoo-success);
            color: white;
        }
        
        .btn-navigate {
            background: var(--moyoo-primary);
            color: white;
        }
        
        .btn-accept:hover, .btn-navigate:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .stats-row {
            display: flex;
            justify-content: space-between;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--moyoo-primary);
        }
        
        .stat-label {
            font-size: 0.7rem;
            color: var(--moyoo-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Responsive Mobile App */
        @media (max-width: 768px) {
            .mobile-app-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
            
            .mobile-app-text h2 {
                font-size: 2rem;
            }
            
            .phone-mockup {
                width: 240px;
                height: 480px;
            }
            
            .download-buttons {
                justify-content: center;
            }
        }
        
        /* Zero Fees Section */
        .zero-fees {
            padding: 100px 0;
            background: var(--moyoo-light);
        }
        
        .zero-fees-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }
        
        .zero-fees-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--moyoo-dark);
        }
        
        .zero-fees-text p {
            font-size: 1.2rem;
            color: var(--moyoo-gray);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .fees-list {
            margin-bottom: 40px;
        }
        
        .fee-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--moyoo-dark);
        }
        
        .fee-item i {
            color: var(--moyoo-success);
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .zero-fees-cta {
            margin-top: 30px;
        }
        
        .zero-fees-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .fees-badge {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--moyoo-primary), var(--moyoo-accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
            animation: pulse 2s infinite;
        }
        
        .badge-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .badge-text {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .badge-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            font-weight: 500;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 25px 50px rgba(255, 107, 53, 0.4);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
            }
        }
        
        /* Responsive Zero Fees */
        @media (max-width: 768px) {
            .zero-fees-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
            
            .zero-fees-text h2 {
                font-size: 2rem;
            }
            
            .fees-badge {
                width: 150px;
                height: 150px;
            }
            
            .badge-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="{{ route('landing.index') }}" class="logo">MOYOO</a>
            <ul class="nav-menu">
                <li><a href="#features" class="nav-link">Fonctionnalités</a></li>
                <li><a href="#pricing" class="nav-link">Tarifs</a></li>
                <li><a href="#testimonials" class="nav-link">Témoignages</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="{{ route('auth.login') }}" class="btn btn-outline">Se connecter</a>
                <a href="{{ route('auth.register') }}" class="btn btn-primary">S'inscrire</a>
            </div>
            <button class="mobile-menu-toggle">
                <i class="ti ti-menu-2"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>#hellomoyoo</h1>
            <p class="subtitle">La plateforme pour mieux gérer vos livraisons.</p>
            <p class="subtitle">Optimisez vos opérations logistiques dès maintenant.</p>
            <div class="hero-buttons">
                <a href="{{ route('auth.register') }}" class="btn btn-success">
                    <i class="ti ti-rocket"></i>
                    Commencer Gratuitement
                </a>
                <a href="#features" class="btn btn-outline">
                    <i class="ti ti-info-circle"></i>
                    Découvrir les Fonctionnalités
                </a>
            </div>
        </div>
    </section>

    <!-- Mobile App Section -->
    <section class="mobile-app">
        <div class="container">
            <div class="mobile-app-content">
                <div class="mobile-app-text">
                    <h2>L'app mobile qu'il vous faut</h2>
                    <p class="mobile-subtitle">Vos livreurs ont accès à une application mobile dédiée pour gérer leurs missions en toute simplicité.</p>
                    
                    <div class="app-features">
                        <div class="app-feature">
                            <div class="app-feature-icon">
                                <i class="ti ti-map-pin"></i>
                            </div>
                            <div class="app-feature-text">
                                <h4>Navigation GPS intégrée</h4>
                                <p>Guidage vocal et navigation optimisée vers les adresses de livraison</p>
                            </div>
                        </div>
                        
                        <div class="app-feature">
                            <div class="app-feature-icon">
                                <i class="ti ti-bell-ringing"></i>
                            </div>
                            <div class="app-feature-text">
                                <h4>Notifications en temps réel</h4>
                                <p>Recevez instantanément les nouvelles missions et mises à jour</p>
                            </div>
                        </div>
                        
                        <div class="app-feature">
                            <div class="app-feature-icon">
                                <i class="ti ti-camera"></i>
                            </div>
                            <div class="app-feature-text">
                                <h4>Preuve de livraison</h4>
                                <p>Photo automatique et signature électronique pour chaque livraison</p>
                            </div>
                        </div>
                        
                        <div class="app-feature">
                            <div class="app-feature-icon">
                                <i class="ti ti-chart-line"></i>
                            </div>
                            <div class="app-feature-text">
                                <h4>Suivi des performances</h4>
                                <p>Consultez vos statistiques et revenus en temps réel</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="download-buttons">
                        <a href="#" class="download-btn">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTM1IiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMTM1IDQwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTM1IiBoZWlnaHQ9IjQwIiByeD0iNSIgZmlsbD0iIzAwMDAwMCIvPgo8dGV4dCB4PSI2Ny41IiB5PSIyNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+R29vZ2xlIFBsYXk8L3RleHQ+Cjwvc3ZnPgo=" alt="Télécharger sur Google Play" />
                        </a>
                        <a href="#" class="download-btn">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTM1IiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMTM1IDQwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTM1IiBoZWlnaHQ9IjQwIiByeD0iNSIgZmlsbD0iIzAwMDAwMCIvPgo8dGV4dCB4PSI2Ny41IiB5PSIyNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+QXBwIFN0b3JlPC90ZXh0Pgo8L3N2Zz4K" alt="Télécharger sur App Store" />
                        </a>
                    </div>
                </div>
                
                <div class="mobile-app-visual">
                    <div class="phone-mockup">
                        <div class="phone-screen">
                            <div class="app-interface">
                                <div class="app-header">
                                    <div class="status-bar">
                                        <span class="time">14:30</span>
                                        <div class="battery">100%</div>
                                    </div>
                                    <div class="app-title">MOYOO Livreur</div>
                                </div>
                                
                                <div class="app-content">
                                    <div class="mission-card">
                                        <div class="mission-header">
                                            <h5>Nouvelle Mission</h5>
                                            <span class="mission-id">#COL-2024-001</span>
                                        </div>
                                        <div class="mission-details">
                                            <div class="detail-item">
                                                <i class="ti ti-map-pin"></i>
                                                <span>Riviera 2, Cocody</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="ti ti-user"></i>
                                                <span>Marie Traoré</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="ti ti-phone"></i>
                                                <span>+225 07 12 34 56</span>
                                            </div>
                                        </div>
                                        <div class="mission-actions">
                                            <button class="btn-accept">Accepter</button>
                                            <button class="btn-navigate">Naviguer</button>
                                        </div>
                                    </div>
                                    
                                    <div class="stats-row">
                                        <div class="stat-item">
                                            <span class="stat-number">12</span>
                                            <span class="stat-label">Livraisons</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">45,000</span>
                                            <span class="stat-label">FCFA</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">4.8</span>
                                            <span class="stat-label">Note</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>Comment ça marche</h2>
                <p>Trois étapes simples pour révolutionner votre logistique</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Créez votre compte</h3>
                    <p>Inscrivez-vous gratuitement et accédez immédiatement à votre tableau de bord de gestion.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Configurez vos livreurs</h3>
                    <p>Ajoutez vos livreurs, configurez leurs véhicules et commencez à suivre leurs missions en temps réel.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Gérez vos livraisons</h3>
                    <p>Créez des colis, assignez des missions et suivez tout en temps réel avec notre moniteur GPS.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>La plateforme qu'il vous faut pour mieux gérer vos livraisons</h2>
                <p>Dites adieu à la frustration ! Simplifiez votre logistique en gérant tout en un seul endroit.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-package"></i>
                    </div>
                    <h3>Gestion des Colis</h3>
                    <p>Créez, suivez et gérez tous vos colis avec des codes uniques et des statuts en temps réel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-truck-delivery"></i>
                    </div>
                    <h3>Gestion des Livreurs</h3>
                    <p>Gérez les profils de vos livreurs, leurs véhicules et suivez leurs performances.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-map-pin"></i>
                    </div>
                    <h3>Moniteur GPS</h3>
                    <p>Suivez la position de vos livreurs en mission sur une carte interactive en temps réel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-users"></i>
                    </div>
                    <h3>Gestion Clients & Marchands</h3>
                    <p>Centralisez les informations de vos clients et marchands pour une gestion simplifiée.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-bell-ringing"></i>
                    </div>
                    <h3>Notifications Automatiques</h3>
                    <p>Envoyez des notifications SMS et push automatiques à vos clients et livreurs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ti ti-chart-bar"></i>
                    </div>
                    <h3>Rapports & Analytics</h3>
                    <p>Accédez à des tableaux de bord et rapports détaillés pour analyser vos performances.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Zero Fees Section -->
    <section class="zero-fees">
        <div class="container">
            <div class="zero-fees-content">
                <div class="zero-fees-text">
                    <h2>Essayez le zéro frais</h2>
                    <p>Nos tarifs sont transparents, justes et conçus pour vous aider à optimiser votre logistique sans vous ruiner.</p>
                    
                    <div class="fees-list">
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais de gestion</span>
                        </div>
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais pour créer un compte</span>
                        </div>
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais sur les notifications</span>
                        </div>
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais sur le suivi GPS</span>
                        </div>
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais sur les rapports</span>
                        </div>
                        <div class="fee-item">
                            <i class="ti ti-check"></i>
                            <span>ZÉRO frais sur l'application mobile</span>
                        </div>
                    </div>
                    
                    <div class="zero-fees-cta">
                        <a href="#pricing" class="btn btn-outline">Consulter les tarifs</a>
                    </div>
                </div>
                
                <div class="zero-fees-visual">
                    <div class="fees-badge">
                        <div class="badge-content">
                            <span class="badge-text">Zéro frais sur MOYOO</span>
                            <span class="badge-subtitle">no limit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Essayez le zéro frais</h2>
                <p>Nos tarifs sont transparents, justes et conçus pour vous aider à optimiser votre logistique sans vous ruiner.</p>
            </div>
            <div class="pricing-grid">
                <!-- Free Plan -->
                <div class="pricing-card">
                    <h3>Gratuit</h3>
                    <div class="price">0 <span class="period">FCFA/mois</span></div>
                    <ul class="pricing-features">
                        <li>Jusqu'à 50 colis/mois</li>
                        <li>Gestion des livreurs</li>
                        <li>Notifications de base</li>
                        <li>Support par email</li>
                        <li>Rapports simples</li>
                    </ul>
                    <a href="{{ route('auth.register') }}" class="btn btn-outline">Commencer Gratuitement</a>
                </div>
                
                <!-- Premium Plan -->
                <div class="pricing-card featured">
                    <div class="pricing-badge">Populaire</div>
                    <h3>Premium</h3>
                    <div class="price">25,000 <span class="period">FCFA/mois</span></div>
                    <ul class="pricing-features">
                        <li>Colis illimités</li>
                        <li>Toutes les fonctionnalités</li>
                        <li><strong>Moniteur GPS en temps réel</strong></li>
                        <li>Support prioritaire</li>
                        <li>Rapports avancés</li>
                        <li>API d'intégration</li>
                    </ul>
                    <a href="{{ route('subscriptions.payment', 2) }}" class="btn btn-primary">Choisir Premium</a>
                </div>
                
                <!-- Premium Annuel Plan -->
                <div class="pricing-card">
                    <h3>Premium Annuel</h3>
                    <div class="price">250,000 <span class="period">FCFA/an</span></div>
                    <ul class="pricing-features">
                        <li>Colis illimités</li>
                        <li>Toutes les fonctionnalités</li>
                        <li><strong>Moniteur GPS en temps réel</strong></li>
                        <li>Support 24/7</li>
                        <li>Formations incluses</li>
                        <li>Intégrations personnalisées</li>
                    </ul>
                    <a href="{{ route('subscriptions.payment', 3) }}" class="btn btn-outline">Choisir Annuel</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Nos clients nous aiment</h2>
                <p>+500 entreprises satisfaites • 4.8/5 étoiles</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">AK</div>
                    <p class="testimonial-text">"MOYOO a transformé notre façon de gérer les livraisons. L'interface est intuitive et le suivi GPS est un atout majeur. Nous avons optimisé nos itinéraires de 40% !"</p>
                    <div class="testimonial-author">Amadou Koné</div>
                    <div class="testimonial-role">Directeur Logistique, LogiCôte</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">MT</div>
                    <p class="testimonial-text">"La facilité d'utilisation de MOYOO est incroyable. Nos livreurs ont rapidement adopté l'application et nos clients adorent les notifications en temps réel. Un gain de temps énorme !"</p>
                    <div class="testimonial-author">Marie Traoré</div>
                    <div class="testimonial-role">Gérante, ShopExpress</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">JD</div>
                    <p class="testimonial-text">"Le retour sur investissement avec MOYOO est impressionnant. Moins d'erreurs, des livraisons plus rapides et une meilleure satisfaction client. C'est un outil indispensable !"</p>
                    <div class="testimonial-author">Jean Diabaté</div>
                    <div class="testimonial-role">CEO, FastDelivery CI</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Qu'attendez-vous ?</h2>
            <p>Reprenez le contrôle de votre logistique</p>
            <div class="hero-buttons">
                <a href="{{ route('auth.register') }}" class="btn btn-success">
                    <i class="ti ti-rocket"></i>
                    Commencer Gratuitement
                </a>
                <a href="{{ route('auth.register') }}" class="btn btn-outline">
                    <i class="ti ti-phone"></i>
                    Nous Contacter
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MOYOO Delivery</h3>
                    <p>Révolutionnez votre logistique avec MOYOO Delivery, la solution complète pour la gestion de vos livraisons.</p>
                </div>
                <div class="footer-section">
                    <h3>Produit</h3>
                    <ul>
                        <li><a href="#features">Fonctionnalités</a></li>
                        <li><a href="#pricing">Tarifs</a></li>
                        <li><a href="{{ route('auth.login') }}">Se connecter</a></li>
                        <li><a href="{{ route('auth.register') }}">S'inscrire</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="{{ route('support.index') }}">Centre d'aide</a></li>
                        <li><a href="{{ route('documentation.index') }}">Documentation</a></li>
                        <li><a href="#contact">Contactez-nous</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul>
                        <li>📞 +225 07 01 23 45 67</li>
                        <li>✉️ support@moyoo.ci</li>
                        <li>📍 Cocody, Riviera 2, Abidjan</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} MOYOO Delivery. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to top button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="ti ti-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/app-logistics-dashboard.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            const navbar = document.getElementById('navbar');
            const scrollToTopBtn = document.getElementById('scrollToTop');
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                    scrollToTopBtn.classList.add('show');
                } else {
                    navbar.classList.remove('scrolled');
                    scrollToTopBtn.classList.remove('show');
                }
            });
            
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Scroll to top functionality
            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Add animation classes on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                    }
                });
            }, observerOptions);
            
            // Observe all feature cards, steps, pricing cards, and testimonials
            document.querySelectorAll('.feature-card, .step, .pricing-card, .testimonial-card').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>