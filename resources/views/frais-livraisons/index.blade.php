@include('layouts.header')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Frais de Livraison</h5>
                        <small class="text-muted">Gestion des frais de livraison</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('frais-livraisons.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Nouveau Frais
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="fraisDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="fraisDropdown">
                                <a class="dropdown-item" href="#" onclick="exportFrais()">
                                    <i class="ti ti-download me-2"></i>Exporter
                                </a>
                                <a class="dropdown-item" href="#" onclick="searchFrais()">
                                    <i class="ti ti-search me-2"></i>Rechercher
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($fraisLivraisons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Libellé</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Zone</th>
                                        <th>Statut</th>
                                        <th>Date Début</th>
                                        <th>Créé par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fraisLivraisons as $frais)
                                        <tr>
                                            <td>
                                                <div>
                                                    <span class="fw-semibold">{{ $frais->libelle }}</span>
                                                    @if($frais->description)
                                                        <br><small class="text-muted">{{ Str::limit($frais->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $frais->type_frais_label }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ number_format($frais->montant, 0, ',', ' ') }} FCFA</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $frais->zone_applicable_label }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $frais->statut_color }}">
                                                    {{ $frais->statut_label }}
                                                </span>
                                            </td>
                                            <td>{{ $frais->date_debut->format('d/m/Y') }}</td>
                                            <td>{{ $frais->createdBy->first_name ?? 'N/A' }} {{ $frais->createdBy->last_name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('frais-livraisons.show', $frais->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('frais-livraisons.edit', $frais->id) }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('frais-livraisons.destroy', $frais->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce frais ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($fraisLivraisons->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $fraisLivraisons->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-currency-franc ti-48px text-muted mb-2"></i>
                            <p class="text-muted">Aucun frais de livraison trouvé</p>
                            <a href="{{ route('frais-livraisons.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Créer le premier frais
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportFrais() {
    alert('Fonction d\'export à implémenter');
}

function searchFrais() {
    alert('Fonction de recherche à implémenter');
}
</script>

@include('layouts.menu')
@include('layouts.footer')
