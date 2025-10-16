@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Poids</h5>
                            <p class="mb-4">Informations détaillées du poids : {{ $poid->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('poids.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du poids -->
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-weight me-2"></i>
                        Informations du Poids
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID du Poids</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-label-primary">#{{ $poid->id }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Libellé</label>
                                <p class="form-control-plaintext">
                                    <strong class="text-primary">{{ $poid->libelle }}</strong>
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
                                        {{ $poid->entreprise->name ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Créé par</label>
                                <p class="form-control-plaintext">
                                    {{ $poid->user->first_name ?? 'N/A' }} {{ $poid->user->last_name ?? '' }}
                                    @if($poid->user->email)
                                        <br><small class="text-muted">{{ $poid->user->email }}</small>
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
                                    {{ $poid->created_at ? $poid->created_at->format('d/m/Y à H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dernière modification</label>
                                <p class="form-control-plaintext">
                                    {{ $poid->updated_at ? $poid->updated_at->format('d/m/Y à H:i') : 'N/A' }}
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
                        <a href="{{ route('poids.edit', $poid->id) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('poids.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-list me-1"></i>
                            Voir tous les poids
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deletePoid({{ $poid->id }}, '{{ $poid->libelle }}')">
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
                        À propos des Poids
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-lightbulb me-2"></i>
                        <strong>Information :</strong> Les poids définissent les différentes catégories de poids pour vos colis.
                        Ils sont utilisés dans le système de tarification et de planification des livraisons.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Utilisation des poids</h6>
                            <ul class="list-unstyled text-muted">
                                <li><i class="ti ti-check me-1 text-success"></i> Définition des catégories de poids</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Calcul des tarifs de livraison</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Planification des tournées</li>
                                <li><i class="ti ti-check me-1 text-success"></i> Gestion des capacités d'engins</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Exemples de poids</h6>
                            <ul class="list-unstyled text-muted">
                                <li><i class="ti ti-weight me-1"></i> 1 Kg - Léger</li>
                                <li><i class="ti ti-weight me-1"></i> 2 Kg - Moyen</li>
                                <li><i class="ti ti-weight me-1"></i> 5 Kg - Lourd</li>
                                <li><i class="ti ti-weight me-1"></i> 10 Kg - Très lourd</li>
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
                <p>Êtes-vous sûr de vouloir supprimer le poids <strong id="poidName"></strong> ?</p>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de suppression
    window.deletePoid = function(id, libelle) {
        document.getElementById('poidName').textContent = libelle;
        document.getElementById('deleteForm').action = '/poids/' + id;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>

@include('layouts.footer')
