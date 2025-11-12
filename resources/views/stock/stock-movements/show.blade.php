@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Mouvement de Stock</h5>
                            <p class="mb-4">Informations complètes du mouvement.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
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
                    <h5 class="card-title mb-0">Informations du Mouvement</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Produit :</strong></div>
                        <div class="col-sm-9">
                            <strong>{{ $movement->product->name ?? 'N/A' }}</strong>
                            @if($movement->product)
                                <br><small class="text-muted">SKU: {{ $movement->product->sku ?? 'N/A' }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Type :</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-label-{{ $movement->type == 'entree' ? 'success' : ($movement->type == 'sortie' ? 'danger' : 'warning') }}">
                                {{ $movement->formatted_type }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Quantité :</strong></div>
                        <div class="col-sm-9">
                            <strong class="{{ $movement->type == 'entree' ? 'text-success' : 'text-danger' }}">
                                {{ $movement->type == 'entree' ? '+' : '-' }}{{ abs($movement->quantity) }}
                            </strong>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Quantité avant :</strong></div>
                        <div class="col-sm-9">{{ $movement->quantity_before }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Quantité après :</strong></div>
                        <div class="col-sm-9">
                            <strong>{{ $movement->quantity_after }}</strong>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Coût unitaire :</strong></div>
                        <div class="col-sm-9">
                            @if($movement->unit_cost)
                                {{ number_format($movement->unit_cost, 0, ',', ' ') }} XOF
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Raison :</strong></div>
                        <div class="col-sm-9">{{ $movement->reason ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Référence :</strong></div>
                        <div class="col-sm-9">{{ $movement->reference ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Emplacement :</strong></div>
                        <div class="col-sm-9">{{ $movement->location ?? 'Principal' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Utilisateur :</strong></div>
                        <div class="col-sm-9">
                            {{ $movement->user->first_name ?? 'N/A' }} {{ $movement->user->last_name ?? '' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Date :</strong></div>
                        <div class="col-sm-9">{{ $movement->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Valeur du mouvement :</span>
                            <strong>
                                @if($movement->unit_cost)
                                    {{ number_format($movement->quantity * $movement->unit_cost, 0, ',', ' ') }} XOF
                                @else
                                    N/A
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

