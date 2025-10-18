@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Historique d'Abonnement</h5>
                        <p class="mb-4">Consultez l'historique de vos abonnements et factures</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('auth.pricing') }}" class="btn btn-outline-primary">
                                <i class="ti ti-crown me-1"></i>
                                Voir les Forfaits
                            </a>
                            <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historique des abonnements -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Mes Abonnements</h5>
            </div>
            <div class="card-body">
                @if($subscriptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Plan</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Fonctionnalités</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">#{{ $subscription->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="ti ti-crown"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $subscription->plan_name }}</h6>
                                                    @if($subscription->notes)
                                                        <small class="text-muted">{{ $subscription->notes }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <h6 class="mb-0 text-primary">{{ $subscription->formatted_price }}</h6>
                                                <small class="text-muted">par mois</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $subscription->status == 'active' ? 'bg-success' : ($subscription->status == 'cancelled' ? 'bg-danger' : 'bg-secondary') }}">
                                                {{ $subscription->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fw-semibold">{{ $subscription->start_date->format('d/m/Y') }}</span>
                                                <br>
                                                <small class="text-muted">{{ $subscription->start_date->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fw-semibold">{{ $subscription->end_date->format('d/m/Y') }}</span>
                                                <br>
                                                <small class="text-muted">{{ $subscription->end_date->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($subscription->features && count($subscription->features) > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($subscription->features as $feature)
                                                        <span class="badge bg-label-info">{{ $feature }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">
                                                        <i class="ti ti-download me-1"></i>
                                                        Télécharger Facture
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="ti ti-eye me-1"></i>
                                                        Voir Détails
                                                    </a>
                                                    @if($subscription->status == 'active')
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="ti ti-x me-1"></i>
                                                            Annuler
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-receipt ti-lg"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">Aucun abonnement trouvé</h5>
                        <p class="text-muted mb-4">Vous n'avez pas encore d'abonnement actif.</p>
                        <a href="{{ route('auth.pricing') }}" class="btn btn-primary">
                            <i class="ti ti-crown me-1"></i>
                            Choisir un Forfait
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@include('layouts.footer')
