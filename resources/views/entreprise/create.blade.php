@include('layouts.header')

@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Créer mon Entreprise de Livraison</h4>
                    <p class="mb-0 text-muted">Renseignez les informations de votre entreprise pour commencer à utiliser la plateforme</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('entreprise.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                           id="mobile" name="mobile" value="{{ old('mobile') }}" required>
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="commune_id" class="form-label">Commune de départ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('commune_id') is-invalid @enderror"
                                            id="commune_id" name="commune_id" required>
                                        <option value="">Sélectionner une commune</option>
                                        @foreach($communes as $commune)
                                            <option value="{{ $commune->id }}" {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                                {{ $commune->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('commune_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="bx bx-info-circle"></i>
                                        Cette commune servira de point de départ pour calculer les tarifs de livraison
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse complète <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                      id="adresse" name="adresse" rows="3" required>{{ old('adresse') }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo de l'entreprise (optionnel)</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                   id="logo" name="logo" accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF. Taille maximale: 2MB</div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bx bx-info-circle"></i> Information importante
                            </h6>
                            <p class="mb-0">
                                Une fois votre entreprise créée, vous pourrez commencer à créer des colis et utiliser toutes les fonctionnalités de la plateforme.
                                La commune de départ que vous sélectionnez sera utilisée pour calculer automatiquement les tarifs de livraison vers les autres communes.
                            </p>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check"></i> Créer mon entreprise
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prévisualisation du logo
    const logoInput = document.getElementById('logo');
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Créer ou mettre à jour la prévisualisation
                let preview = document.getElementById('logo-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'logo-preview';
                    preview.className = 'mt-2 img-thumbnail';
                    preview.style.maxWidth = '150px';
                    preview.style.maxHeight = '150px';
                    logoInput.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

@include('layouts.footer')
