@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Modifier le Poids</h5>
                            <p class="mb-4">Modifiez les informations du poids : {{ $poid->libelle }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('poids.show', $poid->id) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour aux détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de modification -->
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-edit me-2"></i>
                        Modifier les Informations
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>Erreur !</strong> Veuillez corriger les erreurs suivantes :
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('poids.update', $poid->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="libelle" class="form-label">
                                        Libellé du Poids <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('libelle') is-invalid @enderror"
                                           id="libelle"
                                           name="libelle"
                                           value="{{ old('libelle', $poid->libelle) }}"
                                           placeholder="Ex: 1 Kg, 2 Kg, 5 Kg, Léger, Lourd..."
                                           required>
                                    @error('libelle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Le libellé doit être unique et descriptif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('poids.show', $poid->id) }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Enregistrer les Modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations du poids -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        Informations du Poids
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-label-primary">#{{ $poid->id }}</span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Entreprise</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-label-info">
                                {{ $poid->entreprise->name ?? 'N/A' }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Créé par</label>
                        <p class="form-control-plaintext">
                            {{ $poid->user->first_name ?? 'N/A' }} {{ $poid->user->last_name ?? '' }}
                            @if($poid->user->email)
                                <br><small class="text-muted">{{ $poid->user->email }}</small>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Date de création</label>
                        <p class="form-control-plaintext">
                            {{ $poid->created_at ? $poid->created_at->format('d/m/Y à H:i') : 'N/A' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Dernière modification</label>
                        <p class="form-control-plaintext">
                            {{ $poid->updated_at ? $poid->updated_at->format('d/m/Y à H:i') : 'N/A' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Utilisation</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-label-info">Catégorie de poids</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-settings me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('poids.show', $poid->id) }}" class="btn btn-outline-primary">
                            <i class="ti ti-eye me-1"></i>
                            Voir les détails
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

    // Validation en temps réel
    const libelleInput = document.getElementById('libelle');

    libelleInput.addEventListener('input', function() {
        const value = this.value.trim();
        const isValid = value.length >= 1 && value.length <= 255;

        if (isValid) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (value.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

    // Auto-focus sur le champ libellé
    libelleInput.focus();
    libelleInput.select();
});
</script>

@include('layouts.footer')
