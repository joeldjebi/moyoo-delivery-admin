@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Détails du Tarif de Livraison</h5>
                            <p class="mb-4">Informations complètes sur le tarif</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('tarifs.edit', $tarif->id) }}" class="btn btn-warning">
                                    <i class="ti ti-pencil me-1"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails du tarif -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Tarif</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto mb-3">
                                    <i class="ti ti-map-pin"></i>
                                </div>
                                <h6 class="mb-1">Commune</h6>
                                <p class="text-muted mb-0">{{ $tarif->commune->libelle ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-secondary rounded-circle mx-auto mb-3">
                                    <i class="ti ti-truck"></i>
                                </div>
                                <h6 class="mb-1">Type d'Engin</h6>
                                <p class="text-muted mb-0">{{ $tarif->typeEngin->libelle ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-info rounded-circle mx-auto mb-3">
                                    <i class="ti ti-clock"></i>
                                </div>
                                <h6 class="mb-1">Mode de Livraison</h6>
                                <p class="text-muted mb-0">{{ $tarif->modeLivraison->libelle ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-warning rounded-circle mx-auto mb-3">
                                    <i class="ti ti-weight"></i>
                                </div>
                                <h6 class="mb-1">Poids</h6>
                                <p class="text-muted mb-0">{{ $tarif->poids->libelle ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-success rounded-circle mx-auto mb-3">
                                    <i class="ti ti-calendar"></i>
                                </div>
                                <h6 class="mb-1">Période Temporelle</h6>
                                <p class="text-muted mb-0">{{ $tarif->temp->libelle ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="avatar avatar-lg bg-label-danger rounded-circle mx-auto mb-3">
                                    <i class="ti ti-currency-franc"></i>
                                </div>
                                <h6 class="mb-1">Montant</h6>
                                <p class="text-muted mb-0 fw-bold text-success fs-4">
                                    {{ number_format($tarif->amount, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations système -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations Système</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Créé le :</strong></td>
                                    <td>{{ $tarif->created_at->format('d/m/Y à H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dernière modification :</strong></td>
                                    <td>{{ $tarif->updated_at->format('d/m/Y à H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Créé par :</strong></td>
                                    <td>{{ $tarif->createdBy->first_name ?? 'N/A' }} {{ $tarif->createdBy->last_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email créateur :</strong></td>
                                    <td>{{ $tarif->createdBy->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Statut :</strong></td>
                                    <td>
                                        @if($tarif->deleted_at)
                                            <span class="badge bg-label-danger">Supprimé</span>
                                        @else
                                            <span class="badge bg-label-success">Actif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('tarifs.edit', $tarif->id) }}" class="btn btn-warning">
                            <i class="ti ti-pencil me-1"></i>
                            Modifier le Tarif
                        </a>
                        <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-list me-1"></i>
                            Voir tous les Tarifs
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteTarif({{ $tarif->id }}, '{{ $tarif->commune->libelle ?? 'N/A' }}')">
                            <i class="ti ti-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <i class="ti ti-alert-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-2 mb-1">Êtes-vous sûr ?</h4>
                        <p class="text-muted">Cette action ne peut pas être annulée. Le tarif pour <strong id="tarifCommune"></strong> sera définitivement supprimé.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
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
function deleteTarif(tarifId, communeName) {
    document.getElementById('tarifCommune').textContent = communeName;
    document.getElementById('deleteForm').action = `/tarifs/${tarifId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

@include('layouts.footer')
