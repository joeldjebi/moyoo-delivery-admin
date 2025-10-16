@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Détails de l'Utilisateur</h5>
                        <p class="mb-4">Informations complètes sur l'utilisateur</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary">
                                <i class="ti ti-edit me-1"></i>
                                Modifier
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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

<!-- Messages de succès -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Informations de l'utilisateur -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Prénom</label>
                            <p class="text-muted mb-0">{{ $user->first_name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom</label>
                            <p class="text-muted mb-0">{{ $user->last_name }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <p class="text-muted mb-0">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <p class="text-muted mb-0">{{ $user->mobile }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rôle</label>
                            <div>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type d'Utilisateur</label>
                            <div>
                                <span class="badge bg-{{ $user->user_type === 'super_admin' ? 'dark' : ($user->user_type === 'entreprise_admin' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->user_type ?? 'entreprise_user')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Statut</label>
                            <div>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Entreprise</label>
                            <div>
                                @if($user->entreprise)
                                    <span class="badge bg-info">{{ $user->entreprise->name }}</span>
                                @else
                                    <span class="text-muted">Aucune entreprise</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Avatar</h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </span>
                </div>
                <h5 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Modifier
                    </a>

                    @if($user->id !== Auth::id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                <i class="ti ti-trash me-1"></i>
                                Supprimer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informations système -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations Système</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ID Utilisateur</label>
                            <p class="text-muted mb-0">#{{ $user->id }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Créé le</label>
                            <p class="text-muted mb-0">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dernière modification</label>
                            <p class="text-muted mb-0">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Créé par</label>
                            <p class="text-muted mb-0">
                                @if($user->created_by)
                                    Utilisateur #{{ $user->created_by }}
                                @else
                                    Système
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permissions de l'utilisateur -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-shield me-2"></i>
                    Permissions de l'Utilisateur
                </h5>
                @if(auth()->user()->hasPermission('settings.update'))
                    <a href="{{ route('role-permissions.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-settings me-1"></i>
                        Gérer les Rôles
                    </a>
                @endif
            </div>
            <div class="card-body">
                @php
                    $allPermissions = \App\Models\User::getAllAvailablePermissions();

                    // Récupérer les permissions du rôle
                    $rolePermissions = \App\Models\RolePermission::getPermissionsForRole($user->role);

                    // Récupérer les permissions personnalisées
                    $customPermissions = $user->permissions ?? [];

                    // Combiner toutes les permissions
                    $allUserPermissions = array_merge($rolePermissions, $customPermissions);

                    // Grouper par catégorie avec icônes
                    $permissionCategories = [
                        'Utilisateurs' => [
                            'icon' => 'ti ti-users',
                            'color' => 'primary',
                            'permissions' => ['users.create', 'users.read', 'users.update', 'users.delete']
                        ],
                        'Colis' => [
                            'icon' => 'ti ti-package',
                            'color' => 'info',
                            'permissions' => ['colis.create', 'colis.read', 'colis.update', 'colis.delete']
                        ],
                        'Livreurs' => [
                            'icon' => 'ti ti-truck',
                            'color' => 'warning',
                            'permissions' => ['livreurs.create', 'livreurs.read', 'livreurs.update', 'livreurs.delete']
                        ],
                        'Marchands' => [
                            'icon' => 'ti ti-building-store',
                            'color' => 'success',
                            'permissions' => ['marchands.create', 'marchands.read', 'marchands.update', 'marchands.delete']
                        ],
                        'Rapports' => [
                            'icon' => 'ti ti-chart-bar',
                            'color' => 'secondary',
                            'permissions' => ['reports.read']
                        ],
                        'Paramètres' => [
                            'icon' => 'ti ti-settings',
                            'color' => 'dark',
                            'permissions' => ['settings.read', 'settings.update']
                        ]
                    ];
                @endphp

                <!-- Statistiques des permissions -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <div class="avatar avatar-sm mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ti ti-check"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0">{{ count($allUserPermissions) }}</h6>
                                <small class="text-muted">Permissions accordées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <div class="avatar avatar-sm mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-shield"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0">{{ count($rolePermissions) }}</h6>
                                <small class="text-muted">Du rôle</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <div class="avatar avatar-sm mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti ti-user-plus"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0">{{ count($customPermissions) }}</h6>
                                <small class="text-muted">Personnalisées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-secondary">
                            <div class="card-body text-center">
                                <div class="avatar avatar-sm mx-auto mb-2">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="ti ti-x"></i>
                                    </span>
                                </div>
                                <h6 class="mb-0">{{ count($allPermissions) - count($allUserPermissions) }}</h6>
                                <small class="text-muted">Non accordées</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions par catégorie -->
                <div class="row">
                    @foreach($permissionCategories as $category => $categoryData)
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-{{ $categoryData['color'] }}">
                                                <i class="{{ $categoryData['icon'] }}"></i>
                                            </span>
                                        </div>
                                        <h6 class="mb-0">{{ $category }}</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @php
                                        $categoryPermissions = $categoryData['permissions'];
                                        $grantedCount = 0;
                                        foreach($categoryPermissions as $perm) {
                                            if(in_array($perm, $allUserPermissions)) {
                                                $grantedCount++;
                                            }
                                        }
                                    @endphp

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            {{ $grantedCount }}/{{ count($categoryPermissions) }} permissions accordées
                                        </small>
                                        <div class="progress" style="width: 100px; height: 6px;">
                                            <div class="progress-bar bg-{{ $categoryData['color'] }}"
                                                 style="width: {{ count($categoryPermissions) > 0 ? ($grantedCount / count($categoryPermissions)) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>

                                    <div class="permission-list">
                                        @foreach($categoryPermissions as $permission)
                                            @php
                                                $hasPermission = in_array($permission, $allUserPermissions);
                                                $isFromRole = in_array($permission, $rolePermissions);
                                                $isCustom = in_array($permission, $customPermissions);
                                            @endphp
                                            <div class="d-flex align-items-center mb-2 p-2 rounded {{ $hasPermission ? 'bg-light-success' : 'bg-light-secondary' }}">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="checkbox"
                                                           {{ $hasPermission ? 'checked' : '' }} disabled>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-2">{{ $permission }}</strong>
                                                        @if($hasPermission)
                                                            @if($isFromRole)
                                                                <span class="badge bg-primary badge-sm">Rôle</span>
                                                            @elseif($isCustom)
                                                                <span class="badge bg-success badge-sm">Custom</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary badge-sm">Non accordée</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $allPermissions[$permission] ?? 'Permission non définie' }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Légende -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-light border">
                            <h6 class="alert-heading mb-3">
                                <i class="ti ti-info-circle me-2"></i>
                                Légende des Permissions
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">Rôle</span>
                                        <small>Permission accordée par le rôle de l'utilisateur</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">Custom</span>
                                        <small>Permission personnalisée ajoutée individuellement</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-secondary me-2">Non accordée</span>
                                        <small>Permission non accordée à cet utilisateur</small>
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

<!-- Changement de mot de passe -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Changer le Mot de Passe</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.change-password', $user) }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Nouveau Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" required>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_password_confirmation" class="form-label">Confirmer le Nouveau Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="ti ti-key me-1"></i>
                                    Changer le Mot de Passe
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
