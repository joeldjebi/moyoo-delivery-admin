@include('layouts.header')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Historique des Frais de Livraison</h5>
                        <small class="text-muted">Traçabilité des opérations sur les frais</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('historique-frais-livraisons.export', request()->query()) }}" class="btn btn-outline-primary">
                            <i class="ti ti-download me-1"></i>Exporter
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="historiqueDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="historiqueDropdown">
                                <a class="dropdown-item" href="#" onclick="showStatistics()">
                                    <i class="ti ti-chart-bar me-2"></i>Statistiques
                                </a>
                                <a class="dropdown-item" href="#" onclick="clearFilters()">
                                    <i class="ti ti-filter-off me-2"></i>Effacer les filtres
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('historique-frais-livraisons.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="type_operation" class="form-label">Type d'Opération</label>
                                    <select class="form-select" id="type_operation" name="type_operation">
                                        <option value="">Tous les types</option>
                                        <option value="creation" {{ request('type_operation') == 'creation' ? 'selected' : '' }}>Création</option>
                                        <option value="modification" {{ request('type_operation') == 'modification' ? 'selected' : '' }}>Modification</option>
                                        <option value="suppression" {{ request('type_operation') == 'suppression' ? 'selected' : '' }}>Suppression</option>
                                        <option value="application" {{ request('type_operation') == 'application' ? 'selected' : '' }}>Application</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="frais_livraison_id" class="form-label">Frais de Livraison</label>
                                    <select class="form-select" id="frais_livraison_id" name="frais_livraison_id">
                                        <option value="">Tous les frais</option>
                                        @foreach($fraisLivraisons as $frais)
                                            <option value="{{ $frais->id }}" {{ request('frais_livraison_id') == $frais->id ? 'selected' : '' }}>
                                                {{ $frais->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date_debut" class="form-label">Date Début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_fin" class="form-label">Date Fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="user_id" class="form-label">Utilisateur</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">Tous les utilisateurs</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('historique-frais-livraisons.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i>Réinitialiser
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($historique->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Frais de Livraison</th>
                                        <th>Type d'Opération</th>
                                        <th>Description</th>
                                        <th>Montant Avant</th>
                                        <th>Montant Après</th>
                                        <th>Utilisateur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historique as $item)
                                        <tr>
                                            <td>{{ $item->date_operation->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="fw-semibold">{{ $item->fraisLivraison->libelle ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->type_operation_color }}">
                                                    {{ $item->type_operation_label }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($item->description_operation, 50) }}</td>
                                            <td>
                                                @if($item->montant_avant)
                                                    <span class="text-muted">{{ number_format($item->montant_avant, 0, ',', ' ') }} FCFA</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->montant_apres)
                                                    <span class="text-success">{{ number_format($item->montant_apres, 0, ',', ' ') }} FCFA</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->user->first_name ?? 'N/A' }} {{ $item->user->last_name ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('historique-frais-livraisons.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($historique->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $historique->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-history ti-48px text-muted mb-2"></i>
                            <p class="text-muted">Aucun historique trouvé</p>
                            <p class="text-muted small">Ajustez vos filtres pour voir plus de résultats</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showStatistics() {
    // Implémentation des statistiques
    alert('Fonction de statistiques à implémenter');
}

function clearFilters() {
    window.location.href = '{{ route("historique-frais-livraisons.index") }}';
}
</script>

@include('layouts.menu')
@include('layouts.footer')
