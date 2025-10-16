@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Modes de Livraison</h5>
                            <p class="mb-4">Liste de tous les modes de livraison de votre entreprise</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('mode-livraisons.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Nouveau Mode
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des modes de livraison -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Modes de Livraison</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" class="form-control" placeholder="Rechercher un mode..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($modeLivraisons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libellé</th>
                                        <th>Description</th>
                                        <th>Entreprise</th>
                                        <th>Créé par</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modeLivraisons as $modeLivraison)
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-primary">#{{ $modeLivraison->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $modeLivraison->libelle }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $modeLivraison->description ? Str::limit($modeLivraison->description, 50) : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">
                                                    {{ $modeLivraison->entreprise->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($modeLivraison->user->first_name ?? 'U', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $modeLivraison->user->first_name ?? 'N/A' }} {{ $modeLivraison->user->last_name ?? '' }}</div>
                                                        <small class="text-muted">{{ $modeLivraison->user->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $modeLivraison->created_at ? $modeLivraison->created_at->format('d/m/Y à H:i') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('mode-livraisons.show', $modeLivraison->id) }}">
                                                            <i class="ti ti-eye me-1"></i>
                                                            Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('mode-livraisons.edit', $modeLivraison->id) }}">
                                                            <i class="ti ti-pencil me-1"></i>
                                                            Modifier
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <button type="button" class="dropdown-item text-danger" onclick="deleteModeLivraison({{ $modeLivraison->id }}, '{{ $modeLivraison->libelle }}')">
                                                            <i class="ti ti-trash me-1"></i>
                                                            Supprimer
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Affichage de {{ $modeLivraisons->firstItem() }} à {{ $modeLivraisons->lastItem() }} sur {{ $modeLivraisons->total() }} résultats
                            </div>
                            <div>
                                {{ $modeLivraisons->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-truck text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted">Aucun mode de livraison trouvé</h5>
                            <p class="text-muted">Commencez par créer votre premier mode de livraison.</p>
                            <a href="{{ route('mode-livraisons.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer un mode
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le mode de livraison <strong id="modeLivraisonName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de suppression
    window.deleteModeLivraison = function(id, libelle) {
        document.getElementById('modeLivraisonName').textContent = libelle;
        document.getElementById('deleteForm').action = '/mode-livraisons/' + id;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };

    // Fonction de recherche
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    function performSearch() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = '{{ route("mode-livraisons.index") }}?search=' + encodeURIComponent(query);
        }
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
});
</script>

@include('layouts.footer')
