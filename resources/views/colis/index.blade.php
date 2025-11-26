@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Colis</h5>
                            <p class="mb-4">Liste et gestion de tous les colis du système</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('colis.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>
                                    Nouveau Colis
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('colis.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Code, client, téléphone...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>En attente</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>En cours</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Livré</option>
                                <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Annulé par client</option>
                                <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Annulé par livreur</option>
                                <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Annulé par marchand</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="zone_id" class="form-label">Zone</label>
                            <select class="form-select" id="zone_id" name="zone_id">
                                <option value="">Toutes les zones</option>
                                @foreach($zones ?? [] as $zone)
                                    <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                        {{ $zone->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="livreur_id" class="form-label">Livreur</label>
                            <select class="form-select" id="livreur_id" name="livreur_id">
                                <option value="">Tous les livreurs</option>
                                @foreach($livreurs ?? [] as $livreur)
                                    <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                        {{ $livreur->nom }} {{ $livreur->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Filtrer
                                </button>
                                <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Effacer
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des colis -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Colis</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">{{ $colis->total() }} colis au total</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($colis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleAllSelection()">
                                        </th>
                                        <th>Code</th>
                                        <th>Client</th>
                                        <th>Zone/Commune</th>
                                        <th>Livreur</th>
                                        <th>Ramassage</th>
                                        <th>Coût Livraison</th>
                                        <th>Statut</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($colis as $colisItem)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="colis-checkbox" value="{{ $colisItem->id }}" onchange="updateSelectedCount()">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="ti ti-package"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $colisItem->code ?? 'N/A' }}</h6>
                                                        <small class="text-muted">UUID: {{ Str::limit($colisItem->uuid, 8) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ $colisItem->nom_client ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $colisItem->telephone_client ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-label-primary">{{ $colisItem->zone->libelle ?? 'N/A' }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $colisItem->commune->libelle ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($colisItem->livreur)
                                                    <div>
                                                        <h6 class="mb-0">{{ $colisItem->livreur->first_name ?? 'N/A' }} {{ $colisItem->livreur->last_name ?? 'N/A' }}</h6>
                                                        <small class="text-muted">{{ $colisItem->livreur->mobile ?? 'N/A' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Non assigné</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($colisItem->ramassagePrincipal())
                                                    <div>
                                                        <span class="badge bg-info">
                                                            <i class="ti ti-package me-1"></i>
                                                            {{ $colisItem->ramassagePrincipal()->code_ramassage }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">{{ $colisItem->ramassagePrincipal()->marchand->first_name ?? '' }} {{ $colisItem->ramassagePrincipal()->marchand->last_name ?? '' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">
                                                    {{ $colisItem->delivery_cost_formatted }}
                                                </div>
                                            </td>
                                            <td>
                                                @switch($colisItem->status)
                                                    @case(0)
                                                        <span class="badge bg-label-warning">En attente</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge bg-label-info">En cours</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge bg-label-success">Livré</span>
                                                        @break
                                                    @case(3)
                                                        <span class="badge bg-label-danger">Annulé (client)</span>
                                                        @break
                                                    @case(4)
                                                        <span class="badge bg-label-danger">Annulé (livreur)</span>
                                                        @break
                                                    @case(5)
                                                        <span class="badge bg-label-danger">Annulé (marchand)</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-label-secondary">Inconnu</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $colisItem->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('colis.show', $colisItem->id) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('colis.edit', $colisItem->id) }}">
                                                            <i class="ti ti-pencil me-1"></i> Modifier
                                                        </a>
                                                        @if($colisItem->status == 0 && !$colisItem->livreur_id)
                                                            <a class="dropdown-item" href="#" onclick="assignLivreur({{ $colisItem->id }})">
                                                                <i class="ti ti-user-plus me-1"></i> Assigner livreur
                                                            </a>
                                                        @endif
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteColis({{ $colisItem->id }}, '{{ $colisItem->code }}')">
                                                            <i class="ti ti-trash me-1"></i> Supprimer
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Actions en masse -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">
                                            <span id="selectedCount">0</span> colis sélectionné(s)
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                            <i class="ti ti-x me-1"></i>
                                            Désélectionner tout
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="bulkUpdateStatus()" id="bulkStatusBtn" disabled>
                                            <i class="ti ti-edit me-1"></i>
                                            Changer statut
                                        </button>
                                        <button type="button" class="btn btn-info" onclick="bulkAssignLivreur()" id="bulkAssignBtn" disabled>
                                            <i class="ti ti-user-check me-1"></i>
                                            Assigner livreur
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $colis->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-package-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucun colis trouvé</h5>
                            <p class="text-muted">Aucun colis ne correspond aux critères de recherche.</p>
                            <a href="{{ route('colis.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer le premier colis
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'assignation de livreur -->
<div class="modal fade" id="assignLivreurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assigner un livreur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignLivreurForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_livreur_id" class="form-label">Livreur</label>
                        <select class="form-select" id="modal_livreur_id" name="livreur_id" required>
                            <option value="">Sélectionner un livreur</option>
                            @foreach($livreurs ?? [] as $livreur)
                                <option value="{{ $livreur->id }}">{{ $livreur->nom }} {{ $livreur->prenom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_engin_id" class="form-label">Engin (optionnel)</label>
                        <select class="form-select" id="modal_engin_id" name="engin_id">
                            <option value="">Sélectionner un engin</option>
                            @foreach($engins ?? [] as $engin)
                                <option value="{{ $engin->id }}">{{ $engin->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <i class="ti ti-alert-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-2 mb-1">Êtes-vous sûr ?</h4>
                        <p class="text-muted">Cette action ne peut pas être annulée. Le colis <strong id="colisCode"></strong> sera définitivement supprimé.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-1"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Modal supprimé - redirection vers la page d'assignation -->

<script>
// Gestion de la sélection des colis
let selectedColis = new Set();

function updateSelectedCount() {
    console.log('🔄 updateSelectedCount appelée');
    const checkboxes = document.querySelectorAll('.colis-checkbox:checked');
    console.log('📋 Checkboxes cochées:', checkboxes.length);

    selectedColis.clear();
    checkboxes.forEach(checkbox => {
        selectedColis.add(checkbox.value);
    });

    console.log('📊 selectedColis mis à jour:', selectedColis.size, 'colis');

    const selectedCountEl = document.getElementById('selectedCount');
    if (selectedCountEl) {
        selectedCountEl.textContent = selectedColis.size;
    } else {
        console.error('❌ Élément selectedCount non trouvé');
    }

    const bulkButtons = document.querySelectorAll('#bulkStatusBtn, #bulkAssignBtn');
    console.log('🔘 Boutons trouvés:', bulkButtons.length);
    bulkButtons.forEach(btn => {
        btn.disabled = selectedColis.size === 0;
        console.log('🔘 Bouton', btn.id, 'disabled:', btn.disabled);
    });
}

function toggleAllSelection() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.colis-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.colis-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');

    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;

    updateSelectedCount();
}

// Écouter les changements sur les checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.colis-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
});

// Fonctions pour les actions
function assignLivreur(colisId) {
    document.getElementById('assignLivreurForm').action = `/colis/${colisId}/assign-livreur`;
    const modal = new bootstrap.Modal(document.getElementById('assignLivreurModal'));
    modal.show();
}

function deleteColis(colisId, colisCode) {
    document.getElementById('colisCode').textContent = colisCode;
    document.getElementById('deleteForm').action = `/colis/${colisId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function bulkUpdateStatus() {
    if (selectedColis.size === 0) {
        alert('Veuillez sélectionner au moins un colis.');
        return;
    }
    // Implémenter la logique de mise à jour en masse
    alert('Fonctionnalité de mise à jour en masse à implémenter');
}

function bulkAssignLivreur() {
    console.log('🚀 bulkAssignLivreur appelée');
    console.log('📊 selectedColis.size:', selectedColis.size);
    console.log('📋 selectedColis:', selectedColis);

    if (selectedColis.size === 0) {
        alert('Veuillez sélectionner au moins un colis.');
        return;
    }

    // Rediriger vers la page d'assignation avec les IDs des colis sélectionnés
    const colisIds = Array.from(selectedColis).join(',');
    const assignUrl = `{{ route('colis.assign') }}?colis_ids=${colisIds}`;

    console.log('🔗 Redirection vers:', assignUrl);
    window.location.href = assignUrl;
}

// Code du modal supprimé - redirection vers la page d'assignation

// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

@include('layouts.footer')
