@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Colis</h5>
                            <p class="mb-4">Informations complètes du colis : {{ $colis->code ?? 'N/A' }}</p>
                            <label class="form-label fw-semibold">Coût de livraison</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-success fs-6">{{ $colis->delivery_cost_formatted }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('colis.edit', $colis->id) }}" class="btn btn-warning">
                                    <i class="ti ti-pencil me-1"></i>
                                    Modifier
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

    <!-- Informations principales -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-package me-2"></i>
                        Informations du Colis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Code du colis</label>
                                <p class="form-control-plaintext">{{ $colis->code ?? 'N/A' }}</p>
                            </div>
                            @if($colis->ramassagePrincipal())
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ramassage lié</label>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">
                                            <i class="ti ti-package me-1"></i>
                                            {{ $colis->ramassagePrincipal()->code_ramassage }}
                                        </span>
                                        <a href="{{ route('ramassages.show', $colis->ramassagePrincipal()->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye me-1"></i>
                                            Voir le ramassage
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        Marchand: {{ $colis->ramassagePrincipal()->marchand->first_name ?? '' }} {{ $colis->ramassagePrincipal()->marchand->last_name ?? '' }}
                                    </small>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Statut</label>
                                <div>
                                    @switch($colis->status)
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
                                            <span class="badge bg-label-danger">Annulé par le client</span>
                                            @break
                                        @case(4)
                                            <span class="badge bg-label-danger">Annulé par le livreur</span>
                                            @break
                                        @case(5)
                                            <span class="badge bg-label-danger">Annulé par le marchand</span>
                                            @break
                                        @default
                                            <span class="badge bg-label-secondary">Inconnu</span>
                                    @endswitch
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date de création</label>
                                <p class="form-control-plaintext">{{ $colis->created_at ? $colis->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dernière modification</label>
                                <p class="form-control-plaintext">{{ $colis->updated_at ? $colis->updated_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-map-pin me-2"></i>
                        Zone de livraison
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Zone de livraison</label>
                        <p class="form-control-plaintext">
                            @if($colis->zone)
                                <span class="badge bg-label-primary">{{ $colis->zone->libelle ?? 'N/A' }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>
                    </div>
                    @if($colis->date_livraison_prevue)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date de livraison prévue</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($colis->date_livraison_prevue)->format('d/m/Y à H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Informations client et marchand -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-user me-2"></i>
                        Informations Client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom du client</label>
                        <p class="form-control-plaintext">{{ $colis->nom_client ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Téléphone</label>
                        <p class="form-control-plaintext">{{ $colis->telephone_client ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adresse</label>
                        <p class="form-control-plaintext">{{ $colis->adresse_client ?? 'N/A' }}</p>
                    </div>
                    @if($colis->note_client)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Note du client</label>
                            <p class="form-control-plaintext">{{ $colis->note_client }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-building me-2"></i>
                        Informations Marchand
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Marchand</label>
                        <p class="form-control-plaintext">{{ $colis->marchand->first_name ?? 'N/A' }} {{ $colis->marchand->last_name ?? 'N/A' }} - ({{ $colis->marchand->mobile ?? 'N/A' }})</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Boutique</label>
                        <p class="form-control-plaintext">{{ $colis->boutique->libelle ?? 'N/A' }} - ({{ $colis->boutique->mobile ?? 'N/A' }})</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant à encaisser</label>
                        <p class="form-control-plaintext">{{ number_format($colis->montant_a_encaisse ?? 0, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prix de vente</label>
                        <p class="form-control-plaintext">{{ number_format($colis->prix_de_vente ?? 0, 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de livraison -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-truck me-2"></i>
                        Livreur et Engin
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Livreur assigné</label>
                        <p class="form-control-plaintext">
                            @if($colis->livreur)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded bg-label-success">
                                            {{ substr($colis->livreur->nom, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $colis->livreur->nom }} {{ $colis->livreur->prenom }}</h6>
                                        <small class="text-muted">{{ $colis->livreur->telephone }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Engin assigné</label>
                        <p class="form-control-plaintext">
                            @if($colis->engin)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class="ti ti-car"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $colis->engin->libelle }}</h6>
                                        <small class="text-muted">{{ $colis->engin->matricule }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-package me-2"></i>
                        Détails du Colis
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $communeZone = $colis->commune_zone;
                    @endphp
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type de colis</label>
                        <p class="form-control-plaintext">{{ $communeZone->typeColis->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Conditionnement</label>
                        <p class="form-control-plaintext">{{ $communeZone->conditionnementColis->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Poids</label>
                        <p class="form-control-plaintext">{{ $communeZone->poids->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Délai</label>
                        <p class="form-control-plaintext">{{ $communeZone->delai->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode de livraison</label>
                        <p class="form-control-plaintext">{{ $communeZone->modeLivraison->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Période</label>
                        <p class="form-control-plaintext">{{ $colis->temp->libelle ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Coût de livraison</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-label-success fs-6">{{ $colis->delivery_cost_formatted }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions et notes -->
    @if($colis->instructions_livraison || $colis->numero_de_ramassage || $colis->adresse_de_ramassage)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti ti-note me-2"></i>
                            Instructions et Informations Supplémentaires
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($colis->instructions_livraison)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Instructions de livraison</label>
                                        <p class="form-control-plaintext">{{ $colis->instructions_livraison }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($colis->numero_de_ramassage)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Numéro de ramassage</label>
                                        <p class="form-control-plaintext">{{ $colis->numero_de_ramassage }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($colis->adresse_de_ramassage)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Adresse de ramassage</label>
                                        <p class="form-control-plaintext">{{ $colis->adresse_de_ramassage }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-settings me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        @if($colis->status == 0 && !$colis->livreur_id)
                            <button type="button" class="btn btn-primary" onclick="assignLivreur({{ $colis->id }})">
                                <i class="ti ti-user-plus me-1"></i>
                                Assigner un livreur
                            </button>
                        @endif

                        @if($colis->status == 1)
                            <button type="button" class="btn btn-success" onclick="markAsDelivered({{ $colis->id }})">
                                <i class="ti ti-check me-1"></i>
                                Marquer comme livré
                            </button>
                        @endif

                        @if(in_array($colis->status, [0, 1]))
                            <button type="button" class="btn btn-warning" onclick="toggleStatus({{ $colis->id }})">
                                <i class="ti ti-toggle-left me-1"></i>
                                Changer le statut
                            </button>
                        @endif

                        <a href="{{ route('colis.edit', $colis->id) }}" class="btn btn-outline-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>

                        <button type="button" class="btn btn-outline-danger" onclick="deleteColis({{ $colis->id }}, '{{ $colis->code }}')">
                            <i class="ti ti-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
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
                                <option value="{{ $engin->id }}">{{ $engin->libelle }} - {{ $engin->matricule }}</option>
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


<script>
// Fonctions pour les actions
function assignLivreur(colisId) {
    document.getElementById('assignLivreurForm').action = `/colis/${colisId}/assign-livreur`;
    const modal = new bootstrap.Modal(document.getElementById('assignLivreurModal'));
    modal.show();
}

function markAsDelivered(colisId) {
    if (confirm('Êtes-vous sûr de vouloir marquer ce colis comme livré ?')) {
        fetch(`/colis/${colisId}/mark-delivered`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour du statut.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la mise à jour du statut.');
        });
    }
}

function toggleStatus(colisId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de ce colis ?')) {
        fetch(`/colis/${colisId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour du statut.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la mise à jour du statut.');
        });
    }
}

function deleteColis(colisId, colisCode) {
    document.getElementById('colisCode').textContent = colisCode;
    document.getElementById('deleteForm').action = `/colis/${colisId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

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
