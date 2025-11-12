@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Modifier le Stock</h5>
                            <p class="mb-4">Modifiez les informations du stock.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Stock</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stocks.update', $stock) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Produit</label>
                                <input type="text" class="form-control" value="{{ $stock->product->name ?? 'N/A' }}" disabled>
                                <small class="text-muted">Le produit ne peut pas être modifié</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Emplacement</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $stock->location) }}" placeholder="Ex: Entrepôt principal, Magasin 1">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantité actuelle</label>
                                <input type="text" class="form-control" value="{{ $stock->quantity }}" disabled>
                                <small class="text-muted">Utilisez les fonctions d'entrée/sortie pour modifier la quantité</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="min_quantity" class="form-label">Seuil minimum d'alerte</label>
                                <input type="number" class="form-control @error('min_quantity') is-invalid @enderror" id="min_quantity" name="min_quantity" value="{{ old('min_quantity', $stock->min_quantity) }}" min="0">
                                @error('min_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Alerte lorsque le stock atteint ce niveau</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_quantity" class="form-label">Seuil maximum</label>
                                <input type="number" class="form-control @error('max_quantity') is-invalid @enderror" id="max_quantity" name="max_quantity" value="{{ old('max_quantity', $stock->max_quantity) }}" min="0">
                                @error('max_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Stock maximum autorisé (optionnel)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="unit_cost" class="form-label">Coût unitaire</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('unit_cost') is-invalid @enderror" id="unit_cost" name="unit_cost" value="{{ old('unit_cost', $stock->unit_cost) }}" placeholder="0.00">
                                @error('unit_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Coût unitaire moyen en XOF</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Enregistrer les modifications
                            </button>
                            <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

