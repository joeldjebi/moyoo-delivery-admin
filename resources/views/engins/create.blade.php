@include('layouts.header')
@include('layouts.menu')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Créer un Engin</h5>
                    <a href="{{ route('engins.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('engins.store') }}" method="POST" id="enginForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('libelle') is-invalid @enderror"
                                       id="libelle" name="libelle" value="{{ old('libelle') }}"
                                       placeholder="Ex: Moto de livraison" required>
                                @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6 mb-3">
                                <label for="marque" class="form-label">Marque <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('marque') is-invalid @enderror"
                                       id="marque" name="marque" value="{{ old('marque') }}"
                                       placeholder="Ex: Yamaha" required>
                                @error('marque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="modele" class="form-label">Modèle <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('modele') is-invalid @enderror"
                                       id="modele" name="modele" value="{{ old('modele') }}"
                                       placeholder="Ex: YBR 125" required>
                                @error('modele')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="couleur" class="form-label">Couleur <span class="text-danger">*</span></label>
                                <select class="form-select @error('couleur') is-invalid @enderror" id="couleur" name="couleur" required>
                                    <option value="">Sélectionner une couleur</option>
                                    <option value="Blanc" {{ old('couleur') == 'Blanc' ? 'selected' : '' }}>Blanc</option>
                                    <option value="Noir" {{ old('couleur') == 'Noir' ? 'selected' : '' }}>Noir</option>
                                    <option value="Rouge" {{ old('couleur') == 'Rouge' ? 'selected' : '' }}>Rouge</option>
                                    <option value="Bleu" {{ old('couleur') == 'Bleu' ? 'selected' : '' }}>Bleu</option>
                                    <option value="Vert" {{ old('couleur') == 'Vert' ? 'selected' : '' }}>Vert</option>
                                    <option value="Jaune" {{ old('couleur') == 'Jaune' ? 'selected' : '' }}>Jaune</option>
                                    <option value="Orange" {{ old('couleur') == 'Orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="Violet" {{ old('couleur') == 'Violet' ? 'selected' : '' }}>Violet</option>
                                    <option value="Rose" {{ old('couleur') == 'Rose' ? 'selected' : '' }}>Rose</option>
                                    <option value="Gris" {{ old('couleur') == 'Gris' ? 'selected' : '' }}>Gris</option>
                                    <option value="Marron" {{ old('couleur') == 'Marron' ? 'selected' : '' }}>Marron</option>
                                    <option value="Beige" {{ old('couleur') == 'Beige' ? 'selected' : '' }}>Beige</option>
                                    <option value="Argent" {{ old('couleur') == 'Argent' ? 'selected' : '' }}>Argent</option>
                                    <option value="Or" {{ old('couleur') == 'Or' ? 'selected' : '' }}>Or</option>
                                </select>
                                @error('couleur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="immatriculation" class="form-label">Immatriculation <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('immatriculation') is-invalid @enderror"
                                       id="immatriculation" name="immatriculation" value="{{ old('immatriculation') }}"
                                       placeholder="Ex: AB-123-CD" required>
                                @error('immatriculation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="etat" class="form-label">État <span class="text-danger">*</span></label>
                                <select class="form-select @error('etat') is-invalid @enderror" id="etat" name="etat" required>
                                    <option value="">Sélectionner un état</option>
                                    <option value="neuf" {{ old('etat') == 'neuf' ? 'selected' : '' }}>Neuf</option>
                                    <option value="occasion" {{ old('etat') == 'occasion' ? 'selected' : '' }}>Occasion</option>
                                    <option value="endommage" {{ old('etat') == 'endommage' ? 'selected' : '' }}>Endommagé</option>
                                </select>
                                @error('etat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Sélectionner un statut</option>
                                    <option value="actif" {{ old('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ old('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="type_engin_id" class="form-label">Type d'Engin <span class="text-danger">*</span></label>
                                <select class="form-select @error('type_engin_id') is-invalid @enderror" id="type_engin_id" name="type_engin_id" required>
                                    <option value="">Sélectionner un type d'engin</option>
                                    @foreach($typeEngins as $typeEngin)
                                        <option value="{{ $typeEngin->id }}" {{ old('type_engin_id') == $typeEngin->id ? 'selected' : '' }}>
                                            {{ $typeEngin->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_engin_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Créer l'Engin
                                    </button>
                                    <a href="{{ route('engins.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
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
    const form = document.getElementById('enginForm');

    form.addEventListener('submit', function(e) {
        // Validation côté client
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
});
</script>
@include('layouts.footer')
