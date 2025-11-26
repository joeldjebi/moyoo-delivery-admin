@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Créer des Colis</h5>
                        <p class="mb-4">Enregistrer plusieurs colis dans le système</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        <a href="{{ route('colis.packages') }}" class="btn btn-outline-primary me-2">
                            <i class="ti ti-package me-1"></i>
                            Voir les Packages
                        </a>
                        <button type="button" class="btn btn-outline-success" onclick="toggleMultiBoutiquesMode()">
                            <i class="ti ti-building-store me-1"></i>
                            Mode Multi-Boutiques
                        </button>
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
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Section Multi-Boutiques (cachée par défaut) -->
<div id="multiBoutiquesSection" class="row mb-4" style="display: none;">
    <div class="col-lg-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0 text-white">
                    <i class="ti ti-building-store me-2"></i>
                    Mode Multi-Boutiques - Workflow par Étapes
                </h5>
                <small class="text-white-50">Sélectionnez plusieurs boutiques et créez des packages séparés pour chacune</small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('colis.store-multi-boutiques') }}" id="multiBoutiquesForm">
                    @csrf

                    <!-- Étape 1: Sélection du Marchand -->
                    <div class="mt-4">
                        <h6 class="text-success mb-3">
                            <span class="badge bg-success me-2">1</span>
                            Sélectionner le Marchand
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Marchand <span class="text-danger">*</span></label>
                                <select class="form-select" name="marchand_id" id="multiBoutiquesMarchandId" required onchange="loadBoutiquesForMultiMode()">
                                    <option value="">Sélectionner un marchand</option>
                                    @foreach($marchands as $marchand)
                                        <option value="{{ $marchand->id }}">{{ $marchand->first_name }} {{ $marchand->last_name }} ({{ $marchand->mobile }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ramassage (Optionnel)</label>
                                <select class="form-select" name="ramassage_id" id="multiBoutiquesRamassageId" onchange="loadRamassageDataForMultiMode()">
                                    <option value="">Sélectionner un ramassage</option>
                                    @if(isset($ramassages) && count($ramassages) > 0)
                                        @foreach($ramassages as $ramassage)
                                            @if($ramassage->statut === 'termine')
                                                <option value="{{ $ramassage->id }}">
                                                    {{ $ramassage->code_ramassage }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun ramassage disponible</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">
                                    @if(isset($ramassages) && count($ramassages) > 0)
                                        @php
                                            $ramassagesTermines = collect($ramassages)->where('statut', 'termine')->count();
                                        @endphp
                                        {{ $ramassagesTermines }} ramassage(s) terminé(s) disponible(s)
                                    @else
                                        Aucun ramassage terminé trouvé
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Étape 2: Sélection des Boutiques -->
                    <div class="mt-4">
                        <h6 class="text-success mb-3">
                            <span class="badge bg-success me-2">2</span>
                            Sélectionner les Boutiques
                        </h6>
                        <div id="boutiquesMultiSelection" class="row">
                            <div class="col-12">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Veuillez d'abord sélectionner un marchand pour voir ses boutiques
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Étape 3: Configuration des Colis par Boutique -->
                    <div id="boutiquesColisConfiguration" class="mb-4" style="display: none;">
                        <h6 class="text-success mb-3">
                            <span class="badge bg-success me-2">3</span>
                            Configuration des Colis par Boutique
                        </h6>
                        <div id="boutiquesConfigContainer">
                            <!-- Les configurations de boutiques seront ajoutées ici dynamiquement -->
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary" onclick="toggleMultiBoutiquesMode()">
                            <i class="ti ti-x me-1"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success" id="submitMultiBoutiquesBtn" style="display: none;">
                            <i class="ti ti-check me-1"></i>
                            Créer les Packages Multi-Boutiques
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire principal -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('colis.store') }}" id="colisForm">
                    @csrf

                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Étape 1 : Informations Générales</h6>
                        </div>

                        <div class="col-md-4">
                            <label for="nombre_colis" class="form-label">Nombre de colis <span class="text-danger">*</span></label>
                            <input type="number" class="form-select @error('nombre_colis') is-invalid @enderror"
                                   id="nombre_colis" name="nombre_colis" min="1" max="20" value="{{ old('nombre_colis', 1) }}" required>
                            @error('nombre_colis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marchand_id" class="form-label">Marchand <span class="text-danger">*</span></label>
                            <select class="form-select @error('marchand_id') is-invalid @enderror" id="marchand_id" name="marchand_id" required>
                                <option value="">Sélectionner un marchand</option>
                                @foreach($marchands ?? [] as $marchand)
                                    <option value="{{ $marchand->id }}" {{ old('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                        {{ $marchand->first_name }} {{ $marchand->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marchand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="boutique_id" class="form-label">Boutique <span class="text-danger">*</span></label>
                            <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id" name="boutique_id" required>
                                <option value="">Sélectionner d'abord un marchand</option>
                            </select>
                            @error('boutique_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="ramassage_id" class="form-label">Ramassage (Optionnel)</label>
                            <select class="form-select @error('ramassage_id') is-invalid @enderror" id="ramassage_id" name="ramassage_id">
                                <option value="">Sélectionner un ramassage</option>
                                @if(isset($ramassages) && count($ramassages) > 0)
                                    @foreach($ramassages as $ramassage)
                                        @if($ramassage->statut === 'termine')
                                            <option value="{{ $ramassage->id }}" {{ old('ramassage_id') == $ramassage->id ? 'selected' : '' }}>
                                                {{ $ramassage->code_ramassage }}
                                            </option>
                                        @endif
                                    @endforeach
                                @else
                                    <option value="" disabled>Aucun ramassage disponible</option>
                                @endif
                            </select>
                            <small class="form-text text-muted">
                                @if(isset($ramassages) && count($ramassages) > 0)
                                    @php
                                        $ramassagesTermines = collect($ramassages)->where('statut', 'termine')->count();
                                    @endphp
                                    {{ $ramassagesTermines }} ramassage(s) terminé(s) disponible(s)
                                @else
                                    Aucun ramassage terminé trouvé
                                @endif
                            </small>
                            @error('ramassage_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>



                    <!-- Livreur et Engin -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Étape 2 : Assignation (Obligatoire)</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="livreur_id" class="form-label">Livreur <span class="text-danger">*</span></label>
                            <select required class="form-select @error('livreur_id') is-invalid @enderror" id="livreur_id" name="livreur_id">
                                <option value="">Sélectionner un livreur (Obligatoire)</option>
                                @foreach($livreurs ?? [] as $livreur)
                                    <option value="{{ $livreur->id }}" {{ old('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                        {{ $livreur->first_name }} {{ $livreur->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('livreur_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="engin_id" class="form-label">Engin du livreur <span class="text-danger">*</span></label>
                            <select required class="form-select @error('engin_id') is-invalid @enderror" id="engin_id" name="engin_id" disabled onchange="recalculateAllCosts()">
                                <option value="">Sélectionnez d'abord un livreur</option>
                            </select>
                            @error('engin_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Affichage de l'engin du livreur sélectionné -->
                            <div id="livreur-engin-info" class="mt-2" style="display: none;">
                                <small class="text-success">
                                    <i class="ti ti-check-circle me-1"></i>
                                    <span id="livreur-engin-text"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des colis -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Détails des Colis</h6>
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Étape 3 :</strong> Cliquez sur "Générer les formulaires" pour créer les formulaires de colis avec sélection de zone pour chaque colis
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="generateColisForms">
                                <i class="ti ti-plus me-1"></i>
                                Générer les formulaires
                            </button>
                        </div>
                    </div>

                    <!-- Conteneur pour les formulaires de colis -->
                    <div id="colisFormsContainer">
                        <!-- Les formulaires seront générés dynamiquement ici -->
                    </div>

                    <!-- Résumé des coûts -->
                    <div id="costSummary" class="card mt-4" style="display: none; margin-bottom: 20px;">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0" style="color: white;">
                                <i class="fas fa-calculator me-2"></i>
                                Résumé des Coûts de Livraison
                            </h6>
                        </div>
                        <div class="card-body mt-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Nombre de colis :</strong> <span id="totalColis">0</span></p>
                                    <p class="mb-1"><strong>Coût moyen par colis :</strong> <span id="averageCost">0</span> FCFA</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Coût total estimé :</strong></p>
                                    <h4 class="text-success mb-0" id="totalCost">0 FCFA</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                    <i class="ti ti-check me-1"></i>
                                    Créer les Colis
                                </button>
                                <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de sélection des communes -->
<div class="modal fade" id="communeModal" tabindex="-1" aria-labelledby="communeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="communeModalLabel">Sélectionner les zones de livraison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="communeSearch" placeholder="Rechercher une commune..." onkeyup="filterCommunes()">
                </div>
                <div class="mb-3">
                    <div class="alert alert-info d-flex align-items-center" id="communeSelectionInfo">
                        <i class="ti ti-info-circle me-2"></i>
                        <span id="communeSelectionText">Sélectionnez les communes de livraison</span>
                    </div>
                </div>
                <div class="row" id="communesContainer">
                    @foreach($communes as $commune)
                        <div class="col-md-4 mb-2 commune-item" data-commune-name="{{ strtolower($commune->libelle) }}">
                            <div class="form-check">
                                <input class="form-check-input commune-checkbox" type="checkbox" value="{{ $commune->id }}" id="commune_{{ $commune->id }}" onchange="updateCommuneSelectionCounter()">
                                <label class="form-check-label" for="commune_{{ $commune->id }}" onclick="handleLabelClick('commune_{{ $commune->id }}')">
                                    {{ $commune->libelle }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="confirmCommuneSelectionMultiMode()">
                    <i class="ti ti-check me-1"></i>
                    Confirmer la sélection
                </button>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
// Version du script pour éviter les problèmes de cache
console.log('Script colis create v2.0 chargé');

// Fonction pour basculer entre le mode normal et multi-boutiques (doit être dans le scope global)
function toggleMultiBoutiquesMode() {
    console.log('toggleMultiBoutiquesMode appelée');
    const multiBoutiquesSection = document.getElementById('multiBoutiquesSection');
    const normalFormSection = document.getElementById('colisForm').closest('.row');

    if (multiBoutiquesSection.style.display === 'none') {
        // Activer le mode multi-boutiques
        multiBoutiquesSection.style.display = 'block';
        normalFormSection.style.display = 'none';
        resetMultiBoutiquesForm();
    } else {
        // Revenir au mode normal
        multiBoutiquesSection.style.display = 'none';
        normalFormSection.style.display = 'block';
        resetMultiBoutiquesForm();
    }
}

// Fonction pour réinitialiser le formulaire multi-boutiques (doit être dans le scope global)
function resetMultiBoutiquesForm() {
    const marchandSelect = document.getElementById('multiBoutiquesMarchandId');
    const boutiquesContainer = document.getElementById('boutiquesMultiSelection');
    const configContainer = document.getElementById('boutiquesConfigContainer');
    const submitBtn = document.getElementById('submitMultiBoutiquesBtn');

    if (marchandSelect) marchandSelect.value = '';
    if (boutiquesContainer) {
        boutiquesContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="ti ti-info-circle me-2"></i>
                    Veuillez d'abord sélectionner un marchand pour voir ses boutiques
                </div>
            </div>
        `;
    }
    if (configContainer) {
        configContainer.innerHTML = '';
        document.getElementById('boutiquesColisConfiguration').style.display = 'none';
    }
    if (submitBtn) submitBtn.style.display = 'none';
}

// Fonctions globales pour la mise à jour des zones (doivent être dans le scope global)
function updateZoneDisplay(index) {
    const zoneSelect = document.getElementById(`zone-select-${index}`);
    const zoneDisplay = document.getElementById(`zone-display-${index}`);

    if (zoneSelect && zoneDisplay) {
        const selectedOption = zoneSelect.options[zoneSelect.selectedIndex];
        zoneDisplay.textContent = selectedOption ? selectedOption.text : 'Non définie';
    }
}

function updateZoneDisplayMulti(boutiqueId, index) {
    const zoneSelect = document.getElementById(`zone-select-multi-${boutiqueId}-${index}`);
    const zoneDisplay = document.getElementById(`zone-display-multi-${boutiqueId}-${index}`);

    if (zoneSelect && zoneDisplay) {
        const selectedOption = zoneSelect.options[zoneSelect.selectedIndex];
        zoneDisplay.textContent = selectedOption ? selectedOption.text : 'Non définie';
    }
}

// Fonction pour recalculer tous les coûts d'une boutique quand l'engin change
function recalculateAllCostsMulti(boutiqueIndex) {
    console.log(`🔄 RECALCUL TOUS LES COÛTS - Boutique ${boutiqueIndex}`);

    // Trouver tous les formulaires de colis de cette boutique
    const colisForms = document.querySelectorAll(`[id^="cost-display-multi-${boutiqueIndex}-"]`);

    console.log(`📋 FORMULAIRES TROUVÉS - Boutique ${boutiqueIndex}:`, colisForms.length);

    // Ne recalculer que si des formulaires existent
    if (colisForms.length > 0) {
        colisForms.forEach(costDisplay => {
            // Extraire l'index du colis depuis l'ID
            const id = costDisplay.id;
            const colisIndex = id.split('-').pop();

            console.log(`🔄 RECALCUL COÛT - Boutique ${boutiqueIndex}, Colis ${colisIndex}`);

            // Recalculer le coût pour ce colis
            calculateDeliveryCostMulti(boutiqueIndex, colisIndex);
        });
    } else {
        console.log(`⚠️ AUCUN FORMULAIRE TROUVÉ - Boutique ${boutiqueIndex}: Les formulaires n'ont pas encore été générés`);
    }
}

// Fonction pour calculer le coût de livraison en mode multi-boutiques (même logique que mode normal)
function calculateDeliveryCostMulti(boutiqueIndex, colisIndex) {
    console.log(`🚀 DÉBUT CALCUL COÛT MULTI - Boutique ${boutiqueIndex}, Colis ${colisIndex}`);

    const poidsId = document.querySelector(`select[name="boutiques[${boutiqueIndex}][colis][${colisIndex}][poids_id]"]`)?.value;
    const modeLivraisonId = document.querySelector(`select[name="boutiques[${boutiqueIndex}][colis][${colisIndex}][mode_livraison_id]"]`)?.value;
    const tempId = document.querySelector(`select[name="boutiques[${boutiqueIndex}][colis][${colisIndex}][temp_id]"]`)?.value;
    const enginId = document.querySelector(`select[name="boutiques[${boutiqueIndex}][engin_id]"]`)?.value;
    const communeId = document.querySelector(`select[name="boutiques[${boutiqueIndex}][colis][${colisIndex}][commune_id]"]`)?.value;

    const costDisplay = document.getElementById(`cost-display-multi-${boutiqueIndex}-${colisIndex}`);
    const costAmount = document.getElementById(`cost-amount-multi-${boutiqueIndex}-${colisIndex}`);

    console.log(`📊 PARAMÈTRES RÉCUPÉRÉS - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, {
        poidsId,
        modeLivraisonId,
        tempId,
        enginId,
        communeId,
        costDisplay: costDisplay ? 'TROUVÉ' : 'NON TROUVÉ',
        costAmount: costAmount ? 'TROUVÉ' : 'NON TROUVÉ'
    });

    // Vérifier si tous les champs requis sont remplis (même logique que mode normal)
    if (poidsId && modeLivraisonId && enginId && communeId) {
        console.log(`✅ TOUS LES CHAMPS REMPLIS - Boutique ${boutiqueIndex}, Colis ${colisIndex}`);

        // Afficher un indicateur de chargement
        costAmount.textContent = 'Calcul...';
        costDisplay.style.display = 'block';

        const requestData = {
            poids_id: poidsId,
            mode_livraison_id: modeLivraisonId,
            temp_id: tempId,
            engin_id: enginId,
            commune_id: communeId
        };

        console.log(`📤 ENVOI REQUÊTE API - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, requestData);

        // Faire un appel AJAX pour calculer le coût (même logique que mode normal)
        fetch('{{ route("tarifs.calculate-cost") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log(`📥 RÉPONSE HTTP - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            return response.json();
        })
        .then(data => {
            console.log(`📊 RÉPONSE API CALCUL COÛT - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, data);

            if (data.success && data.cost > 0) {
                const formattedCost = new Intl.NumberFormat('fr-FR').format(data.cost);
                costAmount.textContent = formattedCost;
                costDisplay.className = 'alert alert-success d-flex align-items-center';
                console.log(`✅ COÛT AFFICHÉ - Boutique ${boutiqueIndex}, Colis ${colisIndex}: ${formattedCost} FCFA`);
            } else {
                costAmount.textContent = '0';
                costDisplay.className = 'alert alert-warning d-flex align-items-center';
                console.log(`⚠️ COÛT ZÉRO - Boutique ${boutiqueIndex}, Colis ${colisIndex}: ${data.success ? 'Succès mais coût 0' : 'Échec de l\'API'}`);
            }
        })
        .catch(error => {
            console.error(`❌ ERREUR API - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, error);
            costAmount.textContent = 'Erreur';
            costDisplay.className = 'alert alert-danger d-flex align-items-center';
        });
    } else {
        console.log(`⚠️ CHAMPS MANQUANTS - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, {
            poidsId: poidsId ? 'OK' : 'MANQUANT',
            modeLivraisonId: modeLivraisonId ? 'OK' : 'MANQUANT',
            enginId: enginId ? 'OK' : 'MANQUANT',
            communeId: communeId ? 'OK' : 'MANQUANT'
        });
        costDisplay.style.display = 'none';
    }

    console.log(`🏁 FIN CALCUL COÛT MULTI - Boutique ${boutiqueIndex}, Colis ${colisIndex}`);
}

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que les fonctions sont bien définies
    console.log('toggleMultiBoutiquesMode définie:', typeof toggleMultiBoutiquesMode);
    console.log('calculateDeliveryCostMulti définie:', typeof calculateDeliveryCostMulti);

    // Gestion du changement de marchand pour charger les boutiques
    const marchandSelect = document.getElementById('marchand_id');
    if (marchandSelect) {
        marchandSelect.addEventListener('change', function() {
        const marchandId = this.value;
        const boutiqueSelect = document.getElementById('boutique_id');

        if (marchandId) {
            boutiqueSelect.innerHTML = '<option value="">Chargement...</option>';

    fetch(`/colis/boutiques-by-marchand/${marchandId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => response.json())
                .then(data => {
                    boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';
                    if (data.success && data.boutiques) {
                        data.boutiques.forEach(boutique => {
                            boutiqueSelect.innerHTML += `<option value="${boutique.id}">${boutique.libelle}</option>`;
                        });
                    }
                    // Réattacher l'event listener après le rechargement
                    attachBoutiqueEventListener();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    boutiqueSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        } else {
            boutiqueSelect.innerHTML = '<option value="">Sélectionner d\'abord un marchand</option>';
        }
        });
    }

    // Fonction pour charger les boutiques d'un marchand et sélectionner une boutique spécifique
    function loadBoutiquesForMarchand(marchandId, boutiqueId) {
        const boutiqueSelect = document.getElementById('boutique_id');
        if (!boutiqueSelect) return;

        boutiqueSelect.innerHTML = '<option value="">Chargement...</option>';

        fetch(`/colis/boutiques-by-marchand/${marchandId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';
            if (data.success && data.boutiques) {
                data.boutiques.forEach(boutique => {
                    const option = document.createElement('option');
                    option.value = boutique.id;
                    option.textContent = boutique.libelle;
                    if (boutique.id == boutiqueId) {
                        option.selected = true;
                    }
                    boutiqueSelect.appendChild(option);
                });

                console.log('🏪 Boutiques chargées et boutique sélectionnée:', boutiqueId);
                console.log('🏪 Vérification de la sélection boutique dans le DOM:', boutiqueSelect.value);

                // Réattacher l'event listener après le rechargement (avec délai pour éviter les conflits)
                setTimeout(() => {
                    attachBoutiqueEventListener();
                }, 100);

                // Vérifier la complétude du formulaire après un délai
                setTimeout(() => {
                    checkFormCompleteness();
                }, 300);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des boutiques:', error);
            boutiqueSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
    }

    // Fonction pour charger les données d'un ramassage
    function loadRamassageData(ramassageId) {
        fetch(`/api/ramassages/${ramassageId}/colis-data`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('📦 Données reçues du ramassage:', data);
            if (data.success && data.colisData) {
                // Note: Le ramassage ne pré-remplit que les données des colis
                // Les champs marchand et boutique restent indépendants

                // Mettre à jour le nombre de colis
                const nombreColisInput = document.getElementById('nombre_colis');
                if (nombreColisInput) {
                    nombreColisInput.value = data.colisData.length;
                }

                // Vider les formulaires existants
                clearColisForms();

                // Créer les formulaires avec les données du ramassage
                data.colisData.forEach((colisData, index) => {
                    createColisFormWithData(index + 1, colisData);
                });

                // Mettre à jour l'affichage
                updateColisDisplay();

                // Vérifier la complétude du formulaire
                setTimeout(checkFormCompleteness, 100);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données du ramassage:', error);
            alert('Erreur lors du chargement des données du ramassage');
        });
    }

    // Fonction pour créer un formulaire de colis avec des données pré-remplies
    function createColisFormWithData(index, colisData) {
        const colisContainer = document.getElementById('colisFormsContainer');
        if (!colisContainer) {
            console.error('Élément colisFormsContainer non trouvé');
            return;
        }
        const formDiv = document.createElement('div');
        formDiv.className = 'colis-form mb-4';
        formDiv.innerHTML = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Colis ${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeColisForm(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="colis[${index}][nom_client]" value="${colisData.nom_client || colisData.client || ''}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone du client <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+225</span>
                                <input type="tel" class="form-control" name="colis[${index}][telephone_client]" value="${colisData.telephone_client || ''}" placeholder="07 12 34 56 78" required>
                            </div>
                            <div class="form-text">Format: 0707070707 (sans l'indicatif +225)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse du client <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="colis[${index}][adresse_client]" rows="2" required>${colisData.adresse_client || ''}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][commune_id]" required>
                                <option value="">Sélectionner une zone</option>
                                @foreach($communes ?? [] as $commune)
                                    <option value="{{ $commune->id }}">{{ $commune->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Montant à encaisser</label>
                            <input type="number" class="form-control" name="colis[${index}][montant_a_encaisse]" value="${colisData.valeur || ''}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix de vente</label>
                            <input type="number" class="form-control" name="colis[${index}][prix_de_vente]" value="${colisData.valeur || ''}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Numéro de facture</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="colis[${index}][numero_facture]" placeholder="Code généré automatiquement">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillInvoiceNumber(this.previousElementSibling)" title="Générer un nouveau code">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Note client</label>
                            <input type="text" class="form-control" name="colis[${index}][note_client]" value="${colisData.notes || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Type de colis <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][type_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($type_colis ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Conditionnement <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][conditionnement_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($conditionnement_colis ?? [] as $conditionnement)
                                    <option value="{{ $conditionnement->id }}">{{ $conditionnement->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Poids <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][poids_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($poids ?? [] as $poidsItem)
                                    <option value="{{ $poidsItem->id }}">{{ $poidsItem->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Délai <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][delai_id]">
                                <option value="">Sélectionner</option>
                                @foreach($delais ?? [] as $delai)
                                    <option value="{{ $delai->id }}">{{ $delai->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Mode de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][mode_livraison_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($mode_livraisons ?? [] as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Période <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][temp_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($temps ?? [] as $temp)
                                    <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" id="cost-display-${index}" style="display: none;">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>Coût de livraison estimé : <span id="cost-amount-${index}">0</span> FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        colisContainer.appendChild(formDiv);

        // Pré-remplir les sélecteurs avec les données du ramassage
        setTimeout(() => {
            const form = formDiv.querySelector('.card-body');
            if (form) {
                // Commune (Zone de livraison)
                const communeSelect = form.querySelector('select[name*="[commune_id]"]');
                if (communeSelect && colisData.commune_id) {
                    communeSelect.value = colisData.commune_id;
                    console.log('🏘️ Commune pré-remplie:', colisData.commune_id);
                }

                // Type de colis
                const typeSelect = form.querySelector('select[name*="[type_colis_id]"]');
                if (typeSelect && colisData.type_colis_id) {
                    typeSelect.value = colisData.type_colis_id;
                }

                // Conditionnement
                const conditionnementSelect = form.querySelector('select[name*="[conditionnement_colis_id]"]');
                if (conditionnementSelect && colisData.conditionnement_colis_id) {
                    conditionnementSelect.value = colisData.conditionnement_colis_id;
                }

                // Poids
                const poidsSelect = form.querySelector('select[name*="[poids_id]"]');
                if (poidsSelect && colisData.poids_id) {
                    poidsSelect.value = colisData.poids_id;
                }

                // Délai
                const delaiSelect = form.querySelector('select[name*="[delai_id]"]');
                if (delaiSelect && colisData.delai_id) {
                    delaiSelect.value = colisData.delai_id;
                }

                // Mode de livraison
                const modeSelect = form.querySelector('select[name*="[mode_livraison_id]"]');
                if (modeSelect && colisData.mode_livraison_id) {
                    modeSelect.value = colisData.mode_livraison_id;
                }

                // Période
                const periodeSelect = form.querySelector('select[name*="[temp_id]"]');
                if (periodeSelect && colisData.temp_id) {
                    periodeSelect.value = colisData.temp_id;
                }

                // Calculer le coût de livraison après le pré-remplissage
                setTimeout(() => {
                    console.log(`💰 Calcul du coût pour le colis ${index}`);
                    calculateDeliveryCost(index);
                }, 200);
            }
        }, 100);

        // Générer automatiquement le numéro de facture pour ce nouveau formulaire
        setTimeout(() => {
            const invoiceInput = formDiv.querySelector('input[name*="[numero_facture]"]');
            if (invoiceInput && !invoiceInput.value) {
                fillInvoiceNumber(invoiceInput);
            }
        }, 300);

        // Vérifier la complétude du formulaire après création
        setTimeout(checkFormCompleteness, 200);
    }

    // Fonction pour vider les formulaires de colis
    function clearColisForms() {
        console.log('🗑️ clearColisForms() appelée');
        const colisContainer = document.getElementById('colisFormsContainer');
        if (colisContainer) {
            colisContainer.innerHTML = '';
        }
    }

    // Fonction pour mettre à jour l'affichage des colis
    function updateColisDisplay() {
        console.log('📊 updateColisDisplay() appelée');
        const colisContainer = document.getElementById('colisFormsContainer');
        const colisCount = colisContainer ? colisContainer.children.length : 0;

        // Mettre à jour le nombre de colis si nécessaire
        const nombreColisInput = document.getElementById('nombre_colis');
        if (nombreColisInput && colisCount > 0) {
            nombreColisInput.value = colisCount;
        }
    }

    // Gestion du changement de ramassage pour pré-remplir les formulaires
    const ramassageSelect = document.getElementById('ramassage_id');
    if (ramassageSelect) {
        ramassageSelect.addEventListener('change', function() {
            const ramassageId = this.value;
            console.log('🔄 Ramassage sélectionné:', ramassageId);
            if (ramassageId) {
                loadRamassageData(ramassageId);
            } else {
                clearColisForms();
            }
        });
    }



    function createColisForm(index) {
        // Récupérer toutes les communes disponibles depuis le modal
        const allCommuneCheckboxes = document.querySelectorAll('#communeModal .commune-checkbox');
        const allCommunes = Array.from(allCommuneCheckboxes).map(checkbox => ({
            id: checkbox.value,
            libelle: checkbox.nextElementSibling.textContent.trim()
        }));

        // Déterminer la zone par défaut pour ce colis (première commune par défaut)
        const defaultZone = allCommunes[0];

        // Générer les options pour le sélecteur de zone
        const zoneOptions = allCommunes.map(commune =>
            `<option value="${commune.id}" ${commune.id === defaultZone?.id ? 'selected' : ''}>${commune.libelle}</option>`
        ).join('');

        const div = document.createElement('div');
        div.className = 'card mb-3';
        div.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">Colis ${index} - Zone: <span id="zone-display-${index}">${defaultZone?.libelle || 'Non définie'}</span></h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                        <select class="form-select" name="colis[${index}][commune_id]" id="zone-select-${index}" onchange="updateZoneDisplay(${index}); calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner une zone</option>
                            ${zoneOptions}
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="colis[${index}][nom_client]" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone du client <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+225</span>
                            <input type="tel" class="form-control" name="colis[${index}][telephone_client]" placeholder="07 12 34 56 78" required>
                        </div>
                        <div class="form-text">Format: 0707070707 (sans l'indicatif +225)</div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">Adresse du client <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="colis[${index}][adresse_client]" rows="2" required></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="form-label">Montant à encaisser</label>
                        <input type="number" class="form-control" name="colis[${index}][montant_a_encaisse]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix de vente</label>
                        <input type="number" class="form-control" name="colis[${index}][prix_de_vente]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Numéro de facture</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="colis[${index}][numero_facture]" placeholder="Code généré automatiquement">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillInvoiceNumber(this.previousElementSibling)" title="Générer un nouveau code">
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Note client</label>
                        <input type="text" class="form-control" name="colis[${index}][note_client]">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label class="form-label">Type de colis <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][type_colis_id]">
                            <option value="">Sélectionner</option>
                            @foreach($type_colis ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Conditionnement <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][conditionnement_colis_id]">
                            <option value="">Sélectionner</option>
                            @foreach($conditionnement_colis ?? [] as $conditionnement)
                                <option value="{{ $conditionnement->id }}">{{ $conditionnement->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Poids <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][poids_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($poids ?? [] as $poidsItem)
                                <option value="{{ $poidsItem->id }}">{{ $poidsItem->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Délai <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][delai_id]">
                            <option value="">Sélectionner</option>
                            @foreach($delais ?? [] as $delai)
                                <option value="{{ $delai->id }}">{{ $delai->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mode de livraison <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][mode_livraison_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($mode_livraisons ?? [] as $mode)
                                <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Période <span class="text-danger">*</span></label>
                        <select required class="form-select" name="colis[${index}][temp_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($temps ?? [] as $temp)
                                <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex align-items-center" id="cost-display-${index}" style="display: none;">
                            <i class="fas fa-calculator me-2"></i>
                            <strong>Coût de livraison estimé : <span id="cost-amount-${index}">0</span> FCFA</strong>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Générer automatiquement le numéro de facture pour ce nouveau formulaire
        setTimeout(() => {
            const invoiceInput = div.querySelector('input[name*="[numero_facture]"]');
            if (invoiceInput && !invoiceInput.value) {
                fillInvoiceNumber(invoiceInput);
            }
        }, 100);

        return div;
    }


    // Fonction pour gérer le clic sur le label d'une commune
    function handleLabelClick(checkboxId) {
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            // Empêcher le comportement par défaut du label
            event.preventDefault();
            // Inverser l'état de la checkbox
            checkbox.checked = !checkbox.checked;
            // Déclencher l'événement onchange pour mettre à jour le compteur
            updateCommuneSelectionCounter();
        }
    }


    // Fonction pour vérifier si le nombre de colis est renseigné
    function checkFormValidity() {
        const nombreColis = document.getElementById('nombre_colis')?.value || '';
        const generateBtn = document.getElementById('generateColisForms');

        if (generateBtn) {
            if (nombreColis && parseInt(nombreColis) > 0) {
                generateBtn.disabled = false;
                generateBtn.classList.remove('btn-outline-primary');
                generateBtn.classList.add('btn-primary');
            } else {
                generateBtn.disabled = true;
                generateBtn.classList.remove('btn-primary');
                generateBtn.classList.add('btn-outline-primary');
            }
        }
    }



    // Fonction pour ajouter les écouteurs de validation du formulaire
    function addFormValidationListeners() {
        // Écouter les changements sur tous les champs requis
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');

        requiredFields.forEach(field => {
            field.addEventListener('input', checkFormCompleteness);
            field.addEventListener('change', checkFormCompleteness);
        });

        // Vérifier aussi les communes sélectionnées
        const communeCheckboxes = document.querySelectorAll('.commune-checkbox');
        communeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', checkFormCompleteness);
        });
    }

    // Fonction pour générer les formulaires de colis
    function generateColisForms() {
        const nombreColis = parseInt(document.getElementById('nombre_colis').value) || 0;
        const container = document.getElementById('colisFormsContainer');

        if (nombreColis <= 0) {
            alert('Veuillez saisir un nombre de colis valide (1-20)');
            return;
        }

        // Compter les formulaires existants
        const existingForms = container.children.length;
        console.log('📊 Formulaires existants:', existingForms);
        console.log('📊 Nombre de colis demandé:', nombreColis);

        // Si des formulaires existent déjà, ajouter seulement les nouveaux
        if (existingForms > 0) {
            const formsToAdd = nombreColis - existingForms;
            console.log('📊 Formulaires à ajouter:', formsToAdd);

            if (formsToAdd <= 0) {
                alert(`Vous avez déjà ${existingForms} formulaire(s). Le nombre de colis doit être supérieur à ${existingForms} pour ajouter de nouveaux formulaires.`);
                return;
            }

            // Ajouter seulement les nouveaux formulaires
            for (let i = existingForms + 1; i <= nombreColis; i++) {
                const formDiv = createColisForm(i);
                container.appendChild(formDiv);
                console.log(`✅ Formulaire ${i} ajouté`);
            }
        } else {
            // Aucun formulaire existant, générer tous les formulaires
            console.log('📊 Génération de tous les formulaires');
            for (let i = 1; i <= nombreColis; i++) {
                const formDiv = createColisForm(i);
                container.appendChild(formDiv);
                console.log(`✅ Formulaire ${i} créé`);
            }
        }

        // Afficher le conteneur
        container.style.display = 'block';

        // Ajouter des écouteurs d'événements pour vérifier la validité du formulaire
        addFormValidationListeners();

        // Vérifier la validité initiale
        setTimeout(checkFormCompleteness, 100);

        // Mettre à jour le nombre de colis dans le champ
        const nombreColisInput = document.getElementById('nombre_colis');
        if (nombreColisInput) {
            nombreColisInput.value = container.children.length;
            console.log('📊 Nombre de colis mis à jour:', container.children.length);
        }
    }

    // Ajouter l'événement click au bouton
    const generateBtn = document.getElementById('generateColisForms');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateColisForms);
    }

    // Ajouter un écouteur d'événement pour le nombre de colis
    const nombreColisInput = document.getElementById('nombre_colis');

    if (nombreColisInput) {
        nombreColisInput.addEventListener('input', checkFormValidity);
        nombreColisInput.addEventListener('change', checkFormValidity);
    }

    // Ajouter des écouteurs pour les champs principaux
    const boutiqueSelect = document.getElementById('boutique_id');
    const livreurSelect = document.getElementById('livreur_id');
    const enginSelect = document.getElementById('engin_id');

    if (marchandSelect) {
        marchandSelect.addEventListener('change', checkFormCompleteness);
    }
    // Fonction pour attacher l'event listener au sélecteur de boutique
    function attachBoutiqueEventListener() {
        const boutiqueSelect = document.getElementById('boutique_id');
        if (boutiqueSelect) {
            // Supprimer l'ancien event listener s'il existe
            boutiqueSelect.removeEventListener('change', handleBoutiqueChange);
            // Ajouter le nouvel event listener
            boutiqueSelect.addEventListener('change', handleBoutiqueChange);
        }
    }

    // Fonction pour gérer le changement de boutique
    function handleBoutiqueChange() {
        checkFormCompleteness();
        // Charger les ramassages pour la boutique sélectionnée
        const boutiqueId = this.value;

        if (boutiqueId) {
            // Vérifier si un ramassage est déjà sélectionné
            const ramassageSelect = document.getElementById('ramassage_id');
            const selectedRamassageId = ramassageSelect.value;

            if (selectedRamassageId) {
                return; // Ne pas recharger si un ramassage est déjà sélectionné
            }

            loadRamassagesByBoutique(boutiqueId);
        } else {
            // Réinitialiser les ramassages
            const ramassageSelect = document.getElementById('ramassage_id');
            if (ramassageSelect) {
                ramassageSelect.innerHTML = '<option value="">Sélectionner d\'abord une boutique</option>';
                // Réinitialiser le texte informatif
                updateRamassageInfo(0);
            }
        }
    }

    // Fonction pour générer un code de facture de 10 caractères alphanumériques
    function generateInvoiceCode() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';

        for (let i = 0; i < 10; i++) {
            code += characters.charAt(Math.floor(Math.random() * characters.length));
        }

        return code;
    }

    // Fonction pour remplir automatiquement le champ numéro de facture
    function fillInvoiceNumber(inputElement) {
        if (inputElement) {
            inputElement.value = generateInvoiceCode();
        }
    }

    // Rendre les fonctions accessibles globalement
    window.fillInvoiceNumber = fillInvoiceNumber;
    window.generateInvoiceCode = generateInvoiceCode;

    // Fonction pour générer le numéro de facture pour tous les champs existants
    function generateInvoiceNumbersForAllColis() {
        const invoiceInputs = document.querySelectorAll('input[name*="[numero_facture]"]');
        invoiceInputs.forEach(input => {
            if (!input.value) { // Seulement si le champ est vide
                fillInvoiceNumber(input);
            }
        });
    }

    // Générer automatiquement les numéros de facture au chargement de la page
    generateInvoiceNumbersForAllColis();

    // Attacher l'event listener initial
    attachBoutiqueEventListener();
    if (livreurSelect) {
        livreurSelect.addEventListener('change', checkFormCompleteness);
    }
    if (enginSelect) {
        enginSelect.addEventListener('change', checkFormCompleteness);
    }


    // Vérifier la validité initiale au chargement de la page
    setTimeout(checkFormValidity, 100);
});

// Fonction pour vérifier si le formulaire est complet
function checkFormCompleteness() {
    console.log('🔍 checkFormCompleteness() appelée');
    const submitBtn = document.getElementById('submitBtn');
    if (!submitBtn) {
        console.log('❌ submitBtn non trouvé');
        return;
    }

    // Vérifier les champs principaux
    const marchandId = document.getElementById('marchand_id')?.value;
    const boutiqueId = document.getElementById('boutique_id')?.value;
    const livreurId = document.getElementById('livreur_id')?.value;
    const enginId = document.getElementById('engin_id')?.value;

    console.log('🔍 DEBUG - Vérification des champs principaux:');
    console.log('  - marchandId:', marchandId);
    console.log('  - boutiqueId:', boutiqueId);
    console.log('  - livreurId:', livreurId, '(optionnel)');
    console.log('  - enginId:', enginId, '(optionnel)');

    // Vérifier les champs des colis
    const colisForms = document.querySelectorAll('#colisFormsContainer .card');
    console.log('🔍 DEBUG - Nombre de formulaires de colis:', colisForms.length);

    let allColisValid = true;

    colisForms.forEach((form, index) => {
        const nomClient = form.querySelector('input[name*="[nom_client]"]')?.value;
        const telephoneClient = form.querySelector('input[name*="[telephone_client]"]')?.value;
        const adresseClient = form.querySelector('textarea[name*="[adresse_client]"]')?.value;
        const communeId = form.querySelector('select[name*="[commune_id]"]')?.value;

        console.log(`🔍 DEBUG - Colis ${index + 1}:`);
        console.log('  - nomClient:', nomClient);
        console.log('  - telephoneClient:', telephoneClient);
        console.log('  - adresseClient:', adresseClient);
        console.log('  - communeId:', communeId);

        if (!nomClient || !telephoneClient || !adresseClient || !communeId) {
            allColisValid = false;
            console.log(`❌ Colis ${index + 1} invalide - champs manquants`);
        } else {
            console.log(`✅ Colis ${index + 1} valide`);
        }
    });

    console.log('🔍 DEBUG - Résultats:');
    console.log('  - allColisValid:', allColisValid);
    console.log('  - colisForms.length > 0:', colisForms.length > 0);
    console.log('  - marchandId && boutiqueId:', !!(marchandId && boutiqueId));

    // Activer le bouton seulement si les champs obligatoires sont remplis
    // Note: livreur_id et engin_id sont optionnels
    const shouldEnable = marchandId && boutiqueId && allColisValid && colisForms.length > 0;
    console.log('🔍 DEBUG - shouldEnable:', shouldEnable);

    if (shouldEnable) {
        submitBtn.disabled = false;
        console.log('✅ Bouton activé');
    } else {
        submitBtn.disabled = true;
        console.log('❌ Bouton désactivé');
    }
}

// Fonction pour calculer le coût de livraison
function calculateDeliveryCost(index) {
    console.log(`🔍 calculateDeliveryCost appelée pour l'index ${index}`);

    const poidsSelect = document.querySelector(`select[name="colis[${index}][poids_id]"]`);
    const modeSelect = document.querySelector(`select[name="colis[${index}][mode_livraison_id]"]`);
    const tempSelect = document.querySelector(`select[name="colis[${index}][temp_id]"]`);
    const communeSelect = document.querySelector(`select[name="colis[${index}][commune_id]"]`);
    const enginSelect = document.getElementById('engin_id');

    console.log(`🔍 Éléments trouvés:`, {
        poidsSelect: !!poidsSelect,
        modeSelect: !!modeSelect,
        tempSelect: !!tempSelect,
        communeSelect: !!communeSelect,
        enginSelect: !!enginSelect
    });

    const poidsId = poidsSelect ? poidsSelect.value : '';
    const modeLivraisonId = modeSelect ? modeSelect.value : '';
    const tempId = tempSelect ? tempSelect.value : '';
    const enginId = enginSelect ? enginSelect.value : '';
    const communeId = communeSelect ? communeSelect.value : '';

    console.log(`🔍 Valeurs récupérées:`, {
        poidsId,
        modeLivraisonId,
        tempId,
        enginId,
        communeId
    });

    const costDisplay = document.getElementById(`cost-display-${index}`);
    const costAmount = document.getElementById(`cost-amount-${index}`);

    // Vérifier si tous les champs requis sont remplis (enginId est optionnel)
    if (poidsId && modeLivraisonId && communeId) {
        // Afficher un indicateur de chargement
        costAmount.textContent = 'Calcul...';
        costDisplay.style.display = 'block';

        // Faire un appel AJAX pour calculer le coût
        fetch('{{ route("tarifs.calculate-cost") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                poids_id: poidsId,
                mode_livraison_id: modeLivraisonId,
                temp_id: tempId,
                engin_id: enginId,
                commune_id: communeId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cost > 0) {
                costAmount.textContent = new Intl.NumberFormat('fr-FR').format(data.cost);
                costDisplay.className = 'alert alert-success d-flex align-items-center';
            } else {
                costAmount.textContent = '0';
                costDisplay.className = 'alert alert-warning d-flex align-items-center';
            }
            // Mettre à jour le résumé des coûts
            updateCostSummary();
        })
        .catch(error => {
            console.error('Erreur lors du calcul du coût:', error);
            costAmount.textContent = 'Erreur';
            costDisplay.className = 'alert alert-danger d-flex align-items-center';
        });
    } else {
        costDisplay.style.display = 'none';
    }
}

// Fonction pour recalculer tous les coûts (quand l'engin change)
function recalculateAllCosts() {
    const colisForms = document.querySelectorAll('#colisFormsContainer .card');
    colisForms.forEach((form, index) => {
        const colisIndex = index + 1; // Les colis commencent à 1
        calculateDeliveryCost(colisIndex);
    });
    // Mettre à jour le résumé après un délai pour laisser le temps aux calculs
    setTimeout(updateCostSummary, 500);
}

// Fonction pour mettre à jour le résumé des coûts
function updateCostSummary() {
    const colisForms = document.querySelectorAll('#colisFormsContainer .card');
    const costSummary = document.getElementById('costSummary');
    const totalColis = document.getElementById('totalColis');
    const averageCost = document.getElementById('averageCost');
    const totalCost = document.getElementById('totalCost');

    if (colisForms.length === 0) {
        costSummary.style.display = 'none';
        return;
    }

    let totalCostValue = 0;
    let validCosts = 0;

    colisForms.forEach((form, index) => {
        const colisIndex = index + 1;
        const costAmount = document.getElementById(`cost-amount-${colisIndex}`);
        const costDisplay = document.getElementById(`cost-display-${colisIndex}`);

        if (costAmount && costDisplay && costDisplay.style.display !== 'none') {
            const costText = costAmount.textContent;
            if (costText && costText !== 'Calcul...' && costText !== 'Erreur' && costText !== '0') {
                const cost = parseFloat(costText.replace(/\s/g, '').replace(',', ''));
                if (!isNaN(cost)) {
                    totalCostValue += cost;
                    validCosts++;
                }
            }
        }
    });

    if (validCosts > 0) {
        totalColis.textContent = colisForms.length;
        averageCost.textContent = new Intl.NumberFormat('fr-FR').format(Math.round(totalCostValue / validCosts));
        totalCost.textContent = new Intl.NumberFormat('fr-FR').format(totalCostValue) + ' FCFA';
        costSummary.style.display = 'block';
    } else {
        costSummary.style.display = 'none';
    }
}

// ======================================
// FONCTIONS MULTI-BOUTIQUES
// ======================================

// Variables globales pour multi-boutiques
let selectedBoutiques = [];
let boutiquesData = {};

// Fonction pour afficher les alertes de validation
function showValidationAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();

    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="ti ti-${type === 'danger' ? 'alert-circle' : type === 'warning' ? 'alert-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-dismiss après 5 secondes
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Fonction pour créer le conteneur d'alertes
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    document.body.appendChild(container);
    return container;
}



// Fonction pour charger les boutiques en mode multi
function loadBoutiquesForMultiMode() {
    const marchandId = document.getElementById('multiBoutiquesMarchandId').value;
    const boutiquesContainer = document.getElementById('boutiquesMultiSelection');

    if (!marchandId) {
        resetMultiBoutiquesForm();
        return;
    }

    boutiquesContainer.innerHTML = '<div class="col-12"><div class="text-center"><i class="ti ti-loader fa-spin"></i> Chargement des boutiques...</div></div>';

    fetch(`{{ url('/colis/boutiques-by-marchand') }}/${marchandId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.boutiques.length > 0) {
                let html = '<div class="col-12 mb-3"><p class="text-muted">Sélectionnez une ou plusieurs boutiques (maximum 10) :</p></div>';

                data.boutiques.forEach(boutique => {
                    html += `
                        <div class="col-md-4 mb-3">
                            <div class="card border-secondary">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input boutique-checkbox" type="checkbox"
                                               value="${boutique.id}" id="boutique_${boutique.id}"
                                               onchange="handleBoutiqueSelection()">
                                        <label class="form-check-label" for="boutique_${boutique.id}">
                                            <strong>${boutique.libelle}</strong><br>
                                            <small class="text-muted">${boutique.adresse || 'Pas d\'adresse'}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                boutiquesContainer.innerHTML = html;
            } else {
                boutiquesContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="ti ti-alert-triangle me-2"></i>
                            Aucune boutique trouvée pour ce marchand
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des boutiques:', error);
            boutiquesContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="ti ti-alert-circle me-2"></i>
                        Erreur lors du chargement des boutiques
                    </div>
                </div>
            `;
        });
}

// Fonction pour charger les ramassages par boutique
function loadRamassagesByBoutique(boutiqueId) {
    const ramassageSelect = document.getElementById('ramassage_id');

    if (!boutiqueId) {
        ramassageSelect.innerHTML = '<option value="">Sélectionner d\'abord une boutique</option>';
        return;
    }

    ramassageSelect.innerHTML = '<option value="">Chargement des ramassages...</option>';

    fetch(`/api/ramassages/by-boutique/${boutiqueId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.ramassages.length > 0) {
            ramassageSelect.innerHTML = '<option value="">Sélectionner un ramassage</option>';
            data.ramassages.forEach(ramassage => {
                const optionText = `${ramassage.code_ramassage}`;
                ramassageSelect.innerHTML += `<option value="${ramassage.id}">${optionText}</option>`;
            });
            // Mettre à jour le texte informatif
            updateRamassageInfo(data.ramassages.length);
        } else {
            ramassageSelect.innerHTML = '<option value="">Aucun ramassage disponible pour cette boutique</option>';
            // Mettre à jour le texte informatif
            updateRamassageInfo(0);
        }
    })
    .catch(error => {
        console.error('Erreur lors du chargement des ramassages:', error);
        ramassageSelect.innerHTML = '<option value="">Erreur de chargement</option>';
    });
}

// Fonction pour mettre à jour le texte informatif des ramassages
function updateRamassageInfo(count) {
    const ramassageSelect = document.getElementById('ramassage_id');
    if (ramassageSelect) {
        const infoElement = ramassageSelect.parentElement.querySelector('.form-text');
        if (infoElement) {
            if (count > 0) {
                infoElement.textContent = `${count} ramassage(s) disponible(s) pour cette boutique`;
                infoElement.className = 'form-text text-success';
            } else {
                infoElement.textContent = 'Aucun ramassage disponible pour cette boutique';
                infoElement.className = 'form-text text-muted';
            }
        }
    }
}

// Fonction pour gérer la sélection des boutiques
function handleBoutiqueSelection() {
    const checkboxes = document.querySelectorAll('.boutique-checkbox:checked');
    selectedBoutiques = Array.from(checkboxes).map(cb => cb.value);

    if (selectedBoutiques.length > 10) {
        showValidationAlert('Vous ne pouvez sélectionner que 10 boutiques maximum', 'warning');
        checkboxes[checkboxes.length - 1].checked = false;
        selectedBoutiques = selectedBoutiques.slice(0, 10);
        return;
    }

    if (selectedBoutiques.length > 0) {
        showBoutiquesConfiguration();
        // Charger les ramassages pour la première boutique sélectionnée
        if (selectedBoutiques.length === 1) {
            loadRamassagesByBoutique(selectedBoutiques[0]);
        }
    } else {
        hideBoutiquesConfiguration();
        // Réinitialiser les ramassages
        const ramassageSelect = document.getElementById('ramassage_id');
        if (ramassageSelect) {
            ramassageSelect.innerHTML = '<option value="">Sélectionner d\'abord une boutique</option>';
        }
    }
}

// Fonction pour afficher la configuration des boutiques
function showBoutiquesConfiguration() {
    const configSection = document.getElementById('boutiquesColisConfiguration');
    const configContainer = document.getElementById('boutiquesConfigContainer');

    configSection.style.display = 'block';

    let html = '';

    selectedBoutiques.forEach((boutiqueId, index) => {
        const checkbox = document.getElementById(`boutique_${boutiqueId}`);
        const boutiqueName = checkbox.nextElementSibling.querySelector('strong').textContent;

        html += `
            <div class="card border-primary mb-4" id="boutiqueConfig_${boutiqueId}">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 text-white">
                        <i class="ti ti-building-store me-2"></i>
                        Boutique: ${boutiqueName}
                        <span class="badge bg-light text-primary ms-2">${index + 1}/${selectedBoutiques.length}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Nombre de colis <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${index}][nombre_colis]"
                                    onchange="generateColisFormsForBoutique(${boutiqueId}, ${index}, this.value)" required>
                                <option value="">Choisir</option>
                                ${Array.from({length: 20}, (_, i) => i + 1).map(num =>
                                    `<option value="${num}">${num} colis</option>`
                                ).join('')}
                            </select>
                            <input type="hidden" name="boutiques[${index}][boutique_id]" value="${boutiqueId}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Livreur</label>
                            <select class="form-select" name="boutiques[${index}][livreur_id]" onchange="handleLivreurChangeMulti(${index}, this.value)">
                                <option value="">Sélectionner un livreur</option>
                                @foreach($livreurs as $livreur)
                                    <option value="{{ $livreur->id }}">{{ $livreur->prenom }} {{ $livreur->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Engin du livreur</label>
                            <select class="form-select" name="boutiques[${index}][engin_id]" id="engin_id_${index}" disabled onchange="recalculateAllCostsMulti(${index})">
                                <option value="">Sélectionnez d'abord un livreur</option>
                            </select>
                            <div id="livreur-engin-info-${index}" class="mt-1" style="display: none;">
                                <small class="text-success">
                                    <i class="ti ti-check-circle me-1"></i>
                                    <span id="livreur-engin-text-${index}"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div id="boutique_${boutiqueId}_colis_forms" class="colis-forms-container">
                        <!-- Les formulaires de colis seront générés ici -->
                    </div>
                </div>
            </div>
        `;
    });

    configContainer.innerHTML = html;
    document.getElementById('submitMultiBoutiquesBtn').style.display = 'inline-block';
}

// Fonction pour cacher la configuration des boutiques
function hideBoutiquesConfiguration() {
    document.getElementById('boutiquesColisConfiguration').style.display = 'none';
    document.getElementById('submitMultiBoutiquesBtn').style.display = 'none';
}

// Fonction pour générer les formulaires de colis pour une boutique
function generateColisFormsForBoutique(boutiqueId, boutiqueIndex, nombreColis) {
    const container = document.getElementById(`boutique_${boutiqueId}_colis_forms`);

    if (!nombreColis || nombreColis <= 0) {
        container.innerHTML = '';
        return;
    }

    // Récupérer toutes les communes disponibles depuis le modal
    const allCommuneCheckboxes = document.querySelectorAll('#communeModal .commune-checkbox');
    const allCommunes = Array.from(allCommuneCheckboxes).map(checkbox => ({
        id: checkbox.value,
        libelle: checkbox.nextElementSibling.textContent.trim()
    }));

    let html = `
        <div class="alert alert-success mb-3">
            <i class="ti ti-check-circle me-2"></i>
            ${nombreColis} formulaire(s) de colis généré(s) - Sélectionnez la zone de livraison pour chaque colis
        </div>
    `;

    for (let i = 1; i <= nombreColis; i++) {
        // Zone par défaut (première commune)
        const defaultZone = allCommunes[0];

        // Générer les options pour le sélecteur de zone
        const zoneOptions = allCommunes.map(commune =>
            `<option value="${commune.id}" ${commune.id === defaultZone?.id ? 'selected' : ''}>${commune.libelle}</option>`
        ).join('');

        html += `
            <div class="card border-light mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="ti ti-package me-2"></i>
                        Colis ${i} - Zone: <span id="zone-display-multi-${boutiqueId}-${i}">${defaultZone?.libelle || 'Non définie'}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][commune_id]" id="zone-select-multi-${boutiqueId}-${i}" onchange="updateZoneDisplayMulti('${boutiqueId}', ${i}); calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner une zone</option>
                                ${zoneOptions}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][nom_client]" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][telephone_client]" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Adresse de livraison <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][adresse_client]" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Montant à encaisser</label>
                            <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][montant_a_encaisse]" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix de vente</label>
                            <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][prix_de_vente]" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° Facture</label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][numero_facture]">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Poids</label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][poids_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($poids as $poid)
                                    <option value="{{ $poid->id }}">{{ $poid->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode livraison <span class="text-danger">*</span></label>
                            <select required class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][mode_livraison_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($mode_livraisons as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Période <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][temp_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($temps ?? [] as $temp)
                                    <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type de colis <span class="text-danger">*</span></label>
                            <select required class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][type_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($type_colis as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Note client</label>
                            <textarea class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][note_client]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" id="cost-display-multi-${boutiqueIndex}-${i}" style="display: none;">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>Coût de livraison estimé : <span id="cost-amount-multi-${boutiqueIndex}-${i}">0</span> FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

// Fonction pour afficher le modal de sélection des communes pour une boutique
function showCommuneModal(boutiqueId, boutiqueIndex) {
    // Réutiliser le modal existant mais l'adapter pour multi-boutiques
    currentBoutiqueId = boutiqueId;
    currentBoutiqueIndex = boutiqueIndex;

    // Réinitialiser les sélections
    document.querySelectorAll('.commune-checkbox').forEach(cb => cb.checked = false);

    // Initialiser le compteur
    updateCommuneSelectionCounter();

    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('communeModal'));
    modal.show();

    // Modifier le titre du modal
    document.querySelector('#communeModal .modal-title').textContent =
        `Sélectionner les zones de livraison - Boutique ${boutiqueIndex + 1}`;
}

// Fonction pour mettre à jour le compteur de sélection des communes
function updateCommuneSelectionCounter() {
    const selectedCheckboxes = document.querySelectorAll('.commune-checkbox:checked');
    const selectedCount = selectedCheckboxes.length;
    const selectionText = document.getElementById('communeSelectionText');
    const selectionInfo = document.getElementById('communeSelectionInfo');

    if (currentBoutiqueId !== null && currentBoutiqueIndex !== null) {
        const nombreColis = document.querySelector(`select[name="boutiques[${currentBoutiqueIndex}][nombre_colis]"]`).value;

        if (nombreColis) {
            const requiredCount = parseInt(nombreColis);
            selectionText.textContent = `Sélectionné: ${selectedCount} commune(s) pour ${requiredCount} colis (répartition automatique)`;

            // Changer la couleur de l'alerte selon le statut
            if (selectedCount === 0) {
                selectionInfo.className = 'alert alert-info d-flex align-items-center';
            } else if (selectedCount > 0) {
                selectionInfo.className = 'alert alert-success d-flex align-items-center';
            }
        } else {
            selectionText.textContent = `Sélectionné: ${selectedCount} commune(s)`;
            selectionInfo.className = 'alert alert-info d-flex align-items-center';
        }
    } else {
        selectionText.textContent = `Sélectionné: ${selectedCount} commune(s)`;
        selectionInfo.className = 'alert alert-info d-flex align-items-center';
    }
}

// Variables pour le modal multi-boutiques
let currentBoutiqueId = null;
let currentBoutiqueIndex = null;

// Fonction modifiée pour confirmer la sélection des communes (mode multi-boutiques)
function confirmCommuneSelectionForBoutique() {
    if (currentBoutiqueId === null || currentBoutiqueIndex === null) {
        return;
    }

    const selectedCheckboxes = document.querySelectorAll('.commune-checkbox:checked');
    const selectedCommunes = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
    const nombreColis = document.querySelector(`select[name="boutiques[${currentBoutiqueIndex}][nombre_colis]"]`).value;

    if (selectedCommunes.length === 0) {
        showValidationAlert('Veuillez sélectionner au moins une commune', 'warning');
        return;
    }

    // Note: Plusieurs colis peuvent maintenant être livrés dans la même commune
    // Le système répartira automatiquement les colis dans les communes sélectionnées

    // Mettre à jour les champs cachés
    document.getElementById(`boutique_${currentBoutiqueId}_communes_selected`).value = selectedCommunes.join(',');
    document.getElementById(`boutique_${currentBoutiqueId}_zones_count`).textContent =
        `${selectedCommunes.length} zone(s) sélectionnée(s)`;

    // Générer les formulaires de colis
    if (nombreColis) {
        generateColisFormsForBoutiqueWithCommunes(currentBoutiqueId, currentBoutiqueIndex, nombreColis, selectedCommunes);
    }

    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('communeModal'));
    modal.hide();

    // Réinitialiser les variables
    currentBoutiqueId = null;
    currentBoutiqueIndex = null;
}

// Fonction pour générer les formulaires de colis avec les communes sélectionnées
function generateColisFormsForBoutiqueWithCommunes(boutiqueId, boutiqueIndex, nombreColis, selectedCommunes) {
    const container = document.getElementById(`boutique_${boutiqueId}_colis_forms`);

    let html = `
        <div class="alert alert-success mb-3">
            <i class="ti ti-check-circle me-2"></i>
            ${nombreColis} formulaire(s) de colis généré(s) pour ${selectedCommunes.length} zone(s) de livraison
        </div>
    `;

    for (let i = 1; i <= nombreColis; i++) {
        // Répartition automatique : utiliser le modulo pour répartir les colis dans les communes
        const communeIndex = (i - 1) % selectedCommunes.length;
        const defaultCommuneId = selectedCommunes[communeIndex];

        // Récupérer le nom de la commune par défaut depuis le DOM
        const communeCheckbox = document.querySelector(`input[value="${defaultCommuneId}"]`);
        const communeLabel = communeCheckbox ? communeCheckbox.nextElementSibling : null;
        const defaultCommuneName = communeLabel ? communeLabel.textContent.trim() : `Zone ${defaultCommuneId}`;

        // Générer les options pour le sélecteur de zone
        const zoneOptions = selectedCommunes.map(communeId => {
            const checkbox = document.querySelector(`input[value="${communeId}"]`);
            const label = checkbox ? checkbox.nextElementSibling : null;
            const communeName = label ? label.textContent.trim() : `Zone ${communeId}`;
            const isSelected = communeId === defaultCommuneId ? 'selected' : '';
            return `<option value="${communeId}" ${isSelected}>${communeName}</option>`;
        }).join('');

        html += `
            <div class="card border-light mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="ti ti-package me-2"></i>
                        Colis ${i} - Zone: <span id="zone-display-multi-${boutiqueId}-${i}">${defaultCommuneName}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][commune_id]" id="zone-select-multi-${boutiqueId}-${i}" onchange="updateZoneDisplayMulti('${boutiqueId}', ${i}); calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner une zone</option>
                                ${zoneOptions}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][nom_client]" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][telephone_client]" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Adresse de livraison <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][adresse_client]" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Montant à encaisser</label>
                            <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][montant_a_encaisse]" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix de vente</label>
                            <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][prix_de_vente]" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">N° Facture</label>
                            <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][numero_facture]">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Poids <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][poids_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($poids as $poid)
                                    <option value="{{ $poid->id }}">{{ $poid->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][mode_livraison_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($mode_livraisons as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Période <span class="text-danger">*</span></label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][temp_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${i})" required>
                                <option value="">Sélectionner</option>
                                @foreach($temps ?? [] as $temp)
                                    <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type de colis</label>
                            <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${i}][type_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($type_colis as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Note client</label>
                            <textarea class="form-control" name="boutiques[${boutiqueIndex}][colis][${i}][note_client]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" id="cost-display-multi-${boutiqueIndex}-${i}" style="display: none;">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>Coût de livraison estimé : <span id="cost-amount-multi-${boutiqueIndex}-${i}">0</span> FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}


// Fonction pour filtrer les communes dans le modal
function filterCommunes() {
    const searchTerm = document.getElementById('communeSearch').value.toLowerCase();
    const communeItems = document.querySelectorAll('.commune-item');

    communeItems.forEach(item => {
        const communeName = item.getAttribute('data-commune-name');
        if (communeName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Fonction pour confirmer la sélection des communes (compatible multi-mode et mode normal)
function confirmCommuneSelectionMultiMode() {
    // Si on est en mode multi-boutiques
    if (currentBoutiqueId !== null && currentBoutiqueIndex !== null) {
        confirmCommuneSelectionForBoutique();
        return;
    }

    // Sinon, mode normal (fonction existante à implémenter si nécessaire)
    const selectedCheckboxes = document.querySelectorAll('.commune-checkbox:checked');
    const selectedCommunes = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);

    if (selectedCommunes.length === 0) {
        showValidationAlert('Veuillez sélectionner au moins une commune', 'warning');
        return;
    }

    // Pour le mode normal, on peut implémenter la logique ici si nécessaire
    // Pour l'instant, on ferme juste le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('communeModal'));
    modal.hide();
}

// Gestion des engins du livreur (mode normal)
document.addEventListener('DOMContentLoaded', function() {
    // Données des livreurs avec leurs engins
    const livreursData = @json($livreurs);

    const livreurSelect = document.getElementById('livreur_id');
    const enginSelect = document.getElementById('engin_id');
    const livreurEnginInfo = document.getElementById('livreur-engin-info');
    const livreurEnginText = document.getElementById('livreur-engin-text');

    // Gestion du changement de livreur
    if (livreurSelect) {
        livreurSelect.addEventListener('change', function() {
            const selectedLivreurId = this.value;

            if (selectedLivreurId) {
                const livreur = livreursData.find(l => l.id == selectedLivreurId);

                if (livreur && livreur.engin) {
                    // Vider le dropdown et ajouter uniquement l'engin du livreur
                    enginSelect.innerHTML = '';
                    const option = document.createElement('option');
                    option.value = livreur.engin.id;
                    option.textContent = `${livreur.engin.libelle}${livreur.engin.type_engin ? ' (' + livreur.engin.type_engin.libelle + ')' : ''}`;
                    enginSelect.appendChild(option);

                    // Activer le dropdown
                    enginSelect.disabled = false;

                    // Afficher l'info
                    livreurEnginText.textContent = `Engin assigné à ce livreur`;
                    livreurEnginInfo.style.display = 'block';
                } else {
                    // Pas d'engin assigné au livreur
                    enginSelect.innerHTML = '<option value="">Ce livreur n\'a pas d\'engin assigné</option>';
                    enginSelect.disabled = true;
                    livreurEnginInfo.style.display = 'none';
                }
            } else {
                // Réinitialiser si pas de livreur sélectionné
                enginSelect.innerHTML = '<option value="">Sélectionnez d\'abord un livreur</option>';
                enginSelect.disabled = true;
                livreurEnginInfo.style.display = 'none';
            }
        });
    }
});

// Fonction pour gérer le changement de livreur en mode multi-boutiques
function handleLivreurChangeMulti(index, livreurId) {
    const livreursData = @json($livreurs);
    const enginSelect = document.getElementById(`engin_id_${index}`);
    const livreurEnginInfo = document.getElementById(`livreur-engin-info-${index}`);
    const livreurEnginText = document.getElementById(`livreur-engin-text-${index}`);

    if (livreurId) {
        const livreur = livreursData.find(l => l.id == livreurId);

        if (livreur && livreur.engin) {
            // Vider le dropdown et ajouter uniquement l'engin du livreur
            enginSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = livreur.engin.id;
            option.textContent = `${livreur.engin.libelle}${livreur.engin.type_engin ? ' (' + livreur.engin.type_engin.libelle + ')' : ''}`;
            enginSelect.appendChild(option);

            // Activer le dropdown
            enginSelect.disabled = false;

            // Afficher l'info
            livreurEnginText.textContent = `Engin assigné à ce livreur`;
            livreurEnginInfo.style.display = 'block';
        } else {
            // Pas d'engin assigné au livreur
            enginSelect.innerHTML = '<option value="">Ce livreur n\'a pas d\'engin assigné</option>';
            enginSelect.disabled = true;
            livreurEnginInfo.style.display = 'none';
        }
    } else {
        // Réinitialiser si pas de livreur sélectionné
        enginSelect.innerHTML = '<option value="">Sélectionnez d\'abord un livreur</option>';
        enginSelect.disabled = true;
        livreurEnginInfo.style.display = 'none';
    }

    // Fonction pour charger les données d'un ramassage
    function loadRamassageData(ramassageId) {
        fetch(`/api/ramassages/${ramassageId}/colis-data`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('📦 Données reçues du ramassage:', data);
            if (data.success && data.colisData) {
                // Note: Le ramassage ne pré-remplit que les données des colis
                // Les champs marchand et boutique restent indépendants

                // Mettre à jour le nombre de colis
                const nombreColisInput = document.getElementById('nombre_colis');
                if (nombreColisInput) {
                    nombreColisInput.value = data.colisData.length;
                }

                // Vider les formulaires existants
                clearColisForms();

                // Créer les formulaires avec les données du ramassage
                data.colisData.forEach((colisData, index) => {
                    createColisFormWithData(index + 1, colisData);
                });

                // Mettre à jour l'affichage
                updateColisDisplay();

                // Vérifier la complétude du formulaire
                setTimeout(checkFormCompleteness, 100);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données du ramassage:', error);
            alert('Erreur lors du chargement des données du ramassage');
        });
    }

    // Fonction pour créer un formulaire de colis avec des données pré-remplies
    function createColisFormWithData(index, colisData) {
        const colisContainer = document.getElementById('colisFormsContainer');
        if (!colisContainer) {
            console.error('Élément colisFormsContainer non trouvé');
            return;
        }
        const formDiv = document.createElement('div');
        formDiv.className = 'colis-form mb-4';
        formDiv.innerHTML = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Colis ${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeColisForm(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="colis[${index}][nom_client]" value="${colisData.nom_client || colisData.client || ''}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone du client <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+225</span>
                                <input type="tel" class="form-control" name="colis[${index}][telephone_client]" value="${colisData.telephone_client || ''}" placeholder="07 12 34 56 78" required>
                            </div>
                            <div class="form-text">Format: 0707070707 (sans l'indicatif +225)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresse du client <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="colis[${index}][adresse_client]" rows="2" required>${colisData.adresse_client || ''}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][commune_id]" required>
                                <option value="">Sélectionner une zone</option>
                                @foreach($communes ?? [] as $commune)
                                    <option value="{{ $commune->id }}">{{ $commune->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Montant à encaisser</label>
                            <input type="number" class="form-control" name="colis[${index}][montant_a_encaisse]" value="${colisData.valeur || ''}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix de vente</label>
                            <input type="number" class="form-control" name="colis[${index}][prix_de_vente]" value="${colisData.valeur || ''}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Numéro de facture</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="colis[${index}][numero_facture]" placeholder="Code généré automatiquement">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillInvoiceNumber(this.previousElementSibling)" title="Générer un nouveau code">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Note client</label>
                            <input type="text" class="form-control" name="colis[${index}][note_client]" value="${colisData.notes || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Type de colis <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][type_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($type_colis ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Conditionnement <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][conditionnement_colis_id]">
                                <option value="">Sélectionner</option>
                                @foreach($conditionnement_colis ?? [] as $conditionnement)
                                    <option value="{{ $conditionnement->id }}">{{ $conditionnement->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Poids <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][poids_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($poids ?? [] as $poidsItem)
                                    <option value="{{ $poidsItem->id }}">{{ $poidsItem->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Délai <span class="text-danger">*</span></label>
                            <select required class="form-select" name="colis[${index}][delai_id]">
                                <option value="">Sélectionner</option>
                                @foreach($delais ?? [] as $delai)
                                    <option value="{{ $delai->id }}">{{ $delai->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Mode de livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][mode_livraison_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($mode_livraisons ?? [] as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Période <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis[${index}][temp_id]" required>
                                <option value="">Sélectionner</option>
                                @foreach($temps ?? [] as $temp)
                                    <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" id="cost-display-${index}" style="display: none;">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>Coût de livraison estimé : <span id="cost-amount-${index}">0</span> FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        colisContainer.appendChild(formDiv);

        // Pré-remplir les sélecteurs avec les données du ramassage
        setTimeout(() => {
            const form = formDiv.querySelector('.card-body');
            if (form) {
                // Commune (Zone de livraison)
                const communeSelect = form.querySelector('select[name*="[commune_id]"]');
                if (communeSelect && colisData.commune_id) {
                    communeSelect.value = colisData.commune_id;
                    console.log('🏘️ Commune pré-remplie:', colisData.commune_id);
                }

                // Type de colis
                const typeSelect = form.querySelector('select[name*="[type_colis_id]"]');
                if (typeSelect && colisData.type_colis_id) {
                    typeSelect.value = colisData.type_colis_id;
                }

                // Conditionnement
                const conditionnementSelect = form.querySelector('select[name*="[conditionnement_colis_id]"]');
                if (conditionnementSelect && colisData.conditionnement_colis_id) {
                    conditionnementSelect.value = colisData.conditionnement_colis_id;
                }

                // Poids
                const poidsSelect = form.querySelector('select[name*="[poids_id]"]');
                if (poidsSelect && colisData.poids_id) {
                    poidsSelect.value = colisData.poids_id;
                }

                // Délai
                const delaiSelect = form.querySelector('select[name*="[delai_id]"]');
                if (delaiSelect && colisData.delai_id) {
                    delaiSelect.value = colisData.delai_id;
                }

                // Mode de livraison
                const modeSelect = form.querySelector('select[name*="[mode_livraison_id]"]');
                if (modeSelect && colisData.mode_livraison_id) {
                    modeSelect.value = colisData.mode_livraison_id;
                }

                // Période
                const periodeSelect = form.querySelector('select[name*="[temp_id]"]');
                if (periodeSelect && colisData.temp_id) {
                    periodeSelect.value = colisData.temp_id;
                }

                // Calculer le coût de livraison après le pré-remplissage
                setTimeout(() => {
                    console.log(`💰 Calcul du coût pour le colis ${index}`);
                    calculateDeliveryCost(index);
                }, 200);
            }
        }, 100);

        // Générer automatiquement le numéro de facture pour ce nouveau formulaire
        setTimeout(() => {
            const invoiceInput = formDiv.querySelector('input[name*="[numero_facture]"]');
            if (invoiceInput && !invoiceInput.value) {
                fillInvoiceNumber(invoiceInput);
            }
        }, 300);

        // Vérifier la complétude du formulaire après création
        setTimeout(checkFormCompleteness, 200);
    }

    // Fonction pour vider les formulaires de colis
    function clearColisForms() {
        console.log('🗑️ clearColisForms() appelée');
        const colisContainer = document.getElementById('colisFormsContainer');
        if (colisContainer) {
            colisContainer.innerHTML = '';
        }
    }

    // Fonction pour mettre à jour l'affichage des colis
    function updateColisDisplay() {
        console.log('📊 updateColisDisplay() appelée');
        const colisContainer = document.getElementById('colisFormsContainer');
        const colisCount = colisContainer ? colisContainer.children.length : 0;

        // Mettre à jour le nombre de colis si nécessaire
        const nombreColisInput = document.getElementById('nombre_colis');
        if (nombreColisInput && colisCount > 0) {
            nombreColisInput.value = colisCount;
        }
    }

    // Fonction pour le mode multi-boutiques
    function loadRamassageDataForMultiMode() {
        const ramassageId = document.getElementById('multiBoutiquesRamassageId').value;
        if (ramassageId) {
            // Charger les données du ramassage pour le mode multi-boutiques
            fetch(`/api/ramassages/${ramassageId}/colis-data`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.ramassage) {
                    // Pré-remplir le marchand pour le mode multi-boutiques
                    const marchandSelect = document.getElementById('multiBoutiquesMarchandId');
                    if (marchandSelect && data.ramassage.marchand_id) {
                        marchandSelect.value = data.ramassage.marchand_id;
                        // Charger les boutiques sans déclencher l'événement change
                        loadBoutiquesForMarchand(data.ramassage.marchand_id, data.ramassage.boutique_id);
                    }
                }

                // Charger les données des colis
                loadRamassageData(ramassageId);
            })
            .catch(error => {
                console.error('Erreur lors du chargement des données du ramassage:', error);
                // Charger quand même les données des colis
                loadRamassageData(ramassageId);
            });
        }
    }

    // Fonction pour formater le numéro de téléphone
    function formatPhoneNumber(input) {
        // Nettoyer le numéro (supprimer les espaces et caractères non numériques)
        let value = input.value.replace(/\D/g, '');

        // Limiter à 10 chiffres
        if (value.length > 10) {
            value = value.substring(0, 10);
        }

        // Formater avec des espaces (format: 07 12 34 56 78)
        if (value.length >= 2) {
            value = value.substring(0, 2) + ' ' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + ' ' + value.substring(5);
        }
        if (value.length >= 8) {
            value = value.substring(0, 8) + ' ' + value.substring(8);
        }
        if (value.length >= 11) {
            value = value.substring(0, 11) + ' ' + value.substring(11);
        }

        input.value = value;
    }

    // Ajouter le formatage automatique aux champs téléphone existants et futurs
    document.addEventListener('DOMContentLoaded', function() {
        // Formater les champs téléphone existants
        const phoneInputs = document.querySelectorAll('input[name*="telephone_client"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
        });

        // Observer les nouveaux champs téléphone ajoutés dynamiquement
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const phoneInputs = node.querySelectorAll('input[name*="telephone_client"]');
                        phoneInputs.forEach(input => {
                            input.addEventListener('input', function() {
                                formatPhoneNumber(this);
                            });
                        });
                    }
                });
            });
        });

        // Observer les changements dans le conteneur des colis
        const colisContainer = document.getElementById('colis-container');
        if (colisContainer) {
            observer.observe(colisContainer, {
                childList: true,
                subtree: true
            });
        }
    });

}
</script>
