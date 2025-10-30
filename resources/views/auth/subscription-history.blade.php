@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Historique d'Abonnement</h5>
                        <p class="mb-4">Consultez l'historique de vos abonnements et factures</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('auth.pricing') }}" class="btn btn-outline-primary">
                                <i class="ti ti-crown me-1"></i>
                                Voir les Forfaits
                            </a>
                            <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Abonnement actuel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-crown me-2"></i>Abonnement Actuel
                </h5>
            </div>
            <div class="card-body">
                @php
                    $planData = $current_subscription['plan'] ?? null;
                    $subsData = $current_subscription['subscription'] ?? null;
                @endphp
                @if(!empty($subsData) && !empty($subsData['has_active_subscription']))
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading d-flex align-items-center">
                            <i class="ti ti-check ti-sm me-2"></i>Abonnement Actif
                        </h4>
                        <p class="mb-0">Votre entreprise a un abonnement actif.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Plan d'Abonnement</h6>
                            <p><strong>{{ $planData['name'] ?? 'Aucun plan assigné' }}</strong></p>
                            <p class="text-muted">{{ $planData['description'] ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Détails</h6>
                            <p><strong>Prix:</strong> {{ $planData['price'] ?? 'N/A' }} {{ $planData['currency'] ?? '' }}</p>
                            <p><strong>Durée totale:</strong> {{ $subsData['real_duration_days'] ?? 'N/A' }} jours</p>
                            <p><strong>Jours restants:</strong> {{ $subsData['remaining_days'] ?? 'N/A' }} jours</p>
                            @if(!empty($subsData['expires_at']))
                                <p><strong>Expire le:</strong> {{ $subsData['expires_at']->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    @php
                        $planFeatures = [];
                        if (!empty($planData['features'])) {
                            $planFeatures = is_array($planData['features']) ? $planData['features'] : json_decode($planData['features'], true) ?? [];
                        }
                    @endphp
                    @if(!empty($planFeatures) && count($planFeatures) > 0)
                        <h6 class="mt-4">Fonctionnalités Incluses</h6>
                        <div class="row">
                            @foreach($planFeatures as $feature)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span>{{ $feature }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning" role="alert">
                        <h4 class="alert-heading d-flex align-items-center">
                            <i class="ti ti-alert-triangle ti-sm me-2"></i>Aucun Abonnement Actif
                        </h4>
                        <p class="mb-0">Votre entreprise n'a actuellement aucun abonnement actif.</p>
                        <a href="{{ route('auth.pricing') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="ti ti-crown me-1"></i>Voir les Forfaits
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Historique des abonnements -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-history me-2"></i>Historique des Abonnements
                </h5>
            </div>
            <div class="card-body">
                @if($subscriptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Plan</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Fonctionnalités</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">#{{ $subscription->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="ti ti-crown"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $subscription->name }}</h6>
                                                    @if($subscription->description)
                                                        <small class="text-muted">{{ $subscription->description }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <h6 class="mb-0 text-primary">{{ number_format($subscription->price, 0, ',', ' ') }} {{ $subscription->currency }}</h6>
                                                <small class="text-muted">{{ $subscription->getRealDurationDays() }} jours</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $subscription->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $subscription->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                @if($subscription->started_at)
                                                    <span class="fw-semibold">{{ $subscription->started_at->format('d/m/Y') }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $subscription->started_at->format('H:i') }}</small>
                                                @else
                                                    <span class="fw-semibold">-</span>
                                                    <br>
                                                    <small class="text-muted">Non défini</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                @if($subscription->expires_at)
                                                    <span class="fw-semibold">{{ $subscription->expires_at->format('d/m/Y') }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $subscription->expires_at->format('H:i') }}</small>
                                                @else
                                                    <span class="fw-semibold">-</span>
                                                    <br>
                                                    <small class="text-muted">Non défini</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($subscription->pricingPlan && $subscription->pricingPlan->features)
                                                @php
                                                    $features = is_array($subscription->pricingPlan->features)
                                                        ? $subscription->pricingPlan->features
                                                        : json_decode($subscription->pricingPlan->features, true);
                                                @endphp
                                                @if($features && count($features) > 0)
                                                    <div class="features-container" data-subscription-id="{{ $subscription->id }}">
                                                        <!-- Affichage des 3 premières fonctionnalités -->
                                                        <div class="d-flex flex-wrap gap-1 features-visible">
                                                            @foreach($features as $index => $feature)
                                                                @if($index < 3)
                                                                    <span class="badge bg-label-info">{{ $feature }}</span>
                                                                @endif
                                                            @endforeach
                                                        </div>

                                                        <!-- Fonctionnalités cachées -->
                                                        @if(count($features) > 3)
                                                            <div class="features-hidden d-none">
                                                                @foreach($features as $index => $feature)
                                                                    @if($index >= 3)
                                                                        <span class="badge bg-label-info">{{ $feature }}</span>
                                                                    @endif
                                                                @endforeach
                                                            </div>

                                                            <!-- Bouton Voir plus/moins -->
                                                            <div class="mt-1">
                                                                <button type="button" class="btn btn-sm btn-outline-primary toggle-features"
                                                                        data-subscription-id="{{ $subscription->id }}">
                                                                    <i class="ti ti-chevron-down me-1"></i>
                                                                    Voir plus (+{{ count($features) - 3 }})
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">
                                                        <i class="ti ti-download me-1"></i>
                                                        Télécharger Facture
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="ti ti-eye me-1"></i>
                                                        Voir Détails
                                                    </a>
                                                    @if($subscription->is_active)
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-muted" href="#" disabled>
                                                            <i class="ti ti-shield-check me-1"></i>
                                                            Abonnement Actif
                                                        </a>
                                                    @else
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-success" href="#" onclick="activateSubscription({{ $subscription->id }})">
                                                            <i class="ti ti-check me-1"></i>
                                                            Activer
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-receipt ti-lg"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">Aucun abonnement trouvé</h5>
                        <p class="text-muted mb-4">Vous n'avez pas encore d'abonnement actif.</p>
                        <a href="{{ route('auth.pricing') }}" class="btn btn-primary">
                            <i class="ti ti-crown me-1"></i>
                            Choisir un Forfait
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@include('layouts.footer')

<style>
.features-container {
    max-width: 300px;
}

.features-visible, .features-hidden {
    transition: all 0.3s ease;
}

.toggle-features {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.toggle-features:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge.bg-label-info {
    font-size: 0.7rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}
</style>

<script>
function activateSubscription(subscriptionId) {
    if (confirm('Êtes-vous sûr de vouloir activer cet abonnement ? Cela désactivera automatiquement votre abonnement actuel.')) {
        fetch(`/subscriptions/${subscriptionId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        });
    }
}


// Gestion du toggle des fonctionnalités
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les event listeners pour les boutons toggle
    document.querySelectorAll('.toggle-features').forEach(button => {
        button.addEventListener('click', function() {
            const subscriptionId = this.getAttribute('data-subscription-id');
            const container = document.querySelector(`[data-subscription-id="${subscriptionId}"]`);
            const hiddenFeatures = container.querySelector('.features-hidden');
            const button = container.querySelector('.toggle-features');
            const icon = button.querySelector('i');

            if (hiddenFeatures.classList.contains('d-none')) {
                // Afficher les fonctionnalités cachées
                hiddenFeatures.classList.remove('d-none');
                icon.className = 'ti ti-chevron-up me-1';
                button.innerHTML = '<i class="ti ti-chevron-up me-1"></i>Voir moins';
            } else {
                // Masquer les fonctionnalités cachées
                hiddenFeatures.classList.add('d-none');
                icon.className = 'ti ti-chevron-down me-1';
                button.innerHTML = '<i class="ti ti-chevron-down me-1"></i>Voir plus';
            }
        });
    });
});
</script>
