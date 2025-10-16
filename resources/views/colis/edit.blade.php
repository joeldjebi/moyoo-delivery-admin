@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Modifier le Colis</h5>
                            <p class="mb-4">Modification du colis : {{ $colis->code ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('colis.show', $colis->id) }}" class="btn btn-outline-info">
                                    <i class="ti ti-eye me-1"></i>
                                    Voir détails
                                </a>
                                <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary">
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

    <!-- Formulaire de modification -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier les Informations du Colis</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('colis.update', $colis->id) }}" id="colisForm">
                        @csrf
                        @method('PUT')

                        <!-- Informations générales -->
                        <!-- Mode de travail -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">Mode de travail</h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary active" id="normalModeBtn" onclick="toggleMode('normal')">
                                            <i class="ti ti-package me-1"></i>
                                            Mode Normal
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="multiBoutiquesModeBtn" onclick="toggleMode('multi-boutiques')">
                                            <i class="ti ti-building-store me-1"></i>
                                            Mode Multi-Boutiques
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h6 class="text-primary mb-3">Étape 1 : Informations Générales</h6>
                            </div>

                            <div class="col-md-4">
                                <label for="nombre_colis" class="form-label">Nombre de colis <span class="text-danger">*</span></label>
                                <input type="number" class="form-select @error('nombre_colis') is-invalid @enderror"
                                       id="nombre_colis" name="nombre_colis" min="1" max="20" value="{{ old('nombre_colis', 1) }}" required>
                                <small class="text-muted">Ce nombre doit correspondre au nombre de zones de livraison que vous sélectionnerez</small>
                                @error('nombre_colis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="marchand_id" class="form-label">Marchand <span class="text-danger">*</span></label>
                                <select class="form-select @error('marchand_id') is-invalid @enderror" id="marchand_id" name="marchand_id" required>
                                    <option value="">Sélectionner un marchand</option>
                                    @foreach($marchands ?? [] as $marchand)
                                        <option value="{{ $marchand->id }}" {{ old('marchand_id', $colis->marchand_id) == $marchand->id ? 'selected' : '' }}>
                                            {{ $marchand->first_name }} {{ $marchand->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('marchand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="boutique_id" class="form-label">Boutique <span class="text-danger">*</span></label>
                                <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id" name="boutique_id" required>
                                    <option value="">Sélectionner une boutique</option>
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="livreur_id" class="form-label">Livreur</label>
                                <select class="form-select @error('livreur_id') is-invalid @enderror" id="livreur_id" name="livreur_id">
                                    <option value="">Sélectionner un livreur (optionnel)</option>
                                    @foreach($livreurs ?? [] as $livreur)
                                        <option value="{{ $livreur->id }}" {{ old('livreur_id', $colis->livreur_id) == $livreur->id ? 'selected' : '' }}>
                                            {{ $livreur->last_name }} {{ $livreur->first_name }} - {{ $livreur->telephone }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('livreur_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="engin_id" class="form-label">Engin du livreur</label>
                                <select class="form-select @error('engin_id') is-invalid @enderror" id="engin_id" name="engin_id" onchange="recalculateAllCosts()">
                                    <option value="">Sélectionnez d'abord un livreur</option>
                                    @if($colis->engin)
                                        <option value="{{ $colis->engin->id }}" selected>
                                            {{ $colis->engin->libelle }} - {{ $colis->engin->matricule }}{{ $colis->engin->typeEngin ? ' (' . $colis->engin->typeEngin->libelle . ')' : '' }}
                                        </option>
                                    @endif
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


                        <!-- Informations du colis actuel -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">Colis Actuel</h6>
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0" style="color: white;">
                                            <i class="ti ti-package me-2"></i>
                                            {{ $colis->code ?? 'N/A' }}
                                        </h6>
                                    </div>
                                    <div class="card-body mt-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Client:</strong><br>
                                                {{ $colis->nom_client ?? 'N/A' }}<br>
                                                <small class="text-muted">{{ $colis->telephone_client ?? 'N/A' }}</small>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Zone/Commune:</strong><br>
                                                {{ $colis->zone->libelle ?? 'N/A' }}<br>
                                                <small class="text-muted">{{ $colis->commune->libelle ?? 'N/A' }}</small>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Paramètres:</strong><br>
                                                Poids: {{ $colis->poids->libelle ?? 'N/A' }}<br>
                                                Mode: {{ $colis->modeLivraison->libelle ?? 'N/A' }}<br>
                                                Période: {{ $colis->temp->libelle ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Coût de livraison:</strong><br>
                                                <h5 class="text-success mb-0" id="currentColisCost">
                                                    {{ $colis->delivery_cost_formatted }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Détails des colis -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">Détails des Colis</h6>
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>Étape 2 :</strong> Cliquez sur "Générer les formulaires" pour créer les formulaires de colis. La zone de livraison sera sélectionnée dans chaque formulaire généré.
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

                        <!-- Section Multi-Boutiques (cachée par défaut) -->
                        <div id="multiBoutiquesSection" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">Mode Multi-Boutiques</h6>
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Étape 1 :</strong> Sélectionnez un marchand pour charger ses boutiques
                                    </div>
                                </div>
                            </div>

                            <!-- Sélection du marchand pour multi-boutiques -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="multi_marchand_id" class="form-label">Marchand <span class="text-danger">*</span></label>
                                    <select class="form-select" id="multi_marchand_id" name="multi_marchand_id">
                                        <option value="">Sélectionner un marchand</option>
                                        @foreach($marchands ?? [] as $marchand)
                                            <option value="{{ $marchand->id }}">{{ $marchand->first_name }} {{ $marchand->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Actions</label>
                                    <div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="loadBoutiquesForMultiMode()">
                                            <i class="ti ti-refresh me-1"></i>
                                            Charger les boutiques
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Liste des boutiques disponibles -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Boutiques disponibles</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="boutiquesListMulti" class="row">
                                                <div class="col-12 text-center text-muted">
                                                    <i class="ti ti-building-store me-2"></i>
                                                    Sélectionnez un marchand et cliquez sur "Charger les boutiques"
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutiques sélectionnées -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Boutiques sélectionnées</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="selectedBoutiquesMulti" class="row">
                                                <div class="col-12 text-center text-muted">
                                                    <i class="ti ti-info-circle me-2"></i>
                                                    Aucune boutique sélectionnée
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuration des boutiques -->
                            <div id="boutiquesConfiguration" style="display: none;">
                                <!-- Les configurations de boutiques seront générées ici -->
                            </div>

                            <!-- Résumé global des coûts -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary" id="globalCostSummary" style="display: none;">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0" style="color: white;">
                                                <i class="ti ti-calculator me-2"></i>
                                                Résumé Global des Coûts Multi-Boutiques
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Total des boutiques :</strong> <span id="totalBoutiques">0</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Total des colis :</strong> <span id="totalColisGlobal">0</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Coût moyen par colis :</strong> <span id="averageCostGlobal">0 FCFA</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Coût total global :</strong> <span id="totalCostGlobal" class="text-primary fw-bold fs-5">0 FCFA</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton de soumission pour multi-boutiques -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <button type="button" class="btn btn-success" id="submitMultiBoutiquesBtn" style="display: none;">
                                        <i class="ti ti-check me-1"></i>
                                        Mettre à jour les Packages Multi-Boutiques
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- Notes et instructions -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="note_client" class="form-label">Note du client</label>
                                <textarea class="form-control @error('note_client') is-invalid @enderror"
                                          id="note_client" name="note_client" rows="3"
                                          placeholder="Notes ou commentaires du client...">{{ old('note_client', $colis->note_client) }}</textarea>
                                @error('note_client')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="instructions_livraison" class="form-label">Instructions de livraison</label>
                                <textarea class="form-control @error('instructions_livraison') is-invalid @enderror"
                                          id="instructions_livraison" name="instructions_livraison" rows="3"
                                          placeholder="Instructions spéciales pour la livraison...">{{ old('instructions_livraison', $colis->instructions_livraison) }}</textarea>
                                @error('instructions_livraison')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('colis.show', $colis->id) }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Mettre à jour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
// Variables globales pour le mode multi-boutiques
let isMultiBoutiquesMode = false;
let selectedBoutiques = [];

// Fonction pour basculer entre les modes
function toggleMode(mode) {
    const normalModeBtn = document.getElementById('normalModeBtn');
    const multiBoutiquesModeBtn = document.getElementById('multiBoutiquesModeBtn');
    const normalSection = document.querySelector('.row:has(#nombre_colis)').closest('.row').parentElement;
    const multiBoutiquesSection = document.getElementById('multiBoutiquesSection');

    if (mode === 'normal') {
        isMultiBoutiquesMode = false;
        normalModeBtn.classList.add('active');
        normalModeBtn.classList.remove('btn-outline-primary');
        normalModeBtn.classList.add('btn-primary');

        multiBoutiquesModeBtn.classList.remove('active');
        multiBoutiquesModeBtn.classList.remove('btn-success');
        multiBoutiquesModeBtn.classList.add('btn-outline-success');

        // Afficher la section normale
        normalSection.style.display = 'block';
        multiBoutiquesSection.style.display = 'none';

        // Réinitialiser le formulaire multi-boutiques
        resetMultiBoutiquesForm();
    } else if (mode === 'multi-boutiques') {
        isMultiBoutiquesMode = true;
        multiBoutiquesModeBtn.classList.add('active');
        multiBoutiquesModeBtn.classList.remove('btn-outline-success');
        multiBoutiquesModeBtn.classList.add('btn-success');

        normalModeBtn.classList.remove('active');
        normalModeBtn.classList.remove('btn-primary');
        normalModeBtn.classList.add('btn-outline-primary');

        // Afficher la section multi-boutiques
        normalSection.style.display = 'none';
        multiBoutiquesSection.style.display = 'block';
    }
}

// Fonction pour réinitialiser le formulaire multi-boutiques
function resetMultiBoutiquesForm() {
    selectedBoutiques = [];
    document.getElementById('multi_marchand_id').value = '';
    document.getElementById('boutiquesListMulti').innerHTML = `
        <div class="col-12 text-center text-muted">
            <i class="ti ti-building-store me-2"></i>
            Sélectionnez un marchand et cliquez sur "Charger les boutiques"
        </div>
    `;
    document.getElementById('selectedBoutiquesMulti').innerHTML = `
        <div class="col-12 text-center text-muted">
            <i class="ti ti-info-circle me-2"></i>
            Aucune boutique sélectionnée
        </div>
    `;
    document.getElementById('boutiquesConfiguration').style.display = 'none';
    document.getElementById('submitMultiBoutiquesBtn').style.display = 'none';
}

// Fonction pour charger les boutiques en mode multi-boutiques
function loadBoutiquesForMultiMode() {
    const marchandId = document.getElementById('multi_marchand_id').value;
    const boutiquesList = document.getElementById('boutiquesListMulti');

    if (!marchandId) {
        showValidationAlert('Veuillez sélectionner un marchand', 'warning');
        return;
    }

    boutiquesList.innerHTML = '<div class="col-12 text-center"><i class="ti ti-loader me-2"></i>Chargement des boutiques...</div>';

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
        if (data.success && data.boutiques) {
            boutiquesList.innerHTML = '';
            data.boutiques.forEach(boutique => {
                const boutiqueCard = document.createElement('div');
                boutiqueCard.className = 'col-md-6 mb-3';
                boutiqueCard.innerHTML = `
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${boutique.id}" id="boutique_${boutique.id}" onchange="handleBoutiqueSelection(${boutique.id}, '${boutique.libelle}', '${boutique.adresse || 'Pas d\'adresse'}')">
                                <label class="form-check-label" for="boutique_${boutique.id}">
                                    <strong>${boutique.libelle}</strong><br>
                                    <small class="text-muted">${boutique.adresse || 'Pas d\'adresse'}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                `;
                boutiquesList.appendChild(boutiqueCard);
            });
        } else {
            boutiquesList.innerHTML = '<div class="col-12 text-center text-muted">Aucune boutique trouvée pour ce marchand</div>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        boutiquesList.innerHTML = '<div class="col-12 text-center text-danger">Erreur lors du chargement des boutiques</div>';
    });
}

// Fonction pour gérer la sélection des boutiques
function handleBoutiqueSelection(boutiqueId, boutiqueLibelle, boutiqueAdresse) {
    const checkbox = document.getElementById(`boutique_${boutiqueId}`);
    const selectedBoutiquesDiv = document.getElementById('selectedBoutiquesMulti');

    if (checkbox.checked) {
        if (selectedBoutiques.length >= 10) {
            checkbox.checked = false;
            showValidationAlert('Vous ne pouvez sélectionner que 10 boutiques maximum', 'warning');
            return;
        }

        selectedBoutiques.push({
            id: boutiqueId,
            libelle: boutiqueLibelle,
            adresse: boutiqueAdresse
        });
    } else {
        selectedBoutiques = selectedBoutiques.filter(b => b.id !== boutiqueId);
    }

    updateSelectedBoutiquesDisplay();
    showBoutiquesConfiguration();
}

// Fonction pour mettre à jour l'affichage des boutiques sélectionnées
function updateSelectedBoutiquesDisplay() {
    const selectedBoutiquesDiv = document.getElementById('selectedBoutiquesMulti');

    if (selectedBoutiques.length === 0) {
        selectedBoutiquesDiv.innerHTML = `
            <div class="col-12 text-center text-muted">
                <i class="ti ti-info-circle me-2"></i>
                Aucune boutique sélectionnée
            </div>
        `;
    } else {
        selectedBoutiquesDiv.innerHTML = '';
        selectedBoutiques.forEach(boutique => {
            const boutiqueCard = document.createElement('div');
            boutiqueCard.className = 'col-md-6 mb-2';
            boutiqueCard.innerHTML = `
                <div class="card border-success">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${boutique.libelle}</strong><br>
                                <small class="text-muted">${boutique.adresse}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBoutique(${boutique.id})">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            selectedBoutiquesDiv.appendChild(boutiqueCard);
        });
    }
}

// Fonction pour supprimer une boutique sélectionnée
function removeBoutique(boutiqueId) {
    const checkbox = document.getElementById(`boutique_${boutiqueId}`);
    if (checkbox) {
        checkbox.checked = false;
    }

    selectedBoutiques = selectedBoutiques.filter(b => b.id !== boutiqueId);
    updateSelectedBoutiquesDisplay();
    showBoutiquesConfiguration();
}

// Fonction pour afficher la configuration des boutiques
function showBoutiquesConfiguration() {
    const configurationDiv = document.getElementById('boutiquesConfiguration');
    const submitBtn = document.getElementById('submitMultiBoutiquesBtn');

    if (selectedBoutiques.length === 0) {
        configurationDiv.style.display = 'none';
        submitBtn.style.display = 'none';
        return;
    }

    configurationDiv.innerHTML = '';
    selectedBoutiques.forEach((boutique, index) => {
        const boutiqueConfig = document.createElement('div');
        boutiqueConfig.className = 'card mb-3';
        boutiqueConfig.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">${boutique.libelle}</h6>
                <small class="text-muted">${boutique.adresse}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Nombre de colis</label>
                        <input type="number" class="form-control" name="boutiques[${index}][nombre_colis]" min="1" max="20" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Livreur</label>
                        <select class="form-select" name="boutiques[${index}][livreur_id]">
                            <option value="">Sélectionner un livreur</option>
                            @foreach($livreurs ?? [] as $livreur)
                                <option value="{{ $livreur->id }}">{{ $livreur->last_name }} {{ $livreur->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Engin</label>
                        <select class="form-select" name="boutiques[${index}][engin_id]" onchange="recalculateAllCostsMulti(${index})">
                            <option value="">Sélectionner un engin</option>
                            @foreach($engins ?? [] as $engin)
                                <option value="{{ $engin->id }}">{{ $engin->libelle }} - {{ $engin->matricule }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actions</label>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="generateColisFormsForBoutique(${index}, '${boutique.libelle}')">
                                <i class="ti ti-plus me-1"></i>
                                Générer les formulaires
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="boutiques[${index}][boutique_id]" value="${boutique.id}">
                <div id="colisFormsForBoutique_${index}" class="mt-3">
                    <!-- Les formulaires de colis pour cette boutique seront générés ici -->
                </div>
            </div>
        `;
        configurationDiv.appendChild(boutiqueConfig);
    });

    configurationDiv.style.display = 'block';
    submitBtn.style.display = 'block';
}

// Fonction pour générer les formulaires de colis pour une boutique
function generateColisFormsForBoutique(boutiqueIndex, boutiqueLibelle) {
    console.log(`🏗️ DÉBUT GÉNÉRATION FORMULAIRES - Boutique ${boutiqueIndex} (${boutiqueLibelle})`);

    const nombreColisInput = document.querySelector(`input[name="boutiques[${boutiqueIndex}][nombre_colis]"]`);
    const nombreColis = parseInt(nombreColisInput.value) || 0;
    const container = document.getElementById(`colisFormsForBoutique_${boutiqueIndex}`);

    console.log(`📊 PARAMÈTRES GÉNÉRATION - Boutique ${boutiqueIndex}:`, {
        nombreColisInput: nombreColisInput ? 'TROUVÉ' : 'NON TROUVÉ',
        nombreColis,
        container: container ? 'TROUVÉ' : 'NON TROUVÉ',
        boutiqueLibelle
    });

    if (nombreColis <= 0) {
        console.log(`❌ NOMBRE DE COLIS INVALIDE - Boutique ${boutiqueIndex}: ${nombreColis}`);
        showValidationAlert('Veuillez saisir un nombre de colis valide (1-20)', 'warning');
        return;
    }

    console.log(`🧹 VIDAGE CONTAINER - Boutique ${boutiqueIndex}`);
    container.innerHTML = '';

    console.log(`🏗️ GÉNÉRATION DE ${nombreColis} FORMULAIRES - Boutique ${boutiqueIndex}`);
    for (let i = 1; i <= nombreColis; i++) {
        console.log(`📝 CRÉATION FORMULAIRE ${i}/${nombreColis} - Boutique ${boutiqueIndex}`);
        const formDiv = createColisFormForBoutique(boutiqueIndex, i, boutiqueLibelle);
        container.appendChild(formDiv);
        console.log(`✅ FORMULAIRE ${i} AJOUTÉ - Boutique ${boutiqueIndex}`);
    }

    // Ajouter un résumé des coûts pour cette boutique
    const summaryDiv = document.createElement('div');
    summaryDiv.className = 'card mt-3 border-success';
    summaryDiv.innerHTML = `
        <div class="card-header bg-success text-white">
            <h6 class="mb-0" style="color: white;">
                <i class="ti ti-calculator me-2"></i>
                Résumé des Coûts - ${boutiqueLibelle}
            </h6>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <button type="button" class="btn btn-success" onclick="recalculateAllCostsMulti(${boutiqueIndex})">
                        <i class="ti ti-calculator me-1"></i>
                        Calculer tous les coûts
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <strong>Nombre de colis :</strong> <span id="totalColis-${boutiqueIndex}">${nombreColis}</span>
                </div>
                <div class="col-md-4">
                    <strong>Coût moyen :</strong> <span id="averageCost-${boutiqueIndex}">0 FCFA</span>
                </div>
                <div class="col-md-4">
                    <strong>Coût total :</strong> <span id="totalCost-${boutiqueIndex}" class="text-success fw-bold">0 FCFA</span>
                </div>
            </div>
        </div>
    `;
    container.appendChild(summaryDiv);

    // Mettre à jour le résumé initial
    updateBoutiqueCostSummary(boutiqueIndex);

    // Mettre à jour le résumé global
    updateGlobalCostSummary();
}

// Fonction pour créer un formulaire de colis pour une boutique
function createColisFormForBoutique(boutiqueIndex, colisIndex, boutiqueLibelle) {
    console.log(`📝 CRÉATION FORMULAIRE - Boutique ${boutiqueIndex}, Colis ${colisIndex} (${boutiqueLibelle})`);

    const div = document.createElement('div');
    div.className = 'card mb-3 border-info';
    div.innerHTML = `
        <div class="card-header bg-info text-white">
            <h6 class="mb-0" style="color: white;">
                <i class="ti ti-package me-2"></i>
                ${boutiqueLibelle} - Colis ${colisIndex}
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][nom_client]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone du client <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][telephone_client]" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label">Adresse du client <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][adresse_client]" rows="2" required></textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">Montant à encaisser</label>
                    <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][montant_a_encaisse]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix de vente</label>
                    <input type="number" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][prix_de_vente]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Numéro de facture</label>
                    <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][numero_facture]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Note client</label>
                    <input type="text" class="form-control" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][note_client]">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-2">
                    <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][commune_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${colisIndex})" required>
                        <option value="">Sélectionner</option>
                        @foreach($communes ?? [] as $commune)
                            <option value="{{ $commune->id }}">{{ $commune->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type de colis</label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][type_colis_id]">
                        <option value="">Sélectionner</option>
                        @foreach($type_colis ?? [] as $type)
                            <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Conditionnement</label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][conditionnement_colis_id]">
                        <option value="">Sélectionner</option>
                        @foreach($conditionnement_colis ?? [] as $conditionnement)
                            <option value="{{ $conditionnement->id }}">{{ $conditionnement->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Poids <span class="text-danger">*</span></label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][poids_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${colisIndex})" required>
                        <option value="">Sélectionner</option>
                        @foreach($poids ?? [] as $poidsItem)
                            <option value="{{ $poidsItem->id }}">{{ $poidsItem->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Délai</label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][delai_id]">
                        <option value="">Sélectionner</option>
                        @foreach($delais ?? [] as $delai)
                            <option value="{{ $delai->id }}">{{ $delai->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mode de livraison <span class="text-danger">*</span></label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][mode_livraison_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${colisIndex})" required>
                        <option value="">Sélectionner</option>
                        @foreach($mode_livraisons ?? [] as $mode)
                            <option value="{{ $mode->id }}">{{ $mode->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Période <span class="text-danger">*</span></label>
                    <select class="form-select" name="boutiques[${boutiqueIndex}][colis][${colisIndex}][temp_id]" onchange="calculateDeliveryCostMulti(${boutiqueIndex}, ${colisIndex})" required>
                        <option value="">Sélectionner</option>
                        @foreach($temps ?? [] as $temp)
                            <option value="{{ $temp->id }}">{{ $temp->libelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="alert alert-info d-flex align-items-center" id="cost-display-multi-${boutiqueIndex}-${colisIndex}" style="display: block;">
                        <i class="fas fa-calculator me-2"></i>
                        <strong>Coût de livraison estimé : <span id="cost-amount-multi-${boutiqueIndex}-${colisIndex}">Remplissez les champs requis</span> FCFA</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="calculateDeliveryCostMulti(${boutiqueIndex}, ${colisIndex})">
                        <i class="ti ti-calculator me-1"></i>
                        Calculer le coût
                    </button>
                </div>
            </div>
        </div>
    `;

    console.log(`✅ FORMULAIRE CRÉÉ - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, {
        elementId: `cost-display-multi-${boutiqueIndex}-${colisIndex}`,
        amountId: `cost-amount-multi-${boutiqueIndex}-${colisIndex}`,
        divCreated: div ? 'OUI' : 'NON',
        innerHTML: div.innerHTML.includes('cost-display-multi') ? 'DIV COÛT PRÉSENTE' : 'DIV COÛT MANQUANTE'
    });

    return div;
}

// Fonction pour mettre à jour le résumé des coûts d'une boutique
function updateBoutiqueCostSummary(boutiqueIndex) {
    const totalColisSpan = document.getElementById(`totalColis-${boutiqueIndex}`);
    const averageCostSpan = document.getElementById(`averageCost-${boutiqueIndex}`);
    const totalCostSpan = document.getElementById(`totalCost-${boutiqueIndex}`);

    if (!totalColisSpan || !averageCostSpan || !totalCostSpan) return;

    const nombreColis = parseInt(totalColisSpan.textContent) || 0;
    let totalCost = 0;
    let validCosts = 0;

    // Calculer le coût total
    for (let i = 1; i <= nombreColis; i++) {
        const costAmount = document.getElementById(`cost-amount-multi-${boutiqueIndex}-${i}`);
        if (costAmount && costAmount.textContent !== '0' && costAmount.textContent !== 'Calcul...' && costAmount.textContent !== 'Erreur') {
            const cost = parseFloat(costAmount.textContent.replace(/\s/g, '')) || 0;
            totalCost += cost;
            validCosts++;
        }
    }

    // Mettre à jour les affichages
    const averageCost = validCosts > 0 ? totalCost / validCosts : 0;
    averageCostSpan.textContent = new Intl.NumberFormat('fr-FR').format(averageCost) + ' FCFA';
    totalCostSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalCost) + ' FCFA';
}

// Fonction pour mettre à jour le résumé global des coûts
function updateGlobalCostSummary() {
    const globalSummary = document.getElementById('globalCostSummary');
    const totalBoutiquesSpan = document.getElementById('totalBoutiques');
    const totalColisGlobalSpan = document.getElementById('totalColisGlobal');
    const averageCostGlobalSpan = document.getElementById('averageCostGlobal');
    const totalCostGlobalSpan = document.getElementById('totalCostGlobal');

    if (!globalSummary || !totalBoutiquesSpan || !totalColisGlobalSpan || !averageCostGlobalSpan || !totalCostGlobalSpan) return;

    let totalBoutiques = 0;
    let totalColis = 0;
    let totalCost = 0;
    let validCosts = 0;

    // Parcourir toutes les boutiques configurées
    const boutiqueConfigs = document.querySelectorAll('#boutiquesConfiguration .card');
    boutiqueConfigs.forEach((config, index) => {
        const totalColisSpan = document.getElementById(`totalColis-${index}`);
        if (totalColisSpan) {
            const nombreColis = parseInt(totalColisSpan.textContent) || 0;
            if (nombreColis > 0) {
                totalBoutiques++;
                totalColis += nombreColis;

                // Calculer le coût total pour cette boutique
                for (let i = 1; i <= nombreColis; i++) {
                    const costAmount = document.getElementById(`cost-amount-multi-${index}-${i}`);
                    if (costAmount && costAmount.textContent !== '0' && costAmount.textContent !== 'Calcul...' && costAmount.textContent !== 'Erreur') {
                        const cost = parseFloat(costAmount.textContent.replace(/\s/g, '')) || 0;
                        totalCost += cost;
                        validCosts++;
                    }
                }
            }
        }
    });

    // Mettre à jour les affichages
    totalBoutiquesSpan.textContent = totalBoutiques;
    totalColisGlobalSpan.textContent = totalColis;

    const averageCost = validCosts > 0 ? totalCost / validCosts : 0;
    averageCostGlobalSpan.textContent = new Intl.NumberFormat('fr-FR').format(averageCost) + ' FCFA';
    totalCostGlobalSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalCost) + ' FCFA';

    // Afficher le résumé global s'il y a des boutiques configurées
    if (totalBoutiques > 0) {
        globalSummary.style.display = 'block';
    } else {
        globalSummary.style.display = 'none';
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

            // Mettre à jour le résumé des coûts de la boutique
            updateBoutiqueCostSummary(boutiqueIndex);

            // Mettre à jour le résumé global
            updateGlobalCostSummary();
        })
        .catch(error => {
            console.error(`❌ ERREUR API - Boutique ${boutiqueIndex}, Colis ${colisIndex}:`, error);
            costAmount.textContent = 'Erreur';
            costDisplay.className = 'alert alert-danger d-flex align-items-center';

            // Mettre à jour le résumé même en cas d'erreur
            updateBoutiqueCostSummary(boutiqueIndex);
            updateGlobalCostSummary();
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
                    if (data.success && data.boutiques) {
                        boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';
                        data.boutiques.forEach(boutique => {
                            boutiqueSelect.innerHTML += `<option value="${boutique.id}">${boutique.libelle}</option>`;
                        });

                        // Sélectionner la boutique actuelle si elle existe
                        const currentBoutiqueId = '{{ $colis->boutique_id }}';
                        if (currentBoutiqueId) {
                            boutiqueSelect.value = currentBoutiqueId;
                        }
                    } else {
                        boutiqueSelect.innerHTML = '<option value="">Aucune boutique trouvée</option>';
                    }
                })
                    .catch(error => {
                        console.error('Erreur:', error);
                        boutiqueSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
            } else {
                boutiqueSelect.innerHTML = '<option value="">Sélectionner d\'abord un marchand</option>';
            }
        });

        // Charger les boutiques au chargement de la page si un marchand est déjà sélectionné
        if (marchandSelect.value) {
            // Charger les boutiques pour le marchand actuel
            const marchandId = marchandSelect.value;
            const boutiqueSelect = document.getElementById('boutique_id');
            const currentBoutiqueId = '{{ $colis->boutique_id }}';

            if (marchandId && currentBoutiqueId) {
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
                    if (data.success && data.boutiques) {
                        boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';
                        data.boutiques.forEach(boutique => {
                            boutiqueSelect.innerHTML += `<option value="${boutique.id}">${boutique.libelle}</option>`;
                        });

                        // Sélectionner la boutique actuelle
                        boutiqueSelect.value = currentBoutiqueId;
                    } else {
                        boutiqueSelect.innerHTML = '<option value="">Aucune boutique trouvée</option>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    boutiqueSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
            }
        }
    }

    // Fonction pour créer un formulaire de colis
    function createColisForm(index, colisData = {}) {
        const div = document.createElement('div');
        div.className = 'card mb-3';
        div.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">Colis ${index}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="colis[${index}][nom_client]" value="${colisData.nom_client || ''}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone du client <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="colis[${index}][telephone_client]" value="${colisData.telephone_client || ''}" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">Adresse du client <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="colis[${index}][adresse_client]" rows="2" required>${colisData.adresse_client || ''}</textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="form-label">Montant à encaisser</label>
                        <input type="number" class="form-control" name="colis[${index}][montant_a_encaisse]" value="${colisData.montant_a_encaisse || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix de vente</label>
                        <input type="number" class="form-control" name="colis[${index}][prix_de_vente]" value="${colisData.prix_de_vente || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Numéro de facture</label>
                        <input type="text" class="form-control" name="colis[${index}][numero_facture]" value="${colisData.numero_facture || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Note client</label>
                        <input type="text" class="form-control" name="colis[${index}][note_client]" value="${colisData.note_client || ''}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label class="form-label">Zone de livraison <span class="text-danger">*</span></label>
                        <select class="form-select" name="colis[${index}][commune_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($communes ?? [] as $commune)
                                <option value="{{ $commune->id }}" ${colisData.commune_id == '{{ $commune->id }}' ? 'selected' : ''}>{{ $commune->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type de colis</label>
                        <select class="form-select" name="colis[${index}][type_colis_id]">
                            <option value="">Sélectionner</option>
                            @foreach($type_colis ?? [] as $type)
                                <option value="{{ $type->id }}" ${colisData.type_colis_id == '{{ $type->id }}' ? 'selected' : ''}>{{ $type->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Conditionnement</label>
                        <select class="form-select" name="colis[${index}][conditionnement_colis_id]">
                            <option value="">Sélectionner</option>
                            @foreach($conditionnement_colis ?? [] as $conditionnement)
                                <option value="{{ $conditionnement->id }}" ${colisData.conditionnement_colis_id == '{{ $conditionnement->id }}' ? 'selected' : ''}>{{ $conditionnement->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Poids <span class="text-danger">*</span></label>
                        <select class="form-select" name="colis[${index}][poids_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($poids ?? [] as $poidsItem)
                                <option value="{{ $poidsItem->id }}" ${colisData.poids_id == '{{ $poidsItem->id }}' ? 'selected' : ''}>{{ $poidsItem->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Délai</label>
                        <select class="form-select" name="colis[${index}][delai_id]">
                            <option value="">Sélectionner</option>
                            @foreach($delais ?? [] as $delai)
                                <option value="{{ $delai->id }}" ${colisData.delai_id == '{{ $delai->id }}' ? 'selected' : ''}>{{ $delai->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mode de livraison <span class="text-danger">*</span></label>
                        <select class="form-select" name="colis[${index}][mode_livraison_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($mode_livraisons ?? [] as $mode)
                                <option value="{{ $mode->id }}" ${colisData.mode_livraison_id == '{{ $mode->id }}' ? 'selected' : ''}>{{ $mode->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Période <span class="text-danger">*</span></label>
                        <select class="form-select" name="colis[${index}][temp_id]" onchange="calculateDeliveryCost(${index})" required>
                            <option value="">Sélectionner</option>
                            @foreach($temps ?? [] as $temp)
                                <option value="{{ $temp->id }}" ${colisData.temp_id == '{{ $temp->id }}' ? 'selected' : ''}>{{ $temp->libelle }}</option>
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
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Livreur</label>
                        <select class="form-select" name="colis[${index}][livreur_id]">
                            <option value="">Sélectionner un livreur (optionnel)</option>
                            @foreach($livreurs ?? [] as $livreur)
                                <option value="{{ $livreur->id }}" ${colisData.livreur_id == '{{ $livreur->id }}' ? 'selected' : ''}>
                                    {{ $livreur->last_name }} {{ $livreur->first_name }} - {{ $livreur->telephone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Engin</label>
                        <select class="form-select" name="colis[${index}][engin_id]" id="colis_engin_${index}">
                            <option value="">Sélectionner un engin (optionnel)</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        return div;
    }


    // Fonction pour générer les formulaires de colis
    function generateColisForms() {
        const nombreColis = parseInt(document.getElementById('nombre_colis').value) || 0;
        const container = document.getElementById('colisFormsContainer');

        if (nombreColis <= 0) {
            showValidationAlert('Veuillez saisir un nombre de colis valide (1-20)', 'warning');
            return;
        }

        // Vider le conteneur
        container.innerHTML = '';

        // Données du colis existant pour pré-remplir le premier formulaire
        const existingColisData = {
            nom_client: '{{ $colis->nom_client }}',
            telephone_client: '{{ $colis->telephone_client }}',
            adresse_client: '{{ $colis->adresse_client }}',
            montant_a_encaisse: '{{ $colis->montant_a_encaisse }}',
            prix_de_vente: '{{ $colis->prix_de_vente }}',
            numero_facture: '{{ $colis->numero_facture }}',
            note_client: '{{ $colis->note_client }}',
            livreur_id: '{{ $colis->livreur_id }}',
            engin_id: '{{ $colis->engin_id }}',
            type_colis_id: '{{ $colis->type_colis_id }}',
            conditionnement_colis_id: '{{ $colis->conditionnement_colis_id }}',
            poids_id: '{{ $colis->poids_id }}',
            delai_id: '{{ $colis->delai_id }}',
            mode_livraison_id: '{{ $colis->mode_livraison_id }}',
            temp_id: '{{ $colis->temp_id }}',
            commune_id: '{{ $colis->commune_id }}'
        };

        // Générer les formulaires
        for (let i = 1; i <= nombreColis; i++) {
            const formDiv = createColisForm(i, i === 1 ? existingColisData : {});
            container.appendChild(formDiv);

            // Déclencher automatiquement le calcul du coût après un court délai
            setTimeout(() => {
                calculateDeliveryCost(i);
            }, 100 * i); // Délai progressif pour éviter les conflits
        }

        // Afficher le conteneur
        container.style.display = 'block';
    }

    // Ajouter l'événement click au bouton
    const generateBtn = document.getElementById('generateColisForms');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateColisForms);
    }


    // Générer automatiquement le formulaire au chargement de la page
    setTimeout(() => {
        if (document.getElementById('nombre_colis').value > 0) {
            generateColisForms();
        }
    }, 500);
});


// Validation du formulaire
document.getElementById('colisForm').addEventListener('submit', function(e) {
    const marchandId = document.getElementById('marchand_id').value;
    const boutiqueId = document.getElementById('boutique_id').value;

    if (!marchandId) {
        e.preventDefault();
        showValidationAlert('Veuillez sélectionner un marchand.', 'warning');
        return false;
    }

    if (!boutiqueId) {
        e.preventDefault();
        showValidationAlert('Veuillez sélectionner une boutique.', 'warning');
        return false;
    }

    // Vérifier que chaque formulaire de colis a une zone de livraison sélectionnée
    const colisForms = document.querySelectorAll('#colisFormsContainer .card');
    let allFormsValid = true;

    colisForms.forEach((form, index) => {
        const communeId = form.querySelector(`select[name="colis[${index + 1}][commune_id]"]`)?.value;
        if (!communeId) {
            allFormsValid = false;
        }
    });

    if (colisForms.length > 0 && !allFormsValid) {
        e.preventDefault();
        showValidationAlert('Veuillez sélectionner une zone de livraison pour chaque colis.', 'warning');
        return false;
    }

    // Confirmation avec une belle alerte
    e.preventDefault();
    showUpdateConfirmation();
});

// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Fonction pour calculer le coût de livraison
function calculateDeliveryCost(index) {
    const poidsId = document.querySelector(`select[name="colis[${index}][poids_id]"]`).value;
    const modeLivraisonId = document.querySelector(`select[name="colis[${index}][mode_livraison_id]"]`).value;
    const tempId = document.querySelector(`select[name="colis[${index}][temp_id]"]`).value;
    const enginId = document.getElementById('engin_id').value;
    const communeId = document.querySelector(`select[name="colis[${index}][commune_id]"]`).value;

    const costDisplay = document.getElementById(`cost-display-${index}`);
    const costAmount = document.getElementById(`cost-amount-${index}`);

    // Vérifier si tous les champs requis sont remplis
    if (poidsId && modeLivraisonId && enginId && communeId) {
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
}

// Fonction pour recalculer tous les coûts d'une boutique (mode multi-boutiques)
function recalculateAllCostsMulti(boutiqueIndex) {
    const nombreColisInput = document.querySelector(`input[name="boutiques[${boutiqueIndex}][nombre_colis]"]`);
    const nombreColis = parseInt(nombreColisInput.value) || 0;

    for (let i = 1; i <= nombreColis; i++) {
        calculateDeliveryCostMulti(boutiqueIndex, i);
    }
}

// Fonction pour afficher une belle alerte de validation
function showValidationAlert(message, type = 'warning') {
    const alertHtml = `
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    `;

    // Supprimer les anciennes alertes
    const existingToasts = document.querySelectorAll('.toast-container');
    existingToasts.forEach(toast => toast.remove());

    // Ajouter la nouvelle alerte
    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Afficher l'alerte
    const toastElement = document.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();

    // Nettoyer l'alerte quand elle est fermée
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.closest('.toast-container').remove();
    });
}

// Fonction pour afficher une belle alerte de confirmation
function showUpdateConfirmation() {
    // Créer l'alerte personnalisée
    const alertHtml = `
        <div class="modal fade" id="updateConfirmationModal" tabindex="-1" aria-labelledby="updateConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 text-white class="modal-title d-flex align-items-center" id="updateConfirmationModalLabel" style="color: white;">
                            <i class="fas fa-save me-2"></i>
                            Confirmation de mise à jour
                        </h5>
                        <button style="background-color: red;" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-question-circle text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="text-dark mb-3">Êtes-vous sûr de vouloir mettre à jour ce colis ?</h6>
                            <p class="text-muted mb-0">
                                Cette action modifiera les informations du colis <strong>{{ $colis->code ?? 'N/A' }}</strong>
                                et créera de nouveaux colis si des formulaires supplémentaires ont été générés.
                            </p>
                        </div>
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>
                                <strong>Note :</strong> Les modifications seront sauvegardées et le package sera mis à jour automatiquement.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Annuler
                        </button>
                        <button type="button" class="btn btn-primary" id="confirmUpdateBtn">
                            <i class="fas fa-save me-1"></i>
                            Confirmer la mise à jour
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Supprimer l'ancienne alerte si elle existe
    const existingModal = document.getElementById('updateConfirmationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Ajouter la nouvelle alerte au body
    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Afficher l'alerte
    const modal = new bootstrap.Modal(document.getElementById('updateConfirmationModal'));
    modal.show();

    // Gérer la confirmation
    document.getElementById('confirmUpdateBtn').addEventListener('click', function() {
        modal.hide();
        // Soumettre le formulaire
        document.getElementById('colisForm').submit();
    });

    // Nettoyer l'alerte quand elle est fermée
    document.getElementById('updateConfirmationModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Gestion des engins du livreur (même méthode que create.blade.php)
document.addEventListener('DOMContentLoaded', function() {
    // Données des livreurs avec leurs engins
    const livreursData = @json($livreurs);

    const livreurSelect = document.getElementById('livreur_id');
    const enginSelect = document.getElementById('engin_id');
    const livreurEnginInfo = document.getElementById('livreur-engin-info');
    const livreurEnginText = document.getElementById('livreur-engin-text');

    // Fonction AJAX pour récupérer l'engin du livreur
    function loadEnginByLivreur(livreurId) {
        if (!livreurId) {
            enginSelect.innerHTML = '<option value="">Sélectionnez d\'abord un livreur</option>';
            enginSelect.disabled = true;
            livreurEnginInfo.style.display = 'none';
            return;
        }

        // Afficher un indicateur de chargement
        enginSelect.innerHTML = '<option value="">Chargement...</option>';
        enginSelect.disabled = true;

        fetch(`/colis/engins-by-livreur/${livreurId}`, {
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
            if (data.success && data.engin) {
                // Vider le dropdown et ajouter l'engin du livreur
                enginSelect.innerHTML = '';
                const option = document.createElement('option');
                option.value = data.engin.id;
                option.textContent = `${data.engin.libelle} - ${data.engin.matricule}${data.engin.type_engin ? ' (' + data.engin.type_engin.libelle + ')' : ''}`;
                option.selected = true;
                enginSelect.appendChild(option);

                // Activer le dropdown
                enginSelect.disabled = false;

                // Afficher l'info
                livreurEnginText.textContent = `Engin assigné à ce livreur`;
                livreurEnginInfo.style.display = 'block';

                // Mettre à jour aussi les selects d'engin dans les formulaires de colis générés
                updateColisEnginSelects(data.engin);

                console.log('✅ Engin chargé via AJAX:', data.engin);
            } else {
                // Pas d'engin assigné au livreur
                enginSelect.innerHTML = '<option value="">Ce livreur n\'a pas d\'engin assigné</option>';
                enginSelect.disabled = true;
                livreurEnginInfo.style.display = 'none';

                // Réinitialiser les selects d'engin dans les formulaires de colis
                resetColisEnginSelects();

                console.log('⚠️ Aucun engin assigné à ce livreur');
            }
        })
        .catch(error => {
            console.error('❌ Erreur lors du chargement de l\'engin:', error);
            enginSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            enginSelect.disabled = true;
            livreurEnginInfo.style.display = 'none';
        });
    }

    // Gestion du changement de livreur
    if (livreurSelect) {
        livreurSelect.addEventListener('change', function() {
            const selectedLivreurId = this.value;
            console.log('🔄 Livreur changé:', selectedLivreurId);
            loadEnginByLivreur(selectedLivreurId);
        });
    }

    // Fonction pour mettre à jour les selects d'engin dans les formulaires de colis
    function updateColisEnginSelects(engin) {
        const colisEnginSelects = document.querySelectorAll('select[id^="colis_engin_"]');
        colisEnginSelects.forEach(select => {
            select.innerHTML = '';
            const option = document.createElement('option');
            option.value = engin.id;
            option.textContent = `${engin.libelle} - ${engin.matricule}${engin.type_engin ? ' (' + engin.type_engin.libelle + ')' : ''}`;
            option.selected = true;
            select.appendChild(option);
        });
    }

    // Fonction pour réinitialiser les selects d'engin dans les formulaires de colis
    function resetColisEnginSelects() {
        const colisEnginSelects = document.querySelectorAll('select[id^="colis_engin_"]');
        colisEnginSelects.forEach(select => {
            select.innerHTML = '<option value="">Sélectionner un engin (optionnel)</option>';
        });
    }

    // Initialisation au chargement de la page si un livreur est déjà sélectionné
    if (livreurSelect && livreurSelect.value) {
        console.log('🚀 Initialisation: Livreur déjà sélectionné au chargement:', livreurSelect.value);

        // Charger l'engin du livreur par défaut via AJAX
        setTimeout(() => {
            console.log('⏰ Chargement de l\'engin du livreur par défaut...');
            loadEnginByLivreur(livreurSelect.value);
        }, 500);
    } else {
        console.log('ℹ️ Initialisation: Aucun livreur sélectionné au chargement');
    }
});
</script>

@include('layouts.footer')

