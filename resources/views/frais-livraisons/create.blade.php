@include('layouts.header')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Nouveau Frais de Livraison</h5>
                        <small class="text-muted">Créer un nouveau frais de livraison</small>
                    </div>
                    <a href="{{ route('frais-livraisons.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('frais-livraisons.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('libelle') is-invalid @enderror"
                                       id="libelle" name="libelle" value="{{ old('libelle') }}" required>
                                @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="montant" class="form-label">Montant <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('montant') is-invalid @enderror"
                                       id="montant" name="montant" value="{{ old('montant') }}" required>
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_frais" class="form-label">Type de Frais <span class="text-danger">*</span></label>
                                <select class="form-select @error('type_frais') is-invalid @enderror"
                                        id="type_frais" name="type_frais" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="fixe" {{ old('type_frais') == 'fixe' ? 'selected' : '' }}>Frais Fixe</option>
                                    <option value="pourcentage" {{ old('type_frais') == 'pourcentage' ? 'selected' : '' }}>Pourcentage</option>
                                    <option value="par_km" {{ old('type_frais') == 'par_km' ? 'selected' : '' }}>Par Kilomètre</option>
                                    <option value="par_colis" {{ old('type_frais') == 'par_colis' ? 'selected' : '' }}>Par Colis</option>
                                </select>
                                @error('type_frais')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="zone_applicable" class="form-label">Zone Applicable <span class="text-danger">*</span></label>
                                <select class="form-select @error('zone_applicable') is-invalid @enderror"
                                        id="zone_applicable" name="zone_applicable" required>
                                    <option value="">Sélectionner une zone</option>
                                    <option value="toutes" {{ old('zone_applicable') == 'toutes' ? 'selected' : '' }}>Toutes les zones</option>
                                    <option value="urbain" {{ old('zone_applicable') == 'urbain' ? 'selected' : '' }}>Zone urbaine</option>
                                    <option value="rural" {{ old('zone_applicable') == 'rural' ? 'selected' : '' }}>Zone rurale</option>
                                    <option value="specifique" {{ old('zone_applicable') == 'specifique' ? 'selected' : '' }}>Zones spécifiques</option>
                                </select>
                                @error('zone_applicable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3" id="zones_specifiques_container" style="display: none;">
                            <label for="zones_specifiques" class="form-label">Zones Spécifiques</label>
                            <select class="form-select @error('zones_specifiques') is-invalid @enderror"
                                    id="zones_specifiques" name="zones_specifiques[]" multiple>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}"
                                            {{ in_array($commune->id, old('zones_specifiques', [])) ? 'selected' : '' }}>
                                        {{ $commune->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('zones_specifiques')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date de Début <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_debut') is-invalid @enderror"
                                       id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required>
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control @error('date_fin') is-invalid @enderror"
                                       id="date_fin" name="date_fin" value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="actif" name="actif"
                                       {{ old('actif', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="actif">
                                    Actif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Créer
                            </button>
                            <a href="{{ route('frais-livraisons.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const zoneApplicable = document.getElementById('zone_applicable');
    const zonesSpecifiquesContainer = document.getElementById('zones_specifiques_container');

    zoneApplicable.addEventListener('change', function() {
        if (this.value === 'specifique') {
            zonesSpecifiquesContainer.style.display = 'block';
        } else {
            zonesSpecifiquesContainer.style.display = 'none';
        }
    });

    // Déclencher l'événement au chargement si une valeur est déjà sélectionnée
    if (zoneApplicable.value === 'specifique') {
        zonesSpecifiquesContainer.style.display = 'block';
    }
});
</script>

@include('layouts.menu')
@include('layouts.footer')
