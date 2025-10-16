@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Nouveau Tarif de Livraison</h5>
                            <p class="mb-4">Configurez un nouveau tarif de livraison</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                            </div>
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

    <!-- Formulaire de création -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Tarif</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tarifs.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="commune_id" class="form-label">Commune <span class="text-danger">*</span></label>
                                <select class="form-select @error('commune_id') is-invalid @enderror" id="commune_id" name="commune_id" required>
                                    <option value="">Sélectionner une commune</option>
                                    @foreach($communes as $commune)
                                        <option value="{{ $commune->id }}" {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                            {{ $commune->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commune_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="type_engin_id" class="form-label">Type d'Engin <span class="text-danger">*</span></label>
                                <select class="form-select @error('type_engin_id') is-invalid @enderror" id="type_engin_id" name="type_engin_id" required>
                                    <option value="">Sélectionner un type d'engin</option>
                                    @foreach($typeEngins as $typeEngin)
                                        <option value="{{ $typeEngin->id }}" {{ old('type_engin_id') == $typeEngin->id ? 'selected' : '' }}>
                                            {{ $typeEngin->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_engin_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="mode_livraison_id" class="form-label">Mode de Livraison <span class="text-danger">*</span></label>
                                <select class="form-select @error('mode_livraison_id') is-invalid @enderror" id="mode_livraison_id" name="mode_livraison_id" required>
                                    <option value="">Sélectionner un mode de livraison</option>
                                    @foreach($modeLivraisons as $modeLivraison)
                                        <option value="{{ $modeLivraison->id }}" {{ old('mode_livraison_id') == $modeLivraison->id ? 'selected' : '' }}>
                                            {{ $modeLivraison->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mode_livraison_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="poids_id" class="form-label">Poids <span class="text-danger">*</span></label>
                                <select class="form-select @error('poids_id') is-invalid @enderror" id="poids_id" name="poids_id" required>
                                    <option value="">Sélectionner un poids</option>
                                    @foreach($poids as $poid)
                                        <option value="{{ $poid->id }}" {{ old('poids_id') == $poid->id ? 'selected' : '' }}>
                                            {{ $poid->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('poids_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="temp_id" class="form-label">Période Temporelle <span class="text-danger">*</span></label>
                                <select class="form-select @error('temp_id') is-invalid @enderror" id="temp_id" name="temp_id" required>
                                    <option value="">Sélectionner une période</option>
                                    @foreach($temps as $temp)
                                        <option value="{{ $temp->id }}" {{ old('temp_id') == $temp->id ? 'selected' : '' }}>
                                            {{ $temp->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('temp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount') }}"
                                           min="0"
                                           step="0.01"
                                           placeholder="0.00"
                                           required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Créer le Tarif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Aperçu du tarif -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aperçu du Tarif</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto mb-2">
                                    <i class="ti ti-map-pin"></i>
                                </div>
                                <h6 class="mb-1">Commune</h6>
                                <p class="text-muted mb-0" id="preview-commune">-</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-secondary rounded-circle mx-auto mb-2">
                                    <i class="ti ti-truck"></i>
                                </div>
                                <h6 class="mb-1">Type d'Engin</h6>
                                <p class="text-muted mb-0" id="preview-type-engin">-</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-info rounded-circle mx-auto mb-2">
                                    <i class="ti ti-clock"></i>
                                </div>
                                <h6 class="mb-1">Mode & Période</h6>
                                <p class="text-muted mb-0" id="preview-mode-temp">-</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-success rounded-circle mx-auto mb-2">
                                    <i class="ti ti-currency-franc"></i>
                                </div>
                                <h6 class="mb-1">Montant</h6>
                                <p class="text-muted mb-0" id="preview-amount">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// Mise à jour de l'aperçu en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const communeSelect = document.getElementById('commune_id');
    const typeEnginSelect = document.getElementById('type_engin_id');
    const modeLivraisonSelect = document.getElementById('mode_livraison_id');
    const poidsSelect = document.getElementById('poids_id');
    const tempSelect = document.getElementById('temp_id');
    const amountInput = document.getElementById('amount');

    function updatePreview() {
        const commune = communeSelect.options[communeSelect.selectedIndex]?.text || '-';
        const typeEngin = typeEnginSelect.options[typeEnginSelect.selectedIndex]?.text || '-';
        const modeLivraison = modeLivraisonSelect.options[modeLivraisonSelect.selectedIndex]?.text || '-';
        const poids = poidsSelect.options[poidsSelect.selectedIndex]?.text || '-';
        const temp = tempSelect.options[tempSelect.selectedIndex]?.text || '-';
        const amount = amountInput.value ? parseFloat(amountInput.value).toLocaleString('fr-FR') + ' FCFA' : '-';

        document.getElementById('preview-commune').textContent = commune;
        document.getElementById('preview-type-engin').textContent = typeEngin;
        document.getElementById('preview-mode-temp').textContent = modeLivraison + ' - ' + temp;
        document.getElementById('preview-amount').textContent = amount;
    }

    communeSelect.addEventListener('change', updatePreview);
    typeEnginSelect.addEventListener('change', updatePreview);
    modeLivraisonSelect.addEventListener('change', updatePreview);
    poidsSelect.addEventListener('change', updatePreview);
    tempSelect.addEventListener('change', updatePreview);
    amountInput.addEventListener('input', updatePreview);

    // Initialiser l'aperçu
    updatePreview();
});
</script>

@include('layouts.footer')
