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
                                    <i class="ti ti-crown me-1"></i>
                                    Mes Abonnements
                                </a>
                            @endauth
                            <a href="{{ route('auth.subscription-history') }}" class="btn btn-outline-info">
                                <i class="ti ti-history me-1"></i>
                                Mon Historique
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

<!-- Plans de tarification -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Nos Forfaits</h5>
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
                                        @if(auth()->user()->subscription_plan_id == $plan['id'])
                                            <button class="btn btn-outline-success w-100" disabled>
                                                <i class="ti ti-check me-1"></i>
                                                Plan actuel
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('subscriptions.change-plan') }}" class="d-inline w-100">
                                                @csrf
                                                <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                                                <button type="submit" class="btn {{ $plan['button_class'] }} w-100">
                                                    @if($plan['name'] === 'Free')
                                                        <i class="ti ti-arrow-right me-1"></i>
                                                        Passer au Free
                                                    @else
                                                        <i class="ti ti-credit-card me-1"></i>
                                                        S'abonner - {{ $plan['price'] }} {{ $plan['currency'] }}
                                                    @endif
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
                                $features = [
                                    'Accès à la plateforme' => [true, true],
                                    'Notifications WhatsApp' => [false, true],
                                    'Notifications Push' => [false, true],
                                    'Accès API' => [false, true],
                                    'Rapports avancés' => [false, true],
                                    'Support prioritaire' => [false, true]
                                ];
                            @endphp

                            @foreach($features as $feature => $values)
                                <tr>
                                    <td><strong>{{ $feature }}</strong></td>
                                    @foreach($values as $value)
                                        <td class="text-center">
                                            @if(is_bool($value))
                                                @if($value)
                                                    <i class="ti ti-check text-success"></i>
                                                @else
                                                    <i class="ti ti-x text-danger"></i>
                                                @endif
                                            @else
                                                {{ $value }}
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
