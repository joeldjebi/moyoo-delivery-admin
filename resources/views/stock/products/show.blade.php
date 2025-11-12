@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Produit</h5>
                            <p class="mb-4">Informations complètes du produit.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i>
                                Modifier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Produit</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Nom :</strong></div>
                        <div class="col-sm-9">{{ $product->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Catégorie :</strong></div>
                        <div class="col-sm-9">
                            @if($product->category)
                                <span class="badge bg-label-info">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Code SKU :</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-label-secondary">{{ $product->sku ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Code-barres :</strong></div>
                        <div class="col-sm-9">{{ $product->barcode ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Prix :</strong></div>
                        <div class="col-sm-9">
                            <strong>{{ $product->formatted_price }}</strong>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Unité :</strong></div>
                        <div class="col-sm-9">{{ $product->unit }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Description :</strong></div>
                        <div class="col-sm-9">{{ $product->description ?? 'Aucune description' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Statut :</strong></div>
                        <div class="col-sm-9">
                            @if($product->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Date de création :</strong></div>
                        <div class="col-sm-9">{{ $product->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Image</h5>
                </div>
                <div class="card-body text-center">
                    @if($product->image)
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 300px; border-radius: 8px;">
                    @else
                        <div class="avatar avatar-xl">
                            <span class="avatar-initial rounded bg-label-secondary">
                                {{ strtoupper(substr($product->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Stock total :</span>
                            <strong>{{ $product->getTotalStock() }} {{ $product->unit }}</strong>
                        </div>
                    </div>
                    @if($stocks->count() > 0)
                        <hr>
                        <h6 class="mb-3">Stocks par emplacement</h6>
                        @foreach($stocks as $stock)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $stock->location ?? 'Principal' }}:</span>
                                    <strong>{{ $stock->quantity }} {{ $product->unit }}</strong>
                                </div>
                                @if($stock->isLowStock())
                                    <small class="text-danger"><i class="ti ti-alert-triangle"></i> Stock faible</small>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Historique des Mouvements de Stock</h5>
                </div>
                <div class="card-body">
                    @if($movements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Quantité</th>
                                        <th>Avant</th>
                                        <th>Après</th>
                                        <th>Raison</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
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
                                Affichage de {{ $movements->firstItem() ?? 0 }} à {{ $movements->lastItem() ?? 0 }} sur {{ $movements->total() }} résultats
                            </div>
                            <div>
                                {{ $movements->links() }}
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

