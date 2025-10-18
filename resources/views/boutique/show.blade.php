@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails de la Boutique</h5>
                            <p class="mb-4">Informations complètes de la boutique : {{ $boutique->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('boutiques.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                                <a href="{{ route('boutiques.edit', $boutique) }}" class="btn btn-primary">
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

    <!-- Informations de la boutique -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de la Boutique</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom de la boutique</label>
                            <p class="form-control-plaintext">{{ $boutique->libelle }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Marchand</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-info">{{ $boutique->marchand->full_name }}</span>
                            </p>
                        </div>
                        @if($boutique->mobile)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <p class="form-control-plaintext">{{ $boutique->mobile }}</p>
                        </div>
                        @endif
                        @if($boutique->adresse)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <p class="form-control-plaintext">{{ $boutique->adresse }}</p>
                        </div>
                        @endif
                        @if($boutique->adresse)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <p class="form-control-plaintext">{{ $boutique->adresse }}</p>
                        </div>
                        @endif
                        @if($boutique->adresse_gps)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Lien Google Maps</label>
                            <p class="form-control-plaintext">
                                <a href="{{ $boutique->adresse_gps }}" target="_blank" class="text-primary">
                                    <i class="ti ti-external-link me-1"></i>
                                    Voir sur Google Maps
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($boutique->cover_image)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Image de couverture</label>
                            <div class="form-control-plaintext">
                                <img src="{{ asset('storage/' . $boutique->cover_image) }}" alt="Image de la boutique" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                <br>
                                <a href="{{ asset('storage/' . $boutique->cover_image) }}" target="_blank" class="text-primary mt-2 d-inline-block">
                                    <i class="ti ti-external-link me-1"></i>
                                    Voir l'image en grand
                                </a>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <p class="form-control-plaintext">
                                @if($boutique->status === 'active')
                                    <span class="badge bg-label-success">Actif</span>
                                @else
                                    <span class="badge bg-label-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
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
                        <p class="form-control-plaintext">{{ $boutique->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dernière modification</label>
                        <p class="form-control-plaintext">{{ $boutique->updated_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Créé par</label>
                        <p class="form-control-plaintext">{{ $boutique->user->first_name ?? 'N/A' }} {{ $boutique->user->last_name ?? 'N/A' }}</p>
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
                        <a href="{{ route('boutiques.edit', $boutique) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>

                        <form action="{{ route('boutiques.toggle-status', $boutique) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning w-100">
                                @if($boutique->status === 'active')
                                    <i class="ti ti-building-off me-1"></i>
                                    Désactiver
                                @else
                                    <i class="ti ti-building me-1"></i>
                                    Activer
                                @endif
                            </button>
                        </form>

                        <form action="{{ route('boutiques.destroy', $boutique) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette boutique ?')">
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
                    <h4 class="mb-1">{{ $colisStats['total'] ?? 0 }}</h4>
                    <p class="text-muted mb-0">Colis</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ti ti-building-store"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $boutique->marchand->boutiques->count() }}</h4>
                    <p class="text-muted mb-0">Boutiques du marchand</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ti ti-calendar"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-0">Depuis le {{ $boutique->created_at->format('d-m-Y à H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des colis -->
    @if(isset($colisStats) && $colisStats['total'] > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques des Colis</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ti ti-package"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $colisStats['total'] }}</h6>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="ti ti-clock"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $colisStats['en_attente'] }}</h6>
                                    <small class="text-muted">En attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti ti-truck"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $colisStats['en_cours'] }}</h6>
                                    <small class="text-muted">En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-check"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $colisStats['livres'] }}</h6>
                                    <small class="text-muted">Livrés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($colisStats['annules'] > 0)
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="ti ti-x"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $colisStats['annules'] }}</h6>
                                    <small class="text-muted">Annulés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des colis récents -->
    @if(isset($colis) && $colis->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Colis Récents ({{ $colis->count() }})</h5>
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
                                        @php
                                            $statusConfig = match($colis_item->status) {
                                                \App\Models\Colis::STATUS_EN_ATTENTE => ['label' => 'En attente', 'class' => 'bg-label-warning'],
                                                \App\Models\Colis::STATUS_EN_COURS => ['label' => 'En cours', 'class' => 'bg-label-primary'],
                                                \App\Models\Colis::STATUS_LIVRE => ['label' => 'Livré', 'class' => 'bg-label-success'],
                                                \App\Models\Colis::STATUS_ANNULE_CLIENT => ['label' => 'Annulé client', 'class' => 'bg-label-danger'],
                                                \App\Models\Colis::STATUS_ANNULE_LIVREUR => ['label' => 'Annulé livreur', 'class' => 'bg-label-danger'],
                                                \App\Models\Colis::STATUS_ANNULE_MARCHAND => ['label' => 'Annulé marchand', 'class' => 'bg-label-danger'],
                                                default => ['label' => 'Inconnu', 'class' => 'bg-label-secondary']
                                            };
                                        @endphp
                                        <span class="badge {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
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
                    @if($colis->count() > 4)
                    <div class="text-center mt-3">
                        <a href="{{ route('colis.index') }}?boutique_id={{ $boutique->id }}" class="btn btn-outline-primary">
                            <i class="ti ti-eye me-1"></i>
                            Voir plus ({{ $colis->count() - 4 }} autres)
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
                    <p class="text-muted mb-4">Cette boutique n'a pas encore de colis associé.</p>
                    <a href="{{ route('colis.create') }}?boutique_id={{ $boutique->id }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Créer le premier colis
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif


@include('layouts.footer')
