@include('layouts.header')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Détails du Frais de Livraison</h5>
                        <small class="text-muted">{{ $fraisLivraison->libelle }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('frais-livraisons.edit', $fraisLivraison->id) }}" class="btn btn-outline-warning">
                            <i class="ti ti-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('frais-livraisons.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Informations Générales</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Libellé:</td>
                                    <td>{{ $fraisLivraison->libelle }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Description:</td>
                                    <td>{{ $fraisLivraison->description ?? 'Aucune description' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Montant:</td>
                                    <td><span class="fw-semibold text-primary">{{ number_format($fraisLivraison->montant, 0, ',', ' ') }} FCFA</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Type:</td>
                                    <td><span class="badge bg-label-info">{{ $fraisLivraison->type_frais_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Zone Applicable:</td>
                                    <td><span class="badge bg-label-primary">{{ $fraisLivraison->zone_applicable_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Statut:</td>
                                    <td><span class="badge bg-{{ $fraisLivraison->statut_color }}">{{ $fraisLivraison->statut_label }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Période de Validité</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Date de Début:</td>
                                    <td>{{ $fraisLivraison->date_debut->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Date de Fin:</td>
                                    <td>{{ $fraisLivraison->date_fin ? $fraisLivraison->date_fin->format('d/m/Y') : 'Aucune limite' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Créé par:</td>
                                    <td>{{ $fraisLivraison->createdBy->first_name ?? 'N/A' }} {{ $fraisLivraison->createdBy->last_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Date de Création:</td>
                                    <td>{{ $fraisLivraison->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Dernière Modification:</td>
                                    <td>{{ $fraisLivraison->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($fraisLivraison->zones_specifiques && count($fraisLivraison->zones_specifiques) > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Zones Spécifiques</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($fraisLivraison->zones_specifiques as $zoneId)
                                        @php
                                            $commune = \App\Models\Commune::find($zoneId);
                                        @endphp
                                        @if($commune)
                                            <span class="badge bg-label-secondary">{{ $commune->libelle }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($fraisLivraison->historique->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Historique des Modifications</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Utilisateur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fraisLivraison->historique->take(10) as $historique)
                                                <tr>
                                                    <td>{{ $historique->date_operation->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $historique->type_operation_color }}">
                                                            {{ $historique->type_operation_label }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $historique->description_operation }}</td>
                                                    <td>{{ $historique->user->first_name ?? 'N/A' }} {{ $historique->user->last_name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.menu')
@include('layouts.footer')
