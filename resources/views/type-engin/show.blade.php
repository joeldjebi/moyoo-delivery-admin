@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Type d'Engin</h5>
                            <p class="mb-4">Informations détaillées du type d'engin : {{ $type_engin->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('type-engins.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du type d'engin -->
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
                            <p class="form-control-plaintext">{{ $type_engin->libelle }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Créé par</label>
                            <p class="form-control-plaintext">{{ $type_engin->user->first_name ?? 'N/A' }} {{ $type_engin->user->last_name ?? '' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date de création</label>
                            <p class="form-control-plaintext">{{ $type_engin->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Dernière modification</label>
                            <p class="form-control-plaintext">{{ $type_engin->updated_at->format('d/m/Y à H:i') }}</p>
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
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-truck"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $type_engin->engins->count() }}</h6>
                            <small class="text-muted">Engins associés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engins associés -->
    @if($type_engin->engins->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Engins Associés</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Libellé</th>
                                        <th>Marque</th>
                                        <th>Modèle</th>
                                        <th>Immatriculation</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($type_engin->engins as $engin)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="ti ti-truck"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $engin->libelle ?? 'N/A' }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $engin->marque ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $engin->modele ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $engin->immatriculation ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($engin->status == 'active')
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Inactif</span>
                                                @endif
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
                        <a href="{{ route('type-engins.edit', $type_engin) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('type-engins.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        @if($type_engin->engins->count() == 0)
                            <button type="button" class="btn btn-outline-danger ms-auto" onclick="deleteTypeEngin({{ $type_engin->id }}, '{{ $type_engin->libelle }}')">
                                <i class="ti ti-trash me-1"></i>
                                Supprimer
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// Fonction pour supprimer un type d'engin
function deleteTypeEngin(typeEnginId, typeEnginName) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le type d\'engin "' + typeEnginName + '" ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/type-engins/' + typeEnginId;

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
