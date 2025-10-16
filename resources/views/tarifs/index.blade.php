@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Tarifs de Livraison</h5>
                            <p class="mb-4">Configuration des coûts de livraison par commune, type d'engin et mode de livraison</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>
                                    Nouveau Tarif
                                </a>
                            </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('tarifs.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="commune_depart_id" class="form-label">Commune de Départ</label>
                            <select class="form-select" id="commune_depart_id" name="commune_depart_id">
                                <option value="">Toutes</option>
                                @foreach($communes ?? [] as $commune)
                                    <option value="{{ $commune->id }}" {{ request('commune_depart_id') == $commune->id ? 'selected' : '' }}>
                                        {{ $commune->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="commune_id" class="form-label">Commune de Destination</label>
                            <select class="form-select" id="commune_id" name="commune_id">
                                <option value="">Toutes</option>
                                @foreach($communes ?? [] as $commune)
                                    <option value="{{ $commune->id }}" {{ request('commune_id') == $commune->id ? 'selected' : '' }}>
                                        {{ $commune->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type_engin_id" class="form-label">Type d'Engin</label>
                            <select class="form-select" id="type_engin_id" name="type_engin_id">
                                <option value="">Tous</option>
                                @foreach($typeEngins ?? [] as $typeEngin)
                                    <option value="{{ $typeEngin->id }}" {{ request('type_engin_id') == $typeEngin->id ? 'selected' : '' }}>
                                        {{ $typeEngin->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="mode_livraison_id" class="form-label">Mode de Livraison</label>
                            <select class="form-select" id="mode_livraison_id" name="mode_livraison_id">
                                <option value="">Tous</option>
                                @foreach($modeLivraisons ?? [] as $modeLivraison)
                                    <option value="{{ $modeLivraison->id }}" {{ request('mode_livraison_id') == $modeLivraison->id ? 'selected' : '' }}>
                                        {{ $modeLivraison->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="poids_id" class="form-label">Poids</label>
                            <select class="form-select" id="poids_id" name="poids_id">
                                <option value="">Tous</option>
                                @foreach($poids ?? [] as $poid)
                                    <option value="{{ $poid->id }}" {{ request('poids_id') == $poid->id ? 'selected' : '' }}>
                                        {{ $poid->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="temp_id" class="form-label">Période</label>
                            <select class="form-select" id="temp_id" name="temp_id">
                                <option value="">Toutes</option>
                                @foreach($temps ?? [] as $temp)
                                    <option value="{{ $temp->id }}" {{ request('temp_id') == $temp->id ? 'selected' : '' }}>
                                        {{ $temp->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="amount_min" class="form-label">Montant Min (FCFA)</label>
                            <input type="number" class="form-control" id="amount_min" name="amount_min"
                                   value="{{ request('amount_min') }}" placeholder="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="amount_max" class="form-label">Montant Max (FCFA)</label>
                            <input type="number" class="form-control" id="amount_max" name="amount_max"
                                   value="{{ request('amount_max') }}" placeholder="10000" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Filtrer
                                </button>
                                <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Effacer
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="exportTarifs()">
                                    <i class="ti ti-download me-1"></i>
                                    Exporter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tarifs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Tarifs</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">{{ $tarifs->total() }} tarif(s) au total</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($tarifs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Entreprise</th>
                                        <th>Départ → Destination</th>
                                        <th>Type d'Engin</th>
                                        <th>Mode de Livraison</th>
                                        <th>Poids</th>
                                        <th>Période</th>
                                        <th>Montant</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tarifs as $tarif)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-success">
                                                            <i class="ti ti-building"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-success">{{ $tarif->entreprise->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $tarif->entreprise->commune->libelle ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="ti ti-arrow-right"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-primary">{{ $tarif->communeDepart->libelle ?? 'N/A' }}</div>
                                                        <small class="text-muted">→ {{ $tarif->commune->libelle ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-secondary">{{ $tarif->typeEngin->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $tarif->modeLivraison->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-warning">{{ $tarif->poids->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-success">{{ $tarif->temp->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">
                                                    {{ number_format($tarif->amount, 0, ',', ' ') }} FCFA
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $tarif->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('tarifs.show', $tarif->id) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('tarifs.edit', $tarif->id) }}">
                                                            <i class="ti ti-pencil me-1"></i> Modifier
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteTarif({{ $tarif->id }}, '{{ $tarif->commune->libelle ?? 'N/A' }}')">
                                                            <i class="ti ti-trash me-1"></i> Supprimer
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $tarifs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-currency-franc" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucun tarif trouvé</h5>
                            <p class="text-muted">Aucun tarif ne correspond aux critères de recherche.</p>
                            <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer le premier tarif
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <i class="ti ti-alert-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-2 mb-1">Êtes-vous sûr ?</h4>
                        <p class="text-muted">Cette action ne peut pas être annulée. Le tarif pour <strong id="tarifCommune"></strong> sera définitivement supprimé.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
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

<script>
function deleteTarif(tarifId, communeName) {
    document.getElementById('tarifCommune').textContent = communeName;
    document.getElementById('deleteForm').action = `/tarifs/${tarifId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Fonction d'export des tarifs
function exportTarifs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open(`{{ route('tarifs.index') }}?${params.toString()}`, '_blank');
}

// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Filtres en temps réel (optionnel)
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit du formulaire quand on change les filtres (optionnel)
    const filterSelects = document.querySelectorAll('#commune_depart_id, #commune_id, #type_engin_id, #mode_livraison_id, #poids_id, #temp_id');

    // Désactiver l'auto-submit pour l'instant, garder le bouton Filtrer
    // filterSelects.forEach(select => {
    //     select.addEventListener('change', function() {
    //         this.form.submit();
    //     });
    // });

    // Validation des montants
    const amountMin = document.getElementById('amount_min');
    const amountMax = document.getElementById('amount_max');

    if (amountMin && amountMax) {
        amountMin.addEventListener('input', function() {
            if (this.value && amountMax.value && parseInt(this.value) > parseInt(amountMax.value)) {
                this.setCustomValidity('Le montant minimum ne peut pas être supérieur au maximum');
            } else {
                this.setCustomValidity('');
            }
        });

        amountMax.addEventListener('input', function() {
            if (this.value && amountMin.value && parseInt(this.value) < parseInt(amountMin.value)) {
                this.setCustomValidity('Le montant maximum ne peut pas être inférieur au minimum');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>

@include('layouts.footer')
