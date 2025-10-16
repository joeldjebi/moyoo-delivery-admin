@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Ajouter un Marchand</h5>
                            <p class="mb-4">Remplissez les informations du nouveau marchand.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('marchands.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="card-title mb-0">Informations du Marchand</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('marchands.store') }}" method="POST" id="marchandForm">
                        @csrf

                        <div class="row">
                            <!-- Prénom -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name"
                                       name="first_name"
                                       value="{{ old('first_name') }}"
                                       placeholder="Entrez le prénom"
                                       required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nom -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name"
                                       name="last_name"
                                       value="{{ old('last_name') }}"
                                       placeholder="Entrez le nom"
                                       required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Téléphone -->
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel"
                                       class="form-control @error('mobile') is-invalid @enderror"
                                       id="mobile"
                                       name="mobile"
                                       value="{{ old('mobile') }}"
                                       placeholder="Ex: +225 07 12 34 56 78"
                                       required>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="exemple@email.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Commune -->
                            <div class="col-md-6 mb-3">
                                <label for="commune_id" class="form-label">Commune <span class="text-danger">*</span></label>
                                <select class="form-select @error('commune_id') is-invalid @enderror"
                                        id="commune_id"
                                        name="commune_id"
                                        required>
                                    <option value="">Sélectionnez une commune</option>
                                    @foreach($communes as $commune)
                                        <option value="{{ $commune->id }}"
                                                {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                            {{ $commune->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commune_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Statut -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="">Sélectionnez un statut</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Adresse -->
                            <div class="col-12 mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror"
                                          id="adresse"
                                          name="adresse"
                                          rows="3"
                                          placeholder="Entrez l'adresse complète">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('marchands.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Enregistrer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation côté client
document.getElementById('marchandForm').addEventListener('submit', function(e) {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const mobile = document.getElementById('mobile').value.trim();
    const communeId = document.getElementById('commune_id').value;
    const status = document.getElementById('status').value;

    // Validation des champs requis
    if (!firstName) {
        e.preventDefault();
        alert('Veuillez entrer le prénom.');
        document.getElementById('first_name').focus();
        return false;
    }

    if (!lastName) {
        e.preventDefault();
        alert('Veuillez entrer le nom.');
        document.getElementById('last_name').focus();
        return false;
    }

    if (!mobile) {
        e.preventDefault();
        alert('Veuillez entrer le numéro de téléphone.');
        document.getElementById('mobile').focus();
        return false;
    }

    if (!communeId) {
        e.preventDefault();
        alert('Veuillez sélectionner une commune.');
        document.getElementById('commune_id').focus();
        return false;
    }

    if (!status) {
        e.preventDefault();
        alert('Veuillez sélectionner un statut.');
        document.getElementById('status').focus();
        return false;
    }

    // Validation du format du téléphone
    const phoneRegex = /^[0-9+\-\s()]+$/;
    if (!phoneRegex.test(mobile)) {
        e.preventDefault();
        alert('Le format du numéro de téléphone est invalide.');
        document.getElementById('mobile').focus();
        return false;
    }

    // Validation de l'email si fourni
    const email = document.getElementById('email').value.trim();
    if (email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Veuillez entrer une adresse email valide.');
            document.getElementById('email').focus();
            return false;
        }
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
