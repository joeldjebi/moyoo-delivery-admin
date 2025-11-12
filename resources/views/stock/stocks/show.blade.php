@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Stock</h5>
                            <p class="mb-4">Informations complètes du stock.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                            <a href="{{ route('stocks.edit', $stock) }}" class="btn btn-primary">
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
                    <h5 class="card-title mb-0">Informations du Stock</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Produit :</strong></div>
                        <div class="col-sm-9">
                            <strong>{{ $stock->product->name ?? 'N/A' }}</strong>
                            @if($stock->product)
                                <br><small class="text-muted">SKU: {{ $stock->product->sku ?? 'N/A' }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Emplacement :</strong></div>
                        <div class="col-sm-9">{{ $stock->location ?? 'Principal' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Quantité :</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-label-primary" style="font-size: 1rem;">
                                {{ $stock->quantity }} {{ $stock->product->unit ?? '' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Seuil minimum :</strong></div>
                        <div class="col-sm-9">{{ $stock->min_quantity }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Seuil maximum :</strong></div>
                        <div class="col-sm-9">{{ $stock->max_quantity ?? 'Illimité' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Coût unitaire :</strong></div>
                        <div class="col-sm-9">{{ number_format($stock->unit_cost, 0, ',', ' ') }} XOF</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Statut :</strong></div>
                        <div class="col-sm-9">
                            @if($stock->isLowStock())
                                <span class="badge bg-label-danger">Stock faible</span>
                            @elseif($stock->isMaxStock())
                                <span class="badge bg-label-warning">Stock maximum</span>
                            @else
                                <span class="badge bg-label-success">Normal</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Date de création :</strong></div>
                        <div class="col-sm-9">{{ $stock->created_at->format('d/m/Y à H:i') }}</div>
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
                            <span>Valeur du stock :</span>
                            <strong>{{ number_format($stock->quantity * $stock->unit_cost, 0, ',', ' ') }} XOF</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Mouvements :</span>
                            <strong>{{ $stock->movements()->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($stock->movements && $stock->movements->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Historique des Mouvements</h5>
                </div>
                <div class="card-body">
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
                                @foreach($stock->movements as $movement)
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
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@include('layouts.footer')

