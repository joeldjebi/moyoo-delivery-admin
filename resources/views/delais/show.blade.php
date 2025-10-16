@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Délai</h5>
                            <p class="mb-4">Informations détaillées du délai : {{ $delai->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('delais.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du délai -->
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-clock me-2"></i>
                        Informations du Délai
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID du Délai</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-label-primary">#{{ $delai->id }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Libellé</label>
                                <p class="form-control-plaintext">
                                    <strong class="text-primary">{{ $delai->libelle }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Entreprise</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-label-info">
                                        {{ $delai->entreprise->name ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Créé par</label>
                                <p class="form-control-plaintext">
                                    {{ $delai->user->first_name ?? 'N/A' }} {{ $delai->user->last_name ?? '' }}
                                    @if($delai->user->email)
                                        <br><small class="text-muted">{{ $delai->user->email }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de création</label>
                                <p class="form-control-plaintext">
                                    {{ $delai->created_at ? $delai->created_at->format('d/m/Y à H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dernière modification</label>
                                <p class="form-control-plaintext">
                                    {{ $delai->updated_at ? $delai->updated_at->format('d/m/Y à H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-settings me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('delais.edit', $delai->id) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('delais.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-list me-1"></i>
                            Voir tous les délais
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteDelai({{ $delai->id }}, '{{ $delai->libelle }}')">
                            <i class="ti ti-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations sur l'utilisation -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        À propos des Délais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-lightbulb me-2"></i>
                        <strong>Information :</strong> Les délais définissent les temps de livraison disponibles pour vos colis.
                        Ils sont utilisés dans le système de tarification et de planification des livraisons.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Utilisation des délais</h6>
                            <ul class="list-unstyled text-muted">
                                <li><i class="ti ti-check me-1 text-success"></i> Définition des temps de livraison</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Calcul des tarifs de livraison</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Planification des tournées</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Gestion des attentes clients</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Exemples de délais</h6>
                            <ul class="list-unstyled text-muted">
                                <li><i class="ti ti-clock me-1"></i> Express (30min - 1H)</li>
                                <li><i class="ti ti-clock me-1"></i> Rapide (2H - 4H)</li>
                                <li><i class="ti ti-clock me-1"></i> Standard (24H)</li>
                                <li><i class="ti ti-clock me-1"></i> Économique (48H+)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le délai <strong id="delaiName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-1"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de suppression
    window.deleteDelai = function(id, libelle) {
        document.getElementById('delaiName').textContent = libelle;
        document.getElementById('deleteForm').action = '/delais/' + id;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>

@include('layouts.footer')
