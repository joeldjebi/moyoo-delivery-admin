@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-package me-2"></i>
                        Colis du Livreur : {{ $livreur->first_name }} {{ $livreur->last_name }}
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
                                    {{ $colis->total() }} colis au total
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $colis->where('status', 0)->count() }}</h5>
                                    <small>En attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $colis->where('status', 1)->count() }}</h5>
                                    <small>En cours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $colis->where('status', 2)->count() }}</h5>
                                    <small>Livrés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-1">{{ $colis->whereIn('status', [3, 4, 5])->count() }}</h5>
                                    <small>Annulés</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des colis -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th>Zone/Commune</th>
                                    <th>Statut</th>
                                    <th>Date création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($colis as $colisItem)
                                    <tr>
                                        <td>
                                            <strong>{{ $colisItem->code }}</strong>
                                        </td>
                                        <td>{{ $colisItem->nom_client }}</td>
                                        <td>
                                            <a href="tel:{{ $colisItem->telephone_client }}" class="text-decoration-none">
                                                {{ $colisItem->telephone_client }}
                                            </a>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($colisItem->adresse_client, 50) }}</small>
                                        </td>
                                        <td>
                                            @if($colisItem->zone)
                                                <span class="badge bg-info">{{ $colisItem->zone->nom }}</span>
                                            @endif
                                            @if($colisItem->commune)
                                                <br><small class="text-muted">{{ $colisItem->commune->libelle }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($colisItem->status)
                                                @case(0)
                                                    <span class="badge bg-primary">En attente</span>
                                                    @break
                                                @case(1)
                                                    <span class="badge bg-warning">En cours</span>
                                                    @break
                                                @case(2)
                                                    <span class="badge bg-success">Livré</span>
                                                    @break
                                                @case(3)
                                                    <span class="badge bg-danger">Annulé (Client)</span>
                                                    @break
                                                @case(4)
                                                    <span class="badge bg-danger">Annulé (Livreur)</span>
                                                    @break
                                                @case(5)
                                                    <span class="badge bg-danger">Annulé (Marchand)</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Inconnu</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $colisItem->created_at->format('d/m/Y H:i') }}</small>
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
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('colis.show', $colisItem) }}">
                                                            <i class="ti ti-eye me-2"></i>
                                                            Voir détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('colis.edit', $colisItem) }}">
                                                            <i class="ti ti-edit me-2"></i>
                                                            Modifier
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti ti-package-off fs-1 mb-3"></i>
                                                <p>Aucun colis assigné à ce livreur</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($colis->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $colis->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@include('layouts.footer')
