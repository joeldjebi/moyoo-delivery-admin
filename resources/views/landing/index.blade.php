<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>MOYOO Delivery - Logiciel de livraison à la demande</title>
    <meta name="description" content="MOYOO Delivery - Logiciel de livraison à la demande tout-en-un personnalisable" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #ffffff;
            overflow-x: hidden;
        }

        /* En-tête */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 20px 0;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 35px;
            align-items: center;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            text-decoration: none;
            color: #1f2937;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            position: relative;
        }

        .nav-link:hover {
            color: #2563eb;
        }

        .nav-link.active {
            color: #10b981;
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 2px;
            background: #10b981;
            border-radius: 2px;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 2px;
            background: transparent;
            border-radius: 2px;
            transition: background 0.3s ease;
        }

        .demo-button {
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .demo-button:hover {
            background: #1d4ed8;
        }

        /* Section principale */
        .hero-section {
            margin-top: 100px;
            padding: 80px 40px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        /* Section gauche - Contenu marketing */
        .hero-content {
            padding-right: 40px;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.1;
            color: #111827;
            margin-bottom: 24px;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .hero-description {
            font-size: 18px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 540px;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 16px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #2563eb;
            padding: 16px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: 2px solid #2563eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f0f5ff;
        }

        .play-icon {
            width: 0;
            height: 0;
            border-left: 8px solid #2563eb;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            margin-left: 2px;
        }

        /* Section droite - Démonstration */
        .demo-panel {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 24px;
            position: relative;
        }

        /* Barre d'état */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .status-icon {
            width: 16px;
            height: 16px;
        }

        .order-number {
            color: #1f2937;
            font-weight: 600;
            cursor: pointer;
            display: flex;
                align-items: center;
            gap: 6px;
        }

        .order-number::after {
            content: '▼';
            font-size: 10px;
            color: #6b7280;
        }

        /* Bulle de dialogue */
        .message-bubble {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            color: #374151;
            margin-bottom: 24px;
            max-width: 220px;
        }

        /* Carte géographique */
        .map-container {
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            border-radius: 16px;
            height: 280px;
            position: relative;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .map-routes {
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(0,0,0,0.05) 40px, rgba(0,0,0,0.05) 41px),
                repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(0,0,0,0.05) 40px, rgba(0,0,0,0.05) 41px);
        }

        .route-line {
            position: absolute;
            top: 50%;
            left: 20%;
            width: 60%;
            height: 3px;
            background: #2563eb;
            transform: translateY(-50%) rotate(-15deg);
            border-radius: 2px;
        }

        .route-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            background: #2563eb;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .route-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            background: #2563eb;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .driver-marker {
            position: absolute;
            left: 20%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 32px;
            height: 32px;
            background: #2563eb;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        /* Cartes d'information */
        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .info-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .driver-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .vehicle-icon {
            background: #e0e7ff;
            color: #6366f1;
        }

        .info-card-content {
            flex: 1;
        }

        .info-card-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .info-card-details {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rating {
            display: flex;
                align-items: center;
            gap: 4px;
        }

        .star {
            color: #fbbf24;
            font-size: 12px;
        }

        .action-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .action-icon:hover {
            transform: scale(1.1);
        }

        .phone-icon {
            background: #10b981;
        }

        /* Section solutions */
        .solutions-section {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
        }

        .solutions-title {
            font-size: 48px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 40px;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .solutions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .solution-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .solution-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #2563eb;
        }

        .solution-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 40px;
        }

        .solution-card:nth-child(1) .solution-icon {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
        }

        .solution-card:nth-child(2) .solution-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .solution-card:nth-child(3) .solution-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .solution-card:nth-child(4) .solution-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .solution-card:nth-child(5) .solution-icon {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .solution-card:nth-child(6) .solution-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .solution-card:nth-child(7) .solution-icon {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .solution-card:nth-child(8) .solution-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .solution-text {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        /* Section fonctionnalités */
        .features-section {
            padding: 80px 40px;
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
            background: #ffffff;
        }

        .features-title {
            font-size: 48px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .features-subtitle {
            font-size: 18px;
            color: #374151;
            margin-bottom: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-icon {
            margin-left: auto;
            margin-right: auto;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: #dbeafe;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 32px;
        }

        .feature-title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .feature-description {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }

        /* Section Comment ça marche */
        .how-it-works-section {
            padding: 80px 40px;
            max-width: 1200px;
            margin: 0 auto;
            background: #f9fafb;
            margin-bottom: 60px;
        }

        .how-it-works-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .how-it-works-title {
            font-size: 48px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 16px;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .how-it-works-container {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px 0;
        }

        .how-it-works-line {
            position: absolute;
            left: 50%;
            top: 20px;
            bottom: 20px;
            width: 4px;
            background: linear-gradient(180deg, #a855f7 0%, #8b5cf6 50%, #7c3aed 100%);
            transform: translateX(-50%);
            z-index: 1;
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
        }

        .how-it-works-step {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 45px;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .how-it-works-step:hover {
            transform: translateY(-4px);
        }

        .how-it-works-step:last-child {
            margin-bottom: 0;
        }

        .step-number-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 800;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            border: 5px solid #f9fafb;
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4), 0 0 0 0 rgba(139, 92, 246, 0.2);
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .how-it-works-step:hover .step-number-circle {
            transform: translateX(-50%) scale(1.1);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.5), 0 0 0 8px rgba(139, 92, 246, 0.1);
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
            max-width: 380px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            border: 1px solid rgba(139, 92, 246, 0.1);
        }

        .how-it-works-step:hover .step-card {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .step-card.left {
            margin-right: 70px;
        }

        .step-card.right {
            margin-left: 70px;
        }

        .step-card-title {
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .step-card-description {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
        }

        .step-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%);
            border-radius: 20px;
            display: flex;
                align-items: center;
            justify-content: center;
            font-size: 42px;
            position: relative;
            z-index: 3;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
        }

        .how-it-works-step:hover .step-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
        }

        .step-icon.left {
            margin-right: 70px;
        }

        .step-icon.right {
            margin-left: 70px;
        }

        /* Responsive */
        .pricing-section {
            padding: 100px 40px;
            max-width: 1400px;
            margin: 0 auto;
            background: #f9fafb;
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .pricing-label {
            font-size: 14px;
            font-weight: 600;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .pricing-title {
            font-size: 48px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .pricing-subtitle {
            font-size: 20px;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 60px;
        }

        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid transparent;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .pricing-card.featured {
            border-color: #10b981;
            transform: scale(1.05);
        }

        .pricing-card.featured:hover {
            transform: scale(1.05) translateY(-8px);
        }

        .pricing-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .pricing-plan-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .pricing-plan-description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .pricing-price {
            margin-bottom: 40px;
        }

        .pricing-amount {
            font-size: 48px;
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin-bottom: 8px;
        }

        .pricing-period {
            font-size: 16px;
            color: #6b7280;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }

        .pricing-feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
            color: #374151;
            line-height: 1.6;
        }

        .pricing-feature-icon {
            width: 20px;
            height: 20px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .pricing-button {
            width: 100%;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .pricing-button.primary {
            background: #10b981;
            color: white;
        }

        .pricing-button.primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .pricing-button.secondary {
            background: white;
            color: #111827;
            border: 2px solid #e5e7eb;
        }

        .pricing-button.secondary:hover {
            border-color: #10b981;
            color: #10b981;
        }

        /* Footer */
        .footer {
            background: #111827;
            color: #ffffff;
            padding: 60px 40px 30px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 50px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-column ul li {
            margin-bottom: 12px;
        }

        .footer-column a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .footer-column a:hover {
            color: #10b981;
        }

        .footer-about p {
            color: #9ca3af;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            background: #1f2937;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: #10b981;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            color: #9ca3af;
            font-size: 14px;
            margin: 0;
        }

        .footer-bottom-links {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .footer-bottom-links a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: #10b981;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-section {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-content {
                padding-right: 0;
            }

            .nav-menu {
                gap: 20px;
            }

            .solutions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }

            .how-it-works-section {
                padding: 60px 30px;
                margin-bottom: 40px;
            }

            .how-it-works-header {
                margin-bottom: 35px;
            }

            .how-it-works-title {
                font-size: 36px;
            }

            .how-it-works-container {
                max-width: 600px;
                padding: 10px 0;
            }

            .how-it-works-line {
                display: none;
            }

            .how-it-works-step {
                flex-direction: column;
                align-items: center;
                margin-bottom: 40px;
            }

            .how-it-works-step:hover {
                transform: none;
            }

            .step-number-circle {
                position: relative;
                left: auto;
                transform: none;
            margin-bottom: 20px;
                width: 65px;
                height: 65px;
                font-size: 26px;
            }

            .how-it-works-step:hover .step-number-circle {
                transform: scale(1.05);
            }

            .step-card {
            margin: 0;
                max-width: 100%;
                padding: 28px;
            }

            .step-card.left,
            .step-card.right {
                margin: 0 0 20px 0;
            }

            .step-icon {
                position: relative;
                margin: 0;
                width: 80px;
                height: 80px;
                font-size: 38px;
            }

            .how-it-works-step:hover .step-icon {
                transform: scale(1.05) rotate(3deg);
            }

            .step-icon.left,
            .step-icon.right {
                margin: 0;
            }

            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }

            .pricing-card.featured {
                transform: scale(1);
            }

            .pricing-card.featured:hover {
                transform: translateY(-8px);
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 20px;
            }

            .nav-menu {
                display: none;
            }

            .hero-section {
                padding: 40px 20px;
                margin-top: 80px;
            }

            .hero-title {
                font-size: 40px;
            }

            .cta-buttons {
            flex-direction: column;
                align-items: stretch;
            }

            .solutions-section {
                padding: 60px 20px;
            }

            .solutions-title {
                font-size: 36px;
            margin-bottom: 40px;
            }

            .solutions-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .solution-card {
                padding: 30px 20px;
            }

            .features-section {
                padding: 60px 20px;
            }

            .features-title {
                font-size: 36px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .how-it-works-section {
                padding: 60px 20px;
                margin-bottom: 30px;
            }

            .how-it-works-title {
                font-size: 32px;
            }

            .how-it-works-header {
                margin-bottom: 50px;
            }

            .how-it-works-step {
                margin-bottom: 45px;
            }

            .step-number-circle {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }

            .step-card {
                padding: 24px;
            }

            .step-card-title {
                font-size: 22px;
            }

            .step-card-description {
                font-size: 15px;
            }

            .step-icon {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }

            .pricing-section {
                padding: 60px 20px;
            }

            .pricing-title {
                font-size: 36px;
            }

            .pricing-subtitle {
                font-size: 18px;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                margin-top: 40px;
            }

            .pricing-card {
                padding: 30px 20px;
            }

            .pricing-card.featured {
                transform: scale(1);
            }

            .pricing-card.featured:hover {
                transform: translateY(-8px);
            }

            .pricing-amount {
                font-size: 40px;
            }

            .footer {
                padding: 40px 20px 20px;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <header class="header">
        <div class="header-container">
            <a href="#accueil" class="logo">MOYOO</a>

            <nav>
            <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#accueil" class="nav-link">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a href="#solutions" class="nav-link">Solutions</a>
                    </li>
                    <li class="nav-item">
                        <a href="#fonctionnalites" class="nav-link">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a href="#comment-ca-marche" class="nav-link">Comment ça marche</a>
                    </li>
                    <li class="nav-item">
                        <a href="#tarification" class="nav-link">Tarifs</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact" class="nav-link">Contact</a>
                    </li>
            </ul>
    </nav>

            <a href="#tarification" class="demo-button">Essayer une démo</a>
            </div>
    </header>

    <!-- Section principale -->
    <section id="accueil" class="hero-section">
        <!-- Section gauche - Contenu marketing -->
        <div class="hero-content">
            <h1 class="hero-title">
                LOGICIEL DE LIVRAISON<br>
                À LA DEMANDE
            </h1>

            <p class="hero-description">
                Développez votre base de clients en utilisant un logiciel de livraison à la demande tout-en-un personnalisable. Connectez votre entreprise aux restaurants, commerces alimentaires et autres entreprises locales.
            </p>

            <div class="cta-buttons">
                <a href="#" class="btn-primary">Essayer une démo - C'est gratuit</a>
                <a href="#" class="btn-secondary">
                    <span class="play-icon"></span>
                    Regarder la vidéo
                </a>
            </div>
        </div>

        <!-- Section droite - Démonstration -->
        <div class="demo-panel">
            <!-- Barre d'état -->
                                    <div class="status-bar">
                <div class="status-item">
                    <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Démarré</span>
                            </div>
                <div class="status-item">
                    <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Ramassage à 10:30</span>
                            </div>
                <div class="order-number">#123456789</div>
                        </div>

            <!-- Bulle de dialogue -->
            <div class="message-bubble">
                Commande passée il y a 10 min
                            </div>

            <!-- Carte géographique -->
            <div class="map-container">
                <div class="map-routes"></div>
                <div class="route-line"></div>
                <div class="driver-marker">👤</div>
                        </div>

            <!-- Cartes d'information -->
            <div class="info-cards">
                <!-- Carte véhicule -->
                <div class="info-card">
                    <div class="info-card-icon vehicle-icon">🚗</div>
                    <div class="info-card-content">
                        <div class="info-card-name">Honda bleue</div>
                        <div class="info-card-details">
                            <span>12345678</span>
                            </div>
                            </div>
                        </div>
                    </div>
        </div>
    </section>

    <!-- Section solutions -->
    <section id="solutions" class="solutions-section">
        <h2 class="solutions-title">
            UNE SOLUTION PUISSANTE<br>
            POUR TOUS LES SECTEURS
        </h2>

        <div class="solutions-grid">
            <div class="solution-card">
                <div class="solution-icon">🍔</div>
                <p class="solution-text">Livraison de nourriture</p>
                    </div>

            <div class="solution-card">
                <div class="solution-icon">🛒</div>
                <p class="solution-text">Livraison e-commerce</p>
                </div>

            <div class="solution-card">
                <div class="solution-icon">💼</div>
                <p class="solution-text">Livraison B2B</p>
                                    </div>

            <div class="solution-card">
                <div class="solution-icon">🛍️</div>
                <p class="solution-text">Livraison retail</p>
                                </div>

            <div class="solution-card">
                <div class="solution-icon">🏍️</div>
                <p class="solution-text">Livraison express</p>
                                        </div>

            <div class="solution-card">
                <div class="solution-icon">🍇</div>
                <p class="solution-text">Livraison épicerie</p>
                                    </div>

            <div class="solution-card">
                <div class="solution-icon">🚛</div>
                <p class="solution-text">Transport et logistique</p>
                                        </div>

            <div class="solution-card">
                <div class="solution-icon">💊</div>
                <p class="solution-text">Livraison pharmaceutique</p>
            </div>
        </div>
    </section>

    <!-- Section fonctionnalités -->
    <section id="fonctionnalites" class="features-section">
        <h2 class="features-title">FONCTIONNALITÉS CLÉS</h2>
        <p class="features-subtitle"></p>
            Votre entreprise sera plus innovante et influente que jamais lorsque vous utiliserez les fonctionnalités de l'application de livraison à la demande MOYOO !
        </p>

            <div class="features-grid">
                <div class="feature-card">
                <div class="feature-icon">✈️📍</div>
                <h3 class="feature-title">Suivi en temps réel</h3>
                <p class="feature-description">
                    Les clients peuvent suivre la localisation de leurs colis ainsi que le temps d'arrivée estimé avec cette fonctionnalité.
                </p>
                    </div>

                <div class="feature-card">
                <div class="feature-icon">📦📎</div>
                <h3 class="feature-title">Gestion des commandes</h3>
                <p class="feature-description">
                    Avec la fonctionnalité de gestion des commandes, vous pouvez surveiller les performances des livreurs et gérer efficacement votre entreprise.
                </p>
                    </div>

                <div class="feature-card">
                <div class="feature-icon">🕐</div>
                <h3 class="feature-title">Planification</h3>
                <p class="feature-description">
                    Avec la fonctionnalité de commande planifiée, les utilisateurs peuvent planifier en réservant des commandes et réserver l'heure.
                </p>
                    </div>

                <div class="feature-card">
                <div class="feature-icon">👨‍✈️✓</div>
                <h3 class="feature-title">Choix du livreur</h3>
                <p class="feature-description">
                    Lorsqu'une commande de livraison est créée, une notification est envoyée à chaque livreur, qui peut ensuite accepter ou refuser la demande.
                </p>
                    </div>

                <div class="feature-card">
                <div class="feature-icon">💬💬</div>
                <h3 class="feature-title">Messagerie en temps réel</h3>
                <p class="feature-description">
                    La messagerie gratuite et instantanée entre les clients et les livreurs est possible grâce à cette fonctionnalité.
                </p>
                    </div>

                <div class="feature-card">
                <div class="feature-icon">🎯📍</div>
                <h3 class="feature-title">Expédition automatique</h3>
                <p class="feature-description">
                    En sélectionnant cette option, le livreur le plus proche recevra la commande. Les commandes seront expédiées automatiquement.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Comment ça marche -->
    <section id="comment-ca-marche" class="how-it-works-section">
        <div class="how-it-works-header">
            <h2 class="how-it-works-title">Comment ça marche</h2>
        </div>

        <div class="how-it-works-container">
            <div class="how-it-works-line"></div>

            <!-- Étape 1 : Clients -->
            <div class="how-it-works-step">
                <div class="step-number-circle">1</div>
                <div class="step-card left">
                    <h3 class="step-card-title">Clients</h3>
                    <p class="step-card-description">
                        Les clients ou détaillants téléchargent leurs commandes et suivent l'avancement de la livraison en utilisant MOYOO Connect.
                    </p>
                        </div>
                <div class="step-icon right">
                    👥
                        </div>
                        </div>

            <!-- Étape 2 : Répartiteurs -->
            <div class="how-it-works-step">
                <div class="step-number-circle">2</div>
                <div class="step-icon left">
                    📊
                        </div>
                <div class="step-card right">
                    <h3 class="step-card-title">Répartiteurs</h3>
                    <p class="step-card-description">
                        Les répartiteurs optimisent les routes pour les livreurs en un seul clic en utilisant le Tableau de bord Répartiteur.
                    </p>
                        </div>
                    </div>

            <!-- Étape 3 : Livreurs -->
            <div class="how-it-works-step">
                <div class="step-number-circle">3</div>
                <div class="step-card left">
                    <h3 class="step-card-title">Livreurs</h3>
                    <p class="step-card-description">
                        Naviguez, livrez efficacement et collectez les preuves de livraison en utilisant l'Application Livreur MOYOO.
                    </p>
                </div>
                <div class="step-icon right">
                    🚚
                    </div>
                </div>

            <!-- Étape 4 : Destinataires -->
            <div class="how-it-works-step">
                <div class="step-number-circle">4</div>
                <div class="step-icon left">
                    📦
                        </div>
                <div class="step-card right">
                    <h3 class="step-card-title">Destinataires</h3>
                    <p class="step-card-description">
                        Suivent leurs commandes, ajoutent des notes utiles et peuvent contacter directement les clients en utilisant la Page de Suivi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Tarification -->
    <section id="tarification" class="pricing-section">
        <div class="pricing-header">
            <div class="pricing-label">Tarification</div>
            <h2 class="pricing-title">Choisissez le forfait adapté à vos besoins</h2>
            <p class="pricing-subtitle">
                Sélectionnez le plan qui correspond le mieux à votre activité et bénéficiez de toutes les fonctionnalités dont vous avez besoin.
            </p>
            </div>

            <div class="pricing-grid">
            <!-- Plan Starter -->
                <div class="pricing-card">
                <h3 class="pricing-plan-name">Starter</h3>
                <p class="pricing-plan-description">
                    Parfait pour les petites entreprises qui démarrent
                </p>
                <div class="pricing-price">
                    <div class="pricing-amount">50 000 F</div>
                    <div class="pricing-period">/ mois</div>
                </div>
                    <ul class="pricing-features">
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Jusqu'à 100 livraisons par mois</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Suivi en temps réel</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Application mobile livreur</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Tableau de bord de base</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Support email</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Gestion des commandes</span>
                    </li>
                    </ul>
                <a href="#" class="pricing-button secondary">Commencer</a>
                </div>

            <!-- Plan Professional (Featured) -->
                <div class="pricing-card featured">
                    <div class="pricing-badge">Populaire</div>
                <h3 class="pricing-plan-name">Professional</h3>
                <p class="pricing-plan-description">
                    La solution idéale pour les entreprises en croissance
                </p>
                <div class="pricing-price">
                    <div class="pricing-amount">150 000 F</div>
                    <div class="pricing-period">/ mois</div>
                </div>
                    <ul class="pricing-features">
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Jusqu'à 500 livraisons par mois</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Suivi en temps réel avancé</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Application mobile livreur</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Tableau de bord avancé</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Optimisation automatique des routes</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Rapports et analytics</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>API intégration</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Support prioritaire</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Gestion multi-utilisateurs</span>
                    </li>
                    </ul>
                <a href="#" class="pricing-button primary">Commencer</a>
                </div>

            <!-- Plan Enterprise -->
                <div class="pricing-card">
                <h3 class="pricing-plan-name">Enterprise</h3>
                <p class="pricing-plan-description">
                    Pour les grandes entreprises avec des besoins spécifiques
                </p>
                <div class="pricing-price">
                    <div class="pricing-amount">Sur mesure</div>
                    <div class="pricing-period">Personnalisé</div>
                </div>
                    <ul class="pricing-features">
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Livraisons illimitées</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Toutes les fonctionnalités incluses</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Application mobile sur mesure</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Tableau de bord personnalisé</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Intégrations personnalisées</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Formation dédiée</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Gestionnaire de compte dédié</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Support 24/7</span>
                    </li>
                    <li class="pricing-feature">
                        <span class="pricing-feature-icon">✓</span>
                        <span>Contrat personnalisé</span>
                    </li>
                    </ul>
                <a href="#contact" class="pricing-button secondary">Nous contacter</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Colonne 1 : À propos -->
                <div class="footer-column footer-about">
                    <h3>MOYOO</h3>
                    <p>
                        Solution complète de livraison à la demande pour optimiser vos opérations et améliorer l'expérience client.
                    </p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook">📘</a>
                        <a href="#" aria-label="Twitter">🐦</a>
                        <a href="#" aria-label="LinkedIn">💼</a>
                        <a href="#" aria-label="Instagram">📷</a>
                </div>
                </div>

                <!-- Colonne 2 : Solutions -->
                <div class="footer-column">
                    <h3>Solutions</h3>
                    <ul>
                        <li><a href="#solutions">Livraison de nourriture</a></li>
                        <li><a href="#solutions">Livraison e-commerce</a></li>
                        <li><a href="#solutions">Livraison B2B</a></li>
                        <li><a href="#solutions">Livraison retail</a></li>
                        <li><a href="#solutions">Livraison express</a></li>
                    </ul>
                </div>

                <!-- Colonne 3 : Fonctionnalités -->
                <div class="footer-column">
                    <h3>Fonctionnalités</h3>
                    <ul>
                        <li><a href="#fonctionnalites">Suivi en temps réel</a></li>
                        <li><a href="#fonctionnalites">Gestion des commandes</a></li>
                        <li><a href="#fonctionnalites">Optimisation des routes</a></li>
                        <li><a href="#comment-ca-marche">Comment ça marche</a></li>
                        <li><a href="#tarification">Tarification</a></li>
                    </ul>
                </div>

                <!-- Colonne 4 : Contact -->
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="mailto:contact@moyoo.com">contact@moyoo.com</a></li>
                        <li><a href="tel:+221771234567">+221 77 123 45 67</a></li>
                        <li><a href="#contact">Nous contacter</a></li>
                        <li><a href="#tarification">Demander une démo</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 MOYOO. Tous droits réservés.</p>
                <div class="footer-bottom-links">
                    <a href="#">Mentions légales</a>
                    <a href="#">Politique de confidentialité</a>
                    <a href="#">Conditions d'utilisation</a>
                    <a href="#">CGV</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll pour les liens d'ancrage
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                        top: offsetPosition,
                    behavior: 'smooth'
                    });
                }
                });
            });

        // Mise en évidence de l'élément actif du menu lors du scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        function highlightActiveSection() {
            const scrollPosition = window.scrollY + 150;

            // Si on est tout en haut de la page, activer "Accueil"
            if (window.scrollY < 100) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#accueil') {
                        link.classList.add('active');
                    }
                });
                return;
            }

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }

        window.addEventListener('scroll', highlightActiveSection);
        window.addEventListener('load', highlightActiveSection);
        highlightActiveSection(); // Définir l'état initial
    </script>
</body>
</html>
