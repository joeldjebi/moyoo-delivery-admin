@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-user-check me-2"></i>
                        Assignation en masse des Colis
                    </h5>
                    <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé des colis sélectionnés -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-package me-2"></i>
                        Colis sélectionnés ({{ $selectedColis->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Client</th>
                                    <th>Zone</th>
                                    <th>Poids</th>
                                    <th>Mode</th>
                                    <th>Coût</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedColis as $colis)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $colis->code }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $colis->nom_client ?? 'N/A' }}</strong>
                                            @if($colis->telephone_client)
                                                <br><small class="text-muted">{{ $colis->telephone_client }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $colis->commune->libelle ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $colis->poids->libelle ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ $colis->modeLivraison->libelle ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $colis->delivery_cost_formatted }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire d'assignation -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-user-plus me-2"></i>
                        Assigner un livreur
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('colis.bulk-assign-livreur') }}">
                        @csrf
                        @method('PATCH')

                        <!-- Champ caché pour les IDs des colis -->
                        <input type="hidden" name="colis_ids" value="{{ $colisIds }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="livreur_id" class="form-label">
                                        Livreur <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('livreur_id') is-invalid @enderror"
                                            id="livreur_id" name="livreur_id" required>
                                        <option value="">Sélectionner un livreur</option>
                                        @foreach($livreurs as $livreur)
                                            <option value="{{ $livreur->id }}"
                                                    {{ old('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                                {{ $livreur->nom }} {{ $livreur->prenom }}
                                                @if($livreur->telephone)
                                                    - {{ $livreur->telephone }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('livreur_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="engin_id" class="form-label">
                                        Engin du livreur
                                    </label>
                                    <select class="form-select @error('engin_id') is-invalid @enderror"
                                            id="engin_id" name="engin_id" disabled>
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
                        </div>

                        <!-- Informations sur l'assignation -->
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Information :</strong>
                            {{ $selectedColis->count() }} colis seront assignés au livreur sélectionné.
                            Le statut de ces colis passera automatiquement à "En cours".
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-user-check me-1"></i>
                                Assigner les colis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données des livreurs avec leurs engins
    const livreursData = @json($livreurs);

    // Validation du formulaire
    const form = document.querySelector('form');
    const livreurSelect = document.getElementById('livreur_id');
    const enginSelect = document.getElementById('engin_id');
    const livreurEnginInfo = document.getElementById('livreur-engin-info');
    const livreurEnginText = document.getElementById('livreur-engin-text');

    // Gestion du changement de livreur
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

    form.addEventListener('submit', function(e) {
        if (!livreurSelect.value) {
            e.preventDefault();
            alert('Veuillez sélectionner un livreur.');
            livreurSelect.focus();
            return false;
        }

        // Confirmation avant assignation
        const colisCount = {{ $selectedColis->count() }};
        const livreurName = livreurSelect.options[livreurSelect.selectedIndex].text;

        if (!confirm(`Êtes-vous sûr de vouloir assigner ${colisCount} colis au livreur "${livreurName}" ?`)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

@include('layouts.footer')
