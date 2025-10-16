@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Créer un Utilisateur</h5>
                        <p class="mb-4">Ajoutez un nouvel utilisateur à la plateforme</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
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

<!-- Messages d'erreur -->
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

<!-- Formulaire de création -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations de l'Utilisateur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Adresse Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Numéro de Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                   id="mobile" name="mobile"
                                   value="{{ old('mobile') }}" required>
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le Mot de Passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                                <select class="form-select @error('entreprise_id') is-invalid @enderror" id="entreprise_id" name="entreprise_id" required>
                                    <option value="">Sélectionnez une entreprise</option>
                                    @foreach($entreprises as $entreprise)
                                        <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                            {{ $entreprise->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('entreprise_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Sélectionnez un rôle</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="user_type" class="form-label">Type d'Utilisateur <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="entreprise_admin" {{ old('user_type') == 'entreprise_admin' ? 'selected' : '' }}>Administrateur d'Entreprise</option>
                                <option value="entreprise_user" {{ old('user_type') == 'entreprise_user' ? 'selected' : '' }}>Utilisateur d'Entreprise</option>
                            </select>
                            @error('user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Sélectionnez un statut</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Permissions personnalisées -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="ti ti-shield me-2"></i>
                                        Permissions Personnalisées
                                    </h6>
                                    <small class="text-muted">Ajoutez des permissions supplémentaires à celles du rôle</small>
                                </div>
                                <div class="card-body">
                                    @php
                                        $allPermissions = \App\Models\User::getAllAvailablePermissions();
                                        $permissionCategories = [
                                            'Utilisateurs' => ['users.create', 'users.read', 'users.update', 'users.delete'],
                                            'Colis' => ['colis.create', 'colis.read', 'colis.update', 'colis.delete'],
                                            'Livreurs' => ['livreurs.create', 'livreurs.read', 'livreurs.update', 'livreurs.delete'],
                                            'Marchands' => ['marchands.create', 'marchands.read', 'marchands.update', 'marchands.delete'],
                                            'Rapports' => ['reports.read'],
                                            'Paramètres' => ['settings.read', 'settings.update']
                                        ];
                                    @endphp

                                    <div class="row">
                                        @foreach($permissionCategories as $category => $permissions)
                                            <div class="col-md-6 mb-3">
                                                <h6 class="fw-semibold mb-2">{{ $category }}</h6>
                                                <div class="permission-list">
                                                    @foreach($permissions as $permission)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="custom_permissions[]"
                                                                   value="{{ $permission }}"
                                                                   id="custom_{{ $permission }}"
                                                                   {{ in_array($permission, old('custom_permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="custom_{{ $permission }}">
                                                                <strong>{{ $permission }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $allPermissions[$permission] ?? 'Permission non définie' }}</small>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Note :</h6>
                                        <small>Les permissions personnalisées s'ajoutent aux permissions du rôle sélectionné. Laissez vide si vous voulez seulement les permissions du rôle.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    Créer l'Utilisateur
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Informations sur les rôles -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations sur les Rôles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-danger">
                                    <i class="ti ti-crown"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Administrateur</h6>
                                <small class="text-muted">Accès complet à toutes les fonctionnalités</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-warning">
                                    <i class="ti ti-user-check"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Manager</h6>
                                <small class="text-muted">Gestion des équipes et rapports</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-info">
                                    <i class="ti ti-user"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">Utilisateur</h6>
                                <small class="text-muted">Accès standard aux fonctionnalités</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
