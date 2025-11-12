@include('layouts.header')
@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Créer une Catégorie</h5>
                            <p class="mb-4">Remplissez les informations de la nouvelle catégorie.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de la Catégorie</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Électronique, Alimentaire, etc."
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3"
                                          placeholder="Description de la catégorie">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="icon" class="form-label">Icône</label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control @error('icon') is-invalid @enderror"
                                           id="icon"
                                           name="icon"
                                           value="{{ old('icon', 'ti ti-box') }}"
                                           placeholder="ti ti-box"
                                           readonly>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#iconPickerModal">
                                        <i class="ti ti-palette me-1"></i>
                                        Choisir une icône
                                    </button>
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Cliquez sur "Choisir une icône" pour sélectionner</small>
                                <div class="mt-2">
                                    <label class="form-label small">Aperçu :</label>
                                    <div class="d-flex align-items-center">
                                        <i id="iconPreview" class="ti ti-box me-2" style="font-size: 1.5rem;"></i>
                                        <span id="iconPreviewText" class="text-muted small">ti ti-box</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Ordre d'affichage</label>
                                <input type="number"
                                       class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order"
                                       name="sort_order"
                                       value="{{ old('sort_order', 0) }}"
                                       min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Catégorie active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Créer la catégorie
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<!-- Modal Sélecteur d'icônes -->
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="iconPickerModalLabel">Sélectionner une icône</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="iconSearch" placeholder="Rechercher une icône...">
                </div>
                <div class="row g-2" id="iconGrid" style="max-height: 400px; overflow-y: auto;">
                    @php
                        $icons = [
                            // Stock et emballage
                            'ti ti-box' => 'Boîte',
                            'ti ti-package' => 'Paquet',
                            'ti ti-box-multiple' => 'Boîtes',
                            'ti ti-packages' => 'Paquets',
                            'ti ti-archive' => 'Archive',
                            'ti ti-pallet' => 'Palette',
                            'ti ti-building-warehouse' => 'Entrepôt',

                            // Commerce
                            'ti ti-shopping-cart' => 'Panier',
                            'ti ti-shopping-bag' => 'Sac',
                            'ti ti-basket' => 'Panier',
                            'ti ti-building-store' => 'Magasin',
                            'ti ti-store' => 'Boutique',
                            'ti ti-tag' => 'Étiquette',
                            'ti ti-tags' => 'Étiquettes',

                            // Transport
                            'ti ti-truck' => 'Camion',
                            'ti ti-truck-delivery' => 'Livraison',

                            // Finance
                            'ti ti-currency-dollar' => 'Dollar',
                            'ti ti-currency-euro' => 'Euro',
                            'ti ti-cash' => 'Espèces',
                            'ti ti-receipt' => 'Reçu',
                            'ti ti-file-invoice' => 'Facture',

                            // Statistiques
                            'ti ti-chart-bar' => 'Graphique',
                            'ti ti-chart-line' => 'Ligne',
                            'ti ti-chart-pie' => 'Camembert',

                            // Produits alimentaires
                            'ti ti-apple' => 'Pomme',
                            'ti ti-bread' => 'Pain',
                            'ti ti-cake' => 'Gâteau',
                            'ti ti-coffee' => 'Café',
                            'ti ti-bottle' => 'Bouteille',
                            'ti ti-mug' => 'Tasse',
                            'ti ti-fish' => 'Poisson',
                            'ti ti-meat' => 'Viande',
                            'ti ti-egg' => 'Oeuf',
                            'ti ti-milk' => 'Lait',
                            'ti ti-cheese' => 'Fromage',
                            'ti ti-pizza' => 'Pizza',
                            'ti ti-hamburger' => 'Hamburger',
                            'ti ti-salad' => 'Salade',

                            // Fruits et légumes
                            'ti ti-carrot' => 'Carotte',
                            'ti ti-tomato' => 'Tomate',
                            'ti ti-banana' => 'Banane',
                            'ti ti-orange' => 'Orange',
                            'ti ti-strawberry' => 'Fraise',
                            'ti ti-leaf' => 'Feuille',

                            // Électronique
                            'ti ti-device-desktop' => 'Ordinateur',
                            'ti ti-device-mobile' => 'Mobile',
                            'ti ti-phone' => 'Téléphone',
                            'ti ti-camera' => 'Caméra',
                            'ti ti-headphones' => 'Casque',

                            // Divers
                            'ti ti-settings' => 'Paramètres',
                            'ti ti-tools' => 'Outils',
                            'ti ti-barcode' => 'Code-barres',
                            'ti ti-qrcode' => 'QR Code',
                            'ti ti-printer' => 'Imprimante',
                            'ti ti-scan' => 'Scanner',
                            'ti ti-image' => 'Image',
                            'ti ti-file' => 'Fichier',
                            'ti ti-folder' => 'Dossier',
                        ];
                    @endphp
                    @foreach($icons as $iconClass => $iconName)
                        <div class="col-3 col-md-2 icon-item" data-icon="{{ $iconClass }}" data-name="{{ $iconName }}">
                            <div class="card text-center p-2 icon-card" style="cursor: pointer; transition: all 0.2s;">
                                <i class="{{ $iconClass }}" style="font-size: 1.5rem;"></i>
                                <small class="d-block mt-1 text-muted" style="font-size: 0.7rem;">{{ $iconName }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-card:hover {
        background-color: #f0f0f0;
        transform: scale(1.05);
    }
    .icon-card.selected {
        background-color: #e7f3ff;
        border-color: #0d6efd;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('iconPreview');
    const iconPreviewText = document.getElementById('iconPreviewText');
    const iconSearch = document.getElementById('iconSearch');
    const iconItems = document.querySelectorAll('.icon-item');

    // Mettre à jour l'aperçu initial
    updatePreview(iconInput.value);

    // Mettre en surbrillance l'icône actuelle si elle existe
    const currentIcon = iconInput.value;
    iconItems.forEach(item => {
        if (item.dataset.icon === currentIcon) {
            item.querySelector('.icon-card').classList.add('selected');
        }
    });

    // Recherche d'icônes
    iconSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        iconItems.forEach(item => {
            const iconName = item.dataset.name.toLowerCase();
            const iconClass = item.dataset.icon.toLowerCase();
            if (iconName.includes(searchTerm) || iconClass.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Sélection d'icône
    iconItems.forEach(item => {
        item.addEventListener('click', function() {
            const iconClass = this.dataset.icon;
            iconInput.value = iconClass;
            updatePreview(iconClass);

            // Mettre en surbrillance la sélection
            iconItems.forEach(i => i.querySelector('.icon-card').classList.remove('selected'));
            this.querySelector('.icon-card').classList.add('selected');

            // Fermer le modal après un court délai
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('iconPickerModal'));
                modal.hide();
            }, 300);
        });
    });

    function updatePreview(iconClass) {
        iconPreview.className = iconClass + ' me-2';
        iconPreviewText.textContent = iconClass;
    }
});
</script>

