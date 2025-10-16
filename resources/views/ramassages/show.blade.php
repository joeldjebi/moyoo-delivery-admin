@include('layouts.header')

@include('layouts.menu')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Détails du Ramassage</h5>
                        <p class="text-muted mb-0">{{ $ramassage->code_ramassage }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ramassages.edit', $ramassage->id) }}" class="btn btn-outline-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('ramassages.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Informations générales -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Informations Générales</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Statut:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-{{ $ramassage->statut_color }} fs-6">
                                                {{ $ramassage->statut_label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Marchand:</strong>
                                        </div>
                                        <div class="col-sm-8">{{ $ramassage->marchand->first_name ?? '' }} {{ $ramassage->marchand->last_name ?? '' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Boutique:</strong>
                                        </div>
                                        <div class="col-sm-8">{{ $ramassage->boutique->libelle ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Date Demande:</strong>
                                        </div>
                                        <div class="col-sm-8">{{ $ramassage->date_demande->format('d/m/Y') }}</div>
                                    </div>
                                    @if($ramassage->date_planifiee)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Date Planifiée:</strong>
                                            </div>
                                            <div class="col-sm-8">{{ $ramassage->date_planifiee->format('d/m/Y') }}</div>
                                        </div>
                                    @endif
                                    @if($ramassage->date_effectuee)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Date Effectuée:</strong>
                                            </div>
                                            <div class="col-sm-8">{{ $ramassage->date_effectuee->format('d/m/Y') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Contact et adresse -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Contact & Adresse</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Contact du ramassage:</strong>
                                        </div>
                                        <div class="col-sm-8">{{ $ramassage->contact_ramassage }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Adresse:</strong>
                                        </div>
                                        <div class="col-sm-8">{{ $ramassage->adresse_ramassage }}</div>
                                    </div>
                                    @if($ramassage->notes)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Notes:</strong>
                                            </div>
                                            <div class="col-sm-8">{{ $ramassage->notes }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Statistiques</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="badge bg-info fs-4 mb-2">{{ $ramassage->nombre_colis_estime }}</div>
                                                <span class="text-muted">Colis Estimés</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="badge bg-success fs-4 mb-2">{{ $ramassage->nombre_colis_reel }}</div>
                                                <span class="text-muted">Colis Réels</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="badge bg-primary fs-4 mb-2">{{ count($colisDataArray) }}</div>
                                                <span class="text-muted">Colis Associés</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="badge bg-warning fs-4 mb-2">{{ number_format($ramassage->montant_total, 0, ',', ' ') }} FCFA</div>
                                                <span class="text-muted">Montant Total</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions de Planification -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Actions de Planification</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2 flex-wrap">
                            @if($ramassage->statut === 'demande')
                                @if($colisDataArray && is_array($colisDataArray) && count($colisDataArray) > 0)
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#planifierModal">
                                        <i class="ti ti-calendar-plus me-1"></i>
                                        Planifier le Ramassage
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary" disabled title="Ajoutez d'abord des colis pour pouvoir planifier">
                                        <i class="ti ti-calendar-plus me-1"></i>
                                        Planifier le Ramassage
                                    </button>
                                @endif
                                        @elseif($ramassage->statut === 'planifie')
                                            <span class="badge bg-success fs-6">
                                                <i class="ti ti-calendar-check me-1"></i>
                                                Ramassage Planifié
                                            </span>
                                            @if($ramassage->planifications->count() > 0)
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planifierModal">
                                                    <i class="ti ti-edit me-1"></i>
                                                    Modifier la Planification
                                                </button>
                                            @endif
                                        @elseif($ramassage->statut === 'en_cours')
                                            <span class="badge bg-warning fs-6">
                                                <i class="ti ti-truck me-1"></i>
                                                En Cours
                                            </span>
                                        @elseif($ramassage->statut === 'termine')
                                            <span class="badge bg-success fs-6">
                                                <i class="ti ti-check me-1"></i>
                                                Terminé
                                            </span>
                                        @elseif($ramassage->statut === 'annule')
                                            <span class="badge bg-danger fs-6">
                                                <i class="ti ti-x me-1"></i>
                                                Annulé
                                            </span>
                                        @endif

                                        @if($ramassage->statut === 'demande')
                                            <a href="{{ route('ramassages.edit', $ramassage->id) }}" class="btn btn-outline-primary">
                                                <i class="ti ti-edit me-1"></i>
                                                Modifier le Ramassage
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Colis Liés -->
                        @if($ramassage->colisLies->count() > 0)
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-package me-2"></i>
                                            Colis Liés ({{ $ramassage->colisLies->count() }})
                                        </h6>
                                        <small class="text-muted">Colis créés depuis ce ramassage</small>
                                    </div>
                                    <div class="card-body">
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
                                                    @foreach($ramassage->colisLies as $colis)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-semibold">{{ $colis->code }}</span>
                                                            </td>
                                                            <td>{{ $colis->nom_client }}</td>
                                                            <td>{{ $colis->telephone_client }}</td>
                                                            <td>
                                                                <span class="badge bg-label-primary">{{ $colis->zone->nom ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                @switch($colis->status)
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
                                                            <td>{{ $colis->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                <a href="{{ route('colis.show', $colis->id) }}" class="btn btn-sm btn-outline-primary">
                                                                    <i class="ti ti-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Planifications -->
                        @if($ramassage->planifications->count() > 0)
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Planifications</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Livreur</th>
                                                        <th>Date</th>
                                                        <th>Heure Début</th>
                                                        <th>Heure Fin</th>
                                                        <th>Adresse</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($ramassage->planifications as $planification)
                                                        <tr>
                                                            <td>{{ $planification->livreur->first_name ?? '' }} {{ $planification->livreur->last_name ?? '' }}</td>
                                                            <td>{{ $planification->date_planifiee->format('d/m/Y') }}</td>
                                                            <td>{{ $planification->heure_debut }}</td>
                                                            <td>{{ $planification->heure_fin }}</td>
                                                            <td>
                                                                <i class="ti ti-map-pin me-1"></i>{{ $planification->zone_ramassage ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $planification->statut_color }}">
                                                                    {{ $planification->statut_label }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editPlanificationModal"
                                                                        data-planification-id="{{ $planification->id }}"
                                                                        data-livreur-id="{{ $planification->livreur_id }}"
                                                                        data-date="{{ $planification->date_planifiee->format('Y-m-d') }}"
                                                                        data-heure-debut="{{ $planification->heure_debut ? \Carbon\Carbon::parse($planification->heure_debut)->format('H:i') : '' }}"
                                                                        data-heure-fin="{{ $planification->heure_fin ? \Carbon\Carbon::parse($planification->heure_fin)->format('H:i') : '' }}"
                                                                        data-adresse="{{ $planification->zone_ramassage }}"
                                                                        data-statut="{{ $planification->statut_planification ?: 'planifie' }}"
                                                                        data-notes="{{ $planification->notes_planification ?? '' }}">
                                                                    <i class="ti ti-edit"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Adresse de Ramassage -->
                        @if($ramassage->planifications->count() > 0 && $colisDataArray && is_array($colisDataArray) && count($colisDataArray) > 0)
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Adresse de Ramassage</h6>
                                        <small class="text-muted">Colis à ramasser à l'adresse de la boutique</small>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $planification = $ramassage->planifications->first();
                                        @endphp
                                        @if($planification)
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <h6 class="mb-0">
                                                        <i class="ti ti-map-pin me-2"></i>{{ $planification->zone_ramassage ?? 'Adresse inconnue' }}
                                                    </h6>
                                                    <small class="text-muted ms-2">({{ count($colisDataArray) }} colis)</small>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Client</th>
                                                                <th>Téléphone</th>
                                                                <th>Adresse</th>
                                                                <th>Type</th>
                                                                <th>Valeur</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($colisDataArray as $index => $colisData)
                                                                <tr>
                                                                    <td><span class="fw-semibold">{{ $index + 1 }}</span></td>
                                                                    <td>{{ $colisData['client'] ?? 'N/A' }}</td>
                                                                    <td>{{ $colisData['telephone_client'] ?? 'N/A' }}</td>
                                                                    <td>{{ $colisData['adresse_client'] ?? 'N/A' }}</td>
                                                                    <td>
                                                                        @php
                                                                            $type = $types->get($colisData['type_colis_id'] ?? null);
                                                                        @endphp
                                                                        {{ $type->libelle ?? 'N/A' }}
                                                                    </td>
                                                                    <td>{{ number_format($colisData['valeur'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Gestion des Colis -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">Gestion des Colis</h6>
                                    <div class="d-flex gap-2">
                                        @if($ramassage->statut === 'demande')
                                            <a href="{{ route('ramassages.edit', $ramassage->id) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-edit me-1"></i>
                                                Modifier les Colis
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{-- Les données sont maintenant préchargées dans le contrôleur --}}
                                    @if($colisDataArray && is_array($colisDataArray) && count($colisDataArray) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead></thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Client</th>
                                                        <th>Téléphone</th>
                                                        <th>Adresse</th>
                                                        <th>Zone</th>
                                                        <th>Type</th>
                                                        <th>Poids</th>
                                                        <th>Conditionnement</th>
                                                        <th>Délai</th>
                                                        <th>Mode Livraison</th>
                                                        <th>Période</th>
                                                        <th>Valeur</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($colisDataArray as $index => $colisData)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-semibold">{{ $index + 1 }}</span>
                                                            </td>
                                                            <td>{{ $colisData['client'] ?? 'N/A' }}</td>
                                                            <td>{{ $colisData['telephone_client'] ?? 'N/A' }}</td>
                                                            <td>{{ $colisData['adresse_client'] ?? 'N/A' }}</td>
                                                            <td>
                                                                {{ $communes->get($colisData['commune_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $types->get($colisData['type_colis_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $poids->get($colisData['poids_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $conditionnements->get($colisData['conditionnement_colis_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $delais->get($colisData['delai_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $modesLivraison->get($colisData['mode_livraison_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>
                                                                {{ $periodes->get($colisData['temp_id'] ?? null)?->libelle ?? 'N/A' }}
                                                            </td>
                                                            <td>{{ number_format($colisData['valeur'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                            <td>{{ $colisData['notes'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="ti ti-package-off display-4 text-muted mb-3"></i>
                                            <p class="text-muted">Aucun colis associé à ce ramassage</p>
                                            @if($ramassage->statut === 'demande')
                                                <a href="{{ route('ramassages.edit', $ramassage->id) }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i>
                                                    Ajouter des Colis
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Modal pour planifier le ramassage -->
<div class="modal fade" id="planifierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Planifier le Ramassage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ramassages.planifier', $ramassage->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="livreur_id" class="form-label">Livreur</label>
                        <select class="form-select" id="livreur_id" name="livreur_id" required>
                            <option value="">Sélectionner un livreur</option>
                            @foreach($livreurs ?? [] as $livreur)
                                <option value="{{ $livreur->id }}">{{ $livreur->first_name }} {{ $livreur->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_planifiee" class="form-label">Date de Planification pour le ramassage</label>
                                <input type="date" class="form-control" id="date_planifiee" name="date_planifiee" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="heure_planifiee" class="form-label">Heure de Planification pour le ramassage</label>
                                <input type="time" class="form-control" id="heure_planifiee" name="heure_planifiee" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="heure_debut" class="form-label">Heure de Début pour le ramassage</label>
                                <input type="time" class="form-control" id="heure_debut" name="heure_debut" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="heure_fin" class="form-label">Heure de Fin pour le ramassage</label>
                                <input type="time" class="form-control" id="heure_fin" name="heure_fin" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adresse_ramassage" class="form-label">Adresse de Ramassage</label>
                        <input type="text" class="form-control @error('adresse_ramassage') is-invalid @enderror"
                               id="adresse_ramassage" name="adresse_ramassage"
                               value="{{ $adresseGpsBoutique ?? '' }}" required>
                        <small class="form-text text-muted">Adresse GPS de la boutique pour le ramassage</small>
                        @error('adresse_ramassage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes_planification" class="form-label">Notes pour le ramassage</label>
                        <textarea class="form-control" id="notes_planification" name="notes_planification" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Planifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales pour chaque colis - Supprimé car nous utilisons maintenant colis_data -->

@include('layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du modal de planification
    const planifierModal = document.getElementById('planifierModal');
    const datePlanifieeInput = document.getElementById('date_planifiee');
    const heureDebutInput = document.getElementById('heure_debut');
    const heureFinInput = document.getElementById('heure_fin');



    // Définir la date minimum comme aujourd'hui
    if (datePlanifieeInput) {
        const today = new Date().toISOString().split('T')[0];
        datePlanifieeInput.min = today;
    }

    // Validation des heures
    if (heureDebutInput && heureFinInput) {
        heureDebutInput.addEventListener('change', function() {
            if (this.value && heureFinInput.value) {
                if (this.value >= heureFinInput.value) {
                    heureFinInput.value = '';
                    alert('L\'heure de fin doit être postérieure à l\'heure de début');
                }
            }
        });

        heureFinInput.addEventListener('change', function() {
            if (this.value && heureDebutInput.value) {
                if (this.value <= heureDebutInput.value) {
                    this.value = '';
                    alert('L\'heure de fin doit être postérieure à l\'heure de début');
                }
            }
        });
    }

    // Pré-remplir les champs si le ramassage est déjà planifié
    @if($ramassage->statut === 'planifie' && $ramassage->planifications->count() > 0)
        const firstPlanification = @json($ramassage->planifications->first());

        planifierModal.addEventListener('show.bs.modal', function() {
            if (firstPlanification) {
                // Pré-remplir les champs avec les données existantes
                const livreurSelect = document.getElementById('livreur_id');
                const zoneInput = document.getElementById('zone_ramassage');
                const notesInput = document.getElementById('notes_planification');

                if (livreurSelect && firstPlanification.livreur_id) {
                    livreurSelect.value = firstPlanification.livreur_id;
                }

                if (datePlanifieeInput && firstPlanification.date_planifiee) {
                    datePlanifieeInput.value = firstPlanification.date_planifiee;
                }

                if (heureDebutInput && firstPlanification.heure_debut) {
                    heureDebutInput.value = firstPlanification.heure_debut;
                }

                if (heureFinInput && firstPlanification.heure_fin) {
                    heureFinInput.value = firstPlanification.heure_fin;
                }

                // Pré-remplir l'adresse de ramassage avec l'adresse GPS de la boutique
                const adresseGpsBoutique = @json($adresseGpsBoutique);
                const adresseRamassageInput = document.getElementById('adresse_ramassage');
                if (adresseRamassageInput && adresseGpsBoutique) {
                    adresseRamassageInput.value = adresseGpsBoutique;
                }

                if (notesInput && firstPlanification.notes_planification) {
                    notesInput.value = firstPlanification.notes_planification;
                }
            }
        });
    @endif
});
</script>

    <!-- Modal d'édition des planifications -->
    <div class="modal fade" id="editPlanificationModal" tabindex="-1" aria-labelledby="editPlanificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlanificationModalLabel">Modifier la Planification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPlanificationForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_livreur_id" class="form-label">Livreur</label>
                            <select class="form-select" id="edit_livreur_id" name="livreur_id" required>
                                <option value="">Sélectionner un livreur</option>
                                @foreach($livreurs as $livreur)
                                    <option value="{{ $livreur->id }}">{{ $livreur->first_name }} {{ $livreur->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_date_planifiee" class="form-label">Date de Planification</label>
                            <input type="date" class="form-control" id="edit_date_planifiee" name="date_planifiee" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_heure_debut" class="form-label">Heure de Début</label>
                                    <input type="time" class="form-control" id="edit_heure_debut" name="heure_debut" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_heure_fin" class="form-label">Heure de Fin</label>
                                    <input type="time" class="form-control" id="edit_heure_fin" name="heure_fin" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_zone_ramassage" class="form-label">Adresse de Ramassage</label>
                            <input type="text" class="form-control" id="edit_zone_ramassage" name="zone_ramassage" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_statut" class="form-label">Statut</label>
                            <select class="form-select" id="edit_statut" name="statut_planification" required>
                                <option value="planifie">Planifié</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_notes_planification" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes_planification" name="notes_planification" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script pour la modale d'édition des planifications
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editPlanificationModal');
            const editForm = document.getElementById('editPlanificationForm');

            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const planificationId = button.getAttribute('data-planification-id');
                const livreurId = button.getAttribute('data-livreur-id');
                const date = button.getAttribute('data-date');
                const heureDebut = button.getAttribute('data-heure-debut');
                const heureFin = button.getAttribute('data-heure-fin');
                const adresse = button.getAttribute('data-adresse');
                const statut = button.getAttribute('data-statut');
                const notes = button.getAttribute('data-notes');

                console.log('Données récupérées:', {
                    planificationId, livreurId, date, heureDebut, heureFin, adresse, statut, notes
                });

                // Mettre à jour l'action du formulaire
                editForm.action = `/planification-ramassages/${planificationId}`;

                // Pré-remplir les champs
                document.getElementById('edit_livreur_id').value = livreurId || '';
                document.getElementById('edit_date_planifiee').value = date || '';
                document.getElementById('edit_heure_debut').value = heureDebut || '';
                document.getElementById('edit_heure_fin').value = heureFin || '';
                document.getElementById('edit_zone_ramassage').value = adresse || '';
                document.getElementById('edit_statut').value = statut || '';
                document.getElementById('edit_notes_planification').value = notes || '';
            });

            // Gestion de la soumission du formulaire
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(editForm);

                fetch(editForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fermer la modale
                        const modal = bootstrap.Modal.getInstance(editModal);
                        modal.hide();

                        // Recharger la page pour voir les changements
                        location.reload();
                    } else {
                        alert('Erreur lors de la mise à jour: ' + (data.message || 'Erreur inconnue'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la mise à jour');
                });
            });
        });
    </script>
