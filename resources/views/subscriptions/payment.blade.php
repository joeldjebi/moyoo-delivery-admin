@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Paiement - {{ $plan->name }}</h5>
                        <p class="mb-4">Finalisez votre abonnement {{ $plan->name }}.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour aux plans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Récapitulatif du plan -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Récapitulatif</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h4 class="text-primary">{{ $plan->name }}</h4>
                    <div class="display-6 fw-bold text-primary">{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}</div>
                    <div class="text-muted">par mois</div>
                </div>
                
                <p class="text-muted mb-3">{{ $plan->description }}</p>
                
                <h6>Fonctionnalités incluses:</h6>
                <ul class="list-unstyled">
                    @foreach($plan->formatted_features as $feature)
                        <li class="mb-2">
                            <i class="ti ti-check text-success me-2"></i>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Formulaire de paiement -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations de paiement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('subscriptions.process-payment') }}" id="paymentForm">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    
                    <!-- Méthodes de paiement -->
                    <div class="mb-4">
                        <label class="form-label">Méthode de paiement</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mobile_money" value="mobile_money" checked>
                                    <label class="form-check-label" for="mobile_money">
                                        <i class="ti ti-device-mobile me-2"></i>
                                        Mobile Money
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                                    <label class="form-check-label" for="card">
                                        <i class="ti ti-credit-card me-2"></i>
                                        Carte bancaire
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                    <label class="form-check-label" for="bank_transfer">
                                        <i class="ti ti-building-bank me-2"></i>
                                        Virement bancaire
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations utilisateur -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" value="{{ $user->full_name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                        </div>
                    </div>

                    <!-- Détails spécifiques selon la méthode de paiement -->
                    <div id="mobile_money_details" class="payment-method-details">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Mobile Money</strong><br>
                            Vous recevrez un lien de paiement par SMS ou WhatsApp pour finaliser votre abonnement.
                        </div>
                    </div>

                    <div id="card_details" class="payment-method-details" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Paiement par carte</strong><br>
                            Cette fonctionnalité sera bientôt disponible. Veuillez choisir une autre méthode de paiement.
                        </div>
                    </div>

                    <div id="bank_transfer_details" class="payment-method-details" style="display: none;">
                        <div class="alert alert-info">
                            <i class="ti ti-building-bank me-2"></i>
                            <strong>Virement bancaire</strong><br>
                            Vous recevrez les détails du compte bancaire par email pour effectuer le virement.
                        </div>
                    </div>

                    <!-- Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#" target="_blank">conditions générales</a> et la <a href="#" target="_blank">politique de confidentialité</a>
                            </label>
                        </div>
                    </div>

                    <!-- Bouton de paiement -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ti ti-credit-card me-2"></i>
                            Payer {{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetails = document.querySelectorAll('.payment-method-details');

    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Masquer tous les détails
            paymentDetails.forEach(detail => {
                detail.style.display = 'none';
            });

            // Afficher les détails de la méthode sélectionnée
            const selectedDetails = document.getElementById(this.value + '_details');
            if (selectedDetails) {
                selectedDetails.style.display = 'block';
            }
        });
    });
});
</script>
