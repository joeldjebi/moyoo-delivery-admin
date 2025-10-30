@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Forfaits</h5>
                        <p class="mb-4">Choisissez le plan qui correspond le mieux à vos besoins</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            @auth
                                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary">
                                    <i class="ti ti-history me-1"></i>
                                    Mon Historique
                                </a>
                            @endauth
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

<!-- Plans de tarification -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Nos Forfaits</h5>
                @auth
                    @if(auth()->user()->hasActiveSubscription())
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="ti ti-check-circle me-2"></i>
                            <strong>Vous avez un abonnement actif !</strong>
                            Vous pouvez changer de plan à tout moment pour accéder à plus de fonctionnalités.
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Aucun abonnement actif</strong>
                            Choisissez un plan pour accéder à toutes les fonctionnalités de la plateforme.
                        </div>
                    @endif
                @endauth
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($plans as $plan)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 {{ $plan['popular'] ? 'border-primary shadow-lg' : 'border' }}">
                                @if($plan['popular'])
                                    <div class="card-header bg-primary text-white text-center">
                                        <span class="badge bg-white text-primary">Le plus populaire</span>
                                    </div>
                                @endif

                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $plan['name'] }}</h5>
                                    <p class="text-muted mb-4">{{ $plan['description'] }}</p>

                                    <div class="mb-4">
                                        <h2 class="text-primary mb-0">{{ $plan['price'] }} {{ $plan['currency'] }}</h2>
                                        <small class="text-muted">par {{ $plan['period'] }}</small>
                                    </div>

                                    <ul class="list-unstyled mb-4">
                                        @foreach($plan['features'] as $feature)
                                            <li class="mb-2">
                                                <i class="ti ti-check text-success me-2"></i>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="card-footer text-center">
                                    @auth
                                        @if(auth()->user()->hasActiveSubscription() && auth()->user()->subscription_plan_id == $plan['id'])
                                            <button class="btn btn-outline-success w-100" disabled>
                                                <i class="ti ti-check me-1"></i>
                                                Plan actuel
                                            </button>
                                        @elseif($plan['name'] === 'Free')
                                            <button class="btn btn-outline-secondary w-100" disabled>
                                                <i class="ti ti-x me-1"></i>
                                                Non disponible
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('subscriptions.change-plan') }}" class="d-inline w-100">
                                                @csrf
                                                <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                                                <button type="submit" class="btn {{ $plan['button_class'] }} w-100">
                                                    <i class="ti ti-credit-card me-1"></i>
                                                    S'abonner - {{ $plan['price'] }} {{ $plan['currency'] }}
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('auth.register') }}" class="btn {{ $plan['button_class'] }} w-100">
                                            {{ $plan['button_text'] }}
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comparaison des plans -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Comparaison des Plans</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Fonctionnalités</th>
                                @foreach($plans as $plan)
                                    <th class="text-center">{{ $plan['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Construire la comparaison dynamiquement depuis pricing_plans.features
                                $planFeatureMap = [];
                                $allFeatureLabels = [];
                                $planColis = []; // valeur normalisée: '20' ou 'Illimitées'
                                $colisIllimRegex = '/illimit/i';
                                $colis20Regex = '/20\s*colis/i';
                                foreach ($plans as $plan) {
                                    $feat = $plan['features'] ?? [];
                                    $featuresArray = is_array($feat) ? $feat : (json_decode($feat, true) ?? []);
                                    $planFeatureMap[$plan['id']] = $featuresArray;
                                    foreach ($featuresArray as $label) {
                                        // Détection des libellés liés aux colis (pour normalisation)
                                        if (preg_match($colisIllimRegex, $label)) {
                                            $planColis[$plan['id']] = 'Illimitées';
                                            continue;
                                        }
                                        if (preg_match($colis20Regex, $label)) {
                                            // Ne pas écraser l'illimité si déjà détecté
                                            if (!isset($planColis[$plan['id']])) {
                                                $planColis[$plan['id']] = '20';
                                            }
                                            continue;
                                        }
                                        $allFeatureLabels[$label] = true;
                                    }
                                }
                                $featureList = array_keys($allFeatureLabels);
                                sort($featureList);
                            @endphp

                            {{-- Ligne normalisée : Livraisons par mois --}}
                            <tr>
                                <td><strong>Livraisons par mois</strong></td>
                                @foreach($plans as $plan)
                                    @php $value = $planColis[$plan['id']] ?? null; @endphp
                                    <td class="text-center">
                                        @if($value === 'Illimitées')
                                            <span class="badge bg-light text-dark">Illimitées</span>
                                        @elseif($value === '20')
                                            <span class="badge bg-light text-dark">20</span>
                                        @else
                                            <i class="ti ti-x text-danger"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            @foreach($featureList as $feature)
                                <tr>
                                    <td><strong>{{ $feature }}</strong></td>
                                    @foreach($plans as $plan)
                                        @php
                                            $feat = $planFeatureMap[$plan['id']] ?? [];
                                            // Filtrer les features liées aux colis déjà normalisées
                                            $isColisRelated = preg_match($colisIllimRegex, $feature) || preg_match($colis20Regex, $feature);
                                            if ($isColisRelated) { $hasFeature = false; } else { $hasFeature = in_array($feature, $feat, true); }
                                        @endphp
                                        <td class="text-center">
                                            @if($hasFeature)
                                                <i class="ti ti-check text-success"></i>
                                            @else
                                                <i class="ti ti-x text-danger"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ rapide -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Questions Fréquentes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="mb-2">Puis-je changer de plan à tout moment ?</h6>
                            <p class="text-muted mb-0">Oui, vous pouvez upgrader ou downgrader votre plan à tout moment. Les changements prennent effet immédiatement.</p>
                        </div>
                        <div class="mb-4">
                            <h6 class="mb-2">Y a-t-il des frais de configuration ?</h6>
                            <p class="text-muted mb-0">Non, il n'y a aucun frais de configuration. Vous payez uniquement votre abonnement mensuel.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="mb-2">Puis-je annuler mon abonnement ?</h6>
                            <p class="text-muted mb-0">Oui, vous pouvez annuler votre abonnement à tout moment depuis votre tableau de bord.</p>
                        </div>
                        <div class="mb-4">
                            <h6 class="mb-2">Quels modes de paiement acceptez-vous ?</h6>
                            <p class="text-muted mb-0">Nous acceptons les cartes bancaires, PayPal et les virements bancaires.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="{{ route('auth.faq') }}" class="btn btn-outline-primary">
                        <i class="ti ti-help-circle me-1"></i>
                        Voir toutes les FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
