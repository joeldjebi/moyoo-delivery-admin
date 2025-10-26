@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <!-- Icône de succès -->
                    <div class="mb-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-success">
                                <i class="ti ti-check ti-lg"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Titre de succès -->
                    <h2 class="text-success mb-3">Paiement Réussi !</h2>
                    <p class="text-muted mb-4">Votre abonnement a été activé avec succès.</p>

                    <!-- Détails du paiement -->
                    @if(isset($subscription) && isset($plan))
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        <i class="ti ti-crown me-2"></i>Détails de l'Abonnement
                                    </h5>

                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Plan:</strong></p>
                                            <p class="text-primary">{{ $plan->name }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Prix:</strong></p>
                                            <p class="text-success">{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}</p>
                                        </div>
                                    </div>

                                    @if(isset($subscription))
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Durée totale:</strong></p>
                                            <p class="text-info">{{ $subscription->getRealDurationDays() }} jours</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Jours restants:</strong></p>
                                            <p class="text-warning">{{ $subscription->getRemainingDays() }} jours</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if(isset($transaction_id))
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-1"><strong>Transaction ID:</strong></p>
                                            <p class="text-muted small">{{ $transaction_id }}</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if(isset($is_extension) && $is_extension)
                                    <div class="alert alert-info mt-3">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Votre abonnement existant a été étendu avec succès.
                                    </div>
                                    @else
                                    <div class="alert alert-success mt-3">
                                        <i class="ti ti-check-circle me-2"></i>
                                        Votre nouvel abonnement est maintenant actif.
                                    </div>
                                    @endif

                                    @if(isset($subscription) && $subscription->expires_at)
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-1"><strong>Expire le:</strong></p>
                                            <p class="text-muted">{{ $subscription->expires_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-5">
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-primary me-3">
                            <i class="ti ti-arrow-left me-2"></i>Retour aux Abonnements
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                            <i class="ti ti-home me-2"></i>Tableau de Bord
                        </a>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="mt-4">
                        <p class="text-muted small">
                            <i class="ti ti-info-circle me-1"></i>
                            Un email de confirmation vous a été envoyé avec tous les détails de votre abonnement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
