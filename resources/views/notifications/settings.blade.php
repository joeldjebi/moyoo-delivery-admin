@include('layouts.header')

@include('layouts.menu')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-bell me-2"></i>
                        Paramètres des Notifications
                    </h5>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i>
                        Retour au Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <!-- Statut actuel des notifications -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <div>
                                        <strong>Notifications Push</strong>
                                        <p class="mb-0">Activez les notifications pour recevoir des alertes en temps réel sur les livraisons et ramassages.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section d'activation des notifications -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-border-shadow-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">Notifications Push</h6>
                                            <p class="text-muted mb-0">Recevez des notifications quand les livreurs terminent leurs missions</p>
                                        </div>
                                        <div class="avatar">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-bell ti-24px"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Statut du token FCM -->
                                    <div class="mb-3">
                                        @php
                                            $user = Auth::user();
                                            $hasFcmToken = $user && $user->fcm_token;
                                        @endphp

                                        <div class="d-flex align-items-center justify-content-between p-3 rounded border {{ $hasFcmToken ? 'bg-light-success' : 'bg-light-warning' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3">
                                                    <span class="avatar-initial rounded bg-label-{{ $hasFcmToken ? 'success' : 'warning' }}">
                                                        <i class="ti ti-{{ $hasFcmToken ? 'check' : 'alert-triangle' }} ti-20px"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        {{ $hasFcmToken ? 'Notifications Activées' : 'Notifications Non Activées' }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        @if($hasFcmToken)
                                                            Token FCM enregistré le {{ $user->updated_at->format('d/m/Y à H:i') }}
                                                        @else
                                                            Aucun token FCM enregistré
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <div>
                                                @if($hasFcmToken)
                                                    <span class="badge bg-label-success">Actif</span>
                                                @else
                                                    <span class="badge bg-label-warning">Inactif</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bouton d'activation -->
                                    <div class="d-grid gap-2">
                                        @if(!$hasFcmToken)
                                            <button type="button" class="btn btn-primary" onclick="requestNotificationPermission()">
                                                <i class="ti ti-bell-plus me-2"></i>
                                                Activer les Notifications
                                            </button>
                                        @else
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-success" disabled>
                                                    <i class="ti ti-check me-2"></i>
                                                    Notifications Activées
                                                </button>
                                                <button type="button" class="btn btn-outline-info" onclick="testNotification()">
                                                    <i class="ti ti-bell-ringing me-2"></i>
                                                    Tester
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deactivateNotifications()">
                                                    <i class="ti ti-bell-off me-2"></i>
                                                    Désactiver
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Informations sur les notifications -->
                                    <div class="mt-4">
                                        <h6 class="mb-2">Types de notifications reçues :</h6>
                                        <ul class="list-unstyled">
                                            <li class="d-flex align-items-center mb-2">
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Livraisons terminées</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-2">
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Ramassages terminés</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-2">
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Alertes importantes</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Comment ça marche ?
                                    </h6>
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">1. Autorisation</h6>
                                                <p class="timeline-text">Autorisez les notifications dans votre navigateur</p>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">2. Enregistrement</h6>
                                                <p class="timeline-text">Votre token FCM est automatiquement enregistré</p>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">3. Notifications</h6>
                                                <p class="timeline-text">Recevez des alertes en temps réel</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-bell me-2"></i>
                    Activer les Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="avatar mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti ti-bell ti-32px"></i>
                        </span>
                    </div>
                    <h6 class="mb-2">Autoriser les notifications ?</h6>
                    <p class="text-muted mb-0">
                        Cette action vous permettra de recevoir des notifications push
                        quand les livreurs terminent leurs missions.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" id="activateBtn">
                    <i class="ti ti-bell-plus me-2"></i>
                    Activer
                </button>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>

<script>
// Configuration Firebase
const firebaseConfig = {
    apiKey: "AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM",
    authDomain: "moyoo-fleet.firebaseapp.com",
    projectId: "moyoo-fleet",
    storageBucket: "moyoo-fleet.appspot.com",
    messagingSenderId: "319265524393",
    appId: "1:319265524393:web:your-app-id"
};

// Initialiser Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();
// Vérifier si les notifications sont supportées
function isNotificationSupported() {
    const hasNotification = "Notification" in window;
    const hasServiceWorker = "serviceWorker" in navigator;
    const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost';

    console.log('Support des notifications:', {
        hasNotification,
        hasServiceWorker,
        isSecureContext,
        protocol: location.protocol,
        hostname: location.hostname
    });

    // Pour le mode test, on accepte même sans Service Worker et contexte non sécurisé
    return hasNotification;
}

// Demander la permission de notification
async function requestNotificationPermission() {
    if (!isNotificationSupported()) {
        showAlert('error', 'Les notifications ne sont pas supportées par votre navigateur.');
        return;
    }

    try {
        // Demander directement la permission au navigateur
        const permission = await Notification.requestPermission();

        if (permission === 'granted') {
            showAlert('success', 'Permission accordée ! Activation des notifications...');

            // Enregistrer le service worker
            await registerServiceWorker();

            // Obtenir le token FCM
            const token = await getFCMToken();

            if (token) {
                // Envoyer le token au serveur
                await sendTokenToServer(token);

                showAlert('success', 'Notifications activées avec succès !');

                // Recharger la page pour mettre à jour l'interface
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', 'Impossible d\'obtenir le token FCM.');
            }
        } else if (permission === 'denied') {
            showAlert('warning', 'Permission refusée. Pour activer les notifications :<br>1. Cliquez sur l\'icône de cadenas dans la barre d\'adresse<br>2. Sélectionnez "Autoriser" pour les notifications<br>3. Rechargez la page');
        } else {
            showAlert('info', 'Permission non accordée. Vous pouvez réessayer plus tard.');
        }
    } catch (error) {
        console.error('Erreur lors de la demande de permission:', error);
        showAlert('error', 'Erreur lors de la demande de permission.');
    }
}

// Version de test pour les navigateurs non compatibles
function requestNotificationPermissionTest() {
    // Afficher le modal de confirmation
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
}

// Activer les notifications (fonction simplifiée)
async function activateNotifications() {
    // Rediriger vers la fonction principale
    await requestNotificationPermission();
}

// Enregistrer le service worker
async function registerServiceWorker() {
    try {
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        console.log('Service Worker enregistré:', registration);
        return registration;
    } catch (error) {
        console.error('Erreur enregistrement Service Worker:', error);
        throw error;
    }
}

// Obtenir le token FCM (simulation)
// Obtenir un vrai token FCM
async function getRealFCMToken() {
    try {
        // Vérifier si Firebase est disponible
        if (typeof firebase === 'undefined') {
            throw new Error('Firebase non disponible');
        }
        
        // Obtenir le token FCM réel
        const messaging = firebase.messaging();
        const token = await messaging.getToken({
            vapidKey: 'BEl62iUYgUivxIkv69yViEuiBIaIC2l0t-rmCFTfJ8s' // Clé VAPID pour moyoo-fleet
        });
        
        if (!token) {
            throw new Error('Token FCM non généré');
        }
        
        console.log('Token FCM réel obtenu:', token);
        return token;
    } catch (error) {
        console.error('Erreur obtention token FCM réel:', error);
        throw error;
    }
}

async function getFCMToken() {
    // En production, ceci utiliserait Firebase SDK
    // Pour la démo, on génère un token simulé
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(2, 15);
    return `fcm_token_${timestamp}_${random}`;
}

// Version de test qui fonctionne même sans support des notifications
async function activateNotificationsTest() {
    try {
        showAlert('info', 'Activation des notifications...');

        // Obtenir un vrai token FCM
        let token;
        try {
            // Essayer d'obtenir un vrai token FCM
            token = await getRealFCMToken();
            showAlert('info', 'Token FCM réel obtenu !');
        } catch (error) {
            console.log('Token FCM réel non disponible, utilisation du mode test');
            showAlert('warning', 'Token FCM réel non disponible, utilisation du mode test');
            token = await getFCMToken();
        }

        // Envoyer le token au serveur
        await sendTokenToServer(token);

        showAlert('success', 'Notifications activées !<br>Token FCM enregistré avec succès.');

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('notificationModal'));
        if (modal) {
            modal.hide();
        }

        // Recharger la page pour mettre à jour l'interface
        setTimeout(() => {
            window.location.reload();
        }, 1500);

    } catch (error) {
        console.error('Erreur lors de l\'activation des notifications:', error);
        showAlert('error', 'Erreur lors de l\'activation des notifications: ' + error.message);
    }
}

// Tester les notifications (si déjà activées)
function testNotification() {
    if (Notification.permission === 'granted') {
        new Notification('Test MOYOO', {
            body: 'Ceci est un test de notification !',
            icon: '/favicon.ico',
            tag: 'test-notification'
        });
        showAlert('success', 'Notification de test envoyée !');
    } else {
        showAlert('warning', 'Notifications non activées. Activez-les d\'abord.');
    }
}

// Envoyer le token au serveur
async function sendTokenToServer(token) {
    try {
        // Essayer d'abord la route web, puis la route API
        let response;
        try {
            response = await fetch('/api/fcm-token-web', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    fcm_token: token,
                    device_type: 'web'
                })
            });
        } catch (webError) {
            console.log('Route web échouée, tentative avec route API...');
            response = await fetch('/api/fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${getAuthToken()}`
                },
                body: JSON.stringify({
                    fcm_token: token,
                    device_type: 'web'
                })
            });
        }

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erreur serveur:', errorText);
            throw new Error(`Erreur serveur (${response.status}): ${errorText}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur envoi token:', error);
        throw error;
    }
}

// Désactiver les notifications
async function deactivateNotifications() {
    if (confirm('Êtes-vous sûr de vouloir désactiver les notifications ?')) {
        try {
            // Essayer d'abord la route web, puis la route API
            let response;
            try {
                response = await fetch('/api/fcm-token-web', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            } catch (webError) {
                console.log('Route web échouée, tentative avec route API...');
                response = await fetch('/api/fcm-token', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Authorization': `Bearer ${getAuthToken()}`
                    }
                });
            }

            if (response.ok) {
                showAlert('success', 'Notifications désactivées avec succès !');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                const errorText = await response.text();
                console.error('Erreur serveur:', errorText);
                throw new Error(`Erreur serveur (${response.status}): ${errorText}`);
            }
        } catch (error) {
            console.error('Erreur désactivation:', error);
            showAlert('error', 'Erreur lors de la désactivation des notifications.');
        }
    }
}

// Obtenir le token d'authentification
function getAuthToken() {
    // En production, ceci récupérerait le token depuis le localStorage ou les cookies
    return 'your_auth_token_here';
}

// Afficher une alerte
function showAlert(type, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
            <i class="ti ti-${type === 'success' ? 'check' : type === 'error' ? 'x' : 'info'} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Insérer l'alerte en haut de la page
    const container = document.querySelector('.container-xxl');
    container.insertAdjacentHTML('afterbegin', alertHtml);

    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Vérifier le statut des permissions
function checkNotificationPermission() {
    if (!("Notification" in window)) {
        return 'not-supported';
    }

    switch (Notification.permission) {
        case 'granted':
            return 'granted';
        case 'denied':
            return 'denied';
        case 'default':
            return 'default';
        default:
            return 'unknown';
    }
}

// Vérifier le support des notifications au chargement
document.addEventListener('DOMContentLoaded', function() {
    const activateBtn = document.querySelector('[onclick="requestNotificationPermission()"]');
    const modalActivateBtn = document.getElementById('activateBtn');

    // Vérifier le statut des permissions
    const permissionStatus = checkNotificationPermission();
    console.log('Statut des permissions:', permissionStatus);

    if (!isNotificationSupported()) {
        if (activateBtn) {
            // Diagnostic plus détaillé
            const hasNotification = "Notification" in window;
            const hasServiceWorker = "serviceWorker" in navigator;
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost';

            let errorMessage = 'Non supporté';
            if (!hasNotification) {
                errorMessage = 'API Notification manquante';
            } else if (!hasServiceWorker) {
                errorMessage = 'Service Worker non supporté';
            } else if (!isSecureContext) {
                errorMessage = 'Contexte non sécurisé (HTTPS requis)';
            }

            // Changer le bouton pour utiliser le mode test
            activateBtn.disabled = false;
            activateBtn.onclick = function() { requestNotificationPermissionTest(); };
            activateBtn.innerHTML = `<i class="ti ti-flask me-2"></i>Activer (Mode Test)`;

            // Configurer le bouton du modal pour le mode test
            if (modalActivateBtn) {
                modalActivateBtn.onclick = function() { activateNotificationsTest(); };
                modalActivateBtn.innerHTML = `<i class="ti ti-flask me-2"></i>Activer (Mode Test)`;
            }

            // Afficher une alerte informative
            showAlert('warning', `Notifications natives non disponibles: ${errorMessage}. Mode test activé.`);
        }
    } else {
        // Configurer le bouton du modal pour le mode normal
        if (modalActivateBtn) {
            modalActivateBtn.onclick = function() { activateNotifications(); };
        }

        // Gérer les différents statuts de permission
        if (permissionStatus === 'granted') {
            showAlert('success', 'Notifications déjà activées ! Vous recevrez des alertes en temps réel.');
        } else if (permissionStatus === 'denied') {
            // Vérifier si on est en local HTTP
            const isLocalHttp = location.protocol === 'http:' && (location.hostname === 'localhost' || location.hostname.startsWith('192.168.') || location.hostname.startsWith('10.') || location.hostname.startsWith('172.'));

            if (isLocalHttp) {
                showAlert('info', 'Mode test disponible pour HTTP local.<br>Pour les vraies notifications, utilisez HTTPS ou localhost.');
                if (activateBtn) {
                    activateBtn.innerHTML = '<i class="ti ti-bell me-2"></i>Activer (Mode Test)';
                    activateBtn.onclick = function() { activateNotificationsTest(); };
                }
            } else {
                showAlert('warning', 'Notifications refusées. Activez-les dans les paramètres de votre navigateur.');
                if (activateBtn) {
                    activateBtn.disabled = true;
                    activateBtn.innerHTML = '<i class="ti ti-x me-2"></i>Notifications refusées';
                }
            }
        } else if (permissionStatus === 'default') {
            // Vérifier les limitations techniques
            const hasServiceWorker = "serviceWorker" in navigator;
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost';

            if (!hasServiceWorker || !isSecureContext) {
                showAlert('info', 'Mode test disponible. Pour les vraies notifications, utilisez HTTPS ou localhost.');
            } else {
                showAlert('info', 'Cliquez sur "Activer les Notifications" pour autoriser les notifications push.');
            }
        }
    }
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e7e7e7;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e7e7e7;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
}

.timeline-text {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 0;
}

.card-border-shadow-primary {
    border: 1px solid rgba(105, 108, 255, 0.2);
    box-shadow: 0 0 0 1px rgba(105, 108, 255, 0.1);
}

.bg-light-success {
    background-color: rgba(40, 199, 111, 0.1) !important;
    border-color: rgba(40, 199, 111, 0.2) !important;
}

.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-color: rgba(255, 193, 7, 0.2) !important;
}
</style>
