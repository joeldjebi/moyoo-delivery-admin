
@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-truck-delivery me-2"></i>
                        Détails de la Livraison
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('historique-livraisons.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations générales -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Numéro :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-label-info">{{ $livraison->numero_de_livraison }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Statut :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge {{ $livraison->status_badge }}">
                                                {{ $livraison->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Code de validation :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-label-warning">{{ $livraison->code_validation }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Créé le :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <div>
                                                <div class="fw-semibold">{{ $livraison->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $livraison->created_at->format('H:i') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations de livraison -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-map-pin me-2"></i>
                                        Informations de Livraison
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Adresse :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $livraison->adresse_de_livraison ?? 'Non définie' }}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Notes :</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $livraison->note_livraison ?? 'Aucune note' }}
                                        </div>
                                    </div>

                                    @if($livraison->colis)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Colis associé :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <a href="{{ route('colis.show', $livraison->colis->id) }}"
                                                   class="badge bg-label-secondary text-decoration-none">
                                                    {{ $livraison->colis->code }}
                                                    <i class="ti ti-external-link ms-1" style="font-size: 0.7rem;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du colis -->
                    @if($livraison->colis)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-package me-2"></i>
                                    Informations du Colis
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Code :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-label-primary">{{ $livraison->colis->code }}</span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Client :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $livraison->colis->nom_client }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Téléphone :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $livraison->colis->telephone_client }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Adresse :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $livraison->colis->adresse_client }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        @if($livraison->colis->marchand)
                                            <div class="row mb-3">
                                                <div class="col-sm-4">
                                                    <strong>Marchand :</strong>
                                                </div>
                                                <div class="col-sm-8">
                                                    {{ $livraison->colis->marchand->first_name }} {{ $livraison->colis->marchand->last_name }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($livraison->colis->boutique)
                                            <div class="row mb-3">
                                                <div class="col-sm-4">
                                                    <strong>Boutique :</strong>
                                                </div>
                                                <div class="col-sm-8">
                                                    {{ $livraison->colis->boutique->nom }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($livraison->colis->livreur)
                                            <div class="row mb-3">
                                                <div class="col-sm-4">
                                                    <strong>Livreur :</strong>
                                                </div>
                                                <div class="col-sm-8">
                                                    {{ $livraison->colis->livreur->first_name }} {{ $livraison->colis->livreur->last_name }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($livraison->colis->commune)
                                            <div class="row mb-3">
                                                <div class="col-sm-4">
                                                    <strong>Commune :</strong>
                                                </div>
                                                <div class="col-sm-8">
                                                    {{ $livraison->colis->commune->libelle }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Montants -->
                    @if($livraison->colis)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-currency-franc me-2"></i>
                                    Montants
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6 class="text-muted mb-2">Montant à encaisser</h6>
                                            <div class="fw-semibold text-primary fs-4">
                                                {{ number_format($livraison->colis->montant_a_encaisse ?? 0) }} FCFA
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6 class="text-muted mb-2">Prix de vente</h6>
                                            <div class="fw-semibold text-info fs-4">
                                                {{ number_format($livraison->colis->prix_de_vente ?? 0) }} FCFA
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h6 class="text-muted mb-2">Coût de livraison</h6>
                                            <div class="fw-semibold text-success fs-4">
                                                {{ $livraison->colis->delivery_cost_formatted ?? '0 FCFA' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="text-center p-3 border rounded bg-light">
                                            <h6 class="text-muted mb-2">Total</h6>
                                            <div class="fw-bold text-dark fs-3">
                                                {{ number_format(($livraison->colis->montant_a_encaisse ?? 0) + ($livraison->colis->prix_de_vente ?? 0) + $livraison->colis->calculateDeliveryCost()) }} FCFA
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
