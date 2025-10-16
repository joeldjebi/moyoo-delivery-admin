@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Créer un Nouveau Poids</h5>
                            <p class="mb-4">Ajoutez un nouveau poids à votre entreprise</p>
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

    <!-- Formulaire de création -->
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du Poids</h5>
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

                    <form action="{{ route('poids.store') }}" method="POST">
                        @csrf

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
                                           value="{{ old('libelle') }}"
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
                                    <a href="{{ route('poids.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Créer le Poids
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold">À propos des poids</h6>
                        <p class="text-muted small">
                            Les poids définissent les différentes catégories de poids pour vos colis.
                            Ils sont utilisés pour calculer les tarifs et organiser les tournées.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">Exemples de poids</h6>
                        <ul class="list-unstyled text-muted small">
                            <li><i class="ti ti-weight me-1"></i> 1 Kg - Léger</li>
                            <li><i class="ti ti-weight me-1"></i> 2 Kg - Moyen</li>
                            <li><i class="ti ti-weight me-1"></i> 5 Kg - Lourd</li>
                            <li><i class="ti ti-weight me-1"></i> 10 Kg - Très lourd</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <i class="ti ti-lightbulb me-2"></i>
                        <strong>Conseil :</strong> Utilisez des libellés courts et clairs pour faciliter la sélection.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Auto-focus sur le premier champ
    libelleInput.focus();
});
</script>

@include('layouts.footer')
