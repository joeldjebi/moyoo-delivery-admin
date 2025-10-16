@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Marchand</h5>
                            <p class="mb-4">Informations complètes du marchand : {{ $marchand->first_name }} {{ $marchand->last_name }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('marchands.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                                <a href="{{ route('marchands.edit', $marchand) }}" class="btn btn-primary">
                                    <i class="ti ti-pencil me-1"></i>
                                    Modifier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du marchand -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations Personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <p class="form-control-plaintext">{{ $marchand->first_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <p class="form-control-plaintext">{{ $marchand->last_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-info">{{ $marchand->mobile }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext">
                                @if($marchand->email)
                                    <span class="text-primary">{{ $marchand->email }}</span>
                                @else
                                    <span class="text-muted">Non renseigné</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Commune</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-secondary">{{ $marchand->commune->libelle ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <p class="form-control-plaintext">
                                @if($marchand->status === 'active')
                                    <span class="badge bg-label-success">Actif</span>
                                @else
                                    <span class="badge bg-label-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
                        @if($marchand->adresse)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <p class="form-control-plaintext">{{ $marchand->adresse }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations Générales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date de création</label>
                        <p class="form-control-plaintext">{{ $marchand->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dernière modification</label>
                        <p class="form-control-plaintext">{{ $marchand->updated_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Créé par</label>
                        <p class="form-control-plaintext">{{ $marchand->user->first_name ?? 'N/A' }} {{ $marchand->user->last_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('marchands.edit', $marchand) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>

                        <form action="{{ route('marchands.toggle-status', $marchand) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning w-100">
                                @if($marchand->status === 'active')
                                    <i class="ti ti-user-off me-1"></i>
                                    Désactiver
                                @else
                                    <i class="ti ti-user-check me-1"></i>
                                    Activer
                                @endif
                            </button>
                        </form>

                        <form action="{{ route('marchands.destroy', $marchand) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce marchand ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-trash me-1"></i>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti ti-package"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $marchand->colis->count() }}</h4>
                    <p class="text-muted mb-0">Colis</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ti ti-building-store"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $marchand->boutiques->count() }}</h4>
                    <p class="text-muted mb-0">Boutiques</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ti ti-calendar"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-0">Depuis le {{ $marchand->created_at->format('d-m-Y à H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des colis récents -->
    @if($colis_count > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Colis Récents ({{ $colis_count }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($colis->take(4) as $colis_item)
                                <tr>
                                    <td>{{ $colis_item->code ?? 'N/A' }}</td>
                                    <td>{{ $colis_item->nom_client ?? 'N/A' }}</td>
                                    <td>{{ $colis_item->telephone_client ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $colis_item->status == 1 ? 'success' : 'warning' }}">
                                            {{ $colis_item->status == 1 ? 'Livré' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td>{{ $colis_item->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('colis.show', $colis_item) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-eye me-1"></i>
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($colis_count > 4)
                    <div class="text-center mt-3">
                        <a href="{{ route('colis.index') }}?marchand_id={{ $marchand->id }}" class="btn btn-outline-primary">
                            <i class="ti ti-eye me-1"></i>
                            Voir plus ({{ $colis_count - 4 }} autres)
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Aucun colis -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-4">
                        <span class="avatar-initial rounded bg-label-secondary">
                            <i class="ti ti-package ti-48px"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">Aucun colis</h5>
                    <p class="text-muted mb-4">Ce marchand n'a pas encore de colis associé.</p>
                    <a href="{{ route('colis.create') }}?marchand_id={{ $marchand->id }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Créer le premier colis
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des boutiques -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Boutiques du Marchand</h5>
                </div>
                <div class="card-body">
                    @if($boutiques_count > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Adresse</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($boutiques as $boutique)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-label-info">
                                                        <i class="ti ti-building-store"></i>
                                                    </span>
                                                </div>
                                                <strong>{{ $boutique->libelle }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if($boutique->adresse)
                                                <small class="text-muted">{{ $boutique->adresse }}</small>
                                            @else
                                                <span class="text-muted">Non renseignée</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $boutique->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $boutique->status === 'active' ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $boutique->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('boutiques.show', $boutique) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('boutiques.edit', $boutique) }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar avatar-xl mx-auto mb-4">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="ti ti-building-store ti-48px"></i>
                                </span>
                            </div>
                            <h5 class="mb-2">Aucune boutique</h5>
                            <p class="text-muted mb-4">Ce marchand n'a pas encore de boutique associée.</p>
                            <a href="{{ route('boutiques.create') }}?marchand_id={{ $marchand->id }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Créer la première boutique
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@include('layouts.footer')
