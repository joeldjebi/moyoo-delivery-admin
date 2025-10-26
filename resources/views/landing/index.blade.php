<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>MOYOO Delivery - Système de Gestion de Livraisons</title>
    <meta name="description" content="MOYOO Delivery - La solution complète de gestion de livraisons pour les entreprises en Côte d'Ivoire. Gérez vos colis, livreurs et clients avec notre plateforme moderne." />
    <meta name="keywords" content="livraison, gestion, colis, livreur, Côte d'Ivoire, logistique, transport" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../assets/vendor/fonts/flag-icons.css" />
    <link rel="stylesheet" href="../assets/vendor/fonts/tabler-icons.css" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    
    <!-- Page CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/pages/app-logistics-dashboard.css" />
    
    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
    
    <!-- Custom CSS -->
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .pricing-card {
            border: none;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }
        
        .pricing-card:hover {
            transform: scale(1.05);
        }
        
        .pricing-card.featured {
            border: 3px solid #667eea;
            position: relative;
        }
        
        .pricing-card.featured::before {
            content: "POPULAIRE";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #667eea;
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .navbar-custom {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .btn-custom {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section-padding {
            padding: 100px 0;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-gradient" href="#">
                <i class="ti ti-truck-delivery me-2"></i>MOYOO Delivery
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Tarifs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Témoignages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="{{ route('auth.login') }}" class="btn btn-primary btn-custom">
                            <i class="ti ti-login me-2"></i>Connexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="fade-in">
                        <h1 class="display-4 fw-bold text-white mb-4">
                            Révolutionnez votre <span class="text-warning">logistique</span>
                        </h1>
                        <p class="lead text-white-50 mb-4">
                            MOYOO Delivery est la solution complète de gestion de livraisons pour les entreprises en Côte d'Ivoire. Gérez vos colis, livreurs et clients avec notre plateforme moderne et intuitive.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="{{ route('auth.register') }}" class="btn btn-warning btn-custom">
                                <i class="ti ti-rocket me-2"></i>Commencer Gratuitement
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-custom">
                                <i class="ti ti-play me-2"></i>Découvrir
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="floating-animation">
                            <div class="position-relative">
                                <div class="bg-white rounded-4 p-4 shadow-lg">
                                    <i class="ti ti-truck-delivery text-primary" style="font-size: 8rem;"></i>
                                </div>
                                <div class="position-absolute top-0 start-0 translate-middle">
                                    <div class="bg-success rounded-circle p-3">
                                        <i class="ti ti-check text-white"></i>
                                    </div>
                                </div>
                                <div class="position-absolute bottom-0 end-0 translate-middle">
                                    <div class="bg-warning rounded-circle p-3">
                                        <i class="ti ti-map-pin text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section text-white section-padding">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="fade-in">
                        <h2 class="display-4 fw-bold">500+</h2>
                        <p class="lead">Entreprises</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="fade-in">
                        <h2 class="display-4 fw-bold">50K+</h2>
                        <p class="lead">Livraisons</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="fade-in">
                        <h2 class="display-4 fw-bold">2K+</h2>
                        <p class="lead">Livreurs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="fade-in">
                        <h2 class="display-4 fw-bold">99%</h2>
                        <p class="lead">Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="fade-in">
                        <h2 class="display-5 fw-bold text-gradient mb-3">Fonctionnalités Principales</h2>
                        <p class="lead text-muted">Découvrez toutes les fonctionnalités qui font de MOYOO Delivery la solution idéale pour votre entreprise</p>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-primary">
                                    <i class="ti ti-package fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Gestion des Colis</h5>
                            <p class="card-text text-muted">Créez, suivez et gérez tous vos colis avec des codes de suivi uniques et un système de statuts en temps réel.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>Création rapide de colis</li>
                                <li><i class="ti ti-check text-success me-2"></i>Codes de suivi automatiques</li>
                                <li><i class="ti ti-check text-success me-2"></i>Statuts de livraison</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-warning">
                                    <i class="ti ti-user-check fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Gestion des Livreurs</h5>
                            <p class="card-text text-muted">Gérez efficacement vos livreurs avec géolocalisation GPS, profils détaillés et suivi des performances.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>Géolocalisation GPS</li>
                                <li><i class="ti ti-check text-success me-2"></i>Profils détaillés</li>
                                <li><i class="ti ti-check text-success me-2"></i>Suivi des performances</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-danger">
                                    <i class="ti ti-map-pin fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Moniteur Admin</h5>
                            <p class="card-text text-muted">Suivez en temps réel tous vos livreurs sur une carte interactive avec le Moniteur Admin Premium.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>Carte interactive</li>
                                <li><i class="ti ti-check text-success me-2"></i>Suivi temps réel</li>
                                <li><i class="ti ti-check text-success me-2"></i>Plan Premium</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-success">
                                    <i class="ti ti-users fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Gestion des Clients</h5>
                            <p class="card-text text-muted">Base de données complète de vos clients et marchands avec historique des livraisons.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>Profils clients</li>
                                <li><i class="ti ti-check text-success me-2"></i>Historique livraisons</li>
                                <li><i class="ti ti-check text-success me-2"></i>Gestion marchands</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-info">
                                    <i class="ti ti-bell fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text text-muted">Système de notifications SMS et push pour informer tous les acteurs en temps réel.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>SMS automatiques</li>
                                <li><i class="ti ti-check text-success me-2"></i>Notifications push</li>
                                <li><i class="ti ti-check text-success me-2"></i>Alertes personnalisées</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-dark">
                                    <i class="ti ti-chart-bar fs-1"></i>
                                </span>
                            </div>
                            <h5 class="card-title">Rapports & Analytics</h5>
                            <p class="card-text text-muted">Tableaux de bord et rapports détaillés pour analyser vos performances et optimiser vos opérations.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success me-2"></i>Tableaux de bord</li>
                                <li><i class="ti ti-check text-success me-2"></i>Statistiques détaillées</li>
                                <li><i class="ti ti-check text-success me-2"></i>Exports PDF</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="fade-in">
                        <h2 class="display-5 fw-bold text-gradient mb-3">Plans Tarifaires</h2>
                        <p class="lead text-muted">Choisissez le plan qui correspond le mieux à vos besoins</p>
                    </div>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="card pricing-card h-100 text-center">
                        <div class="card-body p-4">
                            <h5 class="card-title">Gratuit</h5>
                            <div class="display-4 fw-bold text-primary mb-3">0 FCFA</div>
                            <p class="text-muted mb-4">Par mois</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Jusqu'à 50 colis/mois</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>2 livreurs maximum</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Support email</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Rapports basiques</li>
                            </ul>
                            <a href="{{ route('auth.register') }}" class="btn btn-outline-primary btn-custom w-100">
                                Commencer
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card pricing-card featured h-100 text-center">
                        <div class="card-body p-4">
                            <h5 class="card-title">Premium</h5>
                            <div class="display-4 fw-bold text-primary mb-3">25,000 FCFA</div>
                            <p class="text-muted mb-4">Par mois</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Colis illimités</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Livreurs illimités</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Moniteur Admin</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Support prioritaire</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Rapports avancés</li>
                            </ul>
                            <a href="{{ route('auth.register') }}" class="btn btn-primary btn-custom w-100">
                                Choisir Premium
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card pricing-card h-100 text-center">
                        <div class="card-body p-4">
                            <h5 class="card-title">Premium Annuel</h5>
                            <div class="display-4 fw-bold text-primary mb-3">250,000 FCFA</div>
                            <p class="text-muted mb-4">Par an (2 mois gratuits)</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Toutes les fonctionnalités Premium</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Support 24/7</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Formation personnalisée</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>API personnalisée</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Économie de 2 mois</li>
                            </ul>
                            <a href="{{ route('auth.register') }}" class="btn btn-outline-primary btn-custom w-100">
                                Choisir Annuel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="fade-in">
                        <h2 class="display-5 fw-bold text-gradient mb-3">Ce que disent nos clients</h2>
                        <p class="lead text-muted">Découvrez les témoignages de nos clients satisfaits</p>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="testimonial-card fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded bg-primary">AK</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Amadou Koné</h6>
                                <small class="text-muted">Directeur, LogiCôte</small>
                            </div>
                        </div>
                        <p class="mb-0">"MOYOO Delivery a révolutionné notre gestion logistique. Le suivi en temps réel et les rapports détaillés nous ont permis d'optimiser nos opérations de 40%."</p>
                        <div class="text-warning mt-2">
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded bg-success">MT</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Marie Traoré</h6>
                                <small class="text-muted">Gérante, ShopExpress</small>
                            </div>
                        </div>
                        <p class="mb-0">"Interface intuitive et fonctionnalités complètes. Nos clients sont ravis du suivi en temps réel de leurs commandes. Un investissement qui se rentabilise rapidement."</p>
                        <div class="text-warning mt-2">
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded bg-warning">JD</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Jean Diabaté</h6>
                                <small class="text-muted">CEO, FastDelivery CI</small>
                            </div>
                        </div>
                        <p class="mb-0">"Le Moniteur Admin Premium est exceptionnel. Nous pouvons suivre tous nos livreurs en temps réel et optimiser nos routes. ROI impressionnant dès le premier mois."</p>
                        <div class="text-warning mt-2">
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                            <i class="ti ti-star-filled"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="fade-in">
                        <h2 class="display-5 fw-bold mb-3">Prêt à révolutionner votre logistique ?</h2>
                        <p class="lead mb-0">Rejoignez plus de 500 entreprises qui font confiance à MOYOO Delivery pour gérer leurs livraisons.</p>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="fade-in">
                        <a href="{{ route('auth.register') }}" class="btn btn-warning btn-custom btn-lg">
                            <i class="ti ti-rocket me-2"></i>Commencer Maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="fade-in">
                        <h2 class="display-5 fw-bold text-gradient mb-3">Contactez-nous</h2>
                        <p class="lead text-muted">Notre équipe est là pour vous accompagner</p>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="text-center fade-in">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-success">
                                <i class="ti ti-phone fs-1"></i>
                            </span>
                        </div>
                        <h5>Support Téléphonique</h5>
                        <p class="text-muted">Lundi - Vendredi<br>8h00 - 18h00</p>
                        <a href="tel:+2250701234567" class="btn btn-outline-success">
                            <i class="ti ti-phone me-2"></i>+225 07 01 23 45 67
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center fade-in">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-info">
                                <i class="ti ti-mail fs-1"></i>
                            </span>
                        </div>
                        <h5>Support Email</h5>
                        <p class="text-muted">Réponse sous 24h<br>7j/7</p>
                        <a href="mailto:support@moyoo.ci" class="btn btn-outline-info">
                            <i class="ti ti-mail me-2"></i>support@moyoo.ci
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center fade-in">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-warning">
                                <i class="ti ti-map-pin fs-1"></i>
                            </span>
                        </div>
                        <h5>Notre Adresse</h5>
                        <p class="text-muted">Cocody, Riviera 2<br>Abidjan, Côte d'Ivoire</p>
                        <a href="#" class="btn btn-outline-warning">
                            <i class="ti ti-map-pin me-2"></i>Voir sur la carte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="ti ti-truck-delivery me-2"></i>MOYOO Delivery
                    </h5>
                    <p class="text-muted">La solution complète de gestion de livraisons pour les entreprises en Côte d'Ivoire.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="ti ti-brand-facebook fs-4"></i></a>
                        <a href="#" class="text-white"><i class="ti ti-brand-twitter fs-4"></i></a>
                        <a href="#" class="text-white"><i class="ti ti-brand-linkedin fs-4"></i></a>
                        <a href="#" class="text-white"><i class="ti ti-brand-instagram fs-4"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Produit</h6>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-muted text-decoration-none">Fonctionnalités</a></li>
                        <li><a href="#pricing" class="text-muted text-decoration-none">Tarifs</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">API</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Intégrations</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Documentation</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Guide d'utilisation</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#contact" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Entreprise</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">À propos</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Carrières</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Presse</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Légal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Conditions d'utilisation</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Politique de confidentialité</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">CGV</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Mentions légales</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2024 MOYOO Delivery. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">Fait avec <i class="ti ti-heart text-danger"></i> en Côte d'Ivoire</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../assets/vendor/libs/hammer/hammer.js"></script>
    <script src="../assets/vendor/libs/i18n/i18n.js"></script>
    <script src="../assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>

    <!-- Custom JS -->
    <script>
        // Smooth scrolling
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

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255,255,255,0.98)';
            } else {
                navbar.style.background = 'rgba(255,255,255,0.95)';
            }
        });
    </script>
</body>
</html>
