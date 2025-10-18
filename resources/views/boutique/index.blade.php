@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Gestion des Boutiques</h5>
                            <p class="mb-4">Gérez facilement vos boutiques et leurs informations.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('boutiques.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter une Boutique
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

    <!-- Liste des boutiques -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des Boutiques</h5>
                    <div class="d-flex gap-2">
                        <!-- Barre de recherche -->
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une boutique...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($boutiques->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Libellé</th>
                                        <th>Téléphone</th>
                                        <th>Marchand</th>
                                        <th>Adresse</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($boutiques as $boutique)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="ti ti-building-store"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $boutique->libelle }}</h6>
                                                        @if($boutique->adresse_gps)
                                                            <small class="text-muted">
                                                                <a href="{{ $boutique->adresse_gps }}" target="_blank" class="text-primary">
                                                                    <i class="ti ti-external-link me-1"></i>
                                                                    {{ Str::limit($boutique->adresse_gps, 15) }}
                                                                </a>
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $boutique->mobile }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $boutique->marchand->full_name }}</span>
                                            </td>
                                            <td>
                                                @if($boutique->adresse)
                                                    <span class="text-primary">{{ Str::limit($boutique->adresse, 30) }}</span>
                                                @else
                                                    <span class="text-muted">Non renseignée</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($boutique->status === 'active')
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $boutique->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('boutiques.show', $boutique) }}">
                                                            <i class="ti ti-eye me-1"></i> Voir
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('boutiques.edit', $boutique) }}">
                                                            <i class="ti ti-pencil me-1"></i> Modifier
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#" onclick="showColisHistory({{ $boutique->id }}, '{{ $boutique->libelle }}')">
                                                            <i class="ti ti-package me-1"></i> Historique des colis
                                                        </a>
                                                        <a class="dropdown-item" href="#" onclick="showLivraisonsHistory({{ $boutique->id }}, '{{ $boutique->libelle }}')">
                                                            <i class="ti ti-truck me-1"></i> Historique des livraisons
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('boutiques.toggle-status', $boutique) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item">
                                                                @if($boutique->status === 'active')
                                                                    <i class="ti ti-building-off me-1"></i> Désactiver
                                                                @else
                                                                    <i class="ti ti-building me-1"></i> Activer
                                                                @endif
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('boutiques.destroy', $boutique) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette boutique ?')">
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
                            {{ $boutiques->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ti ti-building-store-off" style="font-size: 4rem; color: #ccc;"></i>
                            </div>
                            <h5 class="text-muted">Aucune boutique trouvée</h5>
                            <p class="text-muted">Commencez par ajouter votre première boutique.</p>
                            <a href="{{ route('boutiques.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Ajouter une Boutique
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
        window.location.href = `{{ route('boutiques.index') }}?search=${encodeURIComponent(query)}`;
    }
});

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});

// Fonction pour changer le statut d'une boutique
function toggleBoutiqueStatus(boutiqueId, currentStatus) {
    const action = currentStatus === 'active' ? 'désactiver' : 'activer';
    if (confirm(`Êtes-vous sûr de vouloir ${action} cette boutique ?`)) {
        // Créer un formulaire temporaire pour l'action
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/boutiques/${boutiqueId}/toggle-status`;

        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Ajouter la méthode PATCH
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        form.appendChild(methodField);

        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour supprimer une boutique
function deleteBoutique(boutiqueId, boutiqueName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la boutique "${boutiqueName}" ?`)) {
        // Créer un formulaire temporaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/boutiques/${boutiqueId}`;

        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);

        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour afficher l'historique des colis
function showColisHistory(boutiqueId, boutiqueName) {
    window.location.href = `/boutiques/${boutiqueId}/colis`;
}

// Fonction pour afficher l'historique des livraisons
function showLivraisonsHistory(boutiqueId, boutiqueName) {
    window.location.href = `/boutiques/${boutiqueId}/livraisons`;
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
