// Service Worker pour Firebase Cloud Messaging (simulation)
// En production, ce fichier serait généré par Firebase SDK

console.log('Service Worker pour Firebase Cloud Messaging chargé');

// Écouter les messages de notification
self.addEventListener('message', function(event) {
    console.log('Message reçu dans le service worker:', event.data);
    
    if (event.data && event.data.type === 'FCM_MESSAGE') {
        // Traiter le message FCM
        const notificationData = event.data.notification;
        
        // Afficher la notification
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'moyoo-notification',
            data: event.data.data
        });
    }
});

// Écouter les clics sur les notifications
self.addEventListener('notificationclick', function(event) {
    console.log('Notification cliquée:', event.notification);
    
    event.notification.close();
    
    // Ouvrir l'application
    event.waitUntil(
        clients.openWindow('/dashboard')
    );
});

// Écouter les messages push
self.addEventListener('push', function(event) {
    console.log('Push message reçu:', event);
    
    if (event.data) {
        const data = event.data.json();
        console.log('Données du push:', data);
        
        const options = {
            body: data.notification.body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'moyoo-notification',
            data: data.data
        };
        
        event.waitUntil(
            self.registration.showNotification(data.notification.title, options)
        );
    }
});

// Gérer l'installation du service worker
self.addEventListener('install', function(event) {
    console.log('Service Worker installé');
    self.skipWaiting();
});

// Gérer l'activation du service worker
self.addEventListener('activate', function(event) {
    console.log('Service Worker activé');
    event.waitUntil(self.clients.claim());
});
