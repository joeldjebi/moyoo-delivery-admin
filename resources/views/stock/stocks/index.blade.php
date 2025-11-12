@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Stocks</h5>
                            <p class="mb-4">Gérez facilement vos stocks et leurs mouvements.</p>
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
                    <form method="GET" action="{{ route('stocks.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="product_id" class="form-label">Produit</label>
                            <select class="form-select" id="product_id" name="product_id">
                                <option value="">Tous les produits</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">Emplacement</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ request('location') }}" placeholder="Emplacement...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Stock faible</option>
                                <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="max" {{ request('status') == 'max' ? 'selected' : '' }}>Stock maximum</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nom ou SKU...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>
                                Filtrer
                            </button>
                            @if(request()->has('product_id') || request()->has('location') || request()->has('status') || request()->has('search'))
                                <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Réinitialiser
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Stocks</h5>
                    <a href="{{ route('stocks.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Créer un stock
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($stocks) && $stocks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Emplacement</th>
                                        <th>Quantité</th>
                                        <th>Seuil min</th>
                                        <th>Seuil max</th>
                                        <th>Coût unitaire</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stocks as $stock)
                                    <tr>
                                        <td>
                                            <strong>{{ $stock->product->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $stock->location ?? 'Principal' }}</td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $stock->quantity }} {{ $stock->product->unit ?? '' }}</span>
                                        </td>
                                        <td>{{ $stock->min_quantity }}</td>
                                        <td>{{ $stock->max_quantity ?? 'Illimité' }}</td>
                                        <td>{{ number_format($stock->unit_cost, 0, ',', ' ') }} XOF</td>
                                        <td>
                                            @if($stock->isLowStock())
                                                <span class="badge bg-label-danger">Stock faible</span>
                                            @elseif($stock->isMaxStock())
                                                <span class="badge bg-label-warning">Stock maximum</span>
                                            @else
                                                <span class="badge bg-label-success">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('stocks.show', $stock) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('stocks.edit', $stock) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Affichage de {{ $stocks->firstItem() ?? 0 }} à {{ $stocks->lastItem() ?? 0 }} sur {{ $stocks->total() }} résultats
                            </div>
                            <div>
                                {{ $stocks->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-box-off text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Aucun stock trouvé.</p>
                            <p class="text-muted">Créez des produits pour gérer leurs stocks.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

