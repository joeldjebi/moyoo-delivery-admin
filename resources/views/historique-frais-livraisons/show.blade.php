@include('layouts.header')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Détails de l'Historique</h5>
                        <small class="text-muted">{{ $historique->type_operation_label }}</small>
                    </div>
                    <a href="{{ route('historique-frais-livraisons.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Informations de l'Opération</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Type d'Opération:</td>
                                    <td>
                                        <span class="badge bg-{{ $historique->type_operation_color }}">
                                            {{ $historique->type_operation_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Date d'Opération:</td>
                                    <td>{{ $historique->date_operation->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Description:</td>
                                    <td>{{ $historique->description_operation }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Utilisateur:</td>
                                    <td>{{ $historique->user->first_name ?? 'N/A' }} {{ $historique->user->last_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Frais de Livraison:</td>
                                    <td>
                                        @if($historique->fraisLivraison)
                                            <a href="{{ route('frais-livraisons.show', $historique->fraisLivraison->id) }}" class="text-decoration-none">
                                                {{ $historique->fraisLivraison->libelle }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Montants</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Montant Avant:</td>
                                    <td>
                                        @if($historique->montant_avant)
                                            <span class="text-muted">{{ number_format($historique->montant_avant, 0, ',', ' ') }} FCFA</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Montant Après:</td>
                                    <td>
                                        @if($historique->montant_apres)
                                            <span class="text-success">{{ number_format($historique->montant_apres, 0, ',', ' ') }} FCFA</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($historique->montant_avant && $historique->montant_apres)
                                    <tr>
                                        <td class="fw-semibold">Différence:</td>
                                        <td>
                                            @php
                                                $difference = $historique->montant_apres - $historique->montant_avant;
                                            @endphp
                                            <span class="text-{{ $difference >= 0 ? 'success' : 'danger' }}">
                                                {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 0, ',', ' ') }} FCFA
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($historique->colis || $historique->livraison)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Éléments Associés</h6>
                                <div class="row">
                                    @if($historique->colis)
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Colis</h6>
                                                    <p class="card-text">
                                                        <strong>Code:</strong> {{ $historique->colis->code }}<br>
                                                        <strong>Client:</strong> {{ $historique->colis->nom_client }}<br>
                                                        <strong>Zone:</strong> {{ $historique->colis->zone->commune->libelle ?? 'N/A' }}
                                                    </p>
                                                    <a href="{{ route('colis.show', $historique->colis->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye me-1"></i>Voir le colis
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($historique->livraison)
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Livraison</h6>
                                                    <p class="card-text">
                                                        <strong>ID:</strong> {{ $historique->livraison->id }}<br>
                                                        <strong>Statut:</strong> {{ $historique->livraison->status }}<br>
                                                        <strong>Date:</strong> {{ $historique->livraison->created_at->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($historique->donnees_avant || $historique->donnees_apres)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Données Détaillées</h6>
                                <div class="row">
                                    @if($historique->donnees_avant)
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">Données Avant</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="mb-0"><code>{{ json_encode($historique->donnees_avant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($historique->donnees_apres)
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">Données Après</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="mb-0"><code>{{ json_encode($historique->donnees_apres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
