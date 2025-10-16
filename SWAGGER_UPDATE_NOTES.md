# 📚 Mise à Jour Swagger - MOYOO Fleet v2.0.0

## 🎉 **Nouvelles Fonctionnalités Documentées**

### 1. **🔥 Notifications Firebase**
- **Nouveau contrôleur** : `FirebaseNotificationController`
- **Endpoints ajoutés** :
  - `POST /api/admin/firebase/test-notification` - Tester les notifications
  - `GET /api/admin/firebase/status` - Vérifier la configuration Firebase
  - `POST /api/admin/firebase/send-to-livreur` - Envoyer notification à un livreur
  - `POST /api/admin/firebase/send-to-marchand` - Envoyer notification à un marchand

### 2. **📱 Gestion des Tokens FCM**
- **Contrôleur** : `FcmTokenController`
- **Endpoints** :
  - `POST /api/livreur/fcm-token` - Mettre à jour token FCM livreur
  - `DELETE /api/livreur/fcm-token` - Supprimer token FCM livreur
  - `POST /api/marchand/fcm-token` - Mettre à jour token FCM marchand
  - `DELETE /api/marchand/fcm-token` - Supprimer token FCM marchand

### 3. **🚚 Corrections des Routes de Livraison**
- **Route corrigée** : `POST /api/livreur/colis/{id}/start-delivery` (était PUT)
- **Route corrigée** : `POST /api/livreur/colis/{id}/complete-delivery` (était /complete)
- **Route corrigée** : `POST /api/livreur/colis/{id}/cancel-delivery` (était /cancel)

## 📋 **Tags Swagger Mis à Jour**

### **Nouveaux Tags :**
- **FCM Token** : Gestion des tokens FCM
- **Firebase Notifications** : Notifications push Firebase
- **Test** : Endpoints de test

### **Tags Existants :**
- **Authentification** : Login/logout livreurs
- **Profil** : Gestion du profil
- **Livraison Livreur** : Gestion des livraisons
- **Ramassage Livreur** : Gestion des ramassages
- **Statistiques** : Rapports et stats

## 🔧 **Configuration Swagger**

### **Informations Générales :**
- **Titre** : API MOYOO Fleet - Delivery & Notifications
- **Version** : 2.0.0
- **Serveur** : http://192.168.1.9:8000
- **Authentification** : JWT Bearer Token

### **Sécurité :**
- **bearerAuth** : Authentification JWT pour tous les endpoints protégés
- **Tokens FCM** : Gestion sécurisée des tokens de notification

## 🧪 **Endpoints de Test**

### **Test API Général :**
```bash
GET /api/test
```

### **Test Notifications Firebase :**
```bash
POST /api/admin/firebase/test-notification
{
  "token": "fcm_token_example",
  "title": "Test MOYOO",
  "body": "Ceci est un test de notification"
}
```

### **Vérifier Configuration Firebase :**
```bash
GET /api/admin/firebase/status
```

## 📱 **Exemples d'Utilisation**

### **1. Mettre à jour le token FCM d'un livreur :**
```bash
POST /api/livreur/fcm-token
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "fcm_token": "fcm_token_from_mobile_app",
  "device_type": "android"
}
```

### **2. Démarrer une livraison :**
```bash
POST /api/livreur/colis/1/start-delivery
Authorization: Bearer YOUR_JWT_TOKEN
```

### **3. Finaliser une livraison :**
```bash
POST /api/livreur/colis/1/complete-delivery
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: multipart/form-data

{
  "code_validation": "ABC123",
  "photo_proof": [FILE],
  "note_livraison": "Livraison effectuée avec succès",
  "latitude": 5.359952,
  "longitude": -4.008256
}
```

### **4. Envoyer une notification personnalisée :**
```bash
POST /api/admin/firebase/send-to-livreur
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "livreur_id": 1,
  "title": "Nouvelle mission",
  "body": "Vous avez une nouvelle mission assignée",
  "data": {
    "type": "mission",
    "mission_id": 123
  }
}
```

## 🔄 **Notifications Automatiques**

### **Déclenchées automatiquement :**
- **Nouveau colis assigné** → Notification au livreur
- **Nouveau ramassage assigné** → Notification au livreur
- **Colis livré** → Notification au marchand
- **Ramassage effectué** → Notification au marchand
- **Colis annulé** → Notification au livreur

### **Types de notifications :**
- **new_colis** : Nouveau colis assigné
- **new_ramassage** : Nouveau ramassage assigné
- **colis_delivered** : Colis livré
- **ramassage_completed** : Ramassage effectué
- **colis_cancelled** : Colis annulé

## 📊 **Réponses API Standardisées**

### **Succès :**
```json
{
  "success": true,
  "message": "Message de succès",
  "data": { ... }
}
```

### **Erreur :**
```json
{
  "success": false,
  "message": "Message d'erreur",
  "error": "Détails de l'erreur"
}
```

## 🚀 **Accès à la Documentation**

### **URL Swagger :**
- **Interface** : http://192.168.1.9:8000/api/documentation
- **JSON** : http://192.168.1.9:8000/api/documentation.json

### **Regénération :**
```bash
php artisan l5-swagger:generate
```

## 📋 **Checklist de Mise à Jour**

- [x] **Contrôleur Firebase** créé avec annotations complètes
- [x] **Routes Firebase** ajoutées
- [x] **Tags Swagger** mis à jour
- [x] **Informations générales** mises à jour
- [x] **Documentation** générée
- [x] **Exemples d'utilisation** fournis
- [x] **Endpoints de test** documentés

## 🎯 **Prochaines Étapes**

1. **Tester les endpoints** avec Postman ou l'interface Swagger
2. **Vérifier la configuration Firebase** avec `/api/admin/firebase/status`
3. **Tester les notifications** avec un token FCM réel
4. **Intégrer dans l'app mobile** pour récupérer les tokens FCM

---

**🎉 La documentation Swagger est maintenant à jour avec toutes les nouvelles fonctionnalités Firebase !**
