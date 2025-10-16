# 🔥 Configuration Service Account Firebase - MOYOO Fleet

## 📋 **Étapes pour configurer le Service Account Key**

### **1. Obtenir le Service Account Key**

1. **Allez sur [Firebase Console](https://console.firebase.google.com/)**
2. **Sélectionnez votre projet** : `moyoo-fleet`
3. **Cliquez sur l'icône d'engrenage** (⚙️) en haut à gauche
4. **Sélectionnez "Paramètres du projet"**
5. **Allez dans l'onglet "Comptes de service"**
6. **Cliquez sur "Générer une nouvelle clé privée"**
7. **Téléchargez le fichier JSON**

### **2. Extraire les informations du fichier JSON**

Le fichier téléchargé ressemblera à ceci :
```json
{
  "type": "service_account",
  "project_id": "moyoo-fleet",
  "private_key_id": "votre-private-key-id",
  "private_key": "-----BEGIN PRIVATE KEY-----\nVOTRE_CLE_PRIVEE\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@moyoo-fleet.iam.gserviceaccount.com",
  "client_id": "votre-client-id",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40moyoo-fleet.iam.gserviceaccount.com"
}
```

### **3. Configuration dans le fichier .env**

Ajoutez ces variables dans votre fichier `.env` :

```env
# Configuration Firebase pour MOYOO Fleet
FIREBASE_PROJECT_ID=moyoo-fleet

# Service Account Key
FIREBASE_SA_TYPE=service_account
FIREBASE_SA_PROJECT_ID=moyoo-fleet
FIREBASE_SA_PRIVATE_KEY_ID=votre-private-key-id
FIREBASE_SA_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nVOTRE_CLE_PRIVEE\n-----END PRIVATE KEY-----\n"
FIREBASE_SA_CLIENT_EMAIL=firebase-adminsdk-xxxxx@moyoo-fleet.iam.gserviceaccount.com
FIREBASE_SA_CLIENT_ID=votre-client-id
FIREBASE_SA_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_SA_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_SA_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_SA_CLIENT_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40moyoo-fleet.iam.gserviceaccount.com
```

### **4. Important : Format de la clé privée**

⚠️ **ATTENTION** : La clé privée doit être correctement formatée :

```env
# ✅ CORRECT
FIREBASE_SA_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----\n"

# ❌ INCORRECT
FIREBASE_SA_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...-----END PRIVATE KEY-----"
```

### **5. Vérifier la configuration**

```bash
# Tester la configuration
php artisan firebase:config-test

# Tester l'envoi de notification
php artisan firebase:test --token=YOUR_FCM_TOKEN
```

## 🧪 **Test de la Configuration**

### **Commande de test :**
```bash
php artisan firebase:test --token=ddRCnCZETUmIJNZ2QycrEP:APA91bGGD7vLhV_lveIkDSl7-Hnn5y9-aQIkE79Lc-ckCZl7gWiANZ_8XmnmYX4fdfkdCK1PF84RCC_keYzIsNBvC3EDn8Gxyc94JP1kmffSKxTPS1hCNhk
```

### **Résultat attendu :**
```
✅ Notification envoyée avec succès !
```

## 🔧 **Dépannage**

### **Erreur "Service Account Key non configuré"**
- Vérifiez que toutes les variables `FIREBASE_SA_*` sont définies
- Vérifiez que la clé privée est correctement formatée avec `\n`

### **Erreur "Impossible de signer le JWT"**
- Vérifiez que la clé privée est correcte
- Assurez-vous qu'il n'y a pas d'espaces dans la clé
- Vérifiez que la clé commence par `-----BEGIN PRIVATE KEY-----`

### **Erreur 403 Forbidden**
- Vérifiez que le service account a les bonnes permissions
- Assurez-vous que Cloud Messaging est activé

## 📱 **Permissions Requises**

Le service account doit avoir ces rôles :
- **Firebase Admin SDK Administrator Service Agent**
- **Cloud Messaging Admin** (optionnel)

## 🎯 **Avantages du Service Account**

- ✅ **Plus sécurisé** que la Server Key
- ✅ **Conforme** aux standards Google Cloud
- ✅ **Évolutif** et maintenable
- ✅ **Gestion des permissions** granulaire

---

## 🚀 **Une fois configuré, vos notifications Firebase fonctionneront parfaitement !**

**Suivez ces étapes et testez avec la commande `php artisan firebase:test`**
