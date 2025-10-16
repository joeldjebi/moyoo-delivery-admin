@include('layouts.header')
@include('layouts.menu')

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Ajouter une Boutique</h5>
                            <p class="mb-4">Remplissez les informations de la nouvelle boutique.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('boutiques.index') }}" class="btn btn-outline-secondary">
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
                    <h5 class="card-title mb-0">Informations de la Boutique</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('boutiques.store') }}" method="POST" id="boutiqueForm" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Libellé -->
                            <div class="col-md-6 mb-3">
                                <label for="libelle" class="form-label">Nom de la boutique <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('libelle') is-invalid @enderror"
                                       id="libelle"
                                       name="libelle"
                                       value="{{ old('libelle') }}"
                                       placeholder="Entrez le nom de la boutique"
                                       required>
                                @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Marchand -->
                            <div class="col-md-6 mb-3">
                                <label for="marchand_id" class="form-label">Marchand <span class="text-danger">*</span></label>
                                <select class="form-select @error('marchand_id') is-invalid @enderror"
                                        id="marchand_id"
                                        name="marchand_id"
                                        required>
                                    <option value="">Sélectionnez un marchand</option>
                                    @foreach($marchands as $marchand)
                                        <option value="{{ $marchand->id }}"
                                                {{ old('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                            {{ $marchand->first_name }} {{ $marchand->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('marchand_id')
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

                            <!-- Adresse -->
                            <div class="col-12 mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror"
                                          id="adresse"
                                          name="adresse"
                                          rows="3"
                                          placeholder="Entrez l'adresse complète de la boutique">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Adresse GPS (Lien Google Maps) -->
                            <div class="col-md-6 mb-3">
                                <label for="adresse_gps" class="form-label">Lien Google Maps</label>
                                <input type="url"
                                       class="form-control @error('adresse_gps') is-invalid @enderror"
                                       id="adresse_gps"
                                       name="adresse_gps"
                                       value="{{ old('adresse_gps') }}"
                                       placeholder="https://maps.google.com/...">
                                @error('adresse_gps')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image de couverture -->
                            <div class="col-md-6 mb-3">
                                <label for="cover_image" class="form-label">Image de couverture</label>
                                <input type="file"
                                       class="form-control @error('cover_image') is-invalid @enderror"
                                       id="cover_image"
                                       name="cover_image"
                                       accept="image/*">
                                @error('cover_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Formats acceptés: JPG, PNG, GIF (Max: 2MB)</small>
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
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('boutiques.index') }}" class="btn btn-outline-secondary">
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

<script>
// Validation côté client
document.getElementById('boutiqueForm').addEventListener('submit', function(e) {
    const libelle = document.getElementById('libelle').value.trim();
    const marchandId = document.getElementById('marchand_id').value;
    const mobile = document.getElementById('mobile').value.trim();
    const status = document.getElementById('status').value;

    // Validation des champs requis
    if (!libelle) {
        e.preventDefault();
        alert('Veuillez entrer le libellé de la boutique.');
        document.getElementById('libelle').focus();
        return false;
    }

    if (!marchandId) {
        e.preventDefault();
        alert('Veuillez sélectionner un marchand.');
        document.getElementById('marchand_id').focus();
        return false;
    }

    if (!mobile) {
        e.preventDefault();
        alert('Veuillez entrer le numéro de téléphone de la boutique.');
        document.getElementById('mobile').focus();
        return false;
    }

    if (!status) {
        e.preventDefault();
        alert('Veuillez sélectionner un statut.');
        document.getElementById('status').focus();
        return false;
    }

    // Validation du format du téléphone
    const phoneRegex = /^(\+225|225)?[0-9]{8,10}$/;
    const cleanMobile = mobile.replace(/\s+/g, '');
    if (!phoneRegex.test(cleanMobile)) {
        e.preventDefault();
        alert('Veuillez entrer un numéro de téléphone valide (ex: +225 07 12 34 56 78).');
        document.getElementById('mobile').focus();
        return false;
    }

    // Validation du format de l'adresse GPS si fournie
    const adresseGps = document.getElementById('adresse_gps').value.trim();
    if (adresseGps) {
        const urlRegex = /^https?:\/\/.+/;
        if (!urlRegex.test(adresseGps)) {
            e.preventDefault();
            alert('Veuillez entrer un lien Google Maps valide.');
            document.getElementById('adresse_gps').focus();
            return false;
        }
    }

    // Validation de l'image si fournie
    const coverImage = document.getElementById('cover_image').files[0];
    if (coverImage) {
        // Vérifier la taille (2MB max)
        if (coverImage.size > 2 * 1024 * 1024) {
            e.preventDefault();
            alert('L\'image ne doit pas dépasser 2MB.');
            document.getElementById('cover_image').focus();
            return false;
        }

        // Vérifier le type de fichier
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(coverImage.type)) {
            e.preventDefault();
            alert('Format d\'image non supporté. Utilisez JPG, PNG ou GIF.');
            document.getElementById('cover_image').focus();
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
