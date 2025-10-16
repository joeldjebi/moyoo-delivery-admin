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
                <div>
                    <h2 class="mb-1 text-dark fw-bold">📦 Packages de Colis</h2>
                    <p class="text-muted mb-0">Gérez vos lots de colis en toute simplicité</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('colis.create') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-plus me-2"></i>Nouveau Package
                    </a>
                    <a href="{{ route('colis.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-boxes me-2"></i>Voir les Colis
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($packages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 fw-semibold text-dark">📦 Package</th>
                                        <th class="border-0 fw-semibold text-dark">🏪 Marchand</th>
                                        <th class="border-0 fw-semibold text-dark">📊 Colis</th>
                                        <th class="border-0 fw-semibold text-dark">📍 Zones</th>
                                        <th class="border-0 fw-semibold text-dark">🚚 Livreur</th>
                                        <th class="border-0 fw-semibold text-dark">💰 Coût Total</th>
                                        <th class="border-0 fw-semibold text-dark">📅 Date</th>
                                        <th class="border-0 fw-semibold text-dark">⚡ Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packages as $package)
                                        <tr class="border-bottom">
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-box text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $package->numero_package }}</div>
                                                        <small class="text-muted">{{ $package->boutique->libelle ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-medium">{{ $package->marchand->first_name ?? 'N/A' }} {{ $package->marchand->last_name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">{{ $package->nombre_colis }} colis</span>
                                                    @if($package->colis_ids && count($package->colis_ids) > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @php
                                                                $colisCodes = $package->getColisCodes();
                                                            @endphp
                                                            @foreach($colisCodes as $colisId => $code)
                                                                <a href="{{ route('colis.show', $colisId) }}"
                                                                   class="colis-code-link"
                                                                   title="Voir les détails du colis {{ $code }}">
                                                                    <span class="badge bg-light text-primary border">{{ $code }}</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                @if($package->communes_selected)
                                                    @php
                                                        // Gérer différents formats de communes_selected
                                                        if (is_array($package->communes_selected)) {
                                                            $communes = $package->communes_selected;
                                                        } elseif (is_string($package->communes_selected)) {
                                                            // Si c'est une chaîne JSON, la décoder
                                                            $decoded = json_decode($package->communes_selected, true);
                                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                $communes = $decoded;
                                                            } else {
                                                                // Sinon, traiter comme une chaîne séparée par des virgules
                                                                $communes = array_filter(explode(',', $package->communes_selected));
                                                            }
                                                        } else {
                                                            $communes = [];
                                                        }
                                                    @endphp
                                                    @if($communes)
                                                        @foreach($communes as $communeId)
                                                            @php
                                                                $commune = \App\Models\Commune::find(trim($communeId));
                                                            @endphp
                                                            @if($commune)
                                                                <span class="badge bg-light text-dark border me-1">{{ $commune->libelle }}</span>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @else
                                                    <span class="text-muted">Aucune</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @if($package->livreur)
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success bg-opacity-10 rounded-circle p-1 me-2">
                                                            <i class="fas fa-user text-success" style="font-size: 12px;"></i>
                                                        </div>
                                                        <span class="fw-medium">{{ $package->livreur->nom }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Non assigné</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @php
                                                    $totalCost = 0;
                                                    if ($package->colis_ids && count($package->colis_ids) > 0) {
                                                        $colis = \App\Models\Colis::whereIn('id', $package->colis_ids)->get();
                                                        foreach ($colis as $colisItem) {
                                                            $totalCost += $colisItem->calculateDeliveryCost();
                                                        }
                                                    }
                                                @endphp
                                                <div class="fw-bold text-success">
                                                    {{ number_format($totalCost, 0, ',', ' ') }} FCFA
                                                </div>
                                                <small class="text-muted">{{ count($package->colis_ids ?? []) }} colis</small>
                                            </td>
                                            <td class="py-3">
                                                <div class="text-muted">{{ $package->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $package->created_at->format('H:i') }}</small>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('colis.package.show', $package->id) }}"
                                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye me-1"></i>Détails
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-4">
                            {{ $packages->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-boxes fa-2x text-muted"></i>
                                </div>
                            </div>
                            <h4 class="text-muted mb-3">Aucun package trouvé</h4>
                            <p class="text-muted mb-4">Commencez par créer votre premier package de colis</p>
                            <a href="{{ route('colis.create') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Créer un Package
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@include('layouts.footer')
