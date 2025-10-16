@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Délais</h5>
                            <p class="mb-4">Liste de tous les délais de votre entreprise</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('delais.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Nouveau Délai
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des délais -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Délais</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" class="form-control" placeholder="Rechercher un délai..." id="searchInput">
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

                    @if($delais->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libellé</th>
                                        <th>Entreprise</th>
                                        <th>Créé par</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($delais as $delai)
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-primary">#{{ $delai->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $delai->libelle }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">
                                                    {{ $delai->entreprise->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($delai->user->first_name ?? 'U', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $delai->user->first_name ?? 'N/A' }} {{ $delai->user->last_name ?? '' }}</div>
                                                        <small class="text-muted">{{ $delai->user->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ $delai->created_at ? $delai->created_at->format('d/m/Y à H:i') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('delais.show', $delai->id) }}">
                                                            <i class="ti ti-eye me-1"></i>
                                                            Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('delais.edit', $delai->id) }}">
                                                            <i class="ti ti-pencil me-1"></i>
                                                            Modifier
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <button type="button" class="dropdown-item text-danger" onclick="deleteDelai({{ $delai->id }}, '{{ $delai->libelle }}')">
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
                                Affichage de {{ $delais->firstItem() }} à {{ $delais->lastItem() }} sur {{ $delais->total() }} résultats
                            </div>
                            <div>
                                {{ $delais->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-clock text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted">Aucun délai trouvé</h5>
                            <p class="text-muted">Commencez par créer votre premier délai.</p>
                            <a href="{{ route('delais.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer un délai
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
                <p>Êtes-vous sûr de vouloir supprimer le délai <strong id="delaiName"></strong> ?</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de suppression
    window.deleteDelai = function(id, libelle) {
        document.getElementById('delaiName').textContent = libelle;
        document.getElementById('deleteForm').action = '/delais/' + id;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };

    // Fonction de recherche
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    function performSearch() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = '{{ route("delais.index") }}?search=' + encodeURIComponent(query);
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
