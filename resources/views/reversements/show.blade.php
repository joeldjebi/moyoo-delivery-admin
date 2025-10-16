@include('layouts.header')
@include('layouts.menu')

    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <h5 class="card-title text-primary">Détails du Reversement</h5>
                                <p class="mb-4">Consultez les informations détaillées du reversement.</p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <a href="{{ route('reversements.index') }}" class="btn btn-outline-primary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                                @if($reversement->statut === 'en_attente')
                                    @can('reversements.update')
                                        <form action="{{ route('reversements.validate', $reversement) }}" method="POST" style="display: inline;" class="ms-2">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                    onclick="return confirm('Valider ce reversement ?')">
                                                <i class="ti ti-check me-1"></i>
                                                Valider
                                            </button>
                                        </form>
                                        <form action="{{ route('reversements.cancel', $reversement) }}" method="POST" style="display: inline;" class="ms-1">
                                            @csrf
                                            <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('Annuler ce reversement ?')">
                                                <i class="ti ti-x me-1"></i>
                                                Annuler
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails du Reversement -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Reversement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Référence du Reversement</label>
                                <p class="form-control-plaintext">{{ $reversement->reference_reversement }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Statut</label>
                                <p class="form-control-plaintext">
                                    @switch($reversement->statut)
                                        @case('en_attente')
                                            <span class="badge bg-warning">En Attente</span>
                                            @break
                                        @case('valide')
                                            <span class="badge bg-success">Validé</span>
                                            @break
                                        @case('annule')
                                            <span class="badge bg-danger">Annulé</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $reversement->statut }}</span>
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Marchand</label>
                                <p class="form-control-plaintext">
                                    {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Boutique</label>
                                <p class="form-control-plaintext">{{ $reversement->boutique->libelle }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Montant du Reversement</label>
                                <p class="form-control-plaintext h5 text-primary">
                                    {{ number_format($reversement->montant_reverse, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode de Reversement</label>
                                <p class="form-control-plaintext">
                                    @switch($reversement->mode_reversement)
                                        @case('especes')
                                            <span class="badge bg-info">Espèces</span>
                                            @break
                                        @case('virement')
                                            <span class="badge bg-primary">Virement</span>
                                            @break
                                        @case('mobile_money')
                                            <span class="badge bg-success">Mobile Money</span>
                                            @break
                                        @case('cheque')
                                            <span class="badge bg-warning">Chèque</span>
                                            @break
                                        @default
                                            {{ ucfirst($reversement->mode_reversement) }}
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($reversement->notes)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <p class="form-control-plaintext">{{ $reversement->notes }}</p>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de Création</label>
                                <p class="form-control-plaintext">{{ $reversement->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Créé par</label>
                                <p class="form-control-plaintext">
                                    {{ $reversement->createdBy->first_name ?? 'N/A' }} {{ $reversement->createdBy->last_name ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($reversement->date_reversement)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de Validation</label>
                                <p class="form-control-plaintext">{{ $reversement->date_reversement->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Validé par</label>
                                <p class="form-control-plaintext">
                                    {{ $reversement->validatedBy->first_name ?? 'N/A' }} {{ $reversement->validatedBy->last_name ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('reversements.index') }}" class="btn btn-outline-primary">
                            <i class="ti ti-list me-1"></i>
                            Retour à la liste
                        </a>

                        @if($reversement->statut === 'en_attente')
                            @if(auth()->user()->hasPermission('reversements.update'))
                                <button type="button" class="btn btn-success w-100"
                                        data-bs-toggle="modal" data-bs-target="#validateModal">
                                    <i class="ti ti-check me-1"></i>
                                    Valider le Reversement
                                </button>

                                <button type="button" class="btn btn-danger w-100"
                                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler le Reversement
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations Supplémentaires -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations Supplémentaires</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dernière Modification</label>
                        <p class="form-control-plaintext small text-muted">{{ $reversement->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($reversement->justificatif_path)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Justificatif</label>
                        <p class="form-control-plaintext">
                            <a href="{{ asset('storage/' . $reversement->justificatif_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-file-text me-1"></i>
                                Voir le justificatif
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<!-- Modales de confirmation -->
@if($reversement->statut === 'en_attente')
    <!-- Modal de validation -->
    <div class="modal fade" id="validateModal" tabindex="-1" aria-labelledby="validateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="validateModalLabel">
                        <i class="ti ti-check-circle text-success me-2"></i>
                        Valider le Reversement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Confirmation requise</strong>
                    </div>
                    <p>Êtes-vous sûr de vouloir <strong class="text-success">valider</strong> ce reversement ?</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Détails du reversement :</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Référence :</strong> {{ $reversement->reference_reversement }}</li>
                                        <li><strong>Marchand :</strong> {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}</li>
                                        <li><strong>Boutique :</strong> {{ $reversement->boutique->libelle }}</li>
                                        <li><strong>Montant :</strong> <span class="text-success fw-bold">{{ number_format($reversement->montant_reverse) }} FCFA</span></li>
                                        <li><strong>Mode :</strong> {{ $reversement->mode_label }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Balance actuelle :</h6>
                                    @if($reversement->balanceMarchand())
                                        <div class="text-center">
                                            <h4 class="text-primary mb-0">{{ number_format($reversement->balanceMarchand()->balance_actuelle) }} FCFA</h4>
                                            <small class="text-muted">Balance avant validation</small>
                                        </div>
                                        <hr>
                                        <div class="text-center">
                                            <h4 class="text-success mb-0">{{ number_format($reversement->balanceMarchand()->balance_actuelle - $reversement->montant_reverse) }} FCFA</h4>
                                            <small class="text-muted">Balance après validation</small>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <i class="ti ti-alert-triangle text-warning" style="font-size: 2rem;"></i>
                                            <h5 class="text-warning mt-2">Balance non trouvée</h5>
                                            <p class="text-muted small">Impossible de calculer la balance</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action est irréversible. Le montant sera débité de la balance du marchand.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i> Annuler
                    </button>
                    <form action="{{ route('reversements.validate', $reversement) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i> Confirmer la validation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'annulation -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">
                        <i class="ti ti-x-circle text-danger me-2"></i>
                        Annuler le Reversement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Attention</strong>
                    </div>
                    <p>Êtes-vous sûr de vouloir <strong class="text-danger">annuler</strong> ce reversement ?</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Détails du reversement :</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Référence :</strong> {{ $reversement->reference_reversement }}</li>
                                        <li><strong>Marchand :</strong> {{ $reversement->marchand->first_name }} {{ $reversement->marchand->last_name }}</li>
                                        <li><strong>Boutique :</strong> {{ $reversement->boutique->libelle }}</li>
                                        <li><strong>Montant :</strong> <span class="text-danger fw-bold">{{ number_format($reversement->montant_reverse) }} FCFA</span></li>
                                        <li><strong>Mode :</strong> {{ $reversement->mode_label }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Impact de l'annulation :</h6>
                                    <div class="text-center">
                                        <i class="ti ti-shield-check text-info" style="font-size: 3rem;"></i>
                                        <h5 class="mt-2 text-info">Balance préservée</h5>
                                        <p class="text-muted small">La balance du marchand restera inchangée</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note :</strong> Le reversement sera marqué comme annulé et ne pourra plus être validé.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-arrow-left me-1"></i> Retour
                    </button>
                    <form action="{{ route('reversements.cancel', $reversement) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-x me-1"></i> Confirmer l'annulation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@include('layouts.footer')
