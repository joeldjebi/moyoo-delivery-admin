@include('layouts.header')

@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Informations de l'Entreprise</h5>
                    <div>
                        <a href="{{ route('entreprise.edit') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit-alt"></i> Modifier
                        </a>
                        <form action="{{ route('entreprise.toggle-status') }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm {{ $entreprise->statut == 1 ? 'btn-warning' : 'btn-success' }}">
                                <i class="bx {{ $entreprise->statut == 1 ? 'bx-pause' : 'bx-play' }}"></i>
                                {{ $entreprise->statut == 1 ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 30%;">Nom de l'entreprise :</td>
                                        <td>{{ $entreprise->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email :</td>
                                        <td>{{ $entreprise->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Téléphone :</td>
                                        <td>{{ $entreprise->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Adresse :</td>
                                        <td>{{ $entreprise->adresse }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Commune de départ :</td>
                                        <td>
                                            <span class="badge bg-info">{{ $entreprise->commune->libelle }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Statut :</td>
                                        <td>
                                            <span class="badge {{ $entreprise->statut_class }}">
                                                {{ $entreprise->statut_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Créée le :</td>
                                        <td>{{ $entreprise->created_at->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Dernière mise à jour :</td>
                                        <td>{{ $entreprise->updated_at->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            @if($entreprise->logo)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $entreprise->logo) }}"
                                         alt="Logo de {{ $entreprise->name }}"
                                         class="img-fluid rounded shadow"
                                         style="max-width: 200px; max-height: 200px;">
                                </div>
                            @else
                                <div class="mb-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center shadow"
                                         style="width: 200px; height: 200px; margin: 0 auto;">
                                        <i class="bx bx-building text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                    <small class="text-muted">Aucun logo</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Statistiques</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="d-flex flex-column">
                                        <div class="avatar flex-shrink-0 mx-auto mb-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-package"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Total Colis</span>
                                        <h3 class="card-title mb-0">{{ number_format($stats['total_colis']) }}</h3>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="d-flex flex-column">
                                        <div class="avatar flex-shrink-0 mx-auto mb-2">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="bx bx-check-circle"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Livrés</span>
                                        <h3 class="card-title text-success mb-0">{{ number_format($stats['colis_livres']) }}</h3>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="d-flex flex-column">
                                        <div class="avatar flex-shrink-0 mx-auto mb-2">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="bx bx-time-five"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">En cours</span>
                                        <h3 class="card-title text-warning mb-0">{{ number_format($stats['colis_en_cours']) }}</h3>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="d-flex flex-column">
                                        <div class="avatar flex-shrink-0 mx-auto mb-2">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="bx bx-hourglass"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">En attente</span>
                                        <h3 class="card-title text-info mb-0">{{ number_format($stats['colis_en_attente']) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Actions rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('colis.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Créer un nouveau colis
                                </a>
                                <a href="{{ route('colis.index') }}" class="btn btn-outline-primary">
                                    <i class="bx bx-list-ul"></i> Voir tous les colis
                                </a>
                                <a href="{{ route('tarifs.index') }}" class="btn btn-outline-info">
                                    <i class="bx bx-money"></i> Consulter les tarifs
                                </a>
                                <form action="{{ route('entreprise.regenerate-tarifs') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning"
                                            onclick="return confirm('Êtes-vous sûr de vouloir régénérer tous les tarifs de livraison ? Cette action remplacera tous les tarifs existants.')">
                                        <i class="bx bx-refresh"></i> Régénérer les tarifs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
