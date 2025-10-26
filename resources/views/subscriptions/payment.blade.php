@include('layouts.header')
@include('layouts.menu')

<!-- Messages d'erreur et de succès -->
@if(session('error'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i>
                <strong>Erreurs détectées :</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

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
                    @if($plan->formatted_features && count($plan->formatted_features) > 0)
                        @foreach($plan->formatted_features as $feature)
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    @else
                        <li class="mb-2">
                            <i class="ti ti-check text-success me-2"></i>
                            Accès de base à la plateforme
                        </li>
                    @endif
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
                        <label class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('payment_method') is-invalid @enderror"
                                           type="radio" name="payment_method" id="mobile_money"
                                           value="mobile_money" {{ old('payment_method', 'mobile_money') == 'mobile_money' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mobile_money">
                                        <i class="ti ti-device-mobile me-2"></i>
                                        Mobile Money
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('payment_method') is-invalid @enderror"
                                           type="radio" name="payment_method" id="card"
                                           value="card" {{ old('payment_method') == 'card' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="card">
                                        <i class="ti ti-credit-card me-2"></i>
                                        Carte bancaire
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('payment_method') is-invalid @enderror"
                                           type="radio" name="payment_method" id="bank_transfer"
                                           value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bank_transfer">
                                        <i class="ti ti-building-bank me-2"></i>
                                        Virement bancaire
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('payment_method')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
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

                    <!-- Champ téléphone pour Mobile Money -->
                    <div id="phone_field" class="mb-4" style="display: none;">
                        <label class="form-label">Numéro de téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                               name="phone_number" id="phone_number"
                               value="{{ old('phone_number') }}"
                               placeholder="Ex: +2250701234567">
                        @error('phone_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">Format: +225XXXXXXXXXX (10 chiffres)</div>
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
                            <input class="form-check-input @error('terms') is-invalid @enderror"
                                   type="checkbox" id="terms" name="terms"
                                   value="1" {{ old('terms') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#" target="_blank">conditions générales</a> et la <a href="#" target="_blank">politique de confidentialité</a> <span class="text-danger">*</span>
                            </label>
                        </div>
                        @error('terms')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage des détails selon la méthode de paiement
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const phoneField = document.getElementById('phone_field');
    const mobileMoneyDetails = document.getElementById('mobile_money_details');
    const cardDetails = document.getElementById('card_details');
    const bankTransferDetails = document.getElementById('bank_transfer_details');

    function togglePaymentDetails() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');

        // Masquer tous les détails
        mobileMoneyDetails.style.display = 'none';
        cardDetails.style.display = 'none';
        bankTransferDetails.style.display = 'none';
        phoneField.style.display = 'none';

        if (selectedMethod) {
            switch(selectedMethod.value) {
                case 'mobile_money':
                    mobileMoneyDetails.style.display = 'block';
                    phoneField.style.display = 'block';
                    break;
                case 'card':
                    cardDetails.style.display = 'block';
                    break;
                case 'bank_transfer':
                    bankTransferDetails.style.display = 'block';
                    break;
            }
        }
    }

    // Écouter les changements de méthode de paiement
    paymentMethods.forEach(method => {
        method.addEventListener('change', togglePaymentDetails);
    });

    // Initialiser l'affichage
    togglePaymentDetails();

    // Validation du formulaire
    const form = document.getElementById('paymentForm');
    form.addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        const phoneNumber = document.getElementById('phone_number');
        const terms = document.getElementById('terms');

        // Vérifier la méthode de paiement
        if (!selectedMethod) {
            e.preventDefault();
            alert('Veuillez sélectionner une méthode de paiement.');
            return false;
        }

        // Vérifier le numéro de téléphone pour Mobile Money
        if (selectedMethod.value === 'mobile_money') {
            if (!phoneNumber.value.trim()) {
                e.preventDefault();
                alert('Veuillez saisir votre numéro de téléphone pour Mobile Money.');
                phoneNumber.focus();
                return false;
            }

            // Validation basique du format téléphone
            const phoneRegex = /^\+225\d{10}$/;
            if (!phoneRegex.test(phoneNumber.value.trim())) {
                e.preventDefault();
                alert('Veuillez saisir un numéro de téléphone valide (format: +225XXXXXXXXXX - 10 chiffres).');
                phoneNumber.focus();
                return false;
            }
        }

        // Vérifier les conditions
        if (!terms.checked) {
            e.preventDefault();
            alert('Veuillez accepter les conditions générales et la politique de confidentialité.');
            terms.focus();
            return false;
        }

        // Désactiver le bouton pour éviter les double-soumissions
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-2"></i>Traitement en cours...';
    });
});
</script>

@include('layouts.footer')
