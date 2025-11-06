@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Rapport {{ ucfirst($type) }}</h5>
                        <small class="text-muted">Analyse détaillée des {{ $type }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportRapport('{{ $type }}')">
                            <i class="ti ti-download me-1"></i>Exporter
                        </button>
                        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('rapports.show', $type) }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Recherche</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="Code, client, livreur...">
                                </div>
                                @if($type === 'livraisons')
                                    <div class="col-md-2">
                                        <label for="status" class="form-label">Statut</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">Tous les statuts</option>
                                            <option value="en_attente" {{ request('status') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            <option value="en_cours" {{ request('status') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                                            <option value="livre" {{ request('status') == 'livre' ? 'selected' : '' }}>Livré</option>
                                            <option value="retour" {{ request('status') == 'retour' ? 'selected' : '' }}>Retour</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="livreur_id" class="form-label">Livreur</label>
                                        <select class="form-select" id="livreur_id" name="livreur_id">
                                            <option value="">Tous les livreurs</option>
                                            @foreach($livreurs ?? [] as $livreur)
                                                <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                                    {{ $livreur->first_name }} {{ $livreur->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($type === 'colis')
                                    <div class="col-md-2">
                                        <label for="status" class="form-label">Statut</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">Tous les statuts</option>
                                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>En attente</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>En cours</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Livré</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="zone_id" class="form-label">Zone</label>
                                        <select class="form-select" id="zone_id" name="zone_id">
                                            <option value="">Toutes les zones</option>
                                            @foreach($zones ?? [] as $zone)
                                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                    {{ $zone->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($type === 'ramassages')
                                    <div class="col-md-2">
                                        <label for="statut" class="form-label">Statut</label>
                                        <select class="form-select" id="statut" name="statut">
                                            <option value="">Tous les statuts</option>
                                            <option value="planifie" {{ request('statut') == 'planifie' ? 'selected' : '' }}>Planifié</option>
                                            <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                                            <option value="termine" {{ request('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="marchand_id" class="form-label">Marchand</label>
                                        <select class="form-select" id="marchand_id" name="marchand_id">
                                            <option value="">Tous les marchands</option>
                                            @foreach($marchands ?? [] as $marchand)
                                                <option value="{{ $marchand->id }}" {{ request('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                                    {{ $marchand->first_name }} {{ $marchand->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($type === 'frais')
                                    <div class="col-md-2">
                                        <label for="livreur_id" class="form-label">Livreur</label>
                                        <select class="form-select" id="livreur_id" name="livreur_id">
                                            <option value="">Tous les livreurs</option>
                                            @foreach($livreurs ?? [] as $livreur)
                                                <option value="{{ $livreur->id }}" {{ request('livreur_id') == $livreur->id ? 'selected' : '' }}>
                                                    {{ $livreur->first_name }} {{ $livreur->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-2">
                                    <label for="date_debut" class="form-label">Date Début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                                           value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_fin" class="form-label">Date Fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                                           value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">Éléments par page</label>
                                    <select class="form-select" id="per_page" name="per_page">
                                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                                        <option value="10" {{ request('per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page', 5) == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page', 5) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page', 5) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('rapports.show', $type) }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i>Réinitialiser
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($type === 'livraisons')
                        <!-- Informations sur les résultats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="ti ti-info-circle me-1"></i>
                                Affichage de {{ $livraisons->firstItem() ?? 0 }} à {{ $livraisons->lastItem() ?? 0 }} sur {{ $livraisons->total() }} résultats
                            </div>
                            <div class="text-muted small">
                                Page {{ $livraisons->currentPage() }} sur {{ $livraisons->lastPage() }}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code Colis</th>
                                        <th>Client</th>
                                        <th>Zone</th>
                                        <th>Livreur</th>
                                        <th>Statut</th>
                                        <th>Montant à Encaisser</th>
                                        <th>Date Création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($livraisons as $livraison)
                                        <tr>
                                            <td>
                                                @if($livraison->colis)
                                                    <a href="{{ route('colis.show', $livraison->colis->id) }}" class="text-decoration-none">
                                                        <span class="fw-semibold">{{ $livraison->colis->code }}</span>
                                                    </a>
                                                @else
                                                    <span class="fw-semibold">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $livraison->colis->nom_client ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $livraison->colis->zone->nom ?? $livraison->colis->zone->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($livraison->livreur)
                                                    <span class="badge bg-label-info">
                                                        {{ $livraison->livreur->first_name }} {{ $livraison->livreur->last_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Non assigné</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($livraison->status)
                                                    @case('en_attente')
                                                        <span class="badge bg-label-warning">En attente</span>
                                                        @break
                                                    @case('en_cours')
                                                        <span class="badge bg-label-info">En cours</span>
                                                        @break
                                                    @case('livre')
                                                        <span class="badge bg-label-success">Livré</span>
                                                        @break
                                                    @case('annule_client')
                                                        <span class="badge bg-label-danger">Annulé (Client)</span>
                                                        @break
                                                    @case('annule_livreur')
                                                        <span class="badge bg-label-danger">Annulé (Livreur)</span>
                                                        @break
                                                    @case('annule_marchand')
                                                        <span class="badge bg-label-danger">Annulé (Marchand)</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-label-secondary">{{ ucfirst($livraison->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-info">{{ number_format($livraison->montant_a_encaisse ?? 0, 0, ',', ' ') }} FCFA</span>
                                            </td>
                                            <td>{{ $livraison->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#livraisonModal{{ $livraison->id }}" title="Voir les détails">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if($livraison->colis)
                                                        <a href="{{ route('colis.show', $livraison->colis->id) }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Voir le colis">
                                                            <i class="ti ti-package"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="ti ti-truck-off ti-48px text-muted mb-2"></i>
                                                <p class="text-muted">Aucune livraison trouvée</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Modales de détails pour chaque livraison -->
                        @foreach($livraisons as $livraison)
                            <div class="modal fade" id="livraisonModal{{ $livraison->id }}" tabindex="-1" aria-labelledby="livraisonModalLabel{{ $livraison->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="livraisonModalLabel{{ $livraison->id }}">
                                                <i class="ti ti-truck me-2"></i>
                                                Détails de la Livraison #{{ $livraison->id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <!-- Informations principales -->
                                                <div class="col-md-6 mb-4">
                                                    <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Informations Principales</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">ID Livraison:</th>
                                                            <td><span class="badge bg-label-secondary">#{{ $livraison->id }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Code Colis:</th>
                                                            <td>
                                                                @if($livraison->colis)
                                                                    <a href="{{ route('colis.show', $livraison->colis->id) }}" class="text-decoration-none">
                                                                        <span class="fw-semibold">{{ $livraison->colis->code }}</span>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Statut:</th>
                                                            <td>
                                                                @switch($livraison->status)
                                                                    @case('en_attente')
                                                                        <span class="badge bg-label-warning">En attente</span>
                                                                        @break
                                                                    @case('en_cours')
                                                                        <span class="badge bg-label-info">En cours</span>
                                                                        @break
                                                                    @case('livre')
                                                                        <span class="badge bg-label-success">Livré</span>
                                                                        @break
                                                                    @case('annule_client')
                                                                        <span class="badge bg-label-danger">Annulé (Client)</span>
                                                                        @break
                                                                    @case('annule_livreur')
                                                                        <span class="badge bg-label-danger">Annulé (Livreur)</span>
                                                                        @break
                                                                    @case('annule_marchand')
                                                                        <span class="badge bg-label-danger">Annulé (Marchand)</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge bg-label-secondary">{{ ucfirst($livraison->status) }}</span>
                                                                @endswitch
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Livreur:</th>
                                                            <td>
                                                                @if($livraison->livreur)
                                                                    <span class="badge bg-label-info">
                                                                        {{ $livraison->livreur->first_name }} {{ $livraison->livreur->last_name }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">Non assigné</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Zone:</th>
                                                            <td>
                                                                <span class="badge bg-label-primary">{{ $livraison->colis->zone->nom ?? $livraison->colis->zone->libelle ?? 'N/A' }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Client:</th>
                                                            <td>{{ $livraison->colis->nom_client ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Code Validation:</th>
                                                            <td>
                                                                @if($livraison->code_validation_utilise)
                                                                    <span class="badge bg-label-primary">{{ $livraison->code_validation_utilise }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <!-- Informations financières -->
                                                <div class="col-md-6 mb-4">
                                                    <h6 class="text-success mb-3"><i class="ti ti-currency-franc me-2"></i>Informations Financières</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">Montant à Encaisser:</th>
                                                            <td><span class="fw-semibold text-info">{{ number_format($livraison->montant_a_encaisse ?? 0, 0, ',', ' ') }} FCFA</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Prix de Vente:</th>
                                                            <td><span class="fw-semibold text-primary">{{ number_format($livraison->prix_de_vente ?? 0, 0, ',', ' ') }} FCFA</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Frais de Livraison:</th>
                                                            <td><span class="fw-semibold text-success">{{ number_format($livraison->montant_de_la_livraison ?? 0, 0, ',', ' ') }} FCFA</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Package Colis ID:</th>
                                                            <td>
                                                                @if($livraison->package_colis_id)
                                                                    <span class="badge bg-label-secondary">#{{ $livraison->package_colis_id }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Livraison ID:</th>
                                                            <td>
                                                                @if($livraison->livraison_id)
                                                                    <span class="badge bg-label-secondary">#{{ $livraison->livraison_id }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Entreprise ID:</th>
                                                            <td><span class="badge bg-label-secondary">#{{ $livraison->entreprise_id }}</span></td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <!-- Informations de livraison -->
                                                <div class="col-md-6 mb-4">
                                                    <h6 class="text-info mb-3"><i class="ti ti-calendar me-2"></i>Dates et Horaires</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">Date Création:</th>
                                                            <td>{{ $livraison->created_at->format('d/m/Y H:i:s') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Date Mise à Jour:</th>
                                                            <td>{{ $livraison->updated_at->format('d/m/Y H:i:s') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Date Livraison Effective:</th>
                                                            <td>
                                                                @if($livraison->date_livraison_effective)
                                                                    <span class="text-success">{{ \Carbon\Carbon::parse($livraison->date_livraison_effective)->format('d/m/Y H:i:s') }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Créé par:</th>
                                                            <td>{{ $livraison->created_by ?? 'N/A' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <!-- Informations GPS et Preuve -->
                                                <div class="col-md-6 mb-4">
                                                    <h6 class="text-warning mb-3"><i class="ti ti-map-pin me-2"></i>Localisation et Preuve</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">GPS (Lat/Long):</th>
                                                            <td>
                                                                @if($livraison->latitude && $livraison->longitude)
                                                                    <a href="https://www.google.com/maps?q={{ $livraison->latitude }},{{ $livraison->longitude }}" target="_blank" class="badge bg-label-success">
                                                                        <i class="ti ti-map-pin me-1"></i>
                                                                        {{ number_format($livraison->latitude, 6) }}, {{ number_format($livraison->longitude, 6) }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Photo de Preuve:</th>
                                                            <td>
                                                                @if($livraison->photo_proof_path)
                                                                    <a href="{{ asset('storage/' . $livraison->photo_proof_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="ti ti-photo me-1"></i>Voir la photo
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Signature:</th>
                                                            <td>
                                                                @if($livraison->signature_data)
                                                                    <span class="badge bg-label-warning">
                                                                        <i class="ti ti-signature me-1"></i>Signature disponible
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <!-- Note de livraison -->
                                                @if($livraison->note_livraison)
                                                <div class="col-12 mb-4">
                                                    <h6 class="text-secondary mb-3"><i class="ti ti-note me-2"></i>Note de Livraison</h6>
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <p class="mb-0">{{ $livraison->note_livraison }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <!-- Motif d'annulation -->
                                                @if($livraison->motif_annulation)
                                                <div class="col-12 mb-4">
                                                    <h6 class="text-danger mb-3"><i class="ti ti-alert-circle me-2"></i>Motif d'Annulation</h6>
                                                    <div class="card bg-danger-subtle">
                                                        <div class="card-body">
                                                            <p class="mb-0 text-danger">{{ $livraison->motif_annulation }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            @if($livraison->colis)
                                                <a href="{{ route('colis.show', $livraison->colis->id) }}" class="btn btn-outline-primary">
                                                    <i class="ti ti-package me-1"></i>Voir le Colis
                                                </a>
                                            @endif
                                            @if($livraison->packageColis)
                                                <a href="{{ route('colis.package.show', $livraison->packageColis->id) }}" class="btn btn-outline-info">
                                                    <i class="ti ti-box me-1"></i>Voir le Package
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($livraisons->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $livraisons->links() }}
                            </div>
                        @endif
                    @elseif($type === 'colis')
                        <!-- Informations sur les résultats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="ti ti-info-circle me-1"></i>
                                Affichage de {{ $colis->firstItem() ?? 0 }} à {{ $colis->lastItem() ?? 0 }} sur {{ $colis->total() }} résultats
                            </div>
                            <div class="text-muted small">
                                Page {{ $colis->currentPage() }} sur {{ $colis->lastPage() }}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Client</th>
                                        <th>Téléphone</th>
                                        <th>Zone</th>
                                        <th>Statut</th>
                                        <th>Date Création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($colis as $colisItem)
                                        <tr>
                                            <td>
                                                <a href="{{ route('colis.show', $colisItem->id) }}" class="text-decoration-none">
                                                    <span class="fw-semibold">{{ $colisItem->code }}</span>
                                                </a>
                                            </td>
                                            <td>{{ $colisItem->nom_client }}</td>
                                            <td>{{ $colisItem->telephone_client }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $colisItem->zone->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @switch($colisItem->status)
                                                    @case(0)
                                                        <span class="badge bg-label-warning">En attente</span>
                                                        @break
                                                    @case(1)
                                                        <span class="badge bg-label-info">En cours</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge bg-label-success">Livré</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-label-secondary">Inconnu</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $colisItem->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('colis.show', $colisItem->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="ti ti-package-off ti-48px text-muted mb-2"></i>
                                                <p class="text-muted">Aucun colis trouvé</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($colis->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $colis->links() }}
                            </div>
                        @endif
                    @elseif($type === 'ramassages')
                        <!-- Informations sur les résultats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="ti ti-info-circle me-1"></i>
                                Affichage de {{ $ramassages->firstItem() ?? 0 }} à {{ $ramassages->lastItem() ?? 0 }} sur {{ $ramassages->total() }} résultats
                            </div>
                            <div class="text-muted small">
                                Page {{ $ramassages->currentPage() }} sur {{ $ramassages->lastPage() }}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Marchand</th>
                                        <th>Boutique</th>
                                        <th>Date Demande</th>
                                        <th>Statut</th>
                                        <th>Colis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ramassages as $ramassage)
                                        <tr>
                                            <td>
                                                <a href="{{ route('ramassages.show', $ramassage->id) }}" class="text-decoration-none">
                                                    <span class="fw-semibold">{{ $ramassage->code_ramassage }}</span>
                                                </a>
                                            </td>
                                            <td>{{ $ramassage->marchand->first_name ?? 'N/A' }} {{ $ramassage->marchand->last_name ?? 'N/A' }}</td>
                                            <td>{{ $ramassage->boutique->nom ?? 'N/A' }}</td>
                                            <td>{{ $ramassage->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $ramassage->statut == 'termine' ? 'success' : ($ramassage->statut == 'en_cours' ? 'warning' : 'info') }}">
                                                    {{ ucfirst($ramassage->statut) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($ramassage->colis_data)
                                                    @php
                                                        $colisData = is_string($ramassage->colis_data) ? json_decode($ramassage->colis_data, true) : $ramassage->colis_data;
                                                    @endphp
                                                    <span class="badge bg-label-primary">{{ count($colisData) }} colis</span>
                                                @else
                                                    <span class="text-muted">0 colis</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('ramassages.show', $ramassage->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="ti ti-package-off ti-48px text-muted mb-2"></i>
                                                <p class="text-muted">Aucun ramassage trouvé</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($ramassages->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $ramassages->links() }}
                            </div>
                        @endif
                    @elseif($type === 'frais')
                        <!-- Statistiques des frais -->
                        <!-- Première ligne : Livraisons effectuées avec succès -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-success mb-3">
                                    <i class="ti ti-check-circle me-2"></i>
                                    Livraisons Effectuées avec Succès
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-check ti-24px mb-2"></i>
                                        <h6 class="card-title">Livraisons Livrées</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_livraisons_livrees']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-currency ti-24px mb-2"></i>
                                        <h6 class="card-title">Frais Livraisons Livrées</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_frais_livraisons_livrees'], 0) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-cash ti-24px mb-2"></i>
                                        <h6 class="card-title">Encaissement Livré</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_encaissement_livrees']) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-shopping-cart ti-24px mb-2"></i>
                                        <h6 class="card-title">Ventes Livrées</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_vente_livrees']) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deuxième ligne : Livraisons en attente ou en cours -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-warning mb-3">
                                    <i class="ti ti-clock me-2"></i>
                                    Livraisons en Attente ou en Cours
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-clock ti-24px mb-2"></i>
                                        <h6 class="card-title">Livraisons en Attente</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_livraisons_en_attente']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-currency ti-24px mb-2"></i>
                                        <h6 class="card-title">Frais en Attente</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_frais_livraisons_en_attente'], 0) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-cash ti-24px mb-2"></i>
                                        <h6 class="card-title">À Encaisser</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_encaissement_en_attente']) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="ti ti-shopping-cart ti-24px mb-2"></i>
                                        <h6 class="card-title">Ventes en Attente</h6>
                                        <h4 class="mb-0">{{ number_format($fraisStats['total_vente_en_attente']) }} FCFA</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations sur les résultats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="ti ti-info-circle me-1"></i>
                                Affichage de {{ $frais->firstItem() ?? 0 }} à {{ $frais->lastItem() ?? 0 }} sur {{ $frais->total() }} résultats
                            </div>
                            <div class="text-muted small">
                                Page {{ $frais->currentPage() }} sur {{ $frais->lastPage() }}
                            </div>
                        </div>

                        <!-- Tableau des frais de livraison -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code Colis</th>
                                        <th>Livreur</th>
                                        <th>Prix de Vente</th>
                                        <th>Frais Livraison</th>
                                        <th>Montant à Encaisser</th>
                                        <th>Date Livraison</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($frais as $fraisItem)
                                        <tr>
                                            <td>
                                                <a href="{{ route('colis.show', $fraisItem->colis->id) }}" class="text-decoration-none">
                                                    <span class="fw-semibold">{{ $fraisItem->colis->code ?? 'N/A' }}</span>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-primary">
                                                    {{ $fraisItem->livreur->first_name ?? 'N/A' }} {{ $fraisItem->livreur->last_name ?? '' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ number_format($fraisItem->prix_de_vente) }} FCFA</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-success">{{ number_format($fraisItem->montant_de_la_livraison) }} FCFA</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-info">{{ number_format($fraisItem->montant_a_encaisse) }} FCFA</span>
                                            </td>
                                            <td>{{ $fraisItem->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @switch($fraisItem->status)
                                                    @case('en_attente')
                                                        <span class="badge bg-warning">En attente</span>
                                                        @break
                                                    @case('en_cours')
                                                        <span class="badge bg-info">En cours</span>
                                                        @break
                                                    @case('livre')
                                                        <span class="badge bg-success">Livré</span>
                                                        @break
                                                    @case('retour')
                                                        <span class="badge bg-danger">Retour</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($fraisItem->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('colis.show', $fraisItem->colis->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="ti ti-currency-franc ti-48px text-muted mb-2"></i>
                                                <p class="text-muted">Aucun frais de livraison trouvé</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($frais->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $frais->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
function exportRapport(type) {
    // Implémentation de l'export
    alert('Fonction d\'export à implémenter pour le type: ' + type);
}

// Auto-submit du formulaire quand le nombre d'éléments par page change
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            // Soumettre automatiquement le formulaire
            this.form.submit();
        });
    }
});
</script>

@include('layouts.footer')

