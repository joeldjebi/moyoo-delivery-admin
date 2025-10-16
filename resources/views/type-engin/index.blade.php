@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Types d'Engins</h5>
                            <p class="mb-4">Gérez les types d'engins de votre système de livraison</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('type-engins.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter un Type d'Engin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Liste des types d'engins -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Types d'Engins</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">{{ $typeEngins->total() }} types d'engins au total</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($typeEngins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Libellé</th>
                                        <th>Créé par</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($typeEngins as $typeEngin)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="ti ti-truck"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $typeEngin->libelle }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $typeEngin->user->first_name ?? 'N/A' }} {{ $typeEngin->user->last_name ?? '' }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $typeEngin->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('type-engins.show', $typeEngin) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('type-engins.edit', $typeEngin) }}">
                                                            <i class="ti ti-pencil me-1"></i> Modifier
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteTypeEngin({{ $typeEngin->id }}, '{{ $typeEngin->libelle }}')">
                                                            <i class="ti ti-trash me-1"></i> Supprimer
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $typeEngins->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-truck-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucun type d'engin trouvé</h5>
                            <p class="text-muted">Commencez par ajouter votre premier type d'engin.</p>
                            <a href="{{ route('type-engins.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter un Type d'Engin
                            </a>
                        </div>
                    @endif
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
