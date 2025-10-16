@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Marchands</h5>
                            <p class="mb-4">Gérez facilement vos marchands et leurs informations.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('marchands.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter un Marchand
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

    <!-- Liste des marchands -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Marchands</h5>
                    <div class="d-flex gap-2">
                        <!-- Barre de recherche -->
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Rechercher un marchand...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($marchands->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom complet</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Commune</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($marchands as $marchand)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            {{ strtoupper(substr($marchand->first_name, 0, 1)) }}{{ strtoupper(substr($marchand->last_name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $marchand->full_name }}</h6>
                                                        @if($marchand->adresse)
                                                            <small class="text-muted">{{ Str::limit($marchand->adresse, 30) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $marchand->mobile }}</span>
                                            </td>
                                            <td>
                                                @if($marchand->email)
                                                    <span class="text-primary">{{ $marchand->email }}</span>
                                                @else
                                                    <span class="text-muted">Non renseigné</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-label-secondary">{{ $marchand->commune->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($marchand->status === 'active')
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $marchand->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('marchands.show', $marchand) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('marchands.edit', $marchand) }}">
                                                            <i class="ti ti-pencil me-1"></i> Modifier
                                                        </a>
                                                        <form action="{{ route('marchands.toggle-status', $marchand) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item">
                                                                @if($marchand->status === 'active')
                                                                    <i class="ti ti-user-off me-1"></i> Désactiver
                                                                @else
                                                                    <i class="ti ti-user-check me-1"></i> Activer
                                                                @endif
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('marchands.destroy', $marchand) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce marchand ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ti ti-trash me-1"></i> Supprimer
                                                            </button>
                                                        </form>
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
                            {{ $marchands->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-users-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucun marchand trouvé</h5>
                            <p class="text-muted">Commencez par ajouter votre premier marchand.</p>
                            <a href="{{ route('marchands.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter un Marchand
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
// Fonctionnalité de recherche
document.getElementById('searchBtn').addEventListener('click', function() {
    const query = document.getElementById('searchInput').value;
    if(query.trim()) {
        window.location.href = `{{ route('marchands.index') }}?search=${encodeURIComponent(query)}`;
    }
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});

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
