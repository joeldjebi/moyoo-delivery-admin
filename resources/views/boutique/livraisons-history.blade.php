@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Historique des Livraisons</h5>
                            <p class="mb-4">Livraisons de la boutique : {{ $boutique->libelle }}</p>
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

    <!-- Statistiques des livraisons -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="ti ti-truck text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Livraisons</span>
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
                            <i class="ti ti-truck-delivery text-info" style="font-size: 1.5rem;"></i>
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
                    <span class="fw-semibold d-block mb-1">Livrées</span>
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
                    <span class="fw-semibold d-block mb-1">Annulées</span>
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

    <!-- Liste des livraisons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Livraisons de la Boutique</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">{{ $livraisons->total() }} livraisons au total</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($livraisons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Numéro de livraison</th>
                                        <th>Colis</th>
                                        <th>Client</th>
                                        <th>Adresse de livraison</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($livraisons as $livraison)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-success">
                                                            <i class="ti ti-truck"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $livraison->numero_de_livraison ?? 'N/A' }}</h6>
                                                        @if($livraison->code_validation)
                                                            <small class="text-muted">Code: {{ $livraison->code_validation }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-label-info">{{ $livraison->colis->numero_facture ?? 'N/A' }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $livraison->colis->typeColis->nom ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $livraison->colis->nom_client ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $livraison->colis->telephone_client ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">{{ Str::limit($livraison->adresse_de_livraison ?? $livraison->colis->adresse_client, 30) }}</span>
                                            </td>
                                            <td>
                                                @if($livraison->status == 0)
                                                    <span class="badge bg-label-warning">En attente</span>
                                                @elseif($livraison->status == 1)
                                                    <span class="badge bg-label-info">En cours</span>
                                                @elseif($livraison->status == 2)
                                                    <span class="badge bg-label-success">Livré</span>
                                                @elseif($livraison->status == 3)
                                                    <span class="badge bg-label-danger">Annulé par le client</span>
                                                @elseif($livraison->status == 4)
                                                    <span class="badge bg-label-danger">Annulé par le livreur</span>
                                                @elseif($livraison->status == 5)
                                                    <span class="badge bg-label-danger">Annulé par le marchand</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Statut inconnu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $livraison->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-outline-warning" title="Modifier">
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
                            {{ $livraisons->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-truck-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucune livraison trouvée</h5>
                            <p class="text-muted">Cette boutique n'a pas encore de livraisons.</p>
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
