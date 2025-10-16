@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Gestion des Reversements</h5>
                        <p class="mb-4">Gérez les reversements des montants encaissés aux marchands.</p>
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
                            <a href="{{ route('balances.index') }}" class="btn btn-outline-primary ms-2">
                                <i class="ti ti-wallet me-1"></i>
                                Voir les Balances
                            </a>
                        @endcan
                    </div>
                </div>
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

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('reversements.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En Attente</option>
                                <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                                <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marchand</label>
                            <select name="marchand_id" class="form-select">
                                <option value="">Tous les marchands</option>
                                @foreach($marchands as $marchand)
                                    <option value="{{ $marchand->id }}" {{ request('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                        {{ $marchand->first_name }} {{ $marchand->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date Début</label>
                            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date Fin</label>
                            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Par page</label>
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search"></i>
                                </button>
                                <a href="{{ route('reversements.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Liste des reversements -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Liste des Reversements</h5>
            </div>
            <div class="card-body">
                @if($reversements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Marchand</th>
                                    <th>Boutique</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th>Statut</th>
                                    <th>Date Création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reversements as $reversement)
                                    <tr>
                                        <td>
                                            <span class="badge bg-label-info">{{ $reversement->reference_reversement }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        {{ strtoupper(substr($reversement->marchand->first_name, 0, 1)) }}{{ strtoupper(substr($reversement->marchand->last_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><h6 class="mb-0">{{ $reversement->boutique->libelle }}</h6> <small class="text-muted">{{ $reversement->boutique->adresse }}</small></td>
                                        <td class="fw-bold text-success">{{ number_format($reversement->montant_reverse) }} FCFA</td>
                                        <td>
                                            <span class="badge bg-label-secondary">
                                                {{ $reversement->mode_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $reversement->statut_color }}">
                                                {{ $reversement->statut_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $reversement->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('reversements.show', $reversement) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye me-1"></i> Voir
                                                </a>

                                                @if($reversement->statut === 'en_attente')
                                                    @if(auth()->user()->hasPermission('reversements.update'))
                                                        <button type="button" class="btn btn-sm btn-success"
                                                                data-bs-toggle="modal" data-bs-target="#validateModal{{ $reversement->id }}">
                                                            <i class="ti ti-check me-1"></i> Valider
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                data-bs-toggle="modal" data-bs-target="#cancelModal{{ $reversement->id }}">
                                                            <i class="ti ti-x me-1"></i> Annuler
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            <i class="ti ti-info-circle me-1"></i>
                            Affichage de {{ $reversements->firstItem() }} à {{ $reversements->lastItem() }} sur {{ $reversements->total() }} résultats
                        </div>
                        <div>
                            {{ $reversements->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-receipt-off" style="font-size: 4rem; color: #ccc;"></i>
                        </div>
                        <h5 class="text-muted">Aucun reversement trouvé</h5>
                        <p class="text-muted">Commencez par créer votre premier reversement.</p>
                        @can('reversements.create')
                            <a href="{{ route('reversements.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Nouveau Reversement
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Styles personnalisés pour la pagination */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #696cff;
    border-color: #e7e7ff;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.pagination .page-link:hover {
    color: #5a5fcf;
    background-color: #f8f9ff;
    border-color: #e7e7ff;
}

.pagination .page-item.active .page-link {
    background-color: #696cff;
    border-color: #696cff;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #a1acb8;
    background-color: #fff;
    border-color: #e7e7ff;
}

/* Style pour le sélecteur per_page */
.form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}
</style>

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

<!-- Modales de confirmation -->
@foreach($reversements as $reversement)
    @if($reversement->statut === 'en_attente')
        <!-- Modal de validation -->
        <div class="modal fade" id="validateModal{{ $reversement->id }}" tabindex="-1" aria-labelledby="validateModalLabel{{ $reversement->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="validateModalLabel{{ $reversement->id }}">
                            <i class="ti ti-check-circle text-success me-2"></i>
                            Valider le Reversement
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Confirmation requise</strong>
                        </div>
                        <p>Êtes-vous sûr de vouloir <strong class="text-success">valider</strong> ce reversement ?</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Détails du reversement :</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Référence :</strong> {{ $reversement->reference_reversement }}</li>
                                    <li><strong>Marchand :</strong> {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}</li>
                                    <li><strong>Boutique :</strong> {{ $reversement->boutique->libelle }}</li>
                                    <li><strong>Montant :</strong> <span class="text-success fw-bold">{{ number_format($reversement->montant_reverse) }} FCFA</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Attention :</strong> Cette action est irréversible. Le montant sera débité de la balance du marchand.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i> Annuler
                        </button>
                        <form action="{{ route('reversements.validate', $reversement) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Confirmer la validation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal d'annulation -->
        <div class="modal fade" id="cancelModal{{ $reversement->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $reversement->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel{{ $reversement->id }}">
                            <i class="ti ti-x-circle text-danger me-2"></i>
                            Annuler le Reversement
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Attention</strong>
                        </div>
                        <p>Êtes-vous sûr de vouloir <strong class="text-danger">annuler</strong> ce reversement ?</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Détails du reversement :</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Référence :</strong> {{ $reversement->reference_reversement }}</li>
                                    <li><strong>Marchand :</strong> {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}</li>
                                    <li><strong>Boutique :</strong> {{ $reversement->boutique->libelle }}</li>
                                    <li><strong>Montant :</strong> <span class="text-danger fw-bold">{{ number_format($reversement->montant_reverse) }} FCFA</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Note :</strong> Le reversement sera marqué comme annulé et ne pourra plus être validé.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-arrow-left me-1"></i> Retour
                        </button>
                        <form action="{{ route('reversements.cancel', $reversement) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-x me-1"></i> Confirmer l'annulation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@include('layouts.footer')
