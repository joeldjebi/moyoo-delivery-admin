@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-eye me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('historique-livraisons.edit', $historique_livraison) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('historique-livraisons.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>

                <div class="card-body">
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

                    <div class="row">
                        <!-- Informations générales -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Informations générales
                                    </h6>
                                </div>
                                <div class="card-body">

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Statut :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge {{ $historique_livraison->status_badge }}">
                                                {{ $historique_livraison->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Créé le :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $historique_livraison->created_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Modifié le :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $historique_livraison->updated_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>

                                    @if($historique_livraison->user)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Créé par :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->user->first_name ?? 'Utilisateur inconnu' }} {{ $historique_livraison->user->last_name ?? '' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Montants -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-currency-franc me-2"></i>
                                        Montants
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <strong>Montant à encaisser :</strong>
                                        </div>
                                        <div class="col-sm-6 text-end">
                                            <span class="fw-semibold text-primary">
                                                {{ number_format($historique_livraison->colis->montant_a_encaisse) }} FCFA
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <strong>Prix de vente :</strong>
                                        </div>
                                        <div class="col-sm-6 text-end">
                                            <span class="fw-semibold text-info">
                                                {{ number_format($historique_livraison->colis->prix_de_vente) }} FCFA
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <strong>Montant livraison :</strong>
                                        </div>
                                        <div class="col-sm-6 text-end">
                                            <span class="fw-semibold text-success">
                                                {{ number_format($historique_livraison->montant_de_la_livraison) }} FCFA
                                            </span>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>Total :</strong>
                                        </div>
                                        <div class="col-sm-6 text-end">
                                            <span class="fw-bold text-dark fs-5">
                                                {{ number_format($historique_livraison->colis->montant_a_encaisse + $historique_livraison->colis->prix_de_vente + $historique_livraison->montant_de_la_livraison) }} FCFA
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Package Colis -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-package me-2"></i>
                                        Package Colis
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($historique_livraison->packageColis)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Numéro :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-info">
                                                    {{ $historique_livraison->packageColis->numero_package }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Statut :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-secondary">
                                                    {{ $historique_livraison->packageColis->statut ?? 'Non défini' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Créé le :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->packageColis->created_at->format('d/m/Y à H:i') }}
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">Aucun package associé</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Livraison -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-truck-delivery me-2"></i>
                                        Livraison
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($historique_livraison->livraison)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Numéro :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-primary">
                                                    {{ $historique_livraison->livraison->numero_de_livraison }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Statut :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-secondary">
                                                    {{ $historique_livraison->livraison->status_label ?? 'Non défini' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Créé le :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->livraison->created_at->format('d/m/Y à H:i') }}
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">Aucune livraison associée</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Colis -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-box me-2"></i>
                                        Colis
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($historique_livraison->colis)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Code :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-secondary">
                                                    {{ $historique_livraison->colis->code }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Client :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->colis->nom_client }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Téléphone :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->colis->telephone_client }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Adresse :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->colis->adresse_client }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Statut :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-secondary">
                                                    {{ $historique_livraison->colis->status_label ?? 'Non défini' }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">Aucun colis associé</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Livreur -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-user me-2"></i>
                                        Livreur
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($historique_livraison->livreur)
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar me-3">
                                                @if($historique_livraison->livreur->photo)
                                                    <img src="{{ asset('storage/' . $historique_livraison->livreur->photo) }}" alt="Avatar" class="rounded-circle">
                                                @else
                                                    <div class="avatar-initial bg-primary rounded-circle">
                                                        {{ substr($historique_livraison->livreur->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $historique_livraison->livreur->first_name }} {{ $historique_livraison->livreur->last_name }}</h6>
                                                <small class="text-muted">{{ $historique_livraison->livreur->mobile }}</small>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Email :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $historique_livraison->livreur->email }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Statut :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge {{ $historique_livraison->livreur->status == 'actif' ? 'bg-label-success' : 'bg-label-danger' }}">
                                                    {{ ucfirst($historique_livraison->livreur->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        @if($historique_livraison->livreur->engin)
                                            <div class="row mb-3">
                                                <div class="col-sm-4">
                                                    <strong>Engin :</strong>
                                                </div>
                                                <div class="col-sm-8">
                                                    <span class="badge bg-label-info">
                                                        {{ $historique_livraison->livreur->engin->libelle }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted">Aucun livreur associé</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('historique-livraisons.edit', $historique_livraison) }}" class="btn btn-primary">
                                    <i class="ti ti-pencil me-1"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('historique-livraisons.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
