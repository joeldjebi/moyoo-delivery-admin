@include('layouts.header')
@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Engins</h5>
                    <a href="{{ route('engins.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Ajouter un Engin
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Libellé</th>
                                    <th>Matricule</th>
                                    <th>Marque</th>
                                    <th>Modèle</th>
                                    <th>Type d'Engin</th>
                                    <th>État</th>
                                    <th>Statut</th>
                                    <th>Créé par</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($engins as $engin)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        {{ substr($engin->libelle, 0, 2) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $engin->libelle }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $engin->matricule }}</td>
                                        <td>{{ $engin->marque }}</td>
                                        <td>{{ $engin->modele }}</td>
                                        <td>
                                            @if($engin->typeEngin)
                                                <span class="badge bg-label-info">{{ $engin->typeEngin->libelle }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
                                            @if($engin->user)
                                                {{ $engin->user->email }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $engin->created_at ? $engin->created_at->format('d/m/Y à H:i') : 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('engins.show', ['engin' => $engin->id]) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('engins.edit', ['engin' => $engin->id]) }}">
                                                        <i class="ti ti-pencil me-1"></i> Modifier
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteEngin({{ $engin->id }}, '{{ $engin->libelle }}')">
                                                        <i class="ti ti-trash me-1"></i> Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ti ti-car me-2" style="font-size: 2rem; color: #ccc;"></i>
                                                <p class="text-muted mb-0">Aucun engin trouvé</p>
                                                <a href="{{ route('engins.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="ti ti-plus me-1"></i>
                                                    Ajouter le premier engin
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($engins->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $engins->links() }}
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
