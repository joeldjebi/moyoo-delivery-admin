# 🔥 Guide d'implémentation des Notifications Firebase

## 📋 Vue d'ensemble

Ce guide explique comment utiliser le système de notifications Firebase implémenté dans votre application Laravel. Le système est conçu pour être **réutilisable**, **facile à maintenir** et **bien structuré**.

## 🏗️ Architecture

### 1. **Service Principal** - `FirebaseNotificationService`
- **Localisation** : `app/Services/FirebaseNotificationService.php`
- **Rôle** : Gère toutes les communications avec Firebase Cloud Messaging
- **Méthodes principales** :
  - `sendToToken()` - Envoyer à un token spécifique
  - `sendToTokens()` - Envoyer à plusieurs tokens
  - `sendToTopic()` - Envoyer à un topic
  - `sendNewColisNotification()` - Notification nouveau colis
  - `sendNewRamassageNotification()` - Notification nouveau ramassage
  - `sendColisDeliveredNotification()` - Notification colis livré
  - `sendRamassageCompletedNotification()` - Notification ramassage effectué

### 2. **Trait Réutilisable** - `SendsFirebaseNotifications`
- **Localisation** : `app/Traits/SendsFirebaseNotifications.php`
- **Rôle** : Facilite l'utilisation du service dans les contrôleurs
- **Avantages** :
  - Gestion automatique des erreurs
  - Logs intégrés
  - Vérification des tokens FCM
  - Interface simplifiée

### 3. **Contrôleur FCM** - `FcmTokenController`
- **Localisation** : `app/Http/Controllers/Api/FcmTokenController.php`
- **Rôle** : Gère les tokens FCM des utilisateurs
- **Endpoints** :
  - `POST /api/livreur/fcm-token` - Mettre à jour token livreur
  - `POST /api/marchand/fcm-token` - Mettre à jour token marchand
  - `DELETE /api/livreur/fcm-token` - Supprimer token livreur
  - `DELETE /api/marchand/fcm-token` - Supprimer token marchand

## ⚙️ Configuration

### 1. **Variables d'environnement**
Ajoutez dans votre fichier `.env` :
```env
FIREBASE_SERVER_KEY=AAAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 2. **Obtenir la Server Key Firebase**
1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Sélectionnez votre projet
3. Allez dans **Paramètres du projet** > **Cloud Messaging**
4. Copiez la **Server Key** (Legacy Server Key)

## 🚀 Utilisation

### 1. **Dans un Contrôleur**

```php
<?php

namespace App\Http\Controllers;

use App\Traits\SendsFirebaseNotifications;

class MonController extends Controller
{
    use SendsFirebaseNotifications;

    public function maMethode()
    {
        $livreur = Livreur::find(1);
        $colis = Colis::find(1);

        // Envoyer une notification de nouveau colis
        $result = $this->sendNewColisNotification($livreur, $colis);

        if ($result['success']) {
            // Notification envoyée avec succès
        } else {
            // Gérer l'erreur
        }
    }
}
```

### 2. **Notifications Disponibles**

#### Pour les Livreurs :
- `sendNewColisNotification($livreur, $colis)`
- `sendNewRamassageNotification($livreur, $ramassage)`
- `sendColisCancelledNotification($livreur, $colis, $reason)`

#### Pour les Marchands :
- `sendColisDeliveredNotification($marchand, $colis)`
- `sendRamassageCompletedNotification($marchand, $ramassage)`

#### Notifications Personnalisées :
- `sendCustomNotification($token, $title, $body, $data)`
- `sendToMultipleTokens($tokens, $notification, $data)`
- `sendToTopic($topic, $notification, $data)`

### 3. **Gestion des Tokens FCM**

#### Côté Mobile (React Native/Flutter) :
```javascript
// Récupérer le token FCM
import messaging from '@react-native-firebase/messaging';

const getFCMToken = async () => {
    const token = await messaging().getToken();
    return token;
};

// Envoyer le token au serveur
const updateFCMToken = async (token) => {
    const response = await fetch('http://192.168.1.9:8000/api/livreur/fcm-token', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${userToken}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            fcm_token: token,
            device_type: 'android' // ou 'ios'
        })
    });
};
```

#### Côté Serveur (Laravel) :
```php
// Le token est automatiquement mis à jour via l'API
// Aucune action supplémentaire requise
```

## 🧪 Tests

### 1. **Commande de Test**
```bash
# Test simple avec un token
php artisan firebase:test --token=YOUR_FCM_TOKEN

# Test avec un livreur spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=livreur --id=1

# Test avec un marchand spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=marchand --id=1
```

### 2. **Test Manuel via API**
```bash
# Mettre à jour le token FCM d'un livreur
curl -X POST 'http://192.168.1.9:8000/api/livreur/fcm-token' \
  -H 'Authorization: Bearer YOUR_JWT_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "fcm_token": "YOUR_FCM_TOKEN",
    "device_type": "android"
  }'
```

## 📱 Intégration Mobile

### 1. **React Native**
```javascript
// Installation
npm install @react-native-firebase/app @react-native-firebase/messaging

// Configuration
import messaging from '@react-native-firebase/messaging';

// Écouter les notifications
messaging().onMessage(async remoteMessage => {
    console.log('Notification reçue:', remoteMessage);
    // Traiter la notification
});

// Gérer les notifications en arrière-plan
messaging().setBackgroundMessageHandler(async remoteMessage => {
    console.log('Notification en arrière-plan:', remoteMessage);
});
```

### 2. **Flutter**
```dart
// Installation
dependencies:
  firebase_messaging: ^14.7.10

// Configuration
import 'package:firebase_messaging/firebase_messaging.dart';

// Écouter les notifications
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('Notification reçue: ${message.notification?.title}');
});
```

## 🔧 Maintenance

### 1. **Logs**
- Tous les envois de notifications sont loggés
- Consultez `storage/logs/laravel.log` pour les détails
- Recherchez "Firebase Notification" dans les logs

### 2. **Gestion des Erreurs**
- Les tokens invalides sont automatiquement détectés
- Les erreurs sont loggées avec le contexte complet
- Le système continue de fonctionner même en cas d'erreur

### 3. **Performance**
- Les notifications sont envoyées de manière asynchrone
- Pas de blocage de l'interface utilisateur
- Gestion optimisée des erreurs réseau

## 🚨 Dépannage

### 1. **Erreur "Firebase Server Key non configuré"**
- Vérifiez que `FIREBASE_SERVER_KEY` est défini dans `.env`
- Redémarrez le serveur après modification du `.env`

### 2. **Notifications non reçues**
- Vérifiez que le token FCM est valide
- Testez avec la commande `firebase:test`
- Vérifiez les logs pour les erreurs

### 3. **Erreur 401 Unauthorized**
- Vérifiez que la Server Key est correcte
- Assurez-vous que le projet Firebase est actif

## 📊 Exemples d'Utilisation

### 1. **Nouveau Colis Assigné**
```php
// Dans votre contrôleur de colis
public function assignerColis(Request $request, $colisId)
{
    $colis = Colis::find($colisId);
    $livreur = Livreur::find($request->livreur_id);
    
    // Assigner le colis
    $colis->update(['livreur_id' => $livreur->id]);
    
    // Envoyer la notification
    $this->sendNewColisNotification($livreur, $colis);
    
    return response()->json(['success' => true]);
}
```

### 2. **Ramassage Planifié**
```php
// Dans RamassageController (déjà implémenté)
public function planifier(Request $request, $id)
{
    // ... logique de planification ...
    
    // Envoyer la notification
    $livreur = Livreur::find($request->livreur_id);
    $this->sendNewRamassageNotification($livreur, $ramassage);
    
    // ... reste du code ...
}
```

## 🎯 Prochaines Étapes

1. **Ajoutez votre Firebase Server Key** dans le fichier `.env`
2. **Testez avec la commande** `php artisan firebase:test`
3. **Intégrez dans votre app mobile** pour récupérer les tokens FCM
4. **Ajoutez des notifications** dans d'autres parties de votre application

## 📞 Support

Pour toute question ou problème :
1. Vérifiez les logs dans `storage/logs/laravel.log`
2. Testez avec la commande `firebase:test`
3. Vérifiez la configuration Firebase dans la console

---

**🎉 Votre système de notifications Firebase est maintenant prêt à l'emploi !**
