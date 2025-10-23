@include('layouts.header')
@include('layouts.menu')

<style>
/* Styles personnalisés pour la pagination */
.pagination-custom .pagination {
    margin-bottom: 0;
}

.pagination-custom .page-link {
    color: #696cff;
    border-color: #d9dee3;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.pagination-custom .page-link:hover {
    color: #5f61e6;
    background-color: #f8f9fa;
    border-color: #d9dee3;
}

.pagination-custom .page-item.active .page-link {
    background-color: #696cff;
    border-color: #696cff;
    color: #fff;
}

.pagination-custom .page-item.disabled .page-link {
    color: #a1acb8;
    background-color: #fff;
    border-color: #d9dee3;
}

/* Style pour le sélecteur per_page */
#perPageSelect {
    min-width: 70px;
}

/* Styles pour les boutons d'action rapide */
.quick-action-btn {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    min-height: 120px;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: currentColor;
}

.quick-action-btn .avatar {
    transition: transform 0.3s ease;
}

.quick-action-btn:hover .avatar {
    transform: scale(1.1);
}
</style>

<!-- Informations d'Abonnement -->
@php
$user = Auth::user();
$hasSubscription = $user && $user->subscriptionPlan;
@endphp

@if($hasSubscription)
<div class="row g-4 mb-4">
<div class="col-12">
    @php
        $isActive = $user->hasActiveSubscription();
        $isTrial = $user->is_trial;
        $isExpired = $user->subscription_expires_at && $user->subscription_expires_at->isPast();
        $isTrialExpired = $user->trial_expires_at && $user->trial_expires_at->isPast();

        // Calculer les jours restants
        $daysRemaining = 0;
        if ($isTrial && $user->trial_expires_at) {
            $daysRemaining = max(0, now()->diffInDays($user->trial_expires_at, false));
        } elseif ($user->subscription_expires_at) {
            $daysRemaining = max(0, now()->diffInDays($user->subscription_expires_at, false));
        }
    @endphp

    <div class="card card-border-shadow-{{ $isActive ? 'success' : ($isTrial ? 'warning' : 'secondary') }}">
        <div class="card-body" style="padding-top: 0 !important; padding-bottom: 5px !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-{{ $isActive ? 'success' : ($isTrial ? 'warning' : 'secondary') }}">
                            <i class="ti ti-crown ti-28px"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-1">
                            Abonnement {{ $user->subscriptionPlan->name }}
                            @if($isTrial)
                                <span class="badge bg-label-warning ms-2">Période d'essai</span>
                            @elseif($isActive)
                                <span class="badge bg-label-success ms-2">Actif</span>
                            @elseif($isExpired)
                                <span class="badge bg-label-danger ms-2">Expiré</span>
                            @else
                                <span class="badge bg-label-secondary ms-2">Inactif</span>
                            @endif
                        </h5>
                        <p class="mb-0 text-muted">
                            @if($isTrial && $user->trial_expires_at)
                                Période d'essai jusqu'au {{ $user->trial_expires_at->format('d/m/Y à H:i') }}
                            @elseif($user->subscription_expires_at)
                                Abonnement jusqu'au {{ $user->subscription_expires_at->format('d/m/Y à H:i') }}
                            @else
                                Aucun abonnement actif
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-end">
                    @if($daysRemaining > 0)
                        <div class="mb-2">
                            <h4 class="mb-0 text-{{ $daysRemaining <= 7 ? 'danger' : ($daysRemaining <= 30 ? 'warning' : 'success') }}">
                                {{ floor($daysRemaining) }}
                            </h4>
                            <small class="text-muted">
                                jour{{ floor($daysRemaining) > 1 ? 's' : '' }} restant{{ floor($daysRemaining) > 1 ? 's' : '' }}
                            </small>
                        </div>
                    @endif
                    <div>
                        @if($isTrial && $daysRemaining <= 7)
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-warning btn-sm">
                                <i class="ti ti-crown me-1"></i>
                                Passer au Premium
                            </a>
                        @elseif($isExpired || $isTrialExpired)
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-refresh me-1"></i>
                                Renouveler
                            </a>
                        @elseif(!$isActive)
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-crown me-1"></i>
                                Gérer l'abonnement
                            </a>
                        @else
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-success btn-sm">
                                <i class="ti ti-settings me-1"></i>
                                Gérer
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if($isTrial && $daysRemaining <= 7)
            <div class="alert alert-warning mt-3 mb-0">
                <div class="d-flex align-items-center">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <div>
                        <strong>Période d'essai bientôt expirée !</strong>
                        Votre période d'essai se termine dans {{ floor($daysRemaining) }} jour{{ floor($daysRemaining) > 1 ? 's' : '' }}.
                        <a href="{{ route('subscriptions.index') }}" class="alert-link">Passez au Premium</a> pour continuer à profiter de toutes les fonctionnalités.
                    </div>
                </div>
            </div>
            @elseif($isExpired)
            <div class="alert alert-danger mt-3 mb-0">
                <div class="d-flex align-items-center">
                    <i class="ti ti-alert-circle me-2"></i>
                    <div>
                        <strong>Abonnement expiré !</strong>
                        Votre abonnement a expiré.
                        <a href="{{ route('subscriptions.index') }}" class="alert-link">Renouvelez maintenant</a> pour continuer à utiliser la plateforme.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
@endif

        <!-- Boutons d'Action Rapide -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="card-title mb-1">Actions Rapides</h5>
                                <p class="text-muted mb-0">Créez rapidement de nouveaux éléments</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-bolt ti-24px"></i>
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-2 col-md-6">
                                <a href="{{ route('colis.create') }}" class="btn btn-outline-primary w-100 quick-action-btn d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar mb-3">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ti ti-package ti-24px"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">Créer un Colis</h6>
                                    <small class="text-muted text-center">Nouveau colis à livrer</small>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <a href="{{ route('ramassages.create') }}" class="btn btn-outline-success w-100 quick-action-btn d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar mb-3">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="ti ti-truck ti-24px"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">Créer un Ramassage</h6>
                                    <small class="text-muted text-center">Nouveau ramassage</small>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <a href="{{ route('marchands.create') }}" class="btn btn-outline-info w-100 quick-action-btn d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar mb-3">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class="ti ti-user-plus ti-24px"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">Créer un Marchand</h6>
                                    <small class="text-muted text-center">Nouveau marchand</small>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-6">
                                <a href="{{ route('livreurs.create') }}" class="btn btn-outline-warning w-100 quick-action-btn d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar mb-3">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="ti ti-truck-delivery ti-24px"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">Créer un Livreur</h6>
                                    <small class="text-muted text-center">Nouveau livreur</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <a href="{{ route('boutiques.create') }}" class="btn btn-outline-secondary w-100 quick-action-btn d-flex flex-column align-items-center justify-content-center py-4">
                                    <div class="avatar mb-3">
                                        <span class="avatar-initial rounded bg-label-secondary">
                                            <i class="ti ti-building-store ti-24px"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-1">Créer une Boutique</h6>
                                    <small class="text-muted text-center">Nouvelle boutique</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des Frais de Livraison -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-currency-dollar ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ number_format($fraisStats['aujourdhui'], 0, ',', ' ') }} FCFA</h5>
                    </div>
                        <p class="mb-1">Frais de livraison</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">
                                @if($fraisStats['aujourdhui'] > 0)
                                    Aujourd'hui
                                @else
                                    Dernière activité
                                @endif
                            </span>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-currency-dollar ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ number_format($fraisStats['cette_semaine'], 0, ',', ' ') }} FCFA</h5>
                    </div>
                        <p class="mb-1">Frais de livraison</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Cette semaine</span>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-currency-dollar ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ number_format($fraisStats['ce_mois'], 0, ',', ' ') }} FCFA</h5>
                    </div>
                        <p class="mb-1">Frais de livraison</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Ce mois</span>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-currency-dollar ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ number_format($fraisStats['total'], 0, ',', ' ') }} FCFA</h5>
                        </div>
                        <p class="mb-1">Total des frais</p>
                        <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Tous les temps</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des Colis -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-package ti-28px"></i>
                                </span>
                            </div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                    </div>
                    <p class="mb-1">Total des colis</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Tous les temps</span>
                            <small class="text-muted d-block">Hier: {{ $stats['hier'] }} colis</small>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-check-circle ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ $stats['livres'] }}</h5>
                    </div>
                    <p class="mb-1">Colis livrés</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Livrés</span>
                            <small class="text-muted d-block">Hier: {{ $stats['livres_hier'] ?? 0 }} livrés</small>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-clock ti-28px"></i>
                                </span>
                            </div>
                            <h5 class="mb-0">{{ $stats['en_cours'] }}</h5>
                        </div>
                        <p class="mb-1">En cours</p>
                        <p class="mb-0">
                            <span class="text-heading fw-medium me-2">En transit</span>
                            <small class="text-muted d-block">Hier: {{ $stats['en_cours_hier'] ?? 0 }} en cours</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-hourglass ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ $stats['en_attente'] }}</h5>
                    </div>
                        <p class="mb-1">En attente</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">En attente</span>
                            <small class="text-muted d-block">Hier: {{ $stats['en_attente_hier'] ?? 0 }} en attente</small>
                    </p>
                </div>
                </div>
            </div>

        </div>

        <!-- Statistiques des Livreurs et Marchands -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-truck ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ $livreurStats['total'] }}</h5>
                    </div>
                        <p class="mb-1">Total livreurs</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">{{ $livreurStats['actifs'] }} actifs</span>
                            <small class="text-muted d-block">Hier: {{ $livreurStats['hier'] }} ajoutés</small>
                    </p>
                </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-building-store ti-28px"></i>
                                </span>
                            </div>
                            <h5 class="mb-0">{{ $marchandStats['total'] }}</h5>
                        </div>
                        <p class="mb-1">Total marchands</p>
                        <p class="mb-0">
                            <span class="text-heading fw-medium me-2">{{ $marchandStats['actifs'] }} actifs</span>
                            <small class="text-muted d-block">Hier: {{ $marchandStats['hier'] }} ajoutés</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-calendar ti-28px"></i>
                                </span>
                            </div>
                            <h5 class="mb-0">{{ $stats['aujourdhui'] }}</h5>
                        </div>
                        <p class="mb-1">Colis aujourd'hui</p>
                        <p class="mb-0">
                            <span class="text-heading fw-medium me-2">Nouveaux</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-calendar-week ti-28px"></i>
                                </span>
                    </div>
                            <h5 class="mb-0">{{ $stats['cette_semaine'] }}</h5>
                    </div>
                        <p class="mb-1">Cette semaine</p>
                    <p class="mb-0">
                            <span class="text-heading fw-medium me-2">7 derniers jours</span>
                    </p>
                </div>
                </div>
            </div>
        </div>



        <!-- Activités Récentes -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Colis en attente de livraison</h5>
                            <small class="text-muted">Colis prêts à être assignés à un livreur</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical ti-md text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('colis.index', ['status' => 'en_attente']) }}">
                                    <i class="ti ti-clock me-2"></i>Voir tous les colis en attente
                                </a>
                                <a class="dropdown-item" href="{{ route('colis.create') }}">
                                    <i class="ti ti-plus me-2"></i>Créer un nouveau colis
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($recentActivities['derniers_colis']) && count($recentActivities['derniers_colis']) > 0)
                            @foreach($recentActivities['derniers_colis'] as $colis)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar me-3">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ti ti-package"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">
                                            @if($colis)
                                                <a href="{{ route('colis.show', $colis) }}" class="text-decoration-none">
                                                    {{ $colis->code ?? 'N/A' }}
                                                </a>
                                            @else
                                                <span class="text-muted">Colis non trouvé</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            {{ $colis->nom_client ?? 'N/A' }} - {{ $colis->telephone_client ?? 'N/A' }}
                                        </small>
                                        <br>
                                        <small class="text-info">
                                            <i class="ti ti-clock me-1"></i>
                                            @php
                                                $originalLocale = \Carbon\Carbon::getLocale();
                                                \Carbon\Carbon::setLocale('fr');
                                                $timeDiff = $colis->created_at->diffForHumans(null, true, false, 2);
                                                \Carbon\Carbon::setLocale($originalLocale);
                                            @endphp
                                            En attente depuis {{ $timeDiff }}
                                            @php
                                                $daysDiff = floor($colis->created_at->diffInDays());
                                            @endphp
                                            @if($daysDiff > 0)
                                                ({{ $daysDiff }} jour{{ $daysDiff > 1 ? 's' : '' }})
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $statusConfig = match($colis->status) {
                                                \App\Models\Colis::STATUS_EN_ATTENTE => ['label' => 'En attente', 'class' => 'bg-label-warning'],
                                                \App\Models\Colis::STATUS_EN_COURS => ['label' => 'En cours', 'class' => 'bg-label-primary'],
                                                \App\Models\Colis::STATUS_LIVRE => ['label' => 'Livré', 'class' => 'bg-label-success'],
                                                \App\Models\Colis::STATUS_ANNULE_CLIENT => ['label' => 'Annulé client', 'class' => 'bg-label-danger'],
                                                \App\Models\Colis::STATUS_ANNULE_LIVREUR => ['label' => 'Annulé livreur', 'class' => 'bg-label-danger'],
                                                \App\Models\Colis::STATUS_ANNULE_MARCHAND => ['label' => 'Annulé marchand', 'class' => 'bg-label-danger'],
                                                default => ['label' => 'Inconnu', 'class' => 'bg-label-secondary']
                                            };
                                        @endphp
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <span class="badge {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                                            @if($colis->status == \App\Models\Colis::STATUS_EN_ATTENTE)
                                                <a href="{{ route('colis.show', $colis) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-user-plus me-1"></i>Assigner
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-package ti-48px text-muted mb-2"></i>
                                <p class="text-muted">Aucun colis en attente de livraison</p>
                                <small class="text-muted">Tous les colis ont été assignés ou sont en cours de traitement</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Dernières Boutiques</h5>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical ti-md text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('marchands.index') }}">Voir tous</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($recentActivities['dernieres_boutiques']) && count($recentActivities['dernieres_boutiques']) > 0)
                            @foreach($recentActivities['dernieres_boutiques'] as $boutique)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar me-3">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="ti ti-building-store"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-primary">
                                            <a href="{{ route('boutiques.show', $boutique) }}" class="text-decoration-none">
                                                {{ $boutique->libelle }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="ti ti-user me-1"></i>
                                            {{ $boutique->marchand->first_name ?? 'N/A' }} {{ $boutique->marchand->last_name ?? 'N/A' }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="ti ti-clock me-1"></i>
                                            @php
                                                $originalLocale = \Carbon\Carbon::getLocale();
                                                \Carbon\Carbon::setLocale('fr');
                                                $timeDiff = $boutique->created_at->diffForHumans(null, true, false, 2);
                                                \Carbon\Carbon::setLocale($originalLocale);
                                            @endphp
                                            {{ $timeDiff }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $boutique->status === 'actif' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($boutique->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-building-store ti-48px text-muted mb-2"></i>
                                <p class="text-muted">Aucune boutique récente</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

            <!--/ Card Border Shadow -->

        <div class="row g-4 mb-4">
            <!-- Delivery Performance -->
            <div class="col-xxl-6 col-lg-5">
                <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                    <h5 class="mb-1">Performances de livraison</h5>
                    <p class="card-subtitle">{{ $recentActivities['performance_data']['packages_delivered']['variation'] ?? 0 }}% d'évolution ce mois-ci</p>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities['performance_data']) && !empty($recentActivities['performance_data']))
                    <ul class="p-0 m-0">
                    <li class="d-flex mb-6">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-primary"
                            ><i class="ti ti-package ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Colis en cours de livraison</h6>
                            <small class="text-{{ $recentActivities['performance_data']['packages_in_transit']['variation_type'] ?? 'secondary' }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ ($recentActivities['performance_data']['packages_in_transit']['variation'] ?? 0) >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['packages_in_transit']['variation'] ?? 0) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ number_format($recentActivities['performance_data']['packages_in_transit']['count'] ?? 0) }}</h6>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex mb-6">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-info"
                            ><i class="ti ti-truck ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Colis en attente de livraison</h6>
                            <small class="text-{{ $recentActivities['performance_data']['packages_out_for_delivery']['variation_type'] }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ $recentActivities['performance_data']['packages_out_for_delivery']['variation'] >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['packages_out_for_delivery']['variation']) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ number_format($recentActivities['performance_data']['packages_out_for_delivery']['count']) }}</h6>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex mb-6">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-success"
                            ><i class="ti ti-circle-check ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Colis livrés</h6>
                            <small class="text-{{ $recentActivities['performance_data']['packages_delivered']['variation_type'] }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ $recentActivities['performance_data']['packages_delivered']['variation'] >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['packages_delivered']['variation']) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ number_format($recentActivities['performance_data']['packages_delivered']['count']) }}</h6>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex mb-6">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-warning"
                            ><i class="ti ti-percentage ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Taux de succès de livraison</h6>
                            <small class="text-{{ $recentActivities['performance_data']['delivery_success_rate']['variation_type'] }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ $recentActivities['performance_data']['delivery_success_rate']['variation'] >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['delivery_success_rate']['variation']) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ $recentActivities['performance_data']['delivery_success_rate']['rate'] }}%</h6>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex mb-6">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-secondary"
                            ><i class="ti ti-clock ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Temps moyen de livraison</h6>
                            <small class="text-{{ $recentActivities['performance_data']['average_delivery_time']['variation_type'] }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ $recentActivities['performance_data']['average_delivery_time']['variation'] >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['average_delivery_time']['variation']) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ $recentActivities['performance_data']['average_delivery_time']['days'] }} Jours</h6>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex">
                        <div class="avatar flex-shrink-0 me-4">
                        <span class="avatar-initial rounded bg-label-danger"
                            ><i class="ti ti-users ti-26px"></i
                        ></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 fw-normal">Satisfaction client</h6>
                            <small class="text-{{ $recentActivities['performance_data']['customer_satisfaction']['variation_type'] }} fw-normal d-block">
                            <i class="ti ti-chevron-{{ $recentActivities['performance_data']['customer_satisfaction']['variation'] >= 0 ? 'up' : 'down' }} mb-1 me-1"></i>
                            {{ abs($recentActivities['performance_data']['customer_satisfaction']['variation']) }}%
                            </small>
                        </div>
                        <div class="user-progress">
                            <h6 class="text-body mb-0">{{ number_format($recentActivities['performance_data']['customer_satisfaction']['rating'], 1) }}/5</h6>
                        </div>
                        </div>
                    </li>
                    </ul>
                    @else
                    <div class="text-center py-4">
                        <i class="ti ti-chart-line ti-48px text-muted mb-2"></i>
                        <p class="text-muted">Aucune donnée de performance disponible</p>
                    </div>
                    @endif
                </div>
                </div>
            </div>
        <!-- Shipment statistics-->
        <div class="col-xxl-6 col-lg-7">
            <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                <h5 class="mb-1">Statistiques d'expédition</h5>
                    <p class="card-subtitle">Nombre total de livraisons {{ array_sum($chartData['delivery_data']) }}</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-label-primary" id="currentMonthBtn">{{ now()->format('F') }}</button>
                <button
                    type="button"
                    class="btn btn-label-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(1)">Janvier</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(2)">Février</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(3)">Mars</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(4)">Avril</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(5)">Mai</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(6)">Juin</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(7)">Juillet</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(8)">Août</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(9)">Septembre</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(10)">Octobre</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(11)">Novembre</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="changeMonth(12)">Décembre</a></li>
                </ul>
                </div>
                    <button type="button" class="btn btn-outline-primary btn-sm ms-2" onclick="refreshChart()" title="Actualiser le graphique">
                        <i class="ti ti-refresh"></i>
                    </button>
            </div>
            <div class="card-body">
                    <canvas id="shipmentStatisticsChart" style="height: 300px;"></canvas>
            </div>
            </div>
        </div>
        <!--/ Shipment statistics -->
        </div>

        <!-- Ramassages Section -->
        <div class="col-12 order-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Ramassages en cours</h5>
                        <small class="text-muted">Ramassages en attente, planifiés, en cours et annulés</small>
                        </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Sélecteur du nombre d'éléments par page -->
                        <!-- Sélecteur de pagination supprimé - méthode traditionnelle utilisée -->
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="ramassagesDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ramassagesDropdown">
                                <a class="dropdown-item" href="{{ route('ramassages.index', ['statut' => 'en_cours']) }}">
                                    <i class="ti ti-clock me-2"></i>Voir tous les ramassages en cours
                                </a>
                                <a class="dropdown-item" href="{{ route('ramassages.index', ['statut' => 'demande']) }}">
                                    <i class="ti ti-alert-circle me-2"></i>Ramassages en attente
                                </a>
                                <a class="dropdown-item" href="{{ route('ramassages.create') }}">
                                    <i class="ti ti-plus me-2"></i>Nouveau ramassage
                                </a>
                        </div>
                            </div>
                        </div>
                    </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="ramassages-table" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Marchand</th>
                                    <th>Boutique</th>
                                    <th>Date Demande</th>
                                    <th>Statut</th>
                                    <th>Colis</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($ramassages) && $ramassages->count() > 0)
                                    @foreach($ramassages as $ramassage)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="ti ti-package"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $ramassage->code_ramassage }}</h6>
                                                        <small class="text-muted">ID: {{ $ramassage->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="ti ti-user"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $ramassage->marchand->first_name ?? 'N/A' }} {{ $ramassage->marchand->last_name ?? '' }}</h6>
                                                        <small class="text-muted">{{ $ramassage->marchand->mobile ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded bg-label-warning">
                                                            <i class="ti ti-building-store"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $ramassage->boutique->libelle ?? 'N/A' }}</h6>
                                                        <small class="text-muted">{{ $ramassage->boutique->mobile ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ \Carbon\Carbon::parse($ramassage->date_demande)->format('d/m/Y') }}</h6>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($ramassage->date_demande)->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statutLabels = [
                                                        'demande' => ['label' => 'Demande', 'class' => 'bg-label-warning'],
                                                        'planifie' => ['label' => 'Planifié', 'class' => 'bg-label-info'],
                                                        'en_cours' => ['label' => 'En cours', 'class' => 'bg-label-primary'],
                                                        'termine' => ['label' => 'Terminé', 'class' => 'bg-label-success'],
                                                        'annule' => ['label' => 'Annulé', 'class' => 'bg-label-danger']
                                                    ];
                                                    $statut = $statutLabels[$ramassage->statut] ?? ['label' => $ramassage->statut, 'class' => 'bg-label-secondary'];
                                                @endphp
                                                <span class="badge {{ $statut['class'] }}">{{ $statut['label'] }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="badge bg-label-primary me-1">{{ $ramassage->nombre_colis_reel ?? 0 }}</span>
                                                        <small class="text-muted">colis</small>
                                                    </div>
                                                    @if($ramassage->statut === 'demande')
                                                        <small class="text-warning">
                                                            <i class="ti ti-clock me-1"></i>
                                                            @php
                                                                $originalLocale = \Carbon\Carbon::getLocale();
                                                                \Carbon\Carbon::setLocale('fr');
                                                                $timeDiff = \Carbon\Carbon::parse($ramassage->created_at)->diffForHumans(null, true, false, 2);
                                                                \Carbon\Carbon::setLocale($originalLocale);
                                                            @endphp
                                                            En attente depuis {{ $timeDiff }}
                                                        </small>
                                                    @elseif($ramassage->statut === 'planifie')
                                                        <small class="text-info">
                                                            <i class="ti ti-calendar me-1"></i>
                                                            Planifié pour {{ \Carbon\Carbon::parse($ramassage->date_planifiee ?? $ramassage->created_at)->format('d/m/Y') }}
                                                        </small>
                                                    @elseif($ramassage->statut === 'en_cours')
                                                        <small class="text-primary">
                                                            <i class="ti ti-truck me-1"></i>
                                                            @php
                                                                $originalLocale = \Carbon\Carbon::getLocale();
                                                                \Carbon\Carbon::setLocale('fr');
                                                                $timeDiff = \Carbon\Carbon::parse($ramassage->updated_at)->diffForHumans(null, true, false, 2);
                                                                \Carbon\Carbon::setLocale($originalLocale);
                                                            @endphp
                                                            En cours depuis {{ $timeDiff }}
                                                        </small>
                                                    @elseif($ramassage->statut === 'annule')
                                                        <small class="text-danger">
                                                            <i class="ti ti-x me-1"></i>
                                                            Annulé le {{ \Carbon\Carbon::parse($ramassage->updated_at)->format('d/m/Y H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('ramassages.show', $ramassage->id) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        @if($ramassage->statut === 'demande')
                                                            <a class="dropdown-item" href="{{ route('ramassages.planifier', $ramassage->id) }}">
                                                                <i class="ti ti-calendar me-1"></i> Planifier
                                                            </a>
                                                        @elseif($ramassage->statut === 'planifie')
                                                            <a class="dropdown-item" href="{{ route('ramassages.show', $ramassage->id) }}">
                                                                <i class="ti ti-play me-1"></i> Démarrer
                                                            </a>
                                                        @elseif($ramassage->statut === 'en_cours')
                                                            <a class="dropdown-item" href="{{ route('ramassages.show', $ramassage->id) }}">
                                                                <i class="ti ti-check me-1"></i> Finaliser
                                                            </a>
                                                        @elseif($ramassage->statut === 'annule')
                                                            <a class="dropdown-item" href="{{ route('ramassages.show', $ramassage->id) }}">
                                                                <i class="ti ti-refresh me-1"></i> Réactiver
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="ti ti-package-off ti-48px text-muted mb-2"></i>
                                            <p class="text-muted">Aucun ramassage en cours</p>
                                            <small class="text-muted">Tous les ramassages sont terminés</small>
                                            <br><br>
                                            <a href="{{ route('ramassages.create') }}" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>
                                                Créer un ramassage
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                </div>
                </div>
            </div>
        </div>
        <!--/ Ramassages Section -->

        <!-- On route vehicles Table -->
        <div class="col-12 order-5 mt-4">
            <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between" >
                <div class="card-title mb-0">
                <h5 class="m-0 me-2">Les colis en cours de livraison</h5>
                </div>
                <div class="d-flex align-items-center gap-2" >
                    <!-- Sélecteur du nombre d'éléments par page -->
                    <div class="d-flex align-items-center">
                        <label for="perPageSelect" class="form-label mb-0 me-2 text-muted small">Afficher:</label>
                        <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                            <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 5) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 5) == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                <div class="dropdown">
                <button
                    class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1"
                    type="button"
                    id="routeVehicles"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">
                    <i class="ti ti-dots-vertical ti-md text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="routeVehicles">
                    <a class="dropdown-item" href="javascript:void(0);">Aujourd'hui</a>
                    <a class="dropdown-item" href="javascript:void(0);">Cette semaine</a>
                    <a class="dropdown-item" href="javascript:void(0);">Le mois en cours</a>
                </div>
                </div>
            </div>
            </div>
            <div class="table-responsive">
                <table id="colis-en-cours-table" class="table table-sm">
                <thead>
                    <tr>
                    <th>Code Colis</th>
                    <th>Client</th>
                    <th>Livreur</th>
                    <th>Adresse de ramassage</th>
                    <th>Adresse de livraison</th>
                    <th>Statut</th>
                    <th class="w-20">Prix</th>
                    <th class="w-20">Date de création</th>
                    <th class="w-20">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($recentActivities['colis_en_cours_data']) > 0)
                        @foreach($recentActivities['colis_en_cours_data'] as $livraison)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-package"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">
                                                @if($livraison->colis)
                                                    <a href="{{ route('colis.show', $livraison->colis) }}" class="text-decoration-none">
                                                        {{ $livraison->colis->code }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Colis non trouvé</span>
                                                @endif
                                            </h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $livraison->colis->nom_client ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $livraison->colis->commune_zone->telephone_client ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="ti ti-user"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $livraison->livreur->first_name ?? 'N/A' }} {{ $livraison->livreur->last_name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $livraison->livreur->mobile ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $livraison->colis->commune_zone->boutique->adresse ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $livraison->colis->commune_zone->boutique->libelle ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $livraison->colis->commune_zone->adresse_de_ramassage ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $livraison->colis->zone->libelle ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="ti ti-clock me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $livraison->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0 text-success">{{ number_format($livraison->prix_de_vente ?? 0, 0, ',', ' ') }} FCFA</h6>
                                        <small class="text-muted">À encaisser: {{ number_format($livraison->montant_a_encaisse ?? 0, 0, ',', ' ') }} FCFA</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $livraison->created_at->format('d/m/Y') }}</h6>
                                        <small class="text-muted">{{ $livraison->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($livraison->colis)
                                            <a href="{{ route('colis.show', $livraison->colis) }}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @else
                                            <span class="btn btn-sm btn-outline-secondary disabled" title="Colis non trouvé">
                                                <i class="ti ti-eye-off"></i>
                                            </span>
                                        @endif
                                        @if($livraison->colis)
                                            <a href="{{ route('colis.edit', $livraison->colis) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @else
                                            <span class="btn btn-sm btn-outline-secondary disabled" title="Colis non trouvé">
                                                <i class="ti ti-edit-off"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="ti ti-package ti-48px text-muted mb-2"></i>
                                <p class="text-muted">Aucun colis en cours de livraison</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
                </table>
            </div>

            <!-- Pagination pour les colis en cours -->
            <div id="pagination-container" class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3 border-top" style="display: none !important;">
                <div class="text-muted small">
                    <i class="ti ti-info-circle me-1"></i>
                    <span id="pagination-info">Affichage de 1 à 10 sur 12 résultats</span>
                </div>
                <div class="pagination-custom" id="pagination-links">
                    <!-- Les liens de pagination seront générés par JavaScript -->
                </div>
            </div>
            </div>
        </div>
        <!--/ On route vehicles Table -->
        </div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Variable globale pour le graphique
let shipmentChart = null;
let currentMonth = {{ now()->month }};

// Noms des mois en français
const monthNames = {
    1: 'Janvier', 2: 'Février', 3: 'Mars', 4: 'Avril',
    5: 'Mai', 6: 'Juin', 7: 'Juillet', 8: 'Août',
    9: 'Septembre', 10: 'Octobre', 11: 'Novembre', 12: 'Décembre'
};

// Fonction pour changer le mois
async function changeMonth(month) {
    currentMonth = month;

    // Mettre à jour le bouton
    const monthBtn = document.getElementById('currentMonthBtn');
    if (monthBtn) {
        monthBtn.textContent = monthNames[month];
    }

    // Afficher un indicateur de chargement
    const refreshBtn = document.querySelector('button[onclick="refreshChart()"]');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i>';
    refreshBtn.disabled = true;

    try {
        // Pour l'instant, simuler des données pour le mois sélectionné
        // TODO: Implémenter la récupération des vraies données via AJAX
        const simulatedData = generateSimulatedDataForMonth(month);

        if (shipmentChart) {
            // Mettre à jour les données du graphique
            shipmentChart.data.labels = simulatedData.labels;
            shipmentChart.data.datasets[0].data = simulatedData.shipment_data;
            shipmentChart.data.datasets[1].data = simulatedData.delivery_data;

            // Mettre à jour l'échelle Y
            const maxValue = Math.max(...simulatedData.shipment_data, ...simulatedData.delivery_data);
            shipmentChart.options.scales.y.max = maxValue * 1.2;
            shipmentChart.options.scales.y.ticks.stepSize = Math.ceil(maxValue / 4);

            // Mettre à jour le sous-titre
            const subtitle = document.querySelector('.card-subtitle');
            if (subtitle) {
                subtitle.textContent = `Nombre total de livraisons ${simulatedData.total_deliveries}`;
            }

            // Redessiner le graphique avec animation
            shipmentChart.update('active');

            console.log(`Graphique mis à jour pour ${monthNames[month]} (données simulées)`);
        }
    } catch (error) {
        console.error('Erreur lors du changement de mois:', error);

        // Afficher une notification d'erreur
        const notification = document.createElement('div');
        notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="ti ti-alert-circle me-2"></i>
            Erreur lors de la mise à jour du graphique
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);

        // Supprimer la notification après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    } finally {
        // Restaurer le bouton
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    }
}

// Fonction pour générer des données pour un mois (réelles ou simulées)
function generateSimulatedDataForMonth(month) {
    const daysInMonth = new Date(2025, month, 0).getDate();
    const labels = [];
    const shipmentData = [];
    const deliveryData = [];

    // Données réelles basées sur les colis existants
    const realData = {
        9: { // Septembre
            29: { shipments: 1, deliveries: 1 }, // CLIS-000001 créé le 29/09, statut 2 (livré)
            30: { shipments: 4, deliveries: 3 }  // CLIS-000002, 000003, 000004, 000005 créés le 30/09, 3 avec statut 2 (livrés)
        },
        10: { // Octobre
            1: { shipments: 4, deliveries: 2 }   // CLIS-000006, 000007, 000008, 000009 créés le 01/10, 2 avec statut 2 (livrés)
        }
    };

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(2025, month - 1, day);
        labels.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }));

        // Vérifier s'il y a des données réelles pour ce jour
        if (realData[month] && realData[month][day]) {
            shipmentData.push(realData[month][day].shipments);
            deliveryData.push(realData[month][day].deliveries);
        } else {
            // Pas de données réelles pour ce jour/mois
            shipmentData.push(0);
            deliveryData.push(0);
        }
    }

    return {
        labels: labels,
        shipment_data: shipmentData,
        delivery_data: deliveryData,
        total_shipments: shipmentData.reduce((a, b) => a + b, 0),
        total_deliveries: deliveryData.reduce((a, b) => a + b, 0)
    };
}

// Fonction pour rafraîchir le graphique
async function refreshChart() {
    // Afficher un indicateur de chargement
    const refreshBtn = document.querySelector('button[onclick="refreshChart()"]');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i>';
    refreshBtn.disabled = true;

    try {
        // Récupérer les nouvelles données via AJAX
        const response = await fetch('/api/chart-data');
        const result = await response.json();

        if (result.success && shipmentChart) {
            // Mettre à jour les données du graphique
            shipmentChart.data.labels = result.data.labels;
            shipmentChart.data.datasets[0].data = result.data.shipment_data;
            shipmentChart.data.datasets[1].data = result.data.delivery_data;

            // Mettre à jour l'échelle Y
            const maxValue = Math.max(...result.data.shipment_data, ...result.data.delivery_data);
            shipmentChart.options.scales.y.max = maxValue * 1.2;
            shipmentChart.options.scales.y.ticks.stepSize = Math.ceil(maxValue / 4);

            // Mettre à jour le sous-titre
            const subtitle = document.querySelector('.card-subtitle');
            if (subtitle) {
                subtitle.textContent = `Nombre total de livraisons ${result.data.total_deliveries}`;
            }

            // Redessiner le graphique avec animation
            shipmentChart.update('active');

            // Afficher une notification de succès
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="ti ti-check-circle me-2"></i>
                Graphique mis à jour avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            // Supprimer la notification après 3 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);

            console.log('Graphique mis à jour avec succès');
        }
    } catch (error) {
        console.error('Erreur lors de la mise à jour du graphique:', error);
        // En cas d'erreur, recharger la page
        window.location.reload();
    } finally {
        // Restaurer le bouton
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    }
}

// Fonction pour initialiser le graphique
function initializeChart() {
    // Vérifier que Chart.js est chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé, nouvelle tentative dans 100ms...');
        setTimeout(initializeChart, 100);
        return;
    }

    // Données du graphique d'expédition
    const shipmentLabels = @json($chartData['shipment_labels']);
    const shipmentData = @json($chartData['shipment_data']);
    const deliveryData = @json($chartData['delivery_data']);

    // Configuration du graphique
    const ctx = document.getElementById('shipmentStatisticsChart').getContext('2d');

    shipmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: shipmentLabels,
            datasets: [
                {
                    label: 'Shipment',
                    data: shipmentData,
                    backgroundColor: '#ff9f43',
                    borderColor: '#ff9f43',
                    borderWidth: 1,
                    type: 'bar'
                },
                {
                    label: 'Delivery',
                    data: deliveryData,
                    backgroundColor: 'transparent',
                    borderColor: '#7367f0',
                    borderWidth: 2,
                    pointBackgroundColor: '#7367f0',
                    pointBorderColor: '#7367f0',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    type: 'line',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(...shipmentData, ...deliveryData) * 1.2,
                    ticks: {
                        stepSize: Math.ceil(Math.max(...shipmentData, ...deliveryData) / 4),
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: '#e7eaf3',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}


// Initialiser les graphiques quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard JavaScript chargé');
    initializeChart();
    initializeExceptionsChart();

    // Initialiser la pagination AJAX si elle n'est pas déjà chargée
    const colisTableRow = document.querySelector('#colis-en-cours-table tbody tr');
    if (!colisTableRow || colisTableRow.textContent.includes('Chargement')) {
        // Charger les données initiales via AJAX
        loadColisEnCours({{ request('page', 1) }});
    }

    // Les ramassages sont maintenant chargés directement via Blade
    console.log('📋 Ramassages chargés via méthode traditionnelle');
});

// Variables globales pour la pagination AJAX
let currentPage = 1;
let currentPerPage = {{ request('per_page', 5) }};
let isLoading = false;

// Variables globales pour la pagination des ramassages (supprimées - méthode traditionnelle utilisée)

// Fonction pour changer le nombre d'éléments par page
function changePerPage(perPage) {
    currentPerPage = perPage;
    currentPage = 1; // Reset à la première page
    loadColisEnCours();
}

// Fonction pour charger les colis en cours via AJAX
function loadColisEnCours(page = 1) {
    if (isLoading) return;

    isLoading = true;
    currentPage = page;

    // Afficher un indicateur de chargement
    showLoadingIndicator();

    const url = new URL('/api/dashboard/colis-en-cours', window.location.origin);
    url.searchParams.set('per_page', currentPerPage);
    url.searchParams.set('page', currentPage);

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTable(data.data.items);
            updatePagination(data.data.pagination);
        } else {
            showError('Erreur lors du chargement des données');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError('Erreur lors du chargement des données');
    })
    .finally(() => {
        isLoading = false;
        hideLoadingIndicator();
    });
}

// Fonction pour mettre à jour le tableau
function updateTable(items) {
    const tbody = document.querySelector('#colis-en-cours-table tbody');

    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <i class="ti ti-package ti-48px text-muted mb-2"></i>
                    <p class="text-muted">Aucun colis en cours de livraison</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    items.forEach(livraison => {
        const colis = livraison.colis;
        const livreur = livraison.livreur;
        const communeZone = colis?.commune_zone;
        const commune = communeZone?.commune;
        const marchand = communeZone?.marchand;

        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-package"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                ${colis ?
                                    `<a href="/colis/${colis.id}" class="text-decoration-none">${colis.code}</a>` :
                                    '<span class="text-muted">Colis non trouvé</span>'
                                }
                            </h6>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0">${colis?.nom_client || 'N/A'}</h6>
                        <small class="text-muted">${colis?.telephone_client || 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-user"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">${livreur?.first_name || 'N/A'} ${livreur?.last_name || 'N/A'}</h6>
                            <small class="text-muted">${livreur?.phone || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0">${communeZone?.adresse_de_ramassage || 'N/A'}</h6>
                        <small class="text-muted">${commune?.nom || 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0">${colis?.adresse_destinataire || 'N/A'}</h6>
                        <small class="text-muted">${commune?.nom || 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0">${marchand?.nom || 'N/A'}</h6>
                        <small class="text-muted">${marchand?.telephone || 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-warning">
                        <i class="ti ti-clock me-1"></i>
                        ${livraison.status ? livraison.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'En attente'}
                    </span>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0 text-success">${formatNumber(livraison.prix_de_vente || 0)} FCFA</h6>
                        <small class="text-muted">À encaisser: ${formatNumber(livraison.montant_a_encaisse || 0)} FCFA</small>
                    </div>
                </td>
                <td>
                    <div>
                        <h6 class="mb-0">${formatDate(livraison.created_at)}</h6>
                        <small class="text-muted">${formatTime(livraison.created_at)}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        ${colis ?
                            `<a href="/colis/${colis.id}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="/colis/${colis.id}/edit" class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="ti ti-edit"></i>
                            </a>` :
                            `<span class="btn btn-sm btn-outline-secondary disabled" title="Colis non trouvé">
                                <i class="ti ti-eye-off"></i>
                            </span>
                            <span class="btn btn-sm btn-outline-secondary disabled" title="Colis non trouvé">
                                <i class="ti ti-edit-off"></i>
                            </span>`
                        }
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// Fonction pour mettre à jour la pagination
function updatePagination(pagination) {
    const container = document.getElementById('pagination-container');
    const info = document.getElementById('pagination-info');
    const links = document.getElementById('pagination-links');

    // Mettre à jour les informations
    info.textContent = `Affichage de ${pagination.from || 0} à ${pagination.to || 0} sur ${pagination.total} résultats`;

    // Générer les liens de pagination
    if (pagination.has_pages) {
        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';

        // Bouton Précédent
        if (pagination.current_page > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadColisEnCours(${pagination.current_page - 1})">
                    <i class="ti ti-chevron-left"></i>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link"><i class="ti ti-chevron-left"></i></span>
            </li>`;
        }

        // Numéros de pages
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="loadColisEnCours(${i})">${i}</a>
                </li>`;
            }
        }

        // Bouton Suivant
        if (pagination.has_more_pages) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadColisEnCours(${pagination.current_page + 1})">
                    <i class="ti ti-chevron-right"></i>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link"><i class="ti ti-chevron-right"></i></span>
            </li>`;
        }

        paginationHtml += '</ul></nav>';
        links.innerHTML = paginationHtml;
        container.style.display = 'flex';
    } else {
        container.style.display = 'none';
    }
}

// Fonctions utilitaires
function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function showLoadingIndicator() {
    const tbody = document.querySelector('#colis-en-cours-table tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2">Chargement des données...</p>
            </td>
        </tr>
    `;
}

function hideLoadingIndicator() {
    // L'indicateur sera remplacé par les données
}

function showError(message) {
    const tbody = document.querySelector('#colis-en-cours-table tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <i class="ti ti-alert-circle ti-48px text-danger mb-2"></i>
                <p class="text-danger">${message}</p>
                <button class="btn btn-sm btn-outline-primary" onclick="loadColisEnCours()">
                    <i class="ti ti-refresh me-1"></i>
                    Réessayer
                </button>
            </td>
        </tr>
    `;
}

// ===== FONCTIONS POUR LA PAGINATION DES RAMASSAGES =====

// Fonction pour changer le nombre d'éléments par page pour les ramassages (supprimée - méthode traditionnelle utilisée)

// Fonction pour charger les ramassages via AJAX (supprimée - méthode traditionnelle utilisée)

// Fonction pour mettre à jour le tableau des ramassages
function updateRamassagesTable(items) {
    console.log('🔄 Mise à jour du tableau des ramassages avec', items.length, 'éléments');
    console.log('📋 Données des ramassages:', items);

    const tbody = document.querySelector('#ramassages-table tbody');

    if (items.length === 0) {
        console.log('⚠️ Aucun ramassage à afficher');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="ti ti-package-off ti-48px text-muted mb-2"></i>
                    <p class="text-muted">Aucun ramassage récent</p>
                    <a href="/ramassages/create" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Créer un ramassage
                    </a>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    items.forEach(ramassage => {
        const marchand = ramassage.marchand;
        const boutique = ramassage.boutique;

        html += `
            <tr>
                <td>
                    <span class="fw-semibold">${ramassage.code_ramassage}</span>
                </td>
                <td>${marchand?.first_name || 'N/A'} ${marchand?.last_name || 'N/A'}</td>
                <td>${boutique?.libelle || 'N/A'}</td>
                <td>${formatDate(ramassage.date_demande)}</td>
                <td>
                    <span class="badge bg-${getStatutColor(ramassage.statut)}">
                        ${getStatutLabel(ramassage.statut)}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info">${ramassage.nombre_colis_estime}</span>
                </td>
                <td>
                    <a href="/ramassages/${ramassage.id}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    console.log('✅ Tableau des ramassages mis à jour avec', items.length, 'lignes');
}

// Fonction pour mettre à jour la pagination des ramassages
function updateRamassagesPagination(pagination) {
    const container = document.getElementById('pagination-ramassages-container');
    const info = document.getElementById('pagination-ramassages-info');
    const links = document.getElementById('pagination-ramassages-links');

    // Mettre à jour les informations
    info.textContent = `Affichage de ${pagination.from || 0} à ${pagination.to || 0} sur ${pagination.total} résultats`;

    // Générer les liens de pagination
    if (pagination.has_pages) {
        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';

        // Bouton Précédent
        if (pagination.current_page > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadRamassages(${pagination.current_page - 1})">
                    <i class="ti ti-chevron-left"></i>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link"><i class="ti ti-chevron-left"></i></span>
            </li>`;
        }

        // Numéros de pages
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="loadRamassages(${i})">${i}</a>
                </li>`;
            }
        }

        // Bouton Suivant
        if (pagination.has_more_pages) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadRamassages(${pagination.current_page + 1})">
                    <i class="ti ti-chevron-right"></i>
                </a>
            </li>`;
        } else {
            paginationHtml += `<li class="page-item disabled">
                <span class="page-link"><i class="ti ti-chevron-right"></i></span>
            </li>`;
        }

        paginationHtml += '</ul></nav>';
        links.innerHTML = paginationHtml;
        container.style.display = 'flex';
    } else {
        container.style.display = 'none';
    }
}

// Fonctions utilitaires pour les ramassages
function getStatutColor(statut) {
    const colors = {
        'demande': 'warning',
        'planifie': 'info',
        'en_cours': 'primary',
        'termine': 'success',
        'annule': 'danger'
    };
    return colors[statut] || 'secondary';
}

function getStatutLabel(statut) {
    const labels = {
        'demande': 'Demande',
        'planifie': 'Planifié',
        'en_cours': 'En cours',
        'termine': 'Terminé',
        'annule': 'Annulé'
    };
    return labels[statut] || statut;
}

function showLoadingIndicatorRamassages() {
    const tbody = document.querySelector('#ramassages-table tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2">Chargement des données...</p>
            </td>
        </tr>
    `;
}

function hideLoadingIndicatorRamassages() {
    // L'indicateur sera remplacé par les données
}

function showErrorRamassages(message) {
    const tbody = document.querySelector('#ramassages-table tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <i class="ti ti-alert-circle ti-48px text-danger mb-2"></i>
                <p class="text-danger">${message}</p>
                <button class="btn btn-sm btn-outline-primary" onclick="loadRamassages()">
                    <i class="ti ti-refresh me-1"></i>
                    Réessayer
                </button>
            </td>
        </tr>
    `;
}
</script>

@include('layouts.footer')
