@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-user me-2"></i>
                        Détails du Livreur
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('livreurs.edit', $livreur) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('livreurs.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Photo et informations principales -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    @if($livreur->photo)
                                        <img src="{{ asset('storage/' . $livreur->photo) }}"
                                             alt="Photo de {{ $livreur->first_name }}"
                                             class="rounded-circle mb-3"
                                             width="120"
                                             height="120">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                             style="width: 120px; height: 120px;">
                                            <i class="ti ti-user text-white" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif

                                    <h4 class="mb-1">{{ $livreur->first_name }} {{ $livreur->last_name }}</h4>

                                    @if($livreur->status === 'actif')
                                        <span class="badge bg-success mb-3">Actif</span>
                                    @else
                                        <span class="badge bg-secondary mb-3">Inactif</span>
                                    @endif

                                    <div class="d-flex flex-column gap-2">
                                        <a href="tel:{{ $livreur->mobile }}" class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-phone me-1"></i>
                                            {{ $livreur->mobile }}
                                        </a>

                                        @if($livreur->email)
                                            <a href="mailto:{{ $livreur->email }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-mail me-1"></i>
                                                {{ $livreur->email }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques rapides -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Statistiques
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h5 class="mb-1 text-primary">{{ $livreur->colis()->count() }}</h5>
                                                <small class="text-muted">Colis assignés</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="mb-1 text-success">{{ $livreur->colis()->where('status', 2)->count() }}</h5>
                                            <small class="text-muted">Livrés</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations détaillées -->
                        <div class="col-md-8">
                            <!-- Informations personnelles -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-user me-2"></i>
                                        Informations personnelles
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Prénom</label>
                                                <p class="mb-0">{{ $livreur->first_name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nom</label>
                                                <p class="mb-0">{{ $livreur->last_name }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Téléphone</label>
                                                <p class="mb-0">
                                                    <a href="tel:{{ $livreur->mobile }}" class="text-decoration-none">
                                                        <i class="ti ti-phone me-1"></i>
                                                        {{ $livreur->mobile }}
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <p class="mb-0">
                                                    @if($livreur->email)
                                                        <a href="mailto:{{ $livreur->email }}" class="text-decoration-none">
                                                            <i class="ti ti-mail me-1"></i>
                                                            {{ $livreur->email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Non renseigné</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($livreur->permis)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Numéro de permis</label>
                                            <p class="mb-0">{{ $livreur->permis }}</p>
                                        </div>
                                    @endif

                                    @if($livreur->adresse)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Adresse</label>
                                            <p class="mb-0">{{ $livreur->adresse }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Informations professionnelles -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-briefcase me-2"></i>
                                        Informations professionnelles
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Engin assigné</label>
                                                @if($livreur->engin)
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-truck me-2 text-primary"></i>
                                                        <div>
                                                            <p class="mb-0 fw-bold">{{ $livreur->engin->libelle }}</p>
                                                            @if($livreur->engin->typeEngin)
                                                                <small class="text-muted">{{ $livreur->engin->typeEngin->libelle }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class="mb-0 text-muted">Aucun engin assigné</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Communes d'activité</label>
                                                @if($livreur->communes->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($livreur->communes as $commune)
                                                            <span class="badge bg-info">
                                                                <i class="ti ti-map-pin me-1"></i>
                                                                {{ $commune->libelle }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="mb-0 text-muted">Aucune commune assignée</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Statut</label>
                                                <p class="mb-0">
                                                    @if($livreur->status === 'actif')
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactif</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Membre depuis</label>
                                                <p class="mb-0">
                                                    <i class="ti ti-calendar me-1"></i>
                                                    {{ $livreur->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions rapides -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-settings me-2"></i>
                                        Actions rapides
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="{{ route('livreurs.colis', $livreur) }}" class="btn btn-outline-primary w-100 mb-2">
                                                <i class="ti ti-package me-1"></i>
                                                Voir les colis assignés
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('livreurs.livraisons', $livreur) }}" class="btn btn-outline-success w-100 mb-2">
                                                <i class="ti ti-truck-delivery me-1"></i>
                                                Voir l'historique des livraisons
                                            </a>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="{{ route('livreurs.edit', $livreur) }}" class="btn btn-primary w-100 mb-2">
                                                <i class="ti ti-edit me-1"></i>
                                                Modifier les informations
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <form action="{{ route('livreurs.destroy', $livreur) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce livreur ?')"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                                                    <i class="ti ti-trash me-1"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
