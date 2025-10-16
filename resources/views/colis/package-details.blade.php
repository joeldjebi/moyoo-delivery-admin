@include('layouts.header')
@include('layouts.menu')

<style>
.colis-code-link {
    transition: all 0.2s ease;
    cursor: pointer;
}

.colis-code-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-decoration: none !important;
}

.colis-code-link:hover .badge {
    background-color: #0d6efd !important;
    color: white !important;
}
</style>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ route('colis.packages') }}" class="btn btn-outline-secondary me-3">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <div>
                        <h2 class="mb-1 text-dark fw-bold">📦 {{ $package->numero_package }}</h2>
                        <p class="text-muted mb-0">Détails du package de colis</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @switch($package->statut)
                        @case('en_attente')
                            <span class="badge bg-warning fs-6 px-3 py-2">⏳ En attente</span>
                            @break
                        @case('en_cours')
                            <span class="badge bg-primary fs-6 px-3 py-2">🚀 En cours</span>
                            @break
                        @case('livre')
                            <span class="badge bg-success fs-6 px-3 py-2">✅ Livré</span>
                            @break
                        @case('annule')
                            <span class="badge bg-danger fs-6 px-3 py-2">❌ Annulé</span>
                            @break
                        @default
                            <span class="badge bg-secondary fs-6 px-3 py-2">{{ $package->statut }}</span>
                    @endswitch
                </div>
            </div>
        </div>
    </div>
    <!-- Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        📦
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $package->nombre_colis }}</h4>
                    <p class="text-muted mb-0">Colis</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-map-marker-alt fa-lg text-info"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $package->communes_count }}</h4>
                    <p class="text-muted mb-0">Zones</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-user fa-lg text-success"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $package->livreur ? '1' : '0' }}</h4>
                    <p class="text-muted mb-0">Livreur</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-calendar fa-lg text-warning"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $package->created_at->format('d/m') }}</h4>
                    <p class="text-muted mb-0">Créé le</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-money-bill-wave fa-lg text-success"></i>
                    </div>
                    @php
                        $totalDeliveryCost = 0;
                        if ($package->colis_ids) {
                            // Convertir colis_ids en tableau si c'est une chaîne
                            $colisIds = is_string($package->colis_ids) ?
                                array_filter(explode(',', $package->colis_ids)) :
                                (is_array($package->colis_ids) ? $package->colis_ids : []);

                            if (count($colisIds) > 0) {
                                $colis = \App\Models\Colis::whereIn('id', $colisIds)->get();
                                foreach ($colis as $colisItem) {
                                    $totalDeliveryCost += $colisItem->calculateDeliveryCost();
                                }
                            }
                        }
                    @endphp
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalDeliveryCost, 0, ',', ' ') }}</h4>
                    <p class="text-muted mb-0">Coût Total</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations détaillées -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0 fw-semibold">🏪 Informations Commerciales</h5>
                </div>
                <div class="card-body mt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-building text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $package->marchand->first_name ?? 'N/A' }} {{ $package->marchand->last_name ?? 'N/A' }}</div>
                                    <small class="text-muted">Marchand</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-building text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $package->boutique->libelle ?? 'N/A' }}</div>
                                    <small class="text-muted">Boutique</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-user text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $package->createdBy->first_name ?? 'N/A' }} {{ $package->createdBy->last_name ?? 'N/A' }}</div>
                                    <small class="text-muted">Créé par</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0 fw-semibold">🚚 Informations de Livraison</h5>
                </div>
                <div class="card-body mt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-user-tie text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $package->livreur->first_name ?? 'Non assigné' }} {{ $package->livreur->last_name ?? 'Non assigné' }}</div>
                                    <small class="text-muted">Livreur</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-truck text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $package->engin->libelle ?? 'Non assigné' }}</div>
                                    <small class="text-muted">Engin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-hashtag text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        @if($package->colis_count > 0)
                                            @php
                                                $colisCodes = $package->getColisCodes();
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($colisCodes as $colisId => $code)
                                                    <a href="{{ route('colis.show', $colisId) }}"
                                                       class="colis-code-link"
                                                       title="Voir les détails du colis {{ $code }}">
                                                        <span class="badge bg-primary text-white">{{ $code }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            Aucun
                                        @endif
                                    </div>
                                    <small class="text-muted">Codes des colis (cliquez pour voir les détails)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zones de livraison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0 fw-semibold">📍 Zones de Livraison</h5>
                </div>
                <div class="card-body mt-2">
                    @if($package->communes_selected)
                        @php
                            $communes = $package->communes_array;
                        @endphp
                        @if($communes)
                            <div class="row g-2">
                                @foreach($communes as $communeId)
                                    @php
                                        $commune = \App\Models\Commune::find($communeId);
                                    @endphp
                                    @if($commune)
                                        <div class="col-md-3 col-sm-6">
                                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $commune->libelle }}</div>
                                                    <small class="text-muted">Zone de livraison</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-map-marker-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Aucune zone de livraison définie</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Aucune zone de livraison définie</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des colis -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">📦 Colis du Package</h5>
                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $package->colis->count() }} colis</span>
                </div>
                <div class="card-body p-0 mt-2">
                    @if($package->colis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 fw-semibold text-dark">📦 Code</th>
                                        <th class="border-0 fw-semibold text-dark">👤 Client</th>
                                        <th class="border-0 fw-semibold text-dark">📱 Contact</th>
                                        <th class="border-0 fw-semibold text-dark">📍 Zone</th>
                                        <th class="border-0 fw-semibold text-dark">💰 Coût Livraison</th>
                                        <th class="border-0 fw-semibold text-dark">📊 Statut</th>
                                        <th class="border-0 fw-semibold text-dark">⚡ Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->colis as $colis)
                                        <tr class="border-bottom">
                                            <td class="py-3">
                                                <div class="fw-semibold text-primary">{{ $colis->code }}</div>
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-medium">{{ $colis->nom_client ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ Str::limit($colis->adresse_client ?? 'N/A', 30) }}</small>
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-medium">{{ $colis->telephone_client ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-3">
                                                @if($colis->zone)
                                                    <span class="badge bg-light text-dark border">{{ $colis->zone->nom }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @php
                                                    $deliveryCost = $colis->calculateDeliveryCost();
                                                @endphp
                                                @if($deliveryCost > 0)
                                                    <span class="badge bg-success rounded-pill px-3 py-2">{{ number_format($deliveryCost, 0, ',', ' ') }} FCFA</span>
                                                @else
                                                    <span class="badge bg-light text-muted rounded-pill px-3 py-2">0 FCFA</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @switch($colis->status)
                                                    @case(0)
                                                        <span class="badge bg-warning rounded-pill px-3 py-2">⏳ En attente</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge bg-primary rounded-pill px-3 py-2">🚀 En cours</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge bg-success rounded-pill px-3 py-2">✅ Livré</span>
                                                        @break
                                                    @case(3)
                                                        <span class="badge bg-danger rounded-pill px-3 py-2">❌ Annulé</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary rounded-pill px-3 py-2">❓ Inconnu</span>
                                                @endswitch
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('colis.show', $colis->id) }}"
                                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye me-1"></i>Voir
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-box fa-2x text-muted"></i>
                                </div>
                            </div>
                            <h4 class="text-muted mb-3">Aucun colis trouvé</h4>
                            <p class="text-muted">Ce package ne contient aucun colis</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@include('layouts.footer')
