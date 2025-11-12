@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Catégories</h5>
                            <p class="mb-4">Gérez facilement vos catégories de produits.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter une Catégorie
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('categories.index') }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nom ou description...">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactives</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-search me-1"></i>
                                Filtrer
                            </button>
                        </div>
                        @if(request()->has('search') || request()->has('status'))
                            <div class="col-12">
                                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-x me-1"></i>
                                    Réinitialiser les filtres
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Catégories</h5>
                </div>
                <div class="card-body">
                    @if($categories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Produits</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($category->icon)
                                                        <i class="{{ $category->icon }} me-2"></i>
                                                    @endif
                                                    <strong>{{ $category->name }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ Str::limit($category->description ?? 'Aucune description', 50) }}</td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $category->products_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-label-success">Active</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $category->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Affichage de {{ $categories->firstItem() ?? 0 }} à {{ $categories->lastItem() ?? 0 }} sur {{ $categories->total() }} résultats
                            </div>
                            <div>
                                {{ $categories->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-box-off text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Aucune catégorie trouvée.</p>
                            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer la première catégorie
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

