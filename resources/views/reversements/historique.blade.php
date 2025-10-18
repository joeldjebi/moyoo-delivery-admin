@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Historique des Balances</h5>
                        <p class="mb-4">Consultez l'historique des mouvements de balance des marchands.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <a href="{{ route('balances.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour aux Balances
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('historique.balances') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Marchand</label>
                            <select name="marchand_id" class="form-select">
                                <option value="">Tous les marchands</option>
                                @foreach($marchands as $marchand)
                                    <option value="{{ $marchand->id }}" {{ $selected_marchand_id == $marchand->id ? 'selected' : '' }}>
                                        {{ $marchand->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="encaissement" {{ request('type') == 'encaissement' ? 'selected' : '' }}>Encaissement</option>
                                <option value="reversement" {{ request('type') == 'reversement' ? 'selected' : '' }}>Reversement</option>
                                <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date Début</label>
                            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date Fin</label>
                            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search"></i>
                                </button>
                                <a href="{{ route('historique.balances') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Historique -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Historique des Mouvements</h5>
            </div>
            <div class="card-body">
                @if($historique->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Marchand</th>
                                    <th>Boutique</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Balance Avant</th>
                                    <th>Balance Après</th>
                                    <th>Description</th>
                                    <th>Référence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historique as $mouvement)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ $mouvement->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        {{ strtoupper(substr($mouvement->balanceMarchand->marchand->first_name, 0, 1)) }}{{ strtoupper(substr($mouvement->balanceMarchand->marchand->last_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $mouvement->balanceMarchand->marchand->full_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $mouvement->balanceMarchand->boutique->libelle }}</td>
                                        <td>
                                            <span class="badge bg-label-{{ $mouvement->type_color }}">
                                                {{ $mouvement->type_label }}
                                            </span>
                                        </td>
                                        <td class="fw-bold {{ $mouvement->type_operation === 'encaissement' ? 'text-success' : 'text-info' }}">
                                            {{ $mouvement->type_operation === 'encaissement' ? '+' : '-' }}{{ number_format($mouvement->montant) }} FCFA
                                        </td>
                                        <td>{{ number_format($mouvement->balance_avant) }} FCFA</td>
                                        <td class="fw-bold">{{ number_format($mouvement->balance_apres) }} FCFA</td>
                                        <td>
                                            <small class="text-muted">{{ $mouvement->description }}</small>
                                        </td>
                                        <td>
                                            @if($mouvement->reference)
                                                <span class="badge bg-label-info">{{ $mouvement->reference }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Informations de pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            <small>
                                Affichage de {{ $historique->firstItem() ?? 0 }} à {{ $historique->lastItem() ?? 0 }}
                                sur {{ $historique->total() }} entrées
                            </small>
                        </div>
                        <div>
                            <small class="text-muted me-3">Éléments par page:</small>
                            <select class="form-select form-select-sm d-inline-block w-auto" onchange="changePerPage(this.value)">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $historique->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-history" style="font-size: 4rem; color: #ccc;"></i>
                        </div>
                        <h5 class="text-muted">Aucun historique trouvé</h5>
                        <p class="text-muted">L'historique apparaîtra après les premières livraisons et reversements.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
