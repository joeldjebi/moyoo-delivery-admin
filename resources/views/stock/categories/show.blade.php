@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails de la Catégorie</h5>
                            <p class="mb-4">Informations complètes de la catégorie.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary">
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
                    <h5 class="card-title mb-0">Informations de la Catégorie</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Nom :</strong></div>
                        <div class="col-sm-9">
                            @if($category->icon)
                                <i class="{{ $category->icon }} me-2"></i>
                            @endif
                            {{ $category->name }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Description :</strong></div>
                        <div class="col-sm-9">{{ $category->description ?? 'Aucune description' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Statut :</strong></div>
                        <div class="col-sm-9">
                            @if($category->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Ordre d'affichage :</strong></div>
                        <div class="col-sm-9">{{ $category->sort_order }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Date de création :</strong></div>
                        <div class="col-sm-9">{{ $category->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Nombre de produits :</span>
                            <strong>{{ $category->products()->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Produits de cette catégorie</h5>
                </div>
                <div class="card-body">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>SKU</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td><span class="badge bg-label-info">{{ $product->sku ?? 'N/A' }}</span></td>
                                            <td>{{ $product->formatted_price }}</td>
                                            <td>{{ $product->getTotalStock() }}</td>
                                            <td>
                                                @if($product->is_active)
                                                    <span class="badge bg-label-success">Active</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
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
                                Affichage de {{ $products->firstItem() ?? 0 }} à {{ $products->lastItem() ?? 0 }} sur {{ $products->total() }} résultats
                            </div>
                            <div>
                                {{ $products->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-box-off text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Aucun produit dans cette catégorie.</p>
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter un produit
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

