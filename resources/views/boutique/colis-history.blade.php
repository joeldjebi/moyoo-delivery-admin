@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Historique des Colis</h5>
                            <p class="mb-4">Colis de la boutique : {{ $boutique->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('boutiques.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste des boutiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des colis -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-package text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Colis</span>
                    <h3 class="card-title mb-2">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-clock text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">En Attente</span>
                    <h3 class="card-title mb-2">{{ $stats['en_attente'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-truck text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">En Cours</span>
                    <h3 class="card-title mb-2">{{ $stats['en_cours'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-check text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Livrés</span>
                    <h3 class="card-title mb-2">{{ $stats['livre'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-x text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Annulés</span>
                    <h3 class="card-title mb-2">{{ $stats['annule'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

    <!-- Liste des colis -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Colis de la Boutique</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">{{ $colis->total() }} colis au total</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($colis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Client</th>
                                        <th>Téléphone</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($colis as $colisItem)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="ti ti-package"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $colisItem->code ?? 'N/A' }}</h6>
                                                        @if($colisItem->numero_facture)
                                                            <small class="text-muted">Facture: {{ $colisItem->numero_facture }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $colisItem->nom_client ?? 'N/A' }}</strong>
                                                    @if($colisItem->adresse_client)
                                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($colisItem->adresse_client, 30) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">{{ $colisItem->telephone_client ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $colisItem->status == 1 ? 'success' : 'warning' }}">
                                                    {{ $colisItem->status == 1 ? 'Livré' : 'En attente' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $colisItem->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('colis.show', $colisItem) }}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('colis.edit', $colisItem) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $colis->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-package-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucun colis trouvé</h5>
                            <p class="text-muted">Cette boutique n'a pas encore de colis.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

@include('layouts.footer')
