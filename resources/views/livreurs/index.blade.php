@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-truck me-2"></i>
                        Gestion des Livreurs
                    </h5>
                    <a href="{{ route('livreurs.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Nouveau Livreur
                    </a>
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

                    <!-- Filtres et recherche -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Rechercher un livreur..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">Tous les statuts</option>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="ti ti-x me-1"></i>
                                Effacer
                            </button>
                        </div>
                    </div>

                    <!-- Tableau des livreurs -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom complet</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Engin</th>
                                    <th>Zone d'activité</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($livreurs as $livreur)
                                    <tr>
                                        <td>
                                            @if($livreur->photo)
                                                <img src="{{ asset('storage/' . $livreur->photo) }}"
                                                     alt="Photo de {{ $livreur->first_name }}"
                                                     class="rounded-circle"
                                                     width="40"
                                                     height="40">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="ti ti-user text-white"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $livreur->first_name }} {{ $livreur->last_name }}</strong>
                                                @if($livreur->permis)
                                                    <br>
                                                    <small class="text-muted">Permis: {{ $livreur->permis }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <a href="tel:{{ $livreur->mobile }}" class="text-decoration-none">
                                                <i class="ti ti-phone me-1"></i>
                                                {{ $livreur->mobile }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($livreur->email)
                                                <a href="mailto:{{ $livreur->email }}" class="text-decoration-none">
                                                    <i class="ti ti-mail me-1"></i>
                                                    {{ $livreur->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($livreur->engin)
                                                <div>
                                                    <strong>{{ $livreur->engin->libelle }}</strong>
                                                    @if($livreur->engin->typeEngin)
                                                        <br>
                                                        <small class="text-muted">{{ $livreur->engin->typeEngin->libelle }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($livreur->communes->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($livreur->communes as $commune)
                                                        <span class="badge bg-info">{{ $commune->libelle }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($livreur->status === 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('livreurs.show', $livreur) }}">
                                                            <i class="ti ti-eye me-2"></i>
                                                            Voir détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('livreurs.edit', $livreur) }}">
                                                            <i class="ti ti-edit me-2"></i>
                                                            Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('livreurs.colis', $livreur) }}">
                                                            <i class="ti ti-package me-2"></i>
                                                            Voir colis
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('livreurs.livraisons', $livreur) }}">
                                                            <i class="ti ti-truck-delivery me-2"></i>
                                                            Voir livraisons
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('livreurs.destroy', $livreur) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce livreur ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ti ti-trash me-2"></i>
                                                                Supprimer
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti ti-truck-off fs-1 mb-3"></i>
                                                <p>Aucun livreur trouvé</p>
                                                <a href="{{ route('livreurs.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i>
                                                    Ajouter le premier livreur
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($livreurs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $livreurs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    // Fonction de recherche
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusBadge = row.querySelector('.badge');
            const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Événements
    searchInput.addEventListener('input', performSearch);
    statusFilter.addEventListener('change', performSearch);

    // Fonction pour effacer les filtres
    window.clearFilters = function() {
        searchInput.value = '';
        statusFilter.value = '';
        performSearch();
    };
});
</script>

@include('layouts.footer')
