@include('layouts.header')
@include('layouts.menu')

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-md-4">
            <div class="page-title-box">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="page-title">
                        <i class="ti ti-map me-2"></i>Moniteur de Géolocalisation - Admin
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning me-2">
                            <i class="fas fa-crown"></i> Premium
                        </span>
                        <small class="text-muted">
                            Fonctionnalité réservée aux abonnés Premium
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="page-title-box">
                <h5 class="page-title">
                    <i class="ti ti-map me-2"></i>Carte de Suivi des Livreurs
                </h5>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users"></i> Livreurs Actifs
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statut de connexion -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Connexion:</span>
                            <span id="connection-status" class="connection-status disconnected">Déconnecté</span>
                        </div>
                    </div>

                    <!-- Sélecteur de livreur -->
                    <div class="mb-3">
                        <h6><i class="fas fa-user"></i> Sélectionner un Livreur</h6>
                        <select id="livreur-selector" class="form-select">
                            <option value="">Tous les livreurs</option>
                            @foreach($livreursWithActiveMissions as $livreur)
                            <option value="{{ $livreur->id }}"
                                    data-lat="{{ $livreur->lastLocation ? $livreur->lastLocation->latitude : 5.316667 }}"
                                    data-lng="{{ $livreur->lastLocation ? $livreur->lastLocation->longitude : -4.033333 }}"
                                    data-mission-type="{{ $livreur->colis->count() > 0 ? 'livraison' : ($livreur->ramassages->count() > 0 ? 'ramassage' : 'mission') }}">
                                {{ $livreur->first_name }} {{ $livreur->last_name }}
                                @if($livreur->colis->count() > 0)
                                    (Livraison)
                                @elseif($livreur->ramassages->count() > 0)
                                    (Ramassage)
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtres par type de mission -->
                    <div class="mb-3">
                        <h6><i class="fas fa-filter"></i> Filtrer par Type</h6>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm active" data-filter="all">Tous</button>
                            <button type="button" class="btn btn-outline-success btn-sm" data-filter="livraison">Livraisons</button>
                            <button type="button" class="btn btn-outline-info btn-sm" data-filter="ramassage">Ramassages</button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mb-3">
                        <h6><i class="fas fa-crosshairs"></i> Actions</h6>
                        <button id="center-on-livreur" class="btn btn-primary btn-sm w-100 mb-2" disabled>
                            <i class="fas fa-crosshairs"></i> Centrer sur le Livreur
                        </button>
                        <button id="show-all-livreurs" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-globe"></i> Voir Tous
                        </button>
                    </div>

                    <!-- Détails du livreur sélectionné -->
                    <div class="mb-3" id="livreur-details" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> Détails du Livreur</h6>
                        <div id="selected-livreur-info">
                            <!-- Informations détaillées du livreur sélectionné -->
                        </div>
                    </div>

                    <!-- Liste des livreurs en mission -->
                    <div class="mb-3">
                        <h6><i class="fas fa-list"></i> Livreurs en Mission</h6>
                        <div id="livreurs-list">
                            @forelse($livreursWithActiveMissions as $livreur)
                            <div class="livreur-item" data-livreur-id="{{ $livreur->id }}" data-mission-type="{{ $livreur->colis->count() > 0 ? 'livraison' : ($livreur->ramassages->count() > 0 ? 'ramassage' : 'mission') }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $livreur->first_name }} {{ $livreur->last_name }}</span>
                                    @if($livreur->colis->count() > 0)
                                        <span class="badge bg-primary">Livraison</span>
                                    @elseif($livreur->ramassages->count() > 0)
                                        <span class="badge bg-info">Ramassage</span>
                                    @else
                                        <span class="badge bg-success">En mission</span>
                                    @endif
                                </div>
                                @if($livreur->lastLocation)
                                    <small class="text-muted">
                                        {{ number_format($livreur->lastLocation->latitude, 4) }}, {{ number_format($livreur->lastLocation->longitude, 4) }}
                                        <br>Dernière mise à jour: {{ $livreur->lastLocation->timestamp->format('H:i:s') }}
                                    </small>
                                @else
                                    <small class="text-muted">Aucune position enregistrée</small>
                                @endif
                            </div>
                            @empty
                            <div class="text-muted text-center">
                                <i class="fas fa-users"></i><br>
                                Aucun livreur en mission
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="mb-3">
                        <h6><i class="fas fa-chart-bar"></i> Statistiques</h6>
                        <div class="info-item">
                            <span class="label">Total livreurs:</span>
                            <span id="total-livreurs" class="value">{{ $stats['total_livreurs'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">En mission:</span>
                            <span id="total-mission" class="value">{{ $stats['en_mission'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">En livraison:</span>
                            <span id="total-delivery" class="value">{{ $stats['en_livraison'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">En ramassage:</span>
                            <span id="total-pickup" class="value">{{ $stats['en_ramassage'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Hors mission:</span>
                            <span id="total-offline" class="value">{{ $stats['hors_mission'] }}</span>
                        </div>
                    </div>

                    <!-- Dernière mise à jour -->
                    <div class="mb-3">
                        <h6><i class="fas fa-clock"></i> Dernière Mise à Jour</h6>
                        <div id="last-update" class="text-muted">Maintenant</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="col-md-8">
            <div id="admin-map" style="height: 850px; width: 100%;"></div>
        </div>
    </div>
</div>

@include('layouts.footer')

<!-- Styles CSS -->
<style>
.connection-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.connection-status.connected {
    background-color: #d4edda;
    color: #155724;
}

.connection-status.disconnected {
    background-color: #f8d7da;
    color: #721c24;
}

.connection-status.reconnecting {
    background-color: #fff3cd;
    color: #856404;
}

.livreur-item {
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.livreur-item:last-child {
    border-bottom: none;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 0.875rem;
}

.info-item .label {
    color: #6c757d;
}

.info-item .value {
    font-weight: 500;
    color: #495057;
}

#admin-map {
    height: 400px;
    width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.moto-marker {
    animation: pulse 2s infinite;
    transition: all 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.leaflet-marker-icon {
    transition: all 0.5s ease-in-out !important;
}

.form-select {
    font-size: 0.875rem;
}

.btn-group .btn {
    font-size: 0.8rem;
}

.btn-group .btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

#livreur-details {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
}

.livreur-item.highlighted {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
    padding-left: 8px;
}

.livreur-item.hidden {
    display: none;
}
</style>

<!-- Leaflet CSS et JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Socket.IO Client -->
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<!-- Scripts -->
<script>
// Variables globales
let map;
let markers = {};
let socket;
let isConnected = false;
let selectedLivreurId = null;
let currentFilter = 'all';

// Configuration Socket.IO
const SOCKET_URL = 'http://192.168.1.2:3000';

// Initialisation de la carte et Socket.IO
window.addEventListener('load', function() {
    console.log('🗺️ Initialisation OpenStreetMap admin avec Socket.IO...');

    // Initialiser Socket.IO
    initSocket();

    // Initialiser la carte
    setTimeout(initMap, 1000);

    // Initialiser les contrôles
    initControls();

    // Vérifier la connexion après 3 secondes
    setTimeout(function() {
        if (!isConnected) {
            console.log('⚠️ Connexion non établie, tentative de reconnexion...');
            updateConnectionStatus('reconnecting');
            initSocket();
        }
    }, 3000);
});

// Initialisation Socket.IO
function initSocket() {
    console.log('🔌 Connexion Socket.IO...');
    console.log('📡 URL Socket.IO:', SOCKET_URL);

    socket = io(SOCKET_URL, {
        transports: ['polling', 'websocket'],
        timeout: 10000,
        reconnection: true,
        reconnectionAttempts: 5,
        reconnectionDelay: 1000,
        forceNew: true,
        query: {
            type: 'admin'
        },
        auth: {
            type: 'admin'
        }
    });

    // Événements Socket.IO
    socket.on('connect', function() {
        console.log('✅ Socket.IO connecté !');
        console.log('🆔 Socket ID:', socket.id);
        isConnected = true;
        updateConnectionStatus('connected');

        // Rejoindre le canal admin
        socket.emit('admin:join', { type: 'admin' });
        console.log('👨‍💼 Canal admin rejoint');
    });

    socket.on('disconnect', function(reason) {
        console.log('❌ Socket.IO déconnecté ! Raison:', reason);
        isConnected = false;
        updateConnectionStatus('disconnected');
    });

    socket.on('connect_error', function(error) {
        console.error('❌ Erreur Socket.IO:', error);
        isConnected = false;
        updateConnectionStatus('disconnected');
    });

    socket.on('reconnect', function(attemptNumber) {
        console.log('🔄 Socket.IO reconnecté après', attemptNumber, 'tentatives');
        isConnected = true;
        updateConnectionStatus('connected');
    });

    socket.on('reconnect_attempt', function(attemptNumber) {
        console.log('🔄 Tentative de reconnexion', attemptNumber);
        updateConnectionStatus('reconnecting');
    });

    socket.on('reconnect_error', function(error) {
        console.error('❌ Erreur de reconnexion:', error);
        isConnected = false;
        updateConnectionStatus('disconnected');
    });

    // Écouter les mises à jour de localisation (SEULEMENT pour les livreurs en mission)
    socket.on('location:updated', function(data) {
        console.log('📍 Mise à jour localisation reçue:', data);
        // Vérifier que le livreur a une mission active avant de traiter
        if (data.ramassage_id || data.historique_livraison_id) {
            updateLivreurLocation(data);
        } else {
            console.log('⚠️ Position ignorée - Livreur sans mission active');
        }
    });

    // Écouter les changements de statut
    socket.on('location:status:changed', function(data) {
        console.log('📊 Changement de statut reçu:', data);
        updateLivreurStatus(data);
    });

    // Écouter les positions des livreurs (événement admin) - SEULEMENT en mission
    socket.on('admin:livreur:location', function(data) {
        console.log('🚚 Position livreur reçue (admin):', data);
        // Vérifier que le livreur a une mission active
        if (data.ramassage_id || data.historique_livraison_id) {
            updateLivreurLocation(data);
        } else {
            console.log('⚠️ Position admin ignorée - Livreur sans mission active');
        }
    });

    // Écouter les événements de position en temps réel
    socket.on('livreur:location:update', function(data) {
        console.log('🔄 Position temps réel reçue:', data);
        updateLivreurLocation(data);
    });

    // Écouter les notifications de suivi
    socket.on('livreur:tracking:start', function(data) {
        console.log('🚀 Livreur commence le suivi:', data);
        addLivreurToMap(data);
    });

    socket.on('livreur:tracking:stop', function(data) {
        console.log('⏹️ Livreur arrête le suivi:', data);
        removeLivreurFromMap(data);
    });
}

// Initialisation de la carte
function initMap() {
    try {
        console.log('📏 Création de la carte OpenStreetMap admin...');

        // Créer la carte avec Leaflet et OpenStreetMap
        map = L.map('admin-map').setView([5.316667, -4.033333], 13);

        // Ajouter les tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Données initiales des livreurs (TOUS les livreurs en mission, même sans position)
        var initialLivreurs = [
            @foreach($livreursWithActiveMissions as $livreur)
            {
                id: {{ $livreur->id }},
                nom: "{{ $livreur->first_name }} {{ $livreur->last_name }}",
                @if($livreur->lastLocation)
                position: [{{ $livreur->lastLocation->latitude }}, {{ $livreur->lastLocation->longitude }}],
                statut: "{{ $livreur->locationStatus ? ucfirst($livreur->locationStatus->status) : 'Hors ligne' }}",
                couleur: "{{ $livreur->locationStatus && $livreur->locationStatus->status == 'en_cours' ? 'green' : ($livreur->locationStatus && $livreur->locationStatus->status == 'en_pause' ? 'orange' : 'red') }}",
                zone: "{{ $livreur->lastLocation->timestamp->format('H:i:s') }}",
                accuracy: {{ $livreur->lastLocation->accuracy }},
                speed: {{ $livreur->lastLocation->speed }},
                hasLocation: true
                @else
                position: [5.316667, -4.033333], // Position par défaut (Abidjan)
                statut: "En mission (pas de GPS)",
                couleur: "blue",
                zone: "Position par défaut",
                accuracy: 0,
                speed: 0,
                hasLocation: false
                @endif
            },
            @endforeach
        ];

        // Ajouter les marqueurs initiaux
        console.log('🏍️ Ajout des marqueurs initiaux:', initialLivreurs);
        initialLivreurs.forEach(function(livreur) {
            addLivreurMarker(livreur);
        });

        // Les vrais livreurs seront ajoutés via Socket.IO
        console.log('📡 En attente des livreurs connectés via Socket.IO...');

        // Mettre à jour l'heure
        updateLastUpdateTime();

        console.log('✅ Carte OpenStreetMap admin affichée avec marqueurs !');

    } catch (error) {
        console.error('❌ Erreur OpenStreetMap admin:', error);
        showFallbackMap();
    }
}

// Créer une icône marqueur avec couleur personnalisée
function createMotoIcon(color = 'blue') {
    const colorMap = {
        'green': 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        'orange': 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
        'red': 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        'blue': 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png'
    };

    return L.icon({
        iconUrl: colorMap[color] || colorMap['blue'],
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
}

// Ajouter un marqueur de livreur
function addLivreurMarker(livreur) {
    console.log('🏍️ Ajout du marqueur pour livreur:', livreur);

    // Couleur de l'icône selon le statut
    let iconColor = 'blue'; // Bleu par défaut
    if (livreur.couleur === 'green') {
        iconColor = 'green'; // Vert pour actif
    } else if (livreur.couleur === 'orange') {
        iconColor = 'orange'; // Orange pour pause
    } else if (livreur.couleur === 'red') {
        iconColor = 'red'; // Rouge pour hors ligne
    } else if (livreur.couleur === 'blue') {
        iconColor = 'blue'; // Bleu pour pas de GPS
    }

    // Créer le marqueur avec l'icône moto
    var marker = L.marker(livreur.position, { icon: createMotoIcon(iconColor) }).addTo(map);

    // Ajouter un popup pour chaque livreur
    let popupContent = `
        <div style="padding: 10px; text-align: center;">
            <h6 style="margin: 0 0 10px 0; color: #007bff;">🚚 ${livreur.nom}</h6>
            <p style="margin: 0; font-size: 14px;"><strong>Statut:</strong> ${livreur.statut}</p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Dernière mise à jour: ${livreur.zone}</p>
    `;

    if (livreur.hasLocation) {
        popupContent += `
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Position: ${livreur.position[0].toFixed(6)}, ${livreur.position[1].toFixed(6)}</p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Précision: ${livreur.accuracy.toFixed(1)}m | Vitesse: ${livreur.speed.toFixed(1)}km/h</p>
        `;
    } else {
        popupContent += `
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #ff6b6b;">⚠️ Pas de position GPS enregistrée</p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Position par défaut: Abidjan</p>
        `;
    }

    popupContent += `</div>`;

    marker.bindPopup(popupContent);

    // Stocker le marqueur
    markers[livreur.id] = marker;
}

// Mettre à jour la position d'un livreur
function updateLivreurLocation(data) {
    console.log('🔍 updateLivreurLocation appelée avec:', data);

    if (!map) {
        console.error('❌ Carte non initialisée !');
        return;
    }

    console.log('📦 Données admin reçues:', data);

    const livreurId = data.livreur_id || data.livreurId;
    console.log('🆔 Livreur ID extrait:', livreurId);

    // Extraire les coordonnées avec validation
    let latitude, longitude;

    if (data.location) {
        latitude = data.location.latitude;
        longitude = data.location.longitude;
    } else if (data.latitude && data.longitude) {
        latitude = data.latitude;
        longitude = data.longitude;
    } else {
        console.error('❌ Coordonnées manquantes dans les données admin:', data);
        return;
    }

    // Validation des coordonnées
    if (latitude === undefined || longitude === undefined ||
        isNaN(latitude) || isNaN(longitude)) {
        console.error('❌ Coordonnées invalides admin:', { latitude, longitude });
        return;
    }

    const newPosition = [parseFloat(latitude), parseFloat(longitude)];

    if (markers[livreurId]) {
        // Mettre à jour la position du marqueur existant avec animation
        markers[livreurId].setLatLng(newPosition);

        // Ajouter une animation de déplacement
        if (markers[livreurId].getElement()) {
            markers[livreurId].getElement().style.transition = 'all 0.5s ease-in-out';
        }

        // Mettre à jour le popup
        const accuracy = data.location?.accuracy || data.accuracy || 0;
        const speed = data.location?.speed || data.speed || 0;

        const newPopup = `
            <div style="padding: 10px; text-align: center;">
                <h6 style="margin: 0 0 10px 0; color: #007bff;">🏍️ Livreur #${livreurId}</h6>
                <p style="margin: 0; font-size: 14px;"><strong>Statut:</strong> ${data.status || 'En cours'}</p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Dernière mise à jour: ${new Date().toLocaleTimeString()}</p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Position: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}</p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Précision: ${accuracy.toFixed(1)}m | Vitesse: ${speed.toFixed(1)}km/h</p>
            </div>
        `;
        markers[livreurId].bindPopup(newPopup);

        console.log(`📍 Position mise à jour pour livreur ${livreurId}:`, newPosition);
    } else {
        // Créer un nouveau marqueur pour un livreur connecté
        const newLivreur = {
            id: livreurId,
            nom: `Livreur Connecté #${livreurId}`,
            position: newPosition,
            statut: data.status || 'En cours',
            couleur: 'green',
            zone: new Date().toLocaleTimeString(),
            accuracy: data.location?.accuracy || data.accuracy || 0,
            speed: data.location?.speed || data.speed || 0
        };
        addLivreurMarker(newLivreur);
        console.log(`🆕 Nouveau livreur connecté ajouté: ID ${livreurId}`);

        // Mettre à jour la liste des livreurs dans la sidebar
        updateLivreursList(livreurId, newLivreur);
    }

    // Mettre à jour l'heure
    updateLastUpdateTime();
}

// Mettre à jour le statut d'un livreur
function updateLivreurStatus(data) {
    const livreurId = data.livreur_id || data.livreurId;
    const status = data.status;

    if (markers[livreurId]) {
        // Changer la couleur de l'icône selon le statut
        let iconColor = 'red'; // Rouge par défaut
        if (status === 'active' || status === 'en_cours') {
            iconColor = 'green'; // Vert pour actif
        } else if (status === 'paused' || status === 'en_pause') {
            iconColor = 'orange'; // Orange pour pause
        }

        // Mettre à jour l'icône moto
        markers[livreurId].setIcon(createMotoIcon(iconColor));

        console.log(`📊 Statut mis à jour pour livreur ${livreurId}: ${status}`);
    }
}

// Ajouter un livreur à la carte
function addLivreurToMap(data) {
    if (!map) return;

    console.log('🆕 Ajout d\'un nouveau livreur à la carte:', data);

    const livreurId = data.livreur_id;
    const position = data.position || [5.316667, -4.033333]; // Position par défaut

    // Créer un nouveau marqueur avec icône verte
    const newMarker = L.marker(position, { icon: createMotoIcon('green') }).addTo(map);

    // Ajouter un popup
    newMarker.bindPopup(`
        <div style="padding: 10px; text-align: center;">
            <h6 style="margin: 0 0 10px 0; color: #007bff;">🏍️ Livreur #${livreurId}</h6>
            <p style="margin: 0; font-size: 14px;"><strong>Statut:</strong> En ligne</p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Dernière mise à jour: ${new Date().toLocaleTimeString()}</p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">Position: ${position[0].toFixed(6)}, ${position[1].toFixed(6)}</p>
        </div>
    `);

    // Stocker le marqueur
    markers[livreurId] = newMarker;

    console.log(`✅ Livreur ${livreurId} ajouté à la carte avec icône moto 🏍️`);
}

// Supprimer un livreur de la carte
function removeLivreurFromMap(data) {
    const livreurId = data.livreur_id;

    if (markers[livreurId]) {
        map.removeLayer(markers[livreurId]);
        delete markers[livreurId];
        console.log(`🗑️ Livreur ${livreurId} supprimé de la carte`);
    }
}

// Mettre à jour le statut de connexion
function updateConnectionStatus(status) {
    const statusElement = document.getElementById('connection-status');
    if (statusElement) {
        console.log('🔄 Mise à jour statut connexion:', status);

        let statusText = 'Déconnecté';
        let statusClass = 'disconnected';

        switch(status) {
            case 'connected':
                statusText = 'Connecté';
                statusClass = 'connected';
                break;
            case 'reconnecting':
                statusText = 'Reconnexion...';
                statusClass = 'reconnecting';
                break;
            case 'disconnected':
            default:
                statusText = 'Déconnecté';
                statusClass = 'disconnected';
                break;
        }

        statusElement.textContent = statusText;
        statusElement.className = `connection-status ${statusClass}`;

        console.log('✅ Statut mis à jour:', statusText, '(' + statusClass + ')');
    } else {
        console.error('❌ Élément connection-status non trouvé');
    }
}

// Mettre à jour l'heure de dernière mise à jour
function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('last-update');
    if (lastUpdateElement) {
        lastUpdateElement.textContent = new Date().toLocaleTimeString();
    }
}

// Mettre à jour la liste des livreurs dans la sidebar
function updateLivreursList(livreurId, livreurData) {
    const livreursList = document.getElementById('livreurs-list');
    if (!livreursList) return;

    // Vérifier si le livreur existe déjà dans la liste
    let existingItem = document.getElementById(`livreur-${livreurId}`);

    if (!existingItem) {
        // Créer un nouvel élément pour le livreur
        const livreurItem = document.createElement('div');
        livreurItem.id = `livreur-${livreurId}`;
        livreurItem.className = 'livreur-item';

        const statusClass = livreurData.statut === 'En cours' ? 'bg-success' :
                           livreurData.statut === 'En pause' ? 'bg-warning' : 'bg-secondary';
        const statusText = livreurData.statut === 'En cours' ? 'En ligne' :
                          livreurData.statut === 'En pause' ? 'En pause' : 'Hors ligne';

        livreurItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span>${livreurData.nom}</span>
                <span class="badge ${statusClass}">${statusText}</span>
            </div>
            <small class="text-muted">
                ${livreurData.position[0].toFixed(4)}, ${livreurData.position[1].toFixed(4)}
                <br>Dernière mise à jour: ${livreurData.zone}
            </small>
        `;

        livreursList.appendChild(livreurItem);
        console.log(`📋 Livreur ${livreurId} ajouté à la liste sidebar`);
    } else {
        // Mettre à jour l'élément existant
        const statusElement = existingItem.querySelector('.badge');
        const positionElement = existingItem.querySelector('small');

        if (statusElement) {
            const statusClass = livreurData.statut === 'En cours' ? 'bg-success' :
                               livreurData.statut === 'En pause' ? 'bg-warning' : 'bg-secondary';
            const statusText = livreurData.statut === 'En cours' ? 'En ligne' :
                              livreurData.statut === 'En pause' ? 'En pause' : 'Hors ligne';

            statusElement.className = `badge ${statusClass}`;
            statusElement.textContent = statusText;
        }

        if (positionElement) {
            positionElement.innerHTML = `
                ${livreurData.position[0].toFixed(4)}, ${livreurData.position[1].toFixed(4)}
                <br>Dernière mise à jour: ${livreurData.zone}
            `;
        }

        console.log(`📋 Livreur ${livreurId} mis à jour dans la liste sidebar`);
    }
}

// Carte de fallback
function showFallbackMap() {
    console.log('⚠️ OpenStreetMap non disponible, utilisation d\'une carte simple...');
    var container = document.getElementById('admin-map');
    container.innerHTML = `
        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #e0e0e0, #f0f0f0); display: flex; align-items: center; justify-content: center; flex-direction: column; color: #666;">
            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 10px;"></i>
            <h4>Centre Admin: 5.316667, -4.033333</h4>
            <p>Abidjan, Côte d'Ivoire</p>
            <p style="font-size: 12px; margin-top: 20px;">Carte simple (OpenStreetMap non disponible)</p>
        </div>
    `;
}

// Initialiser les contrôles
function initControls() {
    // Sélecteur de livreur
    const livreurSelector = document.getElementById('livreur-selector');
    if (livreurSelector) {
        livreurSelector.addEventListener('change', function() {
            const livreurId = this.value;
            if (livreurId) {
                selectedLivreurId = livreurId;
                centerOnLivreur(livreurId);
                showLivreurDetails(livreurId);
                document.getElementById('center-on-livreur').disabled = false;
            } else {
                selectedLivreurId = null;
                showAllLivreurs();
                hideLivreurDetails();
                document.getElementById('center-on-livreur').disabled = true;
            }
        });
    }

    // Bouton centrer sur livreur
    const centerButton = document.getElementById('center-on-livreur');
    if (centerButton) {
        centerButton.addEventListener('click', function() {
            if (selectedLivreurId) {
                centerOnLivreur(selectedLivreurId);
            }
        });
    }

    // Bouton voir tous
    const showAllButton = document.getElementById('show-all-livreurs');
    if (showAllButton) {
        showAllButton.addEventListener('click', function() {
            showAllLivreurs();
            document.getElementById('livreur-selector').value = '';
            selectedLivreurId = null;
            hideLivreurDetails();
            document.getElementById('center-on-livreur').disabled = true;
        });
    }

    // Filtres par type de mission
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            filterLivreursByType(filter);

            // Mettre à jour l'état des boutons
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

// Centrer sur un livreur spécifique
function centerOnLivreur(livreurId) {
    if (markers[livreurId]) {
        // Centrer la carte sur le livreur sélectionné
        map.setView(markers[livreurId].getLatLng(), 15);

        // Ouvrir le popup du livreur
        markers[livreurId].openPopup();

        // Mettre en évidence le marqueur
        highlightLivreurMarker(livreurId);

        // Mettre en évidence dans la liste
        highlightLivreurInList(livreurId);

        console.log(`🎯 Centré sur le livreur ${livreurId}`);
    } else {
        console.log(`⚠️ Marqueur non trouvé pour le livreur ${livreurId}`);
    }
}

// Mettre en évidence un marqueur de livreur
function highlightLivreurMarker(livreurId) {
    // Réinitialiser tous les marqueurs
    Object.keys(markers).forEach(id => {
        if (markers[id].getIcon) {
            markers[id].setIcon(createMotoIcon('blue'));
        }
    });

    // Mettre en évidence le livreur sélectionné
    if (markers[livreurId]) {
        markers[livreurId].setIcon(createMotoIcon('red'));
        markers[livreurId].setZIndexOffset(1000);
    }
}

// Mettre en évidence un livreur dans la liste
function highlightLivreurInList(livreurId) {
    // Réinitialiser tous les éléments de liste
    document.querySelectorAll('.livreur-item').forEach(item => {
        item.classList.remove('highlighted');
    });

    // Mettre en évidence le livreur sélectionné
    const livreurItem = document.querySelector(`[data-livreur-id="${livreurId}"]`);
    if (livreurItem) {
        livreurItem.classList.add('highlighted');
    }
}

// Afficher tous les livreurs
function showAllLivreurs() {
    // Réinitialiser tous les marqueurs
    Object.keys(markers).forEach(id => {
        if (markers[id].getIcon) {
            markers[id].setIcon(createMotoIcon('blue'));
        }
    });

    // Réinitialiser la liste
    document.querySelectorAll('.livreur-item').forEach(item => {
        item.classList.remove('highlighted');
    });

    // Centrer sur la vue d'ensemble
    if (Object.keys(markers).length > 0) {
        const group = new L.featureGroup(Object.values(markers));
        map.fitBounds(group.getBounds().pad(0.1));
    }

    console.log('🌍 Vue d\'ensemble de tous les livreurs');
}

// Filtrer les livreurs par type
function filterLivreursByType(filter) {
    currentFilter = filter;

    document.querySelectorAll('.livreur-item').forEach(item => {
        const missionType = item.getAttribute('data-mission-type');

        if (filter === 'all' || missionType === filter) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });

    console.log(`🔍 Filtre appliqué: ${filter}`);
}

// Afficher les détails d'un livreur
function showLivreurDetails(livreurId) {
    const detailsDiv = document.getElementById('livreur-details');
    const infoDiv = document.getElementById('selected-livreur-info');

    if (markers[livreurId]) {
        const position = markers[livreurId].getLatLng();
        const popup = markers[livreurId].getPopup();

        infoDiv.innerHTML = `
            <div class="info-item">
                <span class="label">ID:</span>
                <span class="value">${livreurId}</span>
            </div>
            <div class="info-item">
                <span class="label">Position:</span>
                <span class="value">${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}</span>
            </div>
            <div class="info-item">
                <span class="label">Statut:</span>
                <span class="value">En mission</span>
            </div>
        `;

        detailsDiv.style.display = 'block';
    }
}

// Masquer les détails du livreur
function hideLivreurDetails() {
    document.getElementById('livreur-details').style.display = 'none';
}

// Recharger la page toutes les 30 secondes pour les mises à jour
setInterval(function() {
    if (!isConnected) {
        console.log('🔄 Tentative de reconnexion...');
        location.reload();
    }
}, 30000);
</script>
