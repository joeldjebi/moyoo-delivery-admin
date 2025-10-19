@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Plans d'Abonnement</h5>
                        <p class="mb-4">Choisissez le plan qui correspond le mieux à vos besoins.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="text-end">
                            <h6 class="mb-0">Plan actuel</h6>
                            <span class="badge bg-label-{{ $user->subscriptionPlan && $user->subscriptionPlan->isPremium() ? 'primary' : 'secondary' }}">
                                {{ $user->subscriptionPlan ? $user->subscriptionPlan->name : 'Aucun' }}
                            </span>
                            @if($user->isOnTrial())
                                <br><small class="text-muted">Période d'essai jusqu'au {{ $user->trial_expires_at->format('d/m/Y') }}</small>
                            @elseif($user->hasActiveSubscription())
                                <br><small class="text-muted">Expire le {{ $user->subscription_expires_at->format('d/m/Y') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plans d'abonnement -->
<div class="row">
    @foreach($plans as $plan)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 {{ $user->subscription_plan_id == $plan->id ? 'border-primary' : '' }}">
                <div class="card-header text-center">
                    @if($user->subscription_plan_id == $plan->id)
                        <span class="badge bg-primary mb-2">Plan actuel</span>
                    @endif
                    <h5 class="card-title mb-0">{{ $plan->name }}</h5>
                    <div class="mt-3">
                        <span class="display-6 fw-bold text-primary">{{ number_format($plan->price, 0, ',', ' ') }}</span>
                        <span class="text-muted">{{ $plan->currency }}</span>
                        <div class="text-muted small">par mois</div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">{{ $plan->description }}</p>
                    
                    <ul class="list-unstyled">
                        @foreach($plan->formatted_features as $feature)
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer">
                    @if($user->subscription_plan_id == $plan->id)
                        <button class="btn btn-outline-primary w-100" disabled>
                            <i class="ti ti-check me-1"></i>
                            Plan actuel
                        </button>
                    @else
                        <form method="POST" action="{{ route('subscriptions.change-plan') }}" class="d-inline w-100">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="btn btn-{{ $plan->isPremium() ? 'primary' : 'outline-primary' }} w-100">
                                @if($plan->isFree())
                                    <i class="ti ti-arrow-right me-1"></i>
                                    Passer au Free
                                @else
                                    <i class="ti ti-credit-card me-1"></i>
                                    S'abonner - {{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Informations sur l'abonnement actuel -->
@if($user->subscriptionPlan)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Détails de votre abonnement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Plan: {{ $user->subscriptionPlan->name }}</h6>
                            <p class="text-muted">{{ $user->subscriptionPlan->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Statut: {{ $user->getAttribute('subscription_status') }}</h6>
                            @if($user->isOnTrial())
                                <p class="text-muted">Période d'essai jusqu'au {{ $user->trial_expires_at->format('d/m/Y à H:i') }}</p>
                            @elseif($user->hasActiveSubscription())
                                <p class="text-muted">Expire le {{ $user->subscription_expires_at->format('d/m/Y à H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($user->hasActiveSubscription() && !$user->isOnTrial())
                        <div class="mt-3">
                            <form method="POST" action="{{ route('subscriptions.cancel') }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler l'abonnement
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@include('layouts.footer')
