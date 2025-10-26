@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header avec icône et titre -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <!-- Icône de couronne -->
                    <div class="mb-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-warning">
                                <i class="ti ti-crown ti-lg"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Titre principal -->
                    <h2 class="text-warning mb-3">Moniteur Admin - Votre Centre de Contrôle Intelligent</h2>
                    <p class="text-muted mb-4">Découvrez comment le Moniteur Admin révolutionne la gestion de vos livraisons et transforme votre business.</p>

                    <!-- Message d'alerte stylisé -->
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-warning">
                                    <i class="ti ti-lock"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1">Fonctionnalité Premium Exclusivement</h5>
                                <p class="mb-0">Le <strong>Moniteur Admin</strong> est disponible uniquement avec le <strong>Plan Premium</strong> - Votre investissement dans l'excellence opérationnelle.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton d'action rapide -->
                    <div class="text-center mb-5">
                        <a href="{{ route('subscriptions.payment', 3) }}"
                           class="btn btn-warning btn-lg px-5 shadow">
                            <i class="ti ti-crown me-2"></i>
                            Passer au Plan Premium - Débloquer le Moniteur Admin
                        </a>
                        <p class="text-muted mt-2 mb-0">
                            <small>✓ Activation immédiate • ✓ Accès instantané • ✓ Support prioritaire</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section : Qu'est-ce que le Moniteur Admin ? -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="ti ti-eye me-2"></i>Qu'est-ce que le Moniteur Admin ? Votre Tour de Contrôle Digitale
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <p class="lead">Imaginez avoir une tour de contrôle comme dans un aéroport, mais pour vos livraisons. Le Moniteur Admin est exactement cela : votre centre de commandement digital qui vous donne une vue d'ensemble complète de toutes vos opérations en temps réel.</p>

                            <h5 class="mt-4 mb-3">🎯 Concrètement, que voyez-vous sur votre écran ?</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-success">
                                                <i class="ti ti-map-pin"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Position Exacte</h6>
                                            <small class="text-muted">Chaque livreur apparaît comme un point sur la carte avec son nom, statut et direction</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-info">
                                                <i class="ti ti-clock"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Temps de Livraison</h6>
                                            <small class="text-muted">Estimation précise de l'arrivée chez chaque client</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="ti ti-package"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Statut des Colis</h6>
                                            <small class="text-muted">En cours, livré, en attente, problème - tout est visible</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-danger">
                                                <i class="ti ti-alert-triangle"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Alertes Intelligentes</h6>
                                            <small class="text-muted">Notifications automatiques en cas de retard ou problème</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3">💡 Exemple Concret d'Usage</h5>
                            <div class="alert alert-light border">
                                <p class="mb-2"><strong>Scénario :</strong> Vous avez 5 livreurs en route avec 20 colis à livrer dans Abidjan.</p>
                                <p class="mb-2"><strong>Sans Moniteur Admin :</strong> Vous appelez chaque livreur pour savoir où il en est. Temps perdu : 30 minutes.</p>
                                <p class="mb-0"><strong>Avec Moniteur Admin :</strong> Un coup d'œil sur votre écran et vous savez tout instantanément. Temps gagné : 30 minutes par jour = 2h30 par semaine = 10h par mois !</p>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="avatar avatar-xxl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-primary">
                                    <i class="ti ti-dashboard ti-lg"></i>
                                </span>
                            </div>
                            <h5>Dashboard Temps Réel</h5>
                            <p class="text-muted">Toutes vos informations critiques en un seul endroit</p>

                            <div class="mt-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="text-primary">Vue d'ensemble instantanée</h6>
                                        <div class="row text-center mt-3">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-success mb-0">12</h4>
                                                    <small>Livreurs actifs</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-info mb-0">47</h4>
                                                <small>Colis en cours</small>
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

    <!-- Section : Pourquoi c'est important pour votre business ? -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="ti ti-trending-up me-2"></i>Pourquoi le Moniteur Admin Transforme Votre Business ?
                    </h4>
                </div>
                <div class="card-body">
                    <p class="lead text-center mb-4">Le Moniteur Admin n'est pas juste un outil de surveillance, c'est un <strong>multiplicateur de performance</strong> qui transforme chaque aspect de votre activité de livraison.</p>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <div class="avatar avatar-lg mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-success">
                                        <i class="ti ti-clock"></i>
                                    </span>
                                </div>
                                <h5>⏰ Gain de Temps Massif</h5>
                                <p class="text-muted">Réduction de 30% du temps de livraison grâce à l'optimisation des trajets</p>

                                <div class="alert alert-light border-success mt-3">
                                    <h6 class="text-success">💰 Calcul Concret :</h6>
                                    <p class="mb-1"><strong>Avant :</strong> 5 livreurs × 8h/jour = 40h de travail</p>
                                    <p class="mb-1"><strong>Avec Moniteur :</strong> 40h - 30% = 28h de travail</p>
                                    <p class="mb-0"><strong>Gain :</strong> 12h/jour = 60h/semaine = 240h/mois</p>
                                </div>
                                <div class="badge bg-success">+30% Efficacité</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <div class="avatar avatar-lg mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-info">
                                        <i class="ti ti-currency-dollar"></i>
                                    </span>
                                </div>
                                <h5>💵 Réduction des Coûts</h5>
                                <p class="text-muted">Économies significatives sur le carburant et les frais opérationnels</p>

                                <div class="alert alert-light border-info mt-3">
                                    <h6 class="text-info">📊 Économies Réelles :</h6>
                                    <p class="mb-1"><strong>Carburant :</strong> -25% = 50 000 FCFA/mois/livreur</p>
                                    <p class="mb-1"><strong>Maintenance :</strong> -20% = 30 000 FCFA/mois/livreur</p>
                                    <p class="mb-0"><strong>Total :</strong> 80 000 FCFA/mois/livreur</p>
                                </div>
                                <div class="badge bg-info">-25% Coûts</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <div class="avatar avatar-lg mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-warning">
                                        <i class="ti ti-heart"></i>
                                    </span>
                                </div>
                                <h5>❤️ Satisfaction Client</h5>
                                <p class="text-muted">Clients plus satisfaits grâce à un suivi transparent et des livraisons ponctuelles</p>

                                <div class="alert alert-light border-warning mt-3">
                                    <h6 class="text-warning">📈 Impact Client :</h6>
                                    <p class="mb-1"><strong>Ponctualité :</strong> +40% de livraisons à l'heure</p>
                                    <p class="mb-1"><strong>Transparence :</strong> Client sait où est son colis</p>
                                    <p class="mb-0"><strong>Résultat :</strong> +40% de satisfaction</p>
                                </div>
                                <div class="badge bg-warning">+40% Satisfaction</div>
                            </div>
                        </div>
                    </div>

                    <!-- Exemple concret de transformation -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="ti ti-chart-line me-2"></i>Exemple Concret : Transformation d'une Entreprise de Livraison
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-danger">❌ AVANT le Moniteur Admin :</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">• <strong>Problème :</strong> 3 livreurs perdus dans Abidjan</li>
                                                <li class="mb-2">• <strong>Résultat :</strong> 15 colis en retard, 5 clients mécontents</li>
                                                <li class="mb-2">• <strong>Coût :</strong> 2h de recherche + 50 000 FCFA de carburant gaspillé</li>
                                                <li class="mb-2">• <strong>Impact :</strong> 2 clients ont annulé leurs commandes futures</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">✅ APRÈS le Moniteur Admin :</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">• <strong>Solution :</strong> Localisation instantanée des 3 livreurs</li>
                                                <li class="mb-2">• <strong>Résultat :</strong> Redirection immédiate, tous les colis livrés à l'heure</li>
                                                <li class="mb-2">• <strong>Économie :</strong> 0 FCFA gaspillé, 2h de temps sauvées</li>
                                                <li class="mb-2">• <strong>Impact :</strong> Clients satisfaits, commandes futures confirmées</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <div class="badge bg-success fs-6">Résultat : 100% de réussite vs 60% avant</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section : Fonctionnalités détaillées -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="ti ti-settings me-2"></i>Fonctionnalités Avancées : Comment Ça Marche Concrètement ?
                    </h4>
                </div>
                <div class="card-body">
                    <p class="lead text-center mb-4">Chaque fonctionnalité du Moniteur Admin est conçue pour résoudre un problème spécifique que vous rencontrez quotidiennement.</p>

                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                <i class="ti ti-map"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h5>🗺️ Cartographie Interactive</h5>
                                            <p class="text-muted mb-0">Votre vue satellite sur toutes les opérations</p>
                                        </div>
                                    </div>

                                    <h6 class="text-primary">🎯 Ce que vous voyez :</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">• <strong>Points colorés</strong> : Chaque livreur avec sa couleur unique</li>
                                        <li class="mb-2">• <strong>Trajets tracés</strong> : Lignes montrant le parcours prévu</li>
                                        <li class="mb-2">• <strong>Zones de livraison</strong> : Cercles autour des adresses clients</li>
                                        <li class="mb-2">• <strong>Trafic en temps réel</strong> : Zones rouges = embouteillages</li>
                                    </ul>

                                    <div class="alert alert-light border-primary">
                                        <h6 class="text-primary">💡 Exemple d'usage :</h6>
                                        <p class="mb-0">"Je vois que Jean est coincé dans un embouteillage à Cocody. Je peux immédiatement lui dire de prendre l'autoroute du Nord pour éviter 30 minutes de retard."</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <span class="avatar-initial rounded-circle bg-success">
                                                <i class="ti ti-bell"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h5>🔔 Alertes Intelligentes</h5>
                                            <p class="text-muted mb-0">Votre assistant personnel qui surveille 24h/24</p>
                                        </div>
                                    </div>

                                    <h6 class="text-success">🚨 Types d'alertes :</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">• <strong>Retard imminent</strong> : "Paul va avoir 15 min de retard"</li>
                                        <li class="mb-2">• <strong>Livreur arrêté</strong> : "Marie n'a pas bougé depuis 10 min"</li>
                                        <li class="mb-2">• <strong>Zone dangereuse</strong> : "Attention : zone de travaux signalée"</li>
                                        <li class="mb-2">• <strong>Batterie faible</strong> : "Téléphone de Koffi à 15%"</li>
                                    </ul>

                                    <div class="alert alert-light border-success">
                                        <h6 class="text-success">💡 Exemple d'usage :</h6>
                                        <p class="mb-0">"L'alerte me dit que Koffi est en retard. Je l'appelle immédiatement et je réorganise ses livraisons pour éviter l'effet domino."</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="ti ti-chart-bar"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h5>📊 Statistiques Détaillées</h5>
                                            <p class="text-muted mb-0">Vos données business transformées en insights</p>
                                        </div>
                                    </div>

                                    <h6 class="text-warning">📈 Métriques clés :</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">• <strong>Temps moyen de livraison</strong> : 45 min vs 60 min avant</li>
                                        <li class="mb-2">• <strong>Livreur le plus efficace</strong> : Paul = 95% de réussite</li>
                                        <li class="mb-2">• <strong>Zones problématiques</strong> : Marcory = +20% de retards</li>
                                        <li class="mb-2">• <strong>Heures de pointe</strong> : 14h-16h = 40% plus lent</li>
                                    </ul>

                                    <div class="alert alert-light border-warning">
                                        <h6 class="text-warning">💡 Exemple d'usage :</h6>
                                        <p class="mb-0">"Les stats montrent que Marcory pose problème. Je vais affecter Paul (mon meilleur livreur) à cette zone pour améliorer les performances."</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-danger h-100">
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <span class="avatar-initial rounded-circle bg-danger">
                                                <i class="ti ti-shield-check"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h5>🛡️ Sécurité Renforcée</h5>
                                            <p class="text-muted mb-0">Protection maximale pour vos équipes et colis</p>
                                        </div>
                                    </div>

                                    <h6 class="text-danger">🔒 Fonctionnalités sécurité :</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">• <strong>Détection d'arrêt anormal</strong> : Livreur arrêté > 15 min</li>
                                        <li class="mb-2">• <strong>Zones de danger</strong> : Alertes zones à éviter</li>
                                        <li class="mb-2">• <strong>Historique des trajets</strong> : Traçabilité complète</li>
                                        <li class="mb-2">• <strong>Bouton SOS</strong> : Livreur peut alerter en urgence</li>
                                    </ul>

                                    <div class="alert alert-light border-danger">
                                        <h6 class="text-danger">💡 Exemple d'usage :</h6>
                                        <p class="mb-0">"Marie a appuyé sur SOS. Je vois immédiatement sa position et j'envoie de l'aide. Le client est informé du retard et reste calme."</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section : Témoignages et ROI -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="ti ti-star me-2"></i>Retour sur Investissement
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h5>Investissement qui se rentabilise rapidement</h5>
                            <p>Nos clients utilisant le Moniteur Admin constatent en moyenne :</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>30% de réduction des coûts de transport</li>
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>25% d'amélioration de la ponctualité</li>
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>40% d'augmentation de la satisfaction client</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>50% de réduction des plaintes clients</li>
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>20% d'augmentation du nombre de livraisons</li>
                                        <li class="mb-2"><i class="ti ti-check text-success me-2"></i>ROI positif dès le premier mois</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="bg-light rounded p-4">
                                <h3 class="text-success mb-2">ROI</h3>
                                <h2 class="text-success mb-0">+150%</h2>
                                <p class="text-muted mb-0">Retour sur investissement moyen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section : Call to Action -->
    <div class="row">
        <div class="col-12">
            <div class="card border-warning shadow-lg">
                <div class="card-header bg-warning text-white text-center">
                    <h4 class="mb-0">
                        <i class="ti ti-crown me-2"></i>Débloquez le Moniteur Admin
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="ti ti-crown text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">Prêt à transformer votre activité ?</h3>
                    <p class="text-muted mb-4">Rejoignez des centaines d'entreprises qui optimisent déjà leurs livraisons avec le Moniteur Admin.</p>

                    <div class="row justify-content-center mb-4">
                        <div class="col-md-8">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="avatar avatar-sm mb-2">
                                            <span class="avatar-initial rounded-circle bg-success">
                                                <i class="ti ti-shield-check"></i>
                                            </span>
                                        </div>
                                        <h6 class="mb-1">Sécurisé</h6>
                                        <small class="text-muted">Paiements sécurisés</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="avatar avatar-sm mb-2">
                                            <span class="avatar-initial rounded-circle bg-info">
                                                <i class="ti ti-clock"></i>
                                            </span>
                                        </div>
                                        <h6 class="mb-1">Immédiat</h6>
                                        <small class="text-muted">Activation instantanée</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="avatar avatar-sm mb-2">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="ti ti-headset"></i>
                                            </span>
                                        </div>
                                        <h6 class="mb-1">Support</h6>
                                        <small class="text-muted">Assistance 24/7</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton d'action principal -->
                    <a href="{{ route('subscriptions.payment', 3) }}"
                       class="btn btn-warning btn-lg px-5">
                        <i class="ti ti-crown me-2"></i>
                        Passer au Plan Premium
                    </a>

                    <p class="text-muted mt-3 mb-0">
                        <small>✓ Activation immédiate • ✓ Support prioritaire • ✓ Garantie satisfait ou remboursé</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
