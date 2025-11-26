@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Gestion des Permissions des Rôles</h5>
                        <p class="mb-4">Configurez les permissions pour chaque rôle du système</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Messages de succès -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Gestion des permissions par rôle -->
<div class="row">
    @foreach($roles as $role)
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-shield me-2"></i>
                        Rôle: {{ ucfirst($role) }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('role-permissions.update') }}" method="POST" class="role-permission-form">
                        @csrf
                        <input type="hidden" name="role" value="{{ $role }}">

                        <div class="permissions-container">
                            @foreach($availablePermissions as $permission => $description)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permission }}"
                                           id="{{ $role }}_{{ $permission }}"
                                           {{ in_array($permission, $rolePermissions[$role] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $role }}_{{ $permission }}">
                                        <strong>{{ $permission }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $description }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Mettre à jour les permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Statistiques des permissions -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques des Permissions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        {{ strtoupper(substr($role, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ ucfirst($role) }}</h6>
                                    <small class="text-muted">
                                        {{ count($rolePermissions[$role] ?? []) }} permissions attribuées
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des événements pour les formulaires
    const forms = document.querySelectorAll('.role-permission-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const role = formData.get('role');
            const permissions = formData.getAll('permissions[]');

            // Debug: afficher les données dans la console
            console.log('Soumission du formulaire:', {
                role: role,
                permissions: permissions,
                count: permissions.length
            });

            // Confirmation avant mise à jour
            if (!confirm(`Êtes-vous sûr de vouloir mettre à jour les permissions du rôle "${role}" ?`)) {
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>

@include('layouts.footer')
