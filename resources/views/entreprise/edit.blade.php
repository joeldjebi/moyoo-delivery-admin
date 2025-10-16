@include('layouts.header')

@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Modifier mon Entreprise</h4>
                        <p class="mb-0 text-muted">Mettez à jour les informations de votre entreprise</p>
                    </div>
                    <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('entreprise.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $entreprise->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $entreprise->email) }}" required>
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
                                           id="mobile" name="mobile" value="{{ old('mobile', $entreprise->mobile) }}" required>
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
                                            <option value="{{ $commune->id }}"
                                                    {{ old('commune_id', $entreprise->commune_id) == $commune->id ? 'selected' : '' }}>
                                                {{ $commune->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('commune_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="bx bx-info-circle"></i>
                                        Changer la commune de départ peut affecter les calculs de tarifs existants
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse complète <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                      id="adresse" name="adresse" rows="3" required>{{ old('adresse', $entreprise->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo de l'entreprise</label>

                            @if($entreprise->logo)
                                <div class="mb-2">
                                    <label class="form-label">Logo actuel :</label>
                                    <div>
                                        <img src="{{ asset('storage/' . $entreprise->logo) }}"
                                             alt="Logo actuel"
                                             class="img-thumbnail"
                                             style="max-width: 150px; max-height: 150px;">
                                    </div>
                                </div>
                            @endif

                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                   id="logo" name="logo" accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Formats acceptés: JPEG, PNG, JPG, GIF. Taille maximale: 2MB
                                @if($entreprise->logo)
                                    <br><strong>Note:</strong> Sélectionner un nouveau fichier remplacera le logo actuel
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bx bx-warning"></i> Attention
                            </h6>
                            <p class="mb-0">
                                La modification de la commune de départ peut affecter les calculs de tarifs pour vos futurs colis.
                                Les colis existants conserveront leurs tarifs actuels.
                            </p>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('entreprise.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check"></i> Mettre à jour
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
    // Prévisualisation du nouveau logo
    const logoInput = document.getElementById('logo');
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Créer ou mettre à jour la prévisualisation
                let preview = document.getElementById('logo-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'logo-preview';
                    preview.className = 'mt-2';
                    preview.innerHTML = '<label class="form-label">Aperçu du nouveau logo :</label><br><img class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
                    logoInput.parentNode.appendChild(preview);
                }
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

@include('layouts.footer')
