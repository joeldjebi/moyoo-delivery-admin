@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            @if(auth()->user()->isSuperAdmin())
                                Gestion Globale des Utilisateurs
                            @else
                                Utilisateurs de {{ auth()->user()->entreprise->name ?? 'Mon Entreprise' }}
                            @endif
                        </h5>
                        <p class="mb-4">
                            @if(auth()->user()->isSuperAdmin())
                                Gérez tous les utilisateurs de la plateforme
                            @else
                                Gérez les utilisateurs de votre entreprise
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            @if(auth()->user()->hasPermission('users.create'))
                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>
                                    Nouvel Utilisateur
                                </a>
                            @endif
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

<!-- Messages de succès -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Liste des utilisateurs -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Liste des Utilisateurs</h5>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Rôle</th>
                                    <th>Type</th>
                                    @if(auth()->user()->isSuperAdmin())
                                        <th>Entreprise</th>
                                    @endif
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->full_name }}</h6>
                                                    @if($user->trashed())
                                                        <small class="text-danger">Supprimé</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->email }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->mobile }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst(str_replace('_', ' ', $user->user_type ?? 'entreprise_user')) }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->isSuperAdmin())
                                            <td>{{ $user->entreprise->name ?? 'N/A' }}</td>
                                        @endif
                                        <td>
                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('users.show', $user) }}">
                                                        <i class="ti ti-eye me-1"></i> Voir
                                                    </a>
                                                    @if(auth()->user()->hasPermission('users.update') && auth()->user()->canEditUser($user))
                                                        <a class="dropdown-item" href="{{ route('users.edit', $user) }}">
                                                            <i class="ti ti-edit me-1"></i> Modifier
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('users.delete') && auth()->user()->canDeleteUser($user))
                                                        <div class="dropdown-divider"></div>
                                                        @if(!$user->trashed())
                                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger"
                                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                                    <i class="ti ti-trash me-1"></i> Supprimer
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('users.restore', $user->id) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success"
                                                                        onclick="return confirm('Êtes-vous sûr de vouloir restaurer cet utilisateur ?')">
                                                                    <i class="ti ti-refresh me-1"></i> Restaurer
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
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
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-users ti-lg"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">Aucun utilisateur trouvé</h5>
                        <p class="text-muted mb-4">Commencez par créer votre premier utilisateur.</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Créer un Utilisateur
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ti ti-users ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $users->total() }}</h4>
                <p class="text-muted mb-0">Total Utilisateurs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-success">
                        <i class="ti ti-user-check ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $users->where('status', 'active')->count() }}</h4>
                <p class="text-muted mb-0">Utilisateurs Actifs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-danger">
                        <i class="ti ti-crown ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $users->where('role', 'admin')->count() }}</h4>
                <p class="text-muted mb-0">Administrateurs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-warning">
                        <i class="ti ti-user-minus ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $users->whereNotNull('deleted_at')->count() }}</h4>
                <p class="text-muted mb-0">Utilisateurs Supprimés</p>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
