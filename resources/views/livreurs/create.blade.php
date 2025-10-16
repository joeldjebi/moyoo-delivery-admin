@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-user-plus me-2"></i>
                        Ajouter un Livreur
                    </h5>
                    <a href="{{ route('livreurs.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>Erreurs détectées :</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('livreurs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Informations personnelles -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-user me-2"></i>
                                            Informations personnelles
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       class="form-control @error('first_name') is-invalid @enderror"
                                                       id="first_name"
                                                       name="first_name"
                                                       value="{{ old('first_name') }}"
                                                       required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       class="form-control @error('last_name') is-invalid @enderror"
                                                       id="last_name"
                                                       name="last_name"
                                                       value="{{ old('last_name') }}"
                                                       required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="mobile" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                                <input type="tel"
                                                       class="form-control @error('mobile') is-invalid @enderror"
                                                       id="mobile"
                                                       name="mobile"
                                                       value="{{ old('mobile') }}"
                                                       required>
                                                @error('mobile')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email"
                                                       class="form-control @error('email') is-invalid @enderror"
                                                       id="email"
                                                       name="email"
                                                       value="{{ old('email') }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="permis" class="form-label">Numéro de permis</label>
                                            <input type="text"
                                                   class="form-control @error('permis') is-invalid @enderror"
                                                   id="permis"
                                                   name="permis"
                                                   value="{{ old('permis') }}">
                                            @error('permis')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                                      id="adresse"
                                                      name="adresse"
                                                      rows="3">{{ old('adresse') }}</textarea>
                                            @error('adresse')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations professionnelles -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-briefcase me-2"></i>
                                            Informations professionnelles
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="engin_id" class="form-label">Engin assigné <span class="text-danger">*</span></label>
                                            <select class="form-select @error('engin_id') is-invalid @enderror"
                                                    id="engin_id"
                                                    name="engin_id"
                                                    required>
                                                <option value="">Sélectionnez un engin</option>
                                                @foreach($engins as $engin)
                                                    <option value="{{ $engin->id }}"
                                                            {{ old('engin_id') == $engin->id ? 'selected' : '' }}>
                                                        {{ $engin->libelle }}
                                                        @if($engin->typeEngin)
                                                            - {{ $engin->typeEngin->libelle }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('engin_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="zone_activite_id" class="form-label">Commune principale</label>
                                            <select class="form-select @error('zone_activite_id') is-invalid @enderror"
                                                    id="zone_activite_id"
                                                    name="zone_activite_id">
                                                <option value="">Sélectionnez une commune principale</option>
                                                @foreach($communes as $commune)
                                                    <option value="{{ $commune->id }}"
                                                            {{ old('zone_activite_id') == $commune->id ? 'selected' : '' }}>
                                                        {{ $commune->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('zone_activite_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Communes d'activité</label>

                                            <!-- Filtre de recherche -->
                                            <div class="mb-3">
                                                <input type="text"
                                                       class="form-control"
                                                       id="communeSearch"
                                                       placeholder="Rechercher une commune...">
                                            </div>

                                            <!-- Boutons d'action -->
                                            <div class="mb-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllCommunes">
                                                    <i class="ti ti-check-all me-1"></i>
                                                    Tout sélectionner
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllCommunes">
                                                    <i class="ti ti-x me-1"></i>
                                                    Tout désélectionner
                                                </button>
                                            </div>

                                            <!-- Liste des communes avec checkboxes -->
                                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                                <div id="communesList">
                                                    @foreach($communes as $commune)
                                                        <div class="form-check commune-item" data-commune-name="{{ strtolower($commune->libelle) }}">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="communes[]"
                                                                   value="{{ $commune->id }}"
                                                                   id="commune_{{ $commune->id }}"
                                                                   {{ in_array($commune->id, old('communes', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="commune_{{ $commune->id }}">
                                                                {{ $commune->libelle }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="form-text">
                                                <span id="selectedCount">0</span> commune(s) sélectionnée(s)
                                            </div>

                                            @error('communes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo et mot de passe -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ti ti-camera me-2"></i>
                                            Photo et sécurité
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="photo" class="form-label">Photo de profil</label>
                                            <input type="file"
                                                   class="form-control @error('photo') is-invalid @enderror"
                                                   id="photo"
                                                   name="photo"
                                                   accept="image/*">
                                            <div class="form-text">Formats acceptés : JPG, PNG, GIF (max 2MB)</div>
                                            @error('photo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   id="password"
                                                   name="password"
                                                   required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password"
                                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   required>
                                            @error('password_confirmation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('livreurs.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Créer le livreur
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
document.addEventListener('DOMContentLoaded', function() {
    // Aperçu de la photo
    const photoInput = document.getElementById('photo');
    const photoPreview = document.createElement('div');
    photoPreview.className = 'mt-2';
    photoPreview.style.display = 'none';

    photoInput.parentNode.appendChild(photoPreview);

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = `
                    <img src="${e.target.result}"
                         alt="Aperçu"
                         class="img-thumbnail"
                         style="max-width: 150px; max-height: 150px;">
                `;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.style.display = 'none';
        }
    });

    // Validation du mot de passe
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');

    function validatePassword() {
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }

    password.addEventListener('input', validatePassword);
    passwordConfirmation.addEventListener('input', validatePassword);

    // Gestion des communes
    const communeSearch = document.getElementById('communeSearch');
    const communesList = document.getElementById('communesList');
    const communeItems = document.querySelectorAll('.commune-item');
    const selectedCount = document.getElementById('selectedCount');
    const selectAllBtn = document.getElementById('selectAllCommunes');
    const deselectAllBtn = document.getElementById('deselectAllCommunes');

    // Fonction pour mettre à jour le compteur
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('input[name="communes[]"]:checked');
        selectedCount.textContent = checkedBoxes.length;
    }

    // Fonction pour filtrer les communes
    function filterCommunes() {
        const searchTerm = communeSearch.value.toLowerCase();
        communeItems.forEach(item => {
            const communeName = item.getAttribute('data-commune-name');
            if (communeName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Événements
    communeSearch.addEventListener('input', filterCommunes);

    // Sélectionner tout
    selectAllBtn.addEventListener('click', function() {
        const visibleItems = Array.from(communeItems).filter(item => item.style.display !== 'none');
        visibleItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = true;
        });
        updateSelectedCount();
    });

    // Désélectionner tout
    deselectAllBtn.addEventListener('click', function() {
        const visibleItems = Array.from(communeItems).filter(item => item.style.display !== 'none');
        visibleItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // Mettre à jour le compteur quand une checkbox change
    document.querySelectorAll('input[name="communes[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Initialiser le compteur
    updateSelectedCount();
});
</script>

@include('layouts.footer')
