@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Balances des Marchands</h5>
                        <p class="mb-4">Consultez les balances disponibles pour les reversements.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        @can('reversements.create')
                            <a href="{{ route('reversements.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Nouveau Reversement
                            </a>
                        @endcan
                        @can('reversements.read')
                            <a href="{{ route('reversements.index') }}" class="btn btn-outline-primary ms-2">
                                <i class="ti ti-list me-1"></i>
                                Voir les Reversements
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="ti ti-wallet ti-24px mb-2"></i>
                <h6 class="card-title">Total Balance</h6>
                <h4 class="mb-0">{{ number_format($total_balance) }} FCFA</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="ti ti-currency ti-24px mb-2"></i>
                <h6 class="card-title">Total Encaissé</h6>
                <h4 class="mb-0">{{ number_format($total_encaisse) }} FCFA</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="ti ti-send ti-24px mb-2"></i>
                <h6 class="card-title">Total Reversé</h6>
                <h4 class="mb-0">{{ number_format($total_reverse) }} FCFA</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="ti ti-users ti-24px mb-2"></i>
                <h6 class="card-title">Marchands</h6>
                <h4 class="mb-0">{{ $nombre_marchands }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Liste des balances -->
<div class="row">
    @if($balances->count() > 0)
        @foreach($balances as $balance)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="card-title mb-1">{{ $balance->marchand->full_name }}</h6>
                                <p class="text-muted small mb-0">{{ $balance->boutique->nom }}</p>
                            </div>
                            <div class="text-end">
                                <h4 class="text-primary mb-0">
                                    {{ number_format($balance->balance_actuelle) }} FCFA
                                </h4>
                                <small class="text-muted">Balance actuelle</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Encaissé</small>
                                    <div class="fw-bold text-success">{{ number_format($balance->montant_encaisse) }} FCFA</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <small class="text-muted d-block">Reversé</small>
                                    <div class="fw-bold text-info">{{ number_format($balance->montant_reverse) }} FCFA</div>
                                </div>
                            </div>
                        </div>

                        @if($balance->derniere_mise_a_jour)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="ti ti-clock me-1"></i>
                                    Dernière mise à jour: {{ $balance->derniere_mise_a_jour->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        @endif

                        @if($balance->balance_actuelle > 0)
                            <div class="d-grid gap-2">
                                @can('reversements.create')
                                    <a href="{{ route('reversements.create', ['marchand_id' => $balance->marchand_id, 'boutique_id' => $balance->boutique_id]) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="ti ti-send me-1"></i>
                                        Effectuer un Reversement
                                    </a>
                                @endcan
                                @can('reversements.read')
                                    <a href="{{ route('historique.balances', $balance->marchand_id) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="ti ti-history me-1"></i>
                                        Voir l'Historique
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div class="text-center">
                                <span class="badge bg-label-secondary">Aucune balance disponible</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-wallet-off" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                    <h5 class="text-muted">Aucune balance disponible</h5>
                    <p class="text-muted">Les balances apparaîtront après les premières livraisons réussies.</p>
                    <a href="{{ route('colis.index') }}" class="btn btn-primary">
                        <i class="ti ti-package me-1"></i>
                        Voir les Colis
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@include('layouts.footer')
