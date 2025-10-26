@include('layouts.header')
@include('layouts.menu')

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <div class="layout-page">
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <div class="avatar avatar-xl mx-auto mb-3">
                                        <span class="avatar-initial rounded bg-primary">
                                            <i class="ti ti-book-2 fs-1"></i>
                                        </span>
                                    </div>
                                    <h1 class="display-6 fw-bold text-primary mb-2">Documentation MOYOO</h1>
                                    <p class="fs-5 text-muted mb-0">Guide complet pour utiliser le système de livraison MOYOO</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-list me-2"></i>Navigation Rapide
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <a href="#introduction" class="btn btn-outline-primary w-100">
                                                <i class="ti ti-info-circle me-2"></i>Introduction
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#fonctionnalites" class="btn btn-outline-primary w-100">
                                                <i class="ti ti-apps me-2"></i>Fonctionnalités
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#guide-utilisation" class="btn btn-outline-primary w-100">
                                                <i class="ti ti-user me-2"></i>Guide d'Utilisation
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#support" class="btn btn-outline-primary w-100">
                                                <i class="ti ti-headset me-2"></i>Support
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Introduction -->
                    <div class="row mb-4" id="introduction">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-info-circle text-primary me-2"></i>Introduction au Système MOYOO
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <h5>Qu'est-ce que MOYOO ?</h5>
                                            <p class="mb-3">MOYOO est une plateforme complète de gestion de livraisons conçue spécialement pour les entreprises de logistique en Côte d'Ivoire. Notre système vous permet de gérer efficacement vos colis, livreurs, et clients.</p>
                                            
                                            <h5>Avantages du Système</h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i><strong>Gestion Centralisée :</strong> Tous vos colis et livreurs en un seul endroit</li>
                                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i><strong>Suivi en Temps Réel :</strong> Géolocalisation GPS de vos livreurs</li>
                                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i><strong>Notifications Automatiques :</strong> SMS et notifications push</li>
                                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i><strong>Rapports Détaillés :</strong> Statistiques et analyses complètes</li>
                                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i><strong>Multi-Entreprise :</strong> Gestion de plusieurs entreprises</li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="text-center">
                                                <div class="avatar avatar-4xl mx-auto mb-3">
                                                    <span class="avatar-initial rounded bg-primary">
                                                        <i class="ti ti-truck-delivery fs-1"></i>
                                                    </span>
                                                </div>
                                                <h6 class="text-muted">Système de Livraison Intelligent</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fonctionnalités -->
                    <div class="row mb-4" id="fonctionnalites">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-apps text-primary me-2"></i>Fonctionnalités Principales
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <!-- Gestion des Colis -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-info">
                                                            <i class="ti ti-package fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Gestion des Colis</h6>
                                                    <p class="card-text small text-muted">Création, suivi et gestion complète de vos colis avec codes de suivi uniques.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>Création de colis</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Codes de suivi</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Statuts de livraison</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gestion des Livreurs -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-warning">
                                                            <i class="ti ti-user-check fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Gestion des Livreurs</h6>
                                                    <p class="card-text small text-muted">Gestion complète de vos livreurs avec géolocalisation en temps réel.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>Profil livreur</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Géolocalisation GPS</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Statuts en temps réel</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Moniteur Admin -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-danger">
                                                            <i class="ti ti-map-pin fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Moniteur Admin</h6>
                                                    <p class="card-text small text-muted">Suivi en temps réel de tous vos livreurs sur une carte interactive.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>Carte interactive</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Suivi temps réel</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Plan Premium</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gestion des Clients -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-success">
                                                            <i class="ti ti-users fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Gestion des Clients</h6>
                                                    <p class="card-text small text-muted">Base de données complète de vos clients et marchands.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>Profils clients</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Historique livraisons</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Gestion marchands</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notifications -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-secondary">
                                                            <i class="ti ti-bell fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Notifications</h6>
                                                    <p class="card-text small text-muted">Système de notifications SMS et push pour tous les acteurs.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>SMS automatiques</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Notifications push</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Alertes personnalisées</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rapports -->
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <div class="avatar avatar-lg mx-auto mb-3">
                                                        <span class="avatar-initial rounded bg-dark">
                                                            <i class="ti ti-chart-bar fs-4"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="card-title">Rapports & Analytics</h6>
                                                    <p class="card-text small text-muted">Tableaux de bord et rapports détaillés pour analyser vos performances.</p>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="ti ti-check text-success me-1"></i>Tableaux de bord</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Statistiques</li>
                                                        <li><i class="ti ti-check text-success me-1"></i>Exports PDF</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guide d'Utilisation -->
                    <div class="row mb-4" id="guide-utilisation">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-user text-primary me-2"></i>Guide d'Utilisation
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <!-- Étapes de Base -->
                                            <h5 class="mb-3">Étapes de Base pour Commencer</h5>
                                            
                                            <div class="accordion" id="accordionGuide">
                                                <!-- Étape 1 -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                                                            <span class="badge bg-primary me-2">1</span> Configuration Initiale
                                                        </button>
                                                    </h2>
                                                    <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#accordionGuide">
                                                        <div class="accordion-body">
                                                            <p><strong>Première connexion :</strong></p>
                                                            <ol>
                                                                <li>Connectez-vous avec vos identifiants fournis</li>
                                                                <li>Configurez votre profil entreprise</li>
                                                                <li>Ajoutez vos premiers livreurs</li>
                                                                <li>Configurez vos zones de livraison</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Étape 2 -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                                                            <span class="badge bg-primary me-2">2</span> Création de Colis
                                                        </button>
                                                    </h2>
                                                    <div id="step2" class="accordion-collapse collapse" data-bs-parent="#accordionGuide">
                                                        <div class="accordion-body">
                                                            <p><strong>Comment créer un colis :</strong></p>
                                                            <ol>
                                                                <li>Allez dans "Colis" > "Nouveau Colis"</li>
                                                                <li>Sélectionnez le marchand et la boutique</li>
                                                                <li>Renseignez les informations du client</li>
                                                                <li>Choisissez le livreur et l'engin</li>
                                                                <li>Calculez automatiquement le coût</li>
                                                                <li>Validez la création</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Étape 3 -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                                                            <span class="badge bg-primary me-2">3</span> Suivi des Livraisons
                                                        </button>
                                                    </h2>
                                                    <div id="step3" class="accordion-collapse collapse" data-bs-parent="#accordionGuide">
                                                        <div class="accordion-body">
                                                            <p><strong>Suivre vos livraisons :</strong></p>
                                                            <ol>
                                                                <li>Consultez la liste des colis</li>
                                                                <li>Utilisez le Moniteur Admin (Premium) pour le suivi temps réel</li>
                                                                <li>Recevez les notifications de statut</li>
                                                                <li>Gérez les retours et problèmes</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Étape 4 -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                                                            <span class="badge bg-primary me-2">4</span> Gestion des Livreurs
                                                        </button>
                                                    </h2>
                                                    <div id="step4" class="accordion-collapse collapse" data-bs-parent="#accordionGuide">
                                                        <div class="accordion-body">
                                                            <p><strong>Gérer vos livreurs :</strong></p>
                                                            <ol>
                                                                <li>Ajoutez de nouveaux livreurs</li>
                                                                <li>Configurez leurs profils et engins</li>
                                                                <li>Activez la géolocalisation</li>
                                                                <li>Surveillez leurs performances</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            <!-- Conseils -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-lightbulb text-warning me-2"></i>Conseils d'Utilisation
                                                    </h6>
                                                    <ul class="list-unstyled small">
                                                        <li class="mb-2">
                                                            <i class="ti ti-check text-success me-1"></i>
                                                            <strong>Codes de Ramassage :</strong> Utilisez les codes de ramassage pour pré-remplir automatiquement les informations
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="ti ti-check text-success me-1"></i>
                                                            <strong>Notifications :</strong> Activez les notifications pour rester informé en temps réel
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="ti ti-check text-success me-1"></i>
                                                            <strong>Plan Premium :</strong> Passez au Premium pour accéder au Moniteur Admin
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="ti ti-check text-success me-1"></i>
                                                            <strong>Rapports :</strong> Consultez régulièrement vos rapports pour optimiser vos performances
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <!-- Raccourcis -->
                                            <div class="card border-0 bg-primary text-white mt-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="ti ti-keyboard me-2"></i>Raccourcis Clavier
                                                    </h6>
                                                    <ul class="list-unstyled small">
                                                        <li class="mb-1"><kbd>Ctrl</kbd> + <kbd>N</kbd> Nouveau colis</li>
                                                        <li class="mb-1"><kbd>Ctrl</kbd> + <kbd>S</kbd> Sauvegarder</li>
                                                        <li class="mb-1"><kbd>Ctrl</kbd> + <kbd>F</kbd> Rechercher</li>
                                                        <li class="mb-1"><kbd>Esc</kbd> Annuler</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-help text-primary me-2"></i>Questions Fréquentes (FAQ)
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h6>Comment créer un compte livreur ?</h6>
                                            <p class="small text-muted">Allez dans "Livreurs" > "Nouveau Livreur" et remplissez le formulaire avec les informations du livreur.</p>
                                            
                                            <h6>Comment activer la géolocalisation ?</h6>
                                            <p class="small text-muted">La géolocalisation s'active automatiquement quand le livreur se connecte à l'application mobile.</p>
                                            
                                            <h6>Comment calculer les coûts de livraison ?</h6>
                                            <p class="small text-muted">Le système calcule automatiquement les coûts basés sur le poids, la distance, et le mode de livraison.</p>
                                        </div>
                                        <div class="col-lg-6">
                                            <h6>Comment accéder au Moniteur Admin ?</h6>
                                            <p class="small text-muted">Le Moniteur Admin est disponible avec un abonnement Premium. Allez dans "Abonnements" pour souscrire.</p>
                                            
                                            <h6>Comment gérer les retours de colis ?</h6>
                                            <p class="small text-muted">Modifiez le statut du colis en "Retour" et assignez un nouveau livreur si nécessaire.</p>
                                            
                                            <h6>Comment exporter les rapports ?</h6>
                                            <p class="small text-muted">Dans la section "Rapports", utilisez le bouton "Exporter" pour télécharger en PDF ou Excel.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="row mb-4" id="support">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-headset text-primary me-2"></i>Support & Contact
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="avatar avatar-lg mx-auto mb-3">
                                                    <span class="avatar-initial rounded bg-success">
                                                        <i class="ti ti-phone fs-4"></i>
                                                    </span>
                                                </div>
                                                <h6>Support Téléphonique</h6>
                                                <p class="small text-muted">Lundi - Vendredi<br>8h00 - 18h00</p>
                                                <a href="tel:+2250701234567" class="btn btn-outline-success btn-sm">
                                                    <i class="ti ti-phone me-1"></i>+225 07 01 23 45 67
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="avatar avatar-lg mx-auto mb-3">
                                                    <span class="avatar-initial rounded bg-info">
                                                        <i class="ti ti-mail fs-4"></i>
                                                    </span>
                                                </div>
                                                <h6>Support Email</h6>
                                                <p class="small text-muted">Réponse sous 24h<br>7j/7</p>
                                                <a href="mailto:support@moyoo.ci" class="btn btn-outline-info btn-sm">
                                                    <i class="ti ti-mail me-1"></i>support@moyoo.ci
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="avatar avatar-lg mx-auto mb-3">
                                                    <span class="avatar-initial rounded bg-warning">
                                                        <i class="ti ti-message-circle fs-4"></i>
                                                    </span>
                                                </div>
                                                <h6>Chat en Direct</h6>
                                                <p class="small text-muted">Disponible<br>24h/24</p>
                                                <button class="btn btn-outline-warning btn-sm" onclick="alert('Chat en cours de développement')">
                                                    <i class="ti ti-message-circle me-1"></i>Démarrer Chat
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Version & Changelog -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="ti ti-info-circle text-primary me-2"></i>Informations Système
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Version Actuelle</h6>
                                            <p class="mb-2"><strong>MOYOO v2.1.0</strong></p>
                                            <p class="small text-muted">Dernière mise à jour : {{ date('d/m/Y') }}</p>
                                            
                                            <h6 class="mt-3">Nouvelles Fonctionnalités</h6>
                                            <ul class="list-unstyled small">
                                                <li><i class="ti ti-check text-success me-1"></i>Système d'abonnements Premium</li>
                                                <li><i class="ti ti-check text-success me-1"></i>Moniteur Admin temps réel</li>
                                                <li><i class="ti ti-check text-success me-1"></i>Notifications push améliorées</li>
                                                <li><i class="ti ti-check text-success me-1"></i>Interface utilisateur optimisée</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Prochaines Mises à Jour</h6>
                                            <ul class="list-unstyled small">
                                                <li><i class="ti ti-clock text-warning me-1"></i>Application mobile livreur</li>
                                                <li><i class="ti ti-clock text-warning me-1"></i>API publique</li>
                                                <li><i class="ti ti-clock text-warning me-1"></i>Intégration paiements</li>
                                                <li><i class="ti ti-clock text-warning me-1"></i>Rapports avancés</li>
                                            </ul>
                                            
                                            <h6 class="mt-3">Compatibilité</h6>
                                            <p class="small text-muted">
                                                <i class="ti ti-device-desktop me-1"></i>Navigateurs : Chrome, Firefox, Safari, Edge<br>
                                                <i class="ti ti-device-mobile me-1"></i>Mobile : iOS 12+, Android 8+
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Smooth scrolling pour la navigation
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

// Animation des cartes au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer toutes les cartes
document.querySelectorAll('.card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});
</script>

@include('layouts.footer')
