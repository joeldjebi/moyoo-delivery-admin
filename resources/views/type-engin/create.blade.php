@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Ajouter un Type d'Engin</h5>
                            <p class="mb-4">Créez un nouveau type d'engin pour votre système de livraison</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('type-engins.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages d'erreur -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Formulaire de création -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du Type d'Engin</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('type-engins.store') }}" method="POST" id="createTypeEnginForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('libelle') is-invalid @enderror"
                                       id="libelle"
                                       name="libelle"
                                       value="{{ old('libelle') }}"
                                       placeholder="Ex: Moto, Voiture, Camion, Vélo..."
                                       required>
                                @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Le libellé doit contenir uniquement des lettres, espaces, tirets, apostrophes et points.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    Créer le Type d'Engin
                                </button>
                                <a href="{{ route('type-engins.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script>
// Validation côté client
document.getElementById('createTypeEnginForm').addEventListener('submit', function(e) {
    const libelle = document.getElementById('libelle').value.trim();

    // Validation du libellé
    if (!libelle) {
        e.preventDefault();
        alert('Le libellé est obligatoire.');
        document.getElementById('libelle').focus();
        return false;
    }

    if (libelle.length > 255) {
        e.preventDefault();
        alert('Le libellé ne peut pas dépasser 255 caractères.');
        document.getElementById('libelle').focus();
        return false;
    }

    // Validation du format (lettres, espaces, tirets, apostrophes, points)
    const libelleRegex = /^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u;
    if (!libelleRegex.test(libelle)) {
        e.preventDefault();
        alert('Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.');
        document.getElementById('libelle').focus();
        return false;
    }
});

// Auto-dismiss des alertes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

@include('layouts.footer')
