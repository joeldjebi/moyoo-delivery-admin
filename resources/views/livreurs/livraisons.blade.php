@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-truck-delivery me-2"></i>
                        Livraisons du Livreur : {{ $livreur->first_name }} {{ $livreur->last_name }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('livreurs.show', $livreur) }}" class="btn btn-outline-primary">
                            <i class="ti ti-user me-1"></i>
                            Voir profil
                        </a>
                        <a href="{{ route('livreurs.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informations du livreur -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                @if($livreur->photo)
                                    <img src="{{ asset('storage/' . $livreur->photo) }}"
                                         alt="Photo de {{ $livreur->first_name }}"
                                         class="rounded-circle me-3"
                                         width="60"
                                         height="60">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                         style="width: 60px; height: 60px;">
                                        <i class="ti ti-user text-white" style="font-size: 1.5rem;"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $livreur->first_name }} {{ $livreur->last_name }}</h6>
                                    <p class="text-muted mb-0">
                                        <i class="ti ti-phone me-1"></i>{{ $livreur->mobile }}
                                        @if($livreur->email)
                                            | <i class="ti ti-mail me-1"></i>{{ $livreur->email }}
                                        @endif
                                    </p>
                                    @if($livreur->engin)
                                        <small class="text-muted">
                                            <i class="ti ti-truck me-1"></i>{{ $livreur->engin->libelle }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column">
                                <span class="badge bg-{{ $livreur->status === 'actif' ? 'success' : 'secondary' }} mb-2">
                                    {{ $livreur->status === 'actif' ? 'Actif' : 'Inactif' }}
                                </span>
                                <small class="text-muted">
                                    {{ $livraisons->total() }} livraisons au total
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $livraisons->where('status', 0)->count() }}</h5>
                                    <small>En attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $livraisons->where('status', 1)->count() }}</h5>
                                    <small>En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $livraisons->where('status', 2)->count() }}</h5>
                                    <small>Livrées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $livraisons->whereIn('status', [3, 4, 5])->count() }}</h5>
                                    <small>Annulées</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des livraisons -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Colis</th>
                                    <th>Client</th>
                                    <th>Adresse</th>
                                    <th>Marchand/Boutique</th>
                                    <th>Statut</th>
                                    <th>Date création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($livraisons as $livraison)
                                    <tr>
                                        <td>
                                            <strong>{{ $livraison->numero_de_livraison }}</strong>
                                        </td>
                                        <td>
                                            @if($livraison->colis)
                                                <a href="{{ route('colis.show', $livraison->colis) }}" class="text-decoration-none">
                                                    {{ $livraison->colis->code }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($livraison->colis)
                                                {{ $livraison->colis->nom_client }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($livraison->adresse_de_livraison, 50) }}</small>
                                        </td>
                                        <td>
                                            @if($livraison->marchand)
                                                <div>
                                                    <strong>{{ $livraison->marchand->first_name }} {{ $livraison->marchand->last_name }}</strong>
                                                    @if($livraison->boutique)
                                                        <br><small class="text-muted">{{ $livraison->boutique->libelle }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($livraison->status)
                                                @case(0)
                                                    <span class="badge bg-primary">En attente</span>
                                                    @break
                                                @case(1)
                                                    <span class="badge bg-warning">En cours</span>
                                                    @break
                                                @case(2)
                                                    <span class="badge bg-success">Livrée</span>
                                                    @break
                                                @case(3)
                                                    <span class="badge bg-danger">Annulée (Client)</span>
                                                    @break
                                                @case(4)
                                                    <span class="badge bg-danger">Annulée (Livreur)</span>
                                                    @break
                                                @case(5)
                                                    <span class="badge bg-danger">Annulée (Marchand)</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Inconnu</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $livraison->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($livraison->colis)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('colis.show', $livraison->colis) }}">
                                                                <i class="ti ti-eye me-2"></i>
                                                                Voir colis
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if($livraison->code_validation)
                                                        <li>
                                                            <span class="dropdown-item-text">
                                                                <i class="ti ti-key me-2"></i>
                                                                Code: {{ $livraison->code_validation }}
                                                            </span>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti ti-truck-off fs-1 mb-3"></i>
                                                <p>Aucune livraison trouvée pour ce livreur</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($livraisons->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $livraisons->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


@include('layouts.footer')
