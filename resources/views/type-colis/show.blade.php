@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Type de Colis</h5>
                            <p class="mb-4">Informations détaillées du type de colis : {{ $type_colis->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('type-colis.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du type de colis -->
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations Générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Libellé</label>
                            <p class="form-control-plaintext">{{ $type_colis->libelle }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Créé par</label>
                            <p class="form-control-plaintext">{{ $type_colis->user->first_name ?? 'N/A' }} {{ $type_colis->user->last_name ?? '' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date de création</label>
                            <p class="form-control-plaintext">{{ $type_colis->created_at ? $type_colis->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dernière modification</label>
                            <p class="form-control-plaintext">{{ $type_colis->updated_at ? $type_colis->updated_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-package"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $type_colis->colis->count() }}</h6>
                            <small class="text-muted">Colis associés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colis associés -->
    @if($type_colis->colis->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Colis Associés</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Numéro de facture</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($type_colis->colis as $coli)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="ti ti-package"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $coli->numero_facture ?? 'N/A' }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $coli->nom_client ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $coli->telephone_client ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">{{ number_format($coli->montant_a_encaisse ?? 0, 0, ',', ' ') }} FCFA</span>
                                            </td>
                                            <td>
                                                @if($coli->status == 0)
                                                    <span class="badge bg-label-warning">En attente</span>
                                                @elseif($coli->status == 1)
                                                    <span class="badge bg-label-info">En cours</span>
                                                @elseif($coli->status == 2)
                                                    <span class="badge bg-label-success">Livré</span>
                                                @elseif($coli->status == 3)
                                                    <span class="badge bg-label-danger">Annulé par le client</span>
                                                @elseif($coli->status == 4)
                                                    <span class="badge bg-label-danger">Annulé par le livreur</span>
                                                @elseif($coli->status == 5)
                                                    <span class="badge bg-label-danger">Annulé par le marchand</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Statut inconnu</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $coli->created_at ? $coli->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('type-colis.edit', ['type_coli' => $type_colis->id]) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('type-colis.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        @if($type_colis->colis->count() == 0)
                            <button type="button" class="btn btn-outline-danger ms-auto" onclick="deleteTypeColis({{ $type_colis->id }}, '{{ $type_colis->libelle }}')">
                                <i class="ti ti-trash me-1"></i>
                                Supprimer
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour supprimer un type de colis
function deleteTypeColis(typeColisId, typeColisName) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le type de colis "' + typeColisName + '" ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/type-colis/' + typeColisId;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

@include('layouts.footer')
