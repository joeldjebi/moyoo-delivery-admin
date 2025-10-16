@include('layouts.header')
@include('layouts.menu')

<!-- Contenu de la page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-pencil me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('historique-livraisons.show', $historique_livraison) }}" class="btn btn-outline-info">
                            <i class="ti ti-eye me-1"></i>
                            Voir
                        </a>
                        <a href="{{ route('historique-livraisons.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Messages d'erreur -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>Erreurs de validation :</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('historique-livraisons.update', $historique_livraison) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Package Colis -->
                            <div class="col-md-6 mb-3">
                                <label for="package_colis_id" class="form-label">
                                    Package Colis <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('package_colis_id') is-invalid @enderror"
                                        id="package_colis_id" name="package_colis_id" required>
                                    <option value="">Sélectionnez un package</option>
                                    @foreach($package_colis as $package)
                                        <option value="{{ $package->id }}"
                                                {{ (old('package_colis_id', $historique_livraison->package_colis_id ?? '') == $package->id) ? 'selected' : '' }}>
                                            {{ $package->numero_package }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('package_colis_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Livraison -->
                            <div class="col-md-6 mb-3">
                                <label for="livraison_id" class="form-label">
                                    Livraison <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('livraison_id') is-invalid @enderror"
                                        id="livraison_id" name="livraison_id" required>
                                    <option value="">Sélectionnez une livraison</option>
                                    @foreach($livraisons as $livraison)
                                        <option value="{{ $livraison->id }}"
                                                {{ (old('livraison_id', $historique_livraison->livraison_id ?? '') == $livraison->id) ? 'selected' : '' }}>
                                            {{ $livraison->numero_de_livraison }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('livraison_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Colis -->
                            <div class="col-md-6 mb-3">
                                <label for="colis_id" class="form-label">
                                    Colis <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('colis_id') is-invalid @enderror"
                                        id="colis_id" name="colis_id" required>
                                    <option value="">Sélectionnez un colis</option>
                                    @foreach($colis as $coli)
                                        <option value="{{ $coli->id }}"
                                                {{ (old('colis_id', $historique_livraison->colis_id ?? '') == $coli->id) ? 'selected' : '' }}>
                                            {{ $coli->code }} - {{ $coli->nom_client }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('colis_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Livreur -->
                            <div class="col-md-6 mb-3">
                                <label for="livreur_id" class="form-label">
                                    Livreur <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('livreur_id') is-invalid @enderror"
                                        id="livreur_id" name="livreur_id" required>
                                    <option value="">Sélectionnez un livreur</option>
                                    @foreach($livreurs as $livreur)
                                        <option value="{{ $livreur->id }}"
                                                {{ (old('livreur_id', $historique_livraison->livreur_id ?? '') == $livreur->id) ? 'selected' : '' }}>
                                            {{ $livreur->first_name }} {{ $livreur->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('livreur_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Statut -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="">Sélectionnez un statut</option>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}"
                                                {{ (old('status', $historique_livraison->status) == $key) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Montant à encaisser -->
                            <div class="col-md-6 mb-3">
                                <label for="montant_a_encaisse" class="form-label">
                                    Montant à encaisser (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control @error('montant_a_encaisse') is-invalid @enderror"
                                       id="montant_a_encaisse"
                                       name="montant_a_encaisse"
                                       value="{{ old('montant_a_encaisse', $historique_livraison->colis->montant_a_encaisse) }}"
                                       min="0"
                                       required>
                                @error('montant_a_encaisse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prix de vente -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_de_vente" class="form-label">
                                    Prix de vente (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control @error('prix_de_vente') is-invalid @enderror"
                                       id="prix_de_vente"
                                       name="prix_de_vente"
                                       value="{{ old('prix_de_vente', $historique_livraison->colis->prix_de_vente) }}"
                                       min="0"
                                       required>
                                @error('prix_de_vente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Montant de la livraison -->
                            <div class="col-md-6 mb-3">
                                <label for="montant_de_la_livraison" class="form-label">
                                    Montant de la livraison (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control @error('montant_de_la_livraison') is-invalid @enderror"
                                       id="montant_de_la_livraison"
                                       name="montant_de_la_livraison"
                                       value="{{ old('montant_de_la_livraison', $historique_livraison->montant_de_la_livraison) }}"
                                       min="0"
                                       required>
                                @error('montant_de_la_livraison')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Informations de création (lecture seule) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Informations de création
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Créé le :</strong> {{ $historique_livraison->created_at->format('d/m/Y à H:i') }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Dernière modification :</strong> {{ $historique_livraison->updated_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>
                                    @if($historique_livraison->user)
                                        <div class="mt-2">
                                            <strong>Créé par :</strong> {{ $historique_livraison->user->name ?? 'Utilisateur inconnu' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('historique-livraisons.show', $historique_livraison) }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-x me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check me-1"></i>
                                        Mettre à jour
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
    // Auto-focus sur le premier champ
    document.getElementById('package_colis_id').focus();

    // Validation en temps réel
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required], select[required]');

    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });

    // Calcul automatique du total
    function calculateTotal() {
        const montantEncaisse = parseFloat(document.getElementById('montant_a_encaisse').value) || 0;
        const prixVente = parseFloat(document.getElementById('prix_de_vente').value) || 0;
        const montantLivraison = parseFloat(document.getElementById('montant_de_la_livraison').value) || 0;

        const total = montantEncaisse + prixVente + montantLivraison;

        // Afficher le total si un élément existe
        let totalElement = document.getElementById('total-display');
        if (!totalElement) {
            totalElement = document.createElement('div');
            totalElement.id = 'total-display';
            totalElement.className = 'alert alert-success mt-3';
            document.querySelector('.card-body').appendChild(totalElement);
        }

        totalElement.innerHTML = `
            <h6 class="alert-heading">
                <i class="ti ti-calculator me-2"></i>
                Total calculé
            </h6>
            <div class="row">
                <div class="col-md-4">
                    <strong>Montant à encaisser :</strong><br>
                    <span class="text-primary">${montantEncaisse.toLocaleString()} FCFA</span>
                </div>
                <div class="col-md-4">
                    <strong>Prix de vente :</strong><br>
                    <span class="text-info">${prixVente.toLocaleString()} FCFA</span>
                </div>
                <div class="col-md-4">
                    <strong>Montant livraison :</strong><br>
                    <span class="text-success">${montantLivraison.toLocaleString()} FCFA</span>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <h5 class="mb-0">
                    <strong>Total : ${total.toLocaleString()} FCFA</strong>
                </h5>
            </div>
        `;
    }

    // Écouter les changements sur les champs de montant
    ['montant_a_encaisse', 'prix_de_vente', 'montant_de_la_livraison'].forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('input', calculateTotal);
    });

    // Calculer le total au chargement
    calculateTotal();
});
</script>

@include('layouts.footer')
