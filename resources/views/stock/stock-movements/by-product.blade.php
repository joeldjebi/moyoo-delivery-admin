@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Mouvements de Stock - {{ $product->name }}</h5>
                            <p class="mb-4">Historique des mouvements pour ce produit.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour au produit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations du Produit</h6>
                            <p class="mb-1"><strong>Nom :</strong> {{ $product->name }}</p>
                            <p class="mb-1"><strong>SKU :</strong> {{ $product->sku ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Stock actuel :</strong> {{ $product->getTotalStock() }} {{ $product->unit }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Catégorie</h6>
                            @if($product->category)
                                <span class="badge bg-label-info">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
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
                    @if($stockMovements->count() > 0)
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockMovements as $movement)
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
                                            <td>
                                                <a href="{{ route('stock-movements.show', $movement) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
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
                            <p class="text-muted mt-3">Aucun mouvement de stock enregistré pour ce produit.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

