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
                    <div class="row">
                        @foreach($subscriptions as $subscription)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 border {{ $subscription->status == 'active' ? 'border-success' : 'border-secondary' }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $subscription->plan_name }}</h6>
                                        <span class="badge {{ $subscription->status == 'active' ? 'bg-success' : ($subscription->status == 'cancelled' ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ $subscription->status_label }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h3 class="text-primary mb-0">{{ $subscription->formatted_price }}</h3>
                                            <small class="text-muted">par mois</small>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Période d'abonnement :</small>
                                            <div class="d-flex justify-content-between">
                                                <span>{{ $subscription->start_date->format('d/m/Y') }}</span>
                                                <span>{{ $subscription->end_date->format('d/m/Y') }}</span>
                                            </div>
                                        </div>

                                        @if($subscription->features && count($subscription->features) > 0)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-2">Fonctionnalités incluses :</small>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($subscription->features as $feature)
                                                        <li class="mb-1">
                                                            <i class="ti ti-check text-success me-2"></i>
                                                            <small>{{ $feature }}</small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if($subscription->notes)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Notes :</small>
                                                <small class="text-info">{{ $subscription->notes }}</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">ID: #{{ $subscription->id }}</small>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-download me-1"></i>
                                                Facture
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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

<!-- Statistiques des abonnements -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="ti ti-calendar ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $subscriptions->count() }}</h4>
                <p class="text-muted mb-0">Abonnements Totaux</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-success">
                        <i class="ti ti-check-circle ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $subscriptions->where('status', 'active')->count() }}</h4>
                <p class="text-muted mb-0">Abonnements Actifs</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded bg-label-warning">
                        <i class="ti ti-currency-euro ti-lg"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ number_format($subscriptions->sum('price'), 2) }}€</h4>
                <p class="text-muted mb-0">Total Dépensé</p>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
