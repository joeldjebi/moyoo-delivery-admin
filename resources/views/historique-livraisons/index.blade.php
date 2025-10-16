@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-history me-2"></i>
                        {{ $title }}
                    </h5>
                </div>

                <!-- Filtres -->
                <div class="card-body">
                    <form method="GET" action="{{ route('historique-livraisons.index') }}" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="livreur_id" class="form-label">Livreur</label>
                            <select class="form-select" id="livreur_id" name="livreur_id">
                                <option value="">Tous les livreurs</option>
                                @foreach($livreurs as $livreur)
                                    <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                        {{ $livreur->first_name }} {{ $livreur->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Filtrer
                                </button>
                                <a href="{{ route('historique-livraisons.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Messages de succès/erreur -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Tableau des historiques -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Package</th>
                                    <th>Livraison</th>
                                    <th>Colis</th>
                                    <th>Livreur</th>
                                    <th>Statut</th>
                                    <th>Montant à encaisser</th>
                                    <th>Prix de vente</th>
                                    <th>Montant livraison</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historique_livraisons as $historique)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">#{{ $historique->id }}</span>
                                        </td>
                                        <td>
                                            @if($historique->packageColis)
                                                <a href="{{ route('colis.package.show', $historique->packageColis->id) }}"
                                                   class="badge bg-label-info text-decoration-none"
                                                   title="Voir les détails du package">
                                                    {{ $historique->packageColis->numero_package }}
                                                    <i class="ti ti-external-link ms-1" style="font-size: 0.7rem;"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($historique->livraison)
                                                <a href="{{ route('livraisons.show', $historique->livraison->id) }}"
                                                   class="badge bg-label-primary text-decoration-none"
                                                   title="Voir les détails de la livraison">
                                                    {{ $historique->livraison->numero_de_livraison }}
                                                    <i class="ti ti-external-link ms-1" style="font-size: 0.7rem;"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($historique->colis)
                                                <a href="{{ route('colis.show', $historique->colis->id) }}"
                                                   class="badge bg-label-secondary text-decoration-none"
                                                   title="Voir les détails du colis">
                                                    {{ $historique->colis->code }}
                                                    <i class="ti ti-external-link ms-1" style="font-size: 0.7rem;"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($historique->livreur)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        @if($historique->livreur->photo)
                                                            <img src="{{ asset('storage/' . $historique->livreur->photo) }}" alt="Avatar" class="rounded-circle">
                                                        @else
                                                            <div class="avatar-initial bg-primary rounded-circle">
                                                                {{ substr($historique->livreur->first_name, 0, 1) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $historique->livreur->first_name }} {{ $historique->livreur->last_name }}</div>
                                                        <small class="text-muted">{{ $historique->livreur->mobile }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $historique->status_badge }}">
                                                {{ $historique->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-end">
                                                <span class="fw-semibold montant-encaisser">
                                                    {{ number_format($historique->colis->montant_a_encaisse ?? 0) }} FCFA
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-end">
                                                <span class="fw-semibold montant-vente">
                                                    {{ number_format($historique->colis->prix_de_vente ?? 0) }} FCFA
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-end">
                                                <span class="fw-semibold montant-livraison">
                                                    {{ number_format($historique->montant_de_la_livraison) }} FCFA
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-end">
                                                <span class="montant-total">
                                                    {{ number_format(($historique->colis->montant_a_encaisse ?? 0) + ($historique->colis->prix_de_vente ?? 0) + $historique->montant_de_la_livraison) }} FCFA
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $historique->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $historique->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('historique-livraisons.show', $historique) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('historique-livraisons.edit', $historique) }}">
                                                        <i class="ti ti-pencil me-1"></i> Modifier
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       onclick="confirmDelete('{{ route('historique-livraisons.destroy', $historique) }}', '{{ $historique->id }}')">
                                                        <i class="ti ti-trash me-1"></i> Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ti ti-history text-muted" style="font-size: 3rem;"></i>
                                                <h6 class="mt-2 text-muted">Aucun historique de livraison trouvé</h6>
                                                <p class="text-muted">Commencez par créer un nouvel historique de livraison.</p>
                                                <a href="{{ route('historique-livraisons.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i>
                                                    Créer le premier historique
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($historique_livraisons->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $historique_livraisons->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet historique de livraison ?</p>
                <p class="text-muted small">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-1"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour les liens cliquables */
.badge.text-decoration-none:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Styles pour les montants */
.montant-encaisser {
    color: #696cff !important;
}

.montant-vente {
    color: #03c3ec !important;
}

.montant-livraison {
    color: #71dd37 !important;
}

.montant-total {
    color: #233446 !important;
    font-weight: 700 !important;
}

/* Amélioration de la table */
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

/* Responsive pour les montants */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .badge {
        font-size: 0.75rem;
    }
}
</style>

<script>
function confirmDelete(url, id) {
    document.getElementById('deleteForm').action = url;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Animation pour les liens cliquables
document.addEventListener('DOMContentLoaded', function() {
    const clickableBadges = document.querySelectorAll('.badge.text-decoration-none');

    clickableBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        });

        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>

@include('layouts.footer')
