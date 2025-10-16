@include('layouts.header')
@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Détails de l'Engin</h5>
                    <a href="{{ route('engins.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Libellé</label>
                                    <p class="form-control-plaintext">{{ $engin->libelle }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Matricule</label>
                                    <p class="form-control-plaintext">{{ $engin->matricule }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Marque</label>
                                    <p class="form-control-plaintext">{{ $engin->marque }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Modèle</label>
                                    <p class="form-control-plaintext">{{ $engin->modele }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Couleur</label>
                                    <p class="form-control-plaintext">{{ $engin->couleur }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Immatriculation</label>
                                    <p class="form-control-plaintext">{{ $engin->immatriculation }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">État</label>
                                    <p class="form-control-plaintext">
                                        @switch($engin->etat)
                                            @case('neuf')
                                                <span class="badge bg-label-success">Neuf</span>
                                                @break
                                            @case('occasion')
                                                <span class="badge bg-label-warning">Occasion</span>
                                                @break
                                            @case('endommage')
                                                <span class="badge bg-label-danger">Endommagé</span>
                                                @break
                                            @default
                                                <span class="badge bg-label-secondary">{{ $engin->etat }}</span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Statut</label>
                                    <p class="form-control-plaintext">
                                        @switch($engin->status)
                                            @case('actif')
                                                <span class="badge bg-label-success">Actif</span>
                                                @break
                                            @case('inactif')
                                                <span class="badge bg-label-secondary">Inactif</span>
                                                @break
                                            @case('maintenance')
                                                <span class="badge bg-label-warning">Maintenance</span>
                                                @break
                                            @default
                                                <span class="badge bg-label-secondary">{{ $engin->status }}</span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Type d'Engin</label>
                                    <p class="form-control-plaintext">
                                        @if($engin->typeEngin)
                                            <span class="badge bg-label-info">{{ $engin->typeEngin->libelle }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Créé par</label>
                                    <p class="form-control-plaintext">
                                        @if($engin->user)
                                            {{ $engin->user->email }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Date de création</label>
                                    <p class="form-control-plaintext">{{ $engin->created_at ? $engin->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Dernière modification</label>
                                    <p class="form-control-plaintext">{{ $engin->updated_at ? $engin->updated_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Informations</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                {{ substr($engin->libelle, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $engin->libelle }}</h6>
                                            <small class="text-muted">{{ $engin->matricule }}</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Marque</small>
                                        <p class="mb-0 fw-semibold">{{ $engin->marque }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Modèle</small>
                                        <p class="mb-0 fw-semibold">{{ $engin->modele }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Couleur</small>
                                        <p class="mb-0 fw-semibold">{{ $engin->couleur }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="{{ route('engins.edit', ['engin' => $engin->id]) }}" class="btn btn-primary">
                                    <i class="ti ti-pencil me-1"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('engins.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                                @if($engin->colis->count() == 0)
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteEngin({{ $engin->id }}, '{{ $engin->libelle }}')">
                                        <i class="ti ti-trash me-1"></i>
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($engin->colis->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Colis associés ({{ $engin->colis->count() }})</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Libellé</th>
                                                        <th>Poids</th>
                                                        <th>Statut</th>
                                                        <th>Date de création</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($engin->colis as $colis)
                                                        <tr>
                                                            <td>{{ $colis->id }}</td>
                                                            <td>{{ $colis->libelle }}</td>
                                                            <td>{{ $colis->poids }} kg</td>
                                                            <td>
                                                                @switch($colis->statut)
                                                                    @case('en_attente')
                                                                        <span class="badge bg-label-warning">En attente</span>
                                                                        @break
                                                                    @case('en_cours')
                                                                        <span class="badge bg-label-info">En cours</span>
                                                                        @break
                                                                    @case('livre')
                                                                        <span class="badge bg-label-success">Livré</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge bg-label-secondary">{{ $colis->statut }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>{{ $colis->created_at ? $colis->created_at->format('d/m/Y à H:i') : 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalTitle">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <i class="ti ti-alert-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-2 mb-1">Êtes-vous sûr ?</h4>
                        <p class="text-muted">Cette action ne peut pas être annulée. L'engin <strong id="enginName"></strong> sera définitivement supprimé.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
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
</div>
<script>
function deleteEngin(enginId, enginName) {
    document.getElementById('enginName').textContent = enginName;
    document.getElementById('deleteForm').action = `/engins/${enginId}`;

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@include('layouts.footer')
