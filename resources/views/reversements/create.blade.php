@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Nouveau Reversement</h5>
                        <p class="mb-4">Effectuer un reversement de montant encaissé à un marchand.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <a href="{{ route('reversements.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Messages d'erreur -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations du Reversement</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reversements.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marchand <span class="text-danger">*</span></label>
                            <select name="marchand_id" id="marchand_id" class="form-select" required>
                                <option value="">Sélectionner un marchand</option>
                                @foreach($balances as $balance)
                                    <option value="{{ $balance->marchand_id }}"
                                            data-boutique-id="{{ $balance->boutique_id }}"
                                            data-balance="{{ $balance->balance_actuelle }}"
                                            {{ (isset($selected_marchand_id) && $selected_marchand_id == $balance->marchand_id) ? 'selected' : '' }}>
                                        {{ $balance->marchand->first_name }} {{ $balance->marchand->last_name }} - {{ $balance->boutique->libelle }}
                                        ({{ number_format($balance->balance_actuelle) }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Boutique <span class="text-danger">*</span></label>
                            <select name="boutique_id" id="boutique_id" class="form-select" required>
                                <option value="">Sélectionner une boutique</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Montant à Reverser <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="montant_reverse" id="montant_reverse"
                                       class="form-control" step="0.01" min="0.01" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div class="form-text">
                                Balance disponible: <span id="balance_disponible" class="fw-bold text-success">0 FCFA</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mode de Reversement <span class="text-danger">*</span></label>
                            <select name="mode_reversement" class="form-select" required>
                                <option value="">Sélectionner un mode</option>
                                <option value="especes">Espèces</option>
                                <option value="virement">Virement</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cheque">Chèque</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Notes optionnelles sur ce reversement..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i>
                            Créer le Reversement
                        </button>
                        <a href="{{ route('reversements.index') }}" class="btn btn-outline-secondary ms-2">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Marchands avec Balance</h5>
            </div>
            <div class="card-body">
                @if($balances->count() > 0)
                    <div class="list-group">
                        @foreach($balances as $balance)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $balance->marchand->first_name }} {{ $balance->marchand->last_name }}</h6>
                                        <small class="text-muted">{{ $balance->boutique->libelle }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">{{ number_format($balance->balance_actuelle) }} FCFA</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-wallet-off" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Aucune balance disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marchandSelect = document.getElementById('marchand_id');
    const boutiqueSelect = document.getElementById('boutique_id');
    const montantInput = document.getElementById('montant_reverse');
    const balanceDisponible = document.getElementById('balance_disponible');

    // Gérer la sélection du marchand
    marchandSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const boutiqueId = selectedOption.getAttribute('data-boutique-id');
        const balance = selectedOption.getAttribute('data-balance');

        // Mettre à jour la boutique
        boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';
        if (boutiqueId) {
            boutiqueSelect.innerHTML = `<option value="${boutiqueId}" selected>${selectedOption.textContent.split(' - ')[1].split(' (')[0]}</option>`;
        }

        // Mettre à jour la balance disponible
        if (balance) {
            balanceDisponible.textContent = new Intl.NumberFormat('fr-FR').format(balance) + ' FCFA';
            montantInput.max = balance;
        } else {
            balanceDisponible.textContent = '0 FCFA';
            montantInput.max = '';
        }

        // Réinitialiser le montant
        montantInput.value = '';
    });

    // Si un marchand est pré-sélectionné, déclencher l'événement
    if (marchandSelect.value) {
        marchandSelect.dispatchEvent(new Event('change'));
    }

    // Validation du montant
    montantInput.addEventListener('input', function() {
        const maxAmount = parseFloat(this.max);
        const currentAmount = parseFloat(this.value);

        if (currentAmount > maxAmount) {
            this.setCustomValidity('Le montant ne peut pas dépasser la balance disponible');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

@include('layouts.footer')
