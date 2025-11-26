@include('layouts.header')

@include('layouts.menu')
<!-- Intro.js CSS et JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/introjs.min.css">
<script src="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/intro.min.js"></script>

<div class="row">
    <div class="col-md-12">

    <!-- Messages flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
        <div class="card mb-4" id="entreprise-card" data-intro="Voici la fiche récapitulative de votre entreprise. Vous pouvez la mettre à jour et gérer son statut ici." data-step="1">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0" id="entreprise-title">Informations de l'Entreprise</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="startEntrepriseGuide()" title="Voir le guide">
                        <i class="bx bx-help-circle"></i> Guide
                    </button>
                    <a href="{{ route('entreprise.edit') }}" id="entreprise-edit-btn" class="btn btn-primary btn-sm" data-intro="Cliquez ici pour mettre à jour les informations de votre entreprise (nom, contact, adresse, logo...)." data-step="2">
                        <i class="bx bx-edit-alt"></i> Modifier
                    </a>
                    <form action="{{ route('entreprise.toggle-status') }}" method="POST" class="d-inline" id="entreprise-toggle-status-form" data-intro="Activez/Désactivez l'entreprise. En mode désactivé, certaines actions peuvent être limitées." data-step="3">
                        @csrf
                        @method('PUT')
                        <button type="submit" id="entreprise-toggle-status-btn" class="btn btn-sm {{ $entreprise->statut == 1 ? 'btn-warning' : 'btn-success' }}">
                            <i class="bx {{ $entreprise->statut == 1 ? 'bx-pause' : 'bx-play' }}"></i>
                            {{ $entreprise->statut == 1 ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-borderless" id="entreprise-info-table">
                            <tbody>
                                <tr>
                                    <td class="fw-bold" style="width: 30%;">Nom de l'entreprise :</td>
                                    <td id="entreprise-name" data-intro="Nom officiel de votre entreprise tel qu'il apparaît sur la plateforme." data-step="4">{{ $entreprise->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email :</td>
                                    <td id="entreprise-email">{{ $entreprise->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Téléphone :</td>
                                    <td id="entreprise-mobile">{{ $entreprise->mobile }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Adresse :</td>
                                    <td id="entreprise-adresse">{{ $entreprise->adresse }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Commune de départ :</td>
                                    <td>
                                        <span class="badge bg-info" id="entreprise-commune" data-intro="Commune de départ par défaut pour vos opérations et tarifs." data-step="5">{{ $entreprise->commune->libelle }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Statut :</td>
                                    <td>
                                        <span class="badge {{ $entreprise->statut_class }}" id="entreprise-statut">
                                            {{ $entreprise->statut_formatted }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Créée le :</td>
                                    <td>{{ $entreprise->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Dernière mise à jour :</td>
                                    <td>{{ $entreprise->updated_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4 text-center" id="entreprise-logo" data-intro="Ajoutez ou mettez à jour votre logo pour une meilleure identité visuelle." data-step="6">
                        <form id="logo-upload-form" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div id="logo-preview-container">
                                    @if($entreprise->logo)
                                        <img src="{{ asset('storage/' . $entreprise->logo) }}"
                                                alt="Logo de {{ $entreprise->name }}"
                                                id="logo-preview"
                                                class="img-fluid rounded shadow"
                                                style="max-width: 200px; max-height: 200px; cursor: pointer;"
                                                onclick="document.getElementById('logo-input').click()">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center shadow"
                                                id="logo-placeholder"
                                                style="width: 200px; height: 200px; margin: 0 auto; cursor: pointer;"
                                                onclick="document.getElementById('logo-input').click()">
                                            <i class="bx bx-building text-muted" style="font-size: 4rem;"></i>
                                        </div>
                                        <small class="text-muted d-block mt-2">Aucun logo</small>
                                    @endif
                                </div>
                                <input type="file"
                                       id="logo-input"
                                       name="logo"
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                       style="display: none;"
                                       onchange="previewLogo(this)">
                                <div class="mt-2">
                                    <button type="button"
                                            class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('logo-input').click()">
                                        <i class="bx bx-upload"></i> {{ $entreprise->logo ? 'Changer le logo' : 'Ajouter un logo' }}
                                    </button>
                                    @if($entreprise->logo)
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="removeLogo()">
                                            <i class="bx bx-trash"></i> Supprimer
                                        </button>
                                    @endif
                                </div>
                                <div id="logo-upload-progress" class="mt-2" style="display: none;">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                             role="progressbar"
                                             style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted">Upload en cours...</small>
                                </div>
                                <div id="logo-upload-message" class="mt-2"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="row">
            <div class="col-md-6" id="entreprise-stats" data-intro="Un aperçu rapide de vos statistiques clés: colis livrés, en cours, en attente." data-step="7">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Statistiques</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="d-flex flex-column">
                                    <div class="avatar flex-shrink-0 mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="bx bx-package"></i>
                                        </span>
                                    </div>
                                    <span class="fw-semibold d-block mb-1">Total Colis</span>
                                    <h3 class="card-title mb-0">{{ number_format($stats['total_colis']) }}</h3>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="d-flex flex-column">
                                    <div class="avatar flex-shrink-0 mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="bx bx-check-circle"></i>
                                        </span>
                                    </div>
                                    <span class="fw-semibold d-block mb-1">Livrés</span>
                                    <h3 class="card-title text-success mb-0">{{ number_format($stats['colis_livres']) }}</h3>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="d-flex flex-column">
                                    <div class="avatar flex-shrink-0 mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="bx bx-time-five"></i>
                                        </span>
                                    </div>
                                    <span class="fw-semibold d-block mb-1">En cours</span>
                                    <h3 class="card-title text-warning mb-0">{{ number_format($stats['colis_en_cours']) }}</h3>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="d-flex flex-column">
                                    <div class="avatar flex-shrink-0 mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class="bx bx-hourglass"></i>
                                        </span>
                                    </div>
                                    <span class="fw-semibold d-block mb-1">En attente</span>
                                    <h3 class="card-title text-info mb-0">{{ number_format($stats['colis_en_attente']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" id="entreprise-actions" data-intro="Accédez rapidement aux actions fréquentes: créer un colis, consulter les tarifs, etc." data-step="8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('colis.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Créer un nouveau colis
                            </a>
                            <a href="{{ route('colis.index') }}" class="btn btn-outline-primary">
                                <i class="bx bx-list-ul"></i> Voir tous les colis
                            </a>
                            <a href="{{ route('tarifs.index') }}" class="btn btn-outline-info" id="entreprise-tarifs-link" data-intro="Consultez et gérez vos tarifs de livraison ici." data-step="9">
                                <i class="bx bx-money"></i> Consulter les tarifs
                            </a>
                            <form action="{{ route('entreprise.regenerate-tarifs') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning"
                                        onclick="return confirm('Êtes-vous sûr de vouloir régénérer tous les tarifs de livraison ? Cette action remplacera tous les tarifs existants.')">
                                    <i class="bx bx-refresh"></i> Régénérer les tarifs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Guide Entreprise - démarre une seule fois automatiquement
function startEntrepriseGuide() {
    introJs().setOptions({
        steps: [
            { element: '#entreprise-card', intro: "Vue d'ensemble de votre entreprise.", position: 'bottom' },
            { element: '#entreprise-edit-btn', intro: "Mettre à jour les informations de l'entreprise.", position: 'left' },
            { element: '#entreprise-toggle-status-form', intro: "Activer/Désactiver l'entreprise.", position: 'left' },
            { element: '#entreprise-name', intro: "Nom de votre entreprise.", position: 'right' },
            { element: '#entreprise-commune', intro: "Commune de départ par défaut.", position: 'right' },
            { element: '#entreprise-logo', intro: "Logo de l'entreprise.", position: 'left' },
            { element: '#entreprise-stats', intro: "Statistiques clés.", position: 'top' },
            { element: '#entreprise-actions', intro: "Actions rapides pour démarrer.", position: 'top' },
            { element: '#entreprise-tarifs-link', intro: "Accéder aux tarifs de livraison.", position: 'left' }
        ],
        showProgress: true,
        showBullets: true,
        exitOnOverlayClick: false,
        nextLabel: 'Suivant →',
        prevLabel: '← Précédent',
        doneLabel: 'Terminer',
        skipLabel: 'Passer'
    }).start().oncomplete(function(){
        localStorage.setItem('hasSeenEntrepriseGuide', 'true');
    }).onexit(function(){
        localStorage.setItem('hasSeenEntrepriseGuide', 'true');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var seen = localStorage.getItem('hasSeenEntrepriseGuide');
    if (!seen) {
        // Marquer avant lancement pour éviter les relances sur refresh
        localStorage.setItem('hasSeenEntrepriseGuide', 'true');
        setTimeout(function(){
            if (typeof startEntrepriseGuide === 'function') startEntrepriseGuide();
        }, 800);
    }
});

// Fonction pour prévisualiser le logo avant upload
function previewLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var container = document.getElementById('logo-preview-container');
            var placeholder = document.getElementById('logo-placeholder');

            // Supprimer le placeholder s'il existe
            if (placeholder) {
                placeholder.remove();
            }

            // Créer ou mettre à jour l'image de prévisualisation
            var img = document.getElementById('logo-preview');
            if (!img) {
                img = document.createElement('img');
                img.id = 'logo-preview';
                img.className = 'img-fluid rounded shadow';
                img.style.cssText = 'max-width: 200px; max-height: 200px; cursor: pointer;';
                img.onclick = function() { document.getElementById('logo-input').click(); };
                container.innerHTML = '';
                container.appendChild(img);
            }

            img.src = e.target.result;

            // Upload automatique après prévisualisation
            uploadLogo(input.files[0]);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Fonction pour uploader le logo
function uploadLogo(file) {
    var formData = new FormData();
    formData.append('logo', file);
    formData.append('_token', '{{ csrf_token() }}');

    var progressDiv = document.getElementById('logo-upload-progress');
    var progressBar = progressDiv.querySelector('.progress-bar');
    var messageDiv = document.getElementById('logo-upload-message');

    // Afficher la barre de progression
    progressDiv.style.display = 'block';
    progressBar.style.width = '0%';
    messageDiv.innerHTML = '';

    var xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
        }
    });

    xhr.addEventListener('load', function() {
        progressDiv.style.display = 'none';

        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Mettre à jour l'image avec l'URL du serveur
                var img = document.getElementById('logo-preview');
                if (img) {
                    img.src = response.logo_url;
                }

                // Afficher un message de succès
                messageDiv.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    '<small>' + response.message + '</small>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';

                // Masquer le message après 3 secondes
                setTimeout(function() {
                    messageDiv.innerHTML = '';
                }, 3000);

                // Recharger la page après 1 seconde pour mettre à jour l'interface
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<small>' + response.message + '</small>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
            }
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                '<small>Erreur lors de l\'upload du logo</small>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
        }
    });

    xhr.addEventListener('error', function() {
        progressDiv.style.display = 'none';
        messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<small>Erreur de connexion lors de l\'upload</small>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
    });

    xhr.open('POST', '{{ route("entreprise.upload-logo") }}');
    xhr.send(formData);
}

// Fonction pour supprimer le logo
function removeLogo() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer le logo ?')) {
        return;
    }

    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'DELETE');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("entreprise.upload-logo") }}');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            window.location.reload();
        } else {
            alert('Erreur lors de la suppression du logo');
        }
    });

    xhr.send(formData);
}
</script>
@include('layouts.footer')
