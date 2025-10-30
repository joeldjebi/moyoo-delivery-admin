@include('layouts.header')

@include('layouts.menu')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Nouveau Ramassage</h5>
                <a href="{{ route('ramassages.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Retour
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

                <form action="{{ route('ramassages.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="marchand_id" class="form-label">Marchand <span class="text-danger">*</span></label>
                            <select class="form-select" id="marchand_id" name="marchand_id" required>
                                <option value="">Sélectionner un marchand</option>
                                @foreach($marchands as $marchand)
                                    <option value="{{ $marchand->id }}" {{ old('marchand_id') == $marchand->id ? 'selected' : '' }}>
                                        {{ $marchand->first_name }} {{ $marchand->last_name }} ({{ $marchand->mobile }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="boutique_id" class="form-label">Boutique <span class="text-danger">*</span></label>
                            <select class="form-select" id="boutique_id" name="boutique_id" required>
                                <option value="">Sélectionner une boutique</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date_demande" class="form-label">Date de Demande <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_demande" name="date_demande"
                                    value="{{ old('date_demande', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="heure_demande" class="form-label">Heure de Demande <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="heure_demande" name="heure_demande"
                                    value="{{ old('heure_demande', date('H:i')) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nombre_colis_estime" class="form-label">Nombre de Colis Estimé <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="nombre_colis_estime" name="nombre_colis_estime"
                                    value="{{ old('nombre_colis_estime') }}" min="1" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="adresse_ramassage" class="form-label">Adresse de Ramassage <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="adresse_ramassage" name="adresse_ramassage" value="{{ old('adresse_ramassage') }}" required autocomplete="off" placeholder="Saisissez l'adresse...">
                            <input type="hidden" id="adresse_ramassage_lat" name="adresse_ramassage_lat" value="{{ old('adresse_ramassage_lat') }}">
                            <input type="hidden" id="adresse_ramassage_lng" name="adresse_ramassage_lng" value="{{ old('adresse_ramassage_lng') }}">
                            <input type="hidden" id="adresse_ramassage_place_id" name="adresse_ramassage_place_id" value="{{ old('adresse_ramassage_place_id') }}">
                            <small class="text-muted">Commencez à taper et sélectionnez une adresse proposée par Google.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_ramassage" class="form-label">Contact <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_ramassage" name="contact_ramassage"
                                    value="{{ old('contact_ramassage') }}" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Notes pour le ramassage</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Section pour les colis -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Colis à Ramasser</h6>
                                        <small class="text-muted">Créez de nouveaux colis pour ce ramassage</small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Section pour créer de nouveaux colis -->
                                    <div id="newColisSection">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6>Nouveaux Colis à Créer</h6>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-primary btn-sm" id="generateColisForms">
                                                    <i class="ti ti-refresh me-1"></i> Générer les Formulaires
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="addColisForm">
                                                    <i class="ti ti-plus me-1"></i> Ajouter un Colis
                                                </button>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mb-3" id="colisInfo">
                                            <i class="ti ti-info-circle me-2"></i>
                                            <span id="colisInfoText">Veuillez d'abord saisir le nombre de colis estimé, puis cliquez sur "Générer les Formulaires"</span>
                                        </div>
                                        <div id="colisFormsContainer">
                                            <!-- Les formulaires de colis seront générés ici -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('ramassages.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>
                            Créer le Ramassage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marchandSelect = document.getElementById('marchand_id');
    const boutiqueSelect = document.getElementById('boutique_id');
    const nombreColisEstimeInput = document.getElementById('nombre_colis_estime');
    const addColisFormBtn = document.getElementById('addColisForm');
    const generateColisFormsBtn = document.getElementById('generateColisForms');
    const colisFormsContainer = document.getElementById('colisFormsContainer');
    const colisInfoText = document.getElementById('colisInfoText');

    let colisFormCount = 0;

    // Fonction pour générer un formulaire de colis
    function generateColisForm() {
        colisFormCount++;
        const formId = `colis_form_${colisFormCount}`;

        const formHtml = `
            <div class="card mb-3" id="${formId}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Colis #${colisFormCount}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeColisForm('${formId}')">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom du Client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="colis_data[${colisFormCount}][client]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone du Client <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="colis_data[${colisFormCount}][telephone_client]" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Adresse du Client <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="colis_data[${colisFormCount}][adresse_client]" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zone de Livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][commune_id]" required>
                                <option value="">Sélectionner une commune</option>
                                <!-- Les communes seront chargées via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type de Colis <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][type_colis_id]" required>
                                <option value="">Sélectionner un type</option>
                                <!-- Les types de colis seront chargés via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Poids <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][poids_id]" required>
                                <option value="">Sélectionner un poids</option>
                                <!-- Les poids seront chargés via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Conditionnement <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][conditionnement_colis_id]" required>
                                <option value="">Sélectionner un conditionnement</option>
                                <!-- Les conditionnements seront chargés via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Délai <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][delai_id]" required>
                                <option value="">Sélectionner un délai</option>
                                <!-- Les délais seront chargés via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mode de Livraison <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][mode_livraison_id]" required>
                                <option value="">Sélectionner un mode</option>
                                <!-- Les modes de livraison seront chargés via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Période <span class="text-danger">*</span></label>
                            <select class="form-select" name="colis_data[${colisFormCount}][temp_id]" required>
                                <option value="">Sélectionner une période</option>
                                <!-- Les périodes seront chargées via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valeur (FCFA)</label>
                            <input type="number" class="form-control" name="colis_data[${colisFormCount}][valeur]" min="0" step="0.01">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="colis_data[${colisFormCount}][notes]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;

        colisFormsContainer.insertAdjacentHTML('beforeend', formHtml);

        // Charger les options pour ce formulaire
        loadFormOptions(formId);
    }

    // Fonction pour charger les options des formulaires
    function loadFormOptions(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        // Charger les communes
        loadCommunes(form);
        // Charger les types de colis
        loadTypesColis(form);
        // Charger les poids
        loadPoids(form);
        // Charger les conditionnements
        loadConditionnements(form);
        // Charger les délais
        loadDelais(form);
        // Charger les modes de livraison
        loadModesLivraison(form);
        // Charger les périodes
        loadPeriodes(form);
    }

    // Fonction pour charger les communes
    function loadCommunes(form) {
        const communeSelect = form.querySelector('select[name*="[commune_id]"]');
        if (communeSelect) {
            fetch('/api/communes')
                .then(response => response.json())
                .then(data => {
                    if (data.communes) {
                        data.communes.forEach(commune => {
                            const option = document.createElement('option');
                            option.value = commune.id;
                            option.textContent = commune.libelle;
                            communeSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des communes:', error));
        }
    }

    // Fonction pour charger les types de colis
    function loadTypesColis(form) {
        const typeSelect = form.querySelector('select[name*="[type_colis_id]"]');
        if (typeSelect) {
            fetch('/api/types-colis')
                .then(response => response.json())
                .then(data => {
                    if (data.types) {
                        data.types.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.id;
                            option.textContent = type.libelle;
                            typeSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des types de colis:', error));
        }
    }

    // Fonction pour charger les poids
    function loadPoids(form) {
        const poidsSelect = form.querySelector('select[name*="[poids_id]"]');
        if (poidsSelect) {
            fetch('/api/poids')
                .then(response => response.json())
                .then(data => {
                    if (data.poids) {
                        data.poids.forEach(poids => {
                            const option = document.createElement('option');
                            option.value = poids.id;
                            option.textContent = poids.libelle;
                            poidsSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des poids:', error));
        }
    }

    // Fonction pour charger les conditionnements
    function loadConditionnements(form) {
        const conditionnementSelect = form.querySelector('select[name*="[conditionnement_colis_id]"]');
        if (conditionnementSelect) {
            fetch('/api/conditionnements')
                .then(response => response.json())
                .then(data => {
                    if (data.conditionnements) {
                        data.conditionnements.forEach(conditionnement => {
                            const option = document.createElement('option');
                            option.value = conditionnement.id;
                            option.textContent = conditionnement.libelle;
                            conditionnementSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des conditionnements:', error));
        }
    }

    // Fonction pour charger les délais
    function loadDelais(form) {
        const delaiSelect = form.querySelector('select[name*="[delai_id]"]');
        if (delaiSelect) {
            fetch('/api/delais')
                .then(response => response.json())
                .then(data => {
                    if (data.delais) {
                        data.delais.forEach(delai => {
                            const option = document.createElement('option');
                            option.value = delai.id;
                            option.textContent = delai.libelle;
                            delaiSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des délais:', error));
        }
    }

    // Fonction pour charger les modes de livraison
    function loadModesLivraison(form) {
        const modeSelect = form.querySelector('select[name*="[mode_livraison_id]"]');
        if (modeSelect) {
            fetch('/api/modes-livraison')
                .then(response => response.json())
                .then(data => {
                    if (data.modes) {
                        data.modes.forEach(mode => {
                            const option = document.createElement('option');
                            option.value = mode.id;
                            option.textContent = mode.libelle;
                            modeSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des modes de livraison:', error));
        }
    }

    // Fonction pour charger les périodes
    function loadPeriodes(form) {
        const periodeSelect = form.querySelector('select[name*="[temp_id]"]');
        if (periodeSelect) {
            fetch('/api/periodes')
                .then(response => response.json())
                .then(data => {
                    if (data.periodes) {
                        data.periodes.forEach(periode => {
                            const option = document.createElement('option');
                            option.value = periode.id;
                            option.textContent = periode.libelle;
                            periodeSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des périodes:', error));
        }
    }

    // Fonction pour retirer un formulaire de colis
    window.removeColisForm = function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.remove();
            updateColisInfo();
        }
    };

    // Fonction pour générer automatiquement les formulaires basés sur le nombre estimé
    function generateColisFormsFromEstimate() {
        const nombreEstime = parseInt(nombreColisEstimeInput.value);

        if (!nombreEstime || nombreEstime < 1) {
            alert('Veuillez saisir un nombre de colis estimé valide (minimum 1)');
            return;
        }

        // Vider le conteneur existant
        colisFormsContainer.innerHTML = '';
        colisFormCount = 0;

        // Générer les formulaires
        for (let i = 0; i < nombreEstime; i++) {
            generateColisForm();
        }

        // Mettre à jour le message d'information
        colisInfoText.textContent = `${nombreEstime} formulaire(s) de colis généré(s) automatiquement. Le nombre de colis estimé a été mis à jour.`;

        // Changer la couleur de l'alerte
        const colisInfo = document.getElementById('colisInfo');
        colisInfo.className = 'alert alert-success mb-3';
    }

    // Fonction pour mettre à jour le message d'information
    function updateColisInfo() {
        const nombreEstime = parseInt(nombreColisEstimeInput.value);
        const formsCount = colisFormsContainer.children.length;

        // Mettre à jour automatiquement le nombre de colis estimé si des formulaires existent
        if (formsCount > 0 && formsCount !== nombreEstime) {
            nombreColisEstimeInput.value = formsCount;
        }

        const newNombreEstime = parseInt(nombreColisEstimeInput.value);

        if (newNombreEstime && newNombreEstime > 0) {
            if (formsCount === 0) {
                colisInfoText.textContent = `Nombre de colis estimé: ${newNombreEstime}. Cliquez sur "Générer les Formulaires" pour créer ${newNombreEstime} formulaire(s).`;
                const colisInfo = document.getElementById('colisInfo');
                colisInfo.className = 'alert alert-info mb-3';
                // Activer le bouton d'ajout
                addColisFormBtn.disabled = false;
                addColisFormBtn.classList.remove('btn-outline-secondary');
                addColisFormBtn.classList.add('btn-outline-primary');
            } else if (formsCount === newNombreEstime) {
                colisInfoText.textContent = `Parfait ! ${formsCount} formulaire(s) généré(s). Le nombre de colis estimé est synchronisé.`;
                const colisInfo = document.getElementById('colisInfo');
                colisInfo.className = 'alert alert-success mb-3';
                // Activer le bouton d'ajout
                addColisFormBtn.disabled = false;
                addColisFormBtn.classList.remove('btn-outline-secondary');
                addColisFormBtn.classList.add('btn-outline-primary');
            } else if (formsCount < newNombreEstime) {
                colisInfoText.textContent = `${formsCount} formulaire(s) créé(s) pour ${newNombreEstime} colis estimé(s). ${newNombreEstime - formsCount} formulaire(s) manquant(s).`;
                const colisInfo = document.getElementById('colisInfo');
                colisInfo.className = 'alert alert-warning mb-3';
                // Activer le bouton d'ajout
                addColisFormBtn.disabled = false;
                addColisFormBtn.classList.remove('btn-outline-secondary');
                addColisFormBtn.classList.add('btn-outline-primary');
            } else {
                colisInfoText.textContent = `${formsCount} formulaire(s) créé(s). Le nombre de colis estimé a été mis à jour automatiquement.`;
                const colisInfo = document.getElementById('colisInfo');
                colisInfo.className = 'alert alert-info mb-3';
                // Activer le bouton d'ajout
                addColisFormBtn.disabled = false;
                addColisFormBtn.classList.remove('btn-outline-secondary');
                addColisFormBtn.classList.add('btn-outline-primary');
            }
        } else {
            colisInfoText.textContent = 'Veuillez d\'abord saisir le nombre de colis estimé, puis cliquez sur "Générer les Formulaires"';
            const colisInfo = document.getElementById('colisInfo');
            colisInfo.className = 'alert alert-info mb-3';
            // Désactiver le bouton d'ajout
            addColisFormBtn.disabled = true;
            addColisFormBtn.classList.remove('btn-outline-primary');
            addColisFormBtn.classList.add('btn-outline-secondary');
        }
    }

    // Écouter les changements du nombre de colis estimé
    nombreColisEstimeInput.addEventListener('input', updateColisInfo);

    // Écouter le clic sur le bouton de génération automatique
    generateColisFormsBtn.addEventListener('click', generateColisFormsFromEstimate);

    // Écouter le clic sur le bouton d'ajout de formulaire
    addColisFormBtn.addEventListener('click', function() {
        generateColisForm();
        updateColisInfo();
    });

    // Initialiser l'état des boutons au chargement de la page
    updateColisInfo();

    // Écouter les changements de marchand
    marchandSelect.addEventListener('change', function() {
        const marchandId = this.value;

        // Réinitialiser le select des boutiques
        boutiqueSelect.innerHTML = '<option value="">Sélectionner une boutique</option>';

        if (marchandId) {
            // Récupérer les boutiques du marchand sélectionné
            fetch(`/api/boutiques/by-marchand/${marchandId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.boutiques && data.boutiques.length > 0) {
                        data.boutiques.forEach(boutique => {
                            const option = document.createElement('option');
                            option.value = boutique.id;
                            option.textContent = `${boutique.libelle} (${boutique.mobile})`;
                            // Ajouter les données supplémentaires comme attributs data
                            option.dataset.adresseGps = boutique.adresse_gps || '';
                            option.dataset.mobile = boutique.mobile || '';
                            boutiqueSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Aucune boutique trouvée';
                        boutiqueSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des boutiques:', error);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Erreur lors du chargement';
                    boutiqueSelect.appendChild(option);
                });
        }
    });

    // Écouter les changements de boutique pour remplir automatiquement les champs
    boutiqueSelect.addEventListener('change', function() {
        const boutiqueId = this.value;
        if (boutiqueId) {
            // Trouver la boutique sélectionnée
            const selectedOption = this.options[this.selectedIndex];
            const adresseGps = selectedOption.dataset.adresseGps;
            const mobile = selectedOption.dataset.mobile;

            // Remplir automatiquement les champs
            const adresseRamassageField = document.getElementById('adresse_ramassage');
            const contactRamassageField = document.getElementById('contact_ramassage');

            if (adresseGps && adresseRamassageField) {
                adresseRamassageField.value = adresseGps;
            }

            if (mobile && contactRamassageField) {
                contactRamassageField.value = mobile;
            }
        } else {
            // Vider les champs si aucune boutique n'est sélectionnée
            const adresseRamassageField = document.getElementById('adresse_ramassage');
            const contactRamassageField = document.getElementById('contact_ramassage');

            if (adresseRamassageField) {
                adresseRamassageField.value = '';
            }

            if (contactRamassageField) {
                contactRamassageField.value = '';
            }
        }
    });
});
</script>

<!-- Google Places Autocomplete pour l'adresse de ramassage -->
<script>
function initPickupAddressAutocomplete() {
    var input = document.getElementById('adresse_ramassage');
    if (!input) return;
    if (!window.google || !google.maps || !google.maps.places) return;

    var autocomplete = new google.maps.places.Autocomplete(input, {
        fields: ['formatted_address', 'geometry', 'place_id', 'name']
        // componentRestrictions: { country: ['ci'] }
    });

    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place) return;

        var formatted = place.formatted_address || place.name || input.value;
        input.value = formatted;

        var latInput = document.getElementById('adresse_ramassage_lat');
        var lngInput = document.getElementById('adresse_ramassage_lng');
        var placeIdInput = document.getElementById('adresse_ramassage_place_id');

        if (place.geometry && place.geometry.location) {
            if (latInput) latInput.value = place.geometry.location.lat();
            if (lngInput) lngInput.value = place.geometry.location.lng();
        }
        if (place.place_id && placeIdInput) {
            placeIdInput.value = place.place_id;
        }
    });
}
</script>
<style>
/* S'assurer que la liste des suggestions (pac-container) passe au-dessus des modals/cards */
.pac-container { z-index: 2000 !important; }
</style>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDSH9d5fmy1JBzTrBBQFEsTQw5LvAGM&libraries=places&loading=async&callback=initPickupAddressAutocomplete" async defer></script>
