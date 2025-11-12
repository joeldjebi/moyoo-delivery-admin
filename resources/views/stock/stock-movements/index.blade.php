@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Mouvements de Stock</h5>
                            <p class="mb-4">Historique de tous les mouvements de stock.</p>
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

    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('stock-movements.index') }}" class="row g-3">
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
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Tous les types</option>
                                <option value="entree" {{ request('type') == 'entree' ? 'selected' : '' }}>Entrée</option>
                                <option value="sortie" {{ request('type') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                                <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
                                <option value="transfert" {{ request('type') == 'transfert' ? 'selected' : '' }}>Transfert</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nom, SKU, raison...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>
                                Filtrer
                            </button>
                            @if(request()->has('product_id') || request()->has('type') || request()->has('date_from') || request()->has('date_to') || request()->has('search'))
                                <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="card-title mb-0">Historique des Mouvements</h5>
                </div>
                <div class="card-body">
                    @if(isset($stockMovements) && $stockMovements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Produit</th>
                                        <th>Type</th>
                                        <th>Quantité</th>
                                        <th>Avant</th>
                                        <th>Après</th>
                                        <th>Raison</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $movement->product->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $movement->type == 'entree' ? 'success' : ($movement->type == 'sortie' ? 'danger' : 'warning') }}">
                                                {{ $movement->formatted_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="{{ $movement->type == 'entree' ? 'text-success' : 'text-danger' }}">
                                                {{ $movement->type == 'entree' ? '+' : '-' }}{{ abs($movement->quantity) }}
                                            </strong>
                                        </td>
                                        <td>{{ $movement->quantity_before }}</td>
                                        <td>{{ $movement->quantity_after }}</td>
                                        <td>{{ $movement->reason ?? 'N/A' }}</td>
                                        <td>{{ $movement->user->first_name ?? 'N/A' }} {{ $movement->user->last_name ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Affichage de {{ $stockMovements->firstItem() ?? 0 }} à {{ $stockMovements->lastItem() ?? 0 }} sur {{ $stockMovements->total() }} résultats
                            </div>
                            <div>
                                {{ $stockMovements->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-history text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Aucun mouvement de stock enregistré.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

