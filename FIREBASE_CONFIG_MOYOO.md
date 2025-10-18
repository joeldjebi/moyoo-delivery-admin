# 🔥 Configuration Firebase pour MOYOO Fleet

## 📋 **Informations de votre projet :**
- **Project ID** : `moyoo-fleet`
- **Project Number** : `319265524393`
- **API Key** : `AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM`

## ⚙️ **Configuration dans votre fichier .env :**

```env
# Configuration Firebase pour MOYOO Fleet
FIREBASE_PROJECT_ID=moyoo-fleet

# Méthode 1: Service Account Key (Recommandée)
# Obtenez votre Service Account Key depuis Firebase Console
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

# Méthode 2: API Key (Alternative simple)
FIREBASE_API_KEY=AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM
```

## 🚀 **Étapes pour obtenir le Service Account Key :**

### 1. **Allez sur Firebase Console**
- URL : https://console.firebase.google.com/
- Sélectionnez le projet **moyoo-fleet**

### 2. **Accédez aux Comptes de Service**
- Cliquez sur l'icône d'engrenage (Paramètres du projet)
- Allez dans l'onglet **"Comptes de service"**

### 3. **Générez une nouvelle clé**
- Cliquez sur **"Générer une nouvelle clé privée"**
- Téléchargez le fichier JSON

### 4. **Extrayez les informations du fichier JSON**
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

## 🧪 **Test de la Configuration :**

```bash
# Test simple avec un token FCM
php artisan firebase:test --token=YOUR_FCM_TOKEN

# Test avec un livreur spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=livreur --id=1

# Test avec un marchand spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=marchand --id=1
```

## 📱 **Configuration Mobile :**

### **Android (package: com.moyoofleet.delivery_app)**
```javascript
// Dans votre app React Native/Flutter
import messaging from '@react-native-firebase/messaging';

const getFCMToken = async () => {
    const token = await messaging().getToken();
    console.log('FCM Token:', token);
    return token;
};

// Envoyer le token au serveur
const updateFCMToken = async (token) => {
    const response = await fetch('http://192.168.1.5:8000/api/livreur/fcm-token', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${userToken}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            fcm_token: token,
            device_type: 'android'
        })
    });
};
```

## 🔧 **Dépannage :**

### **Erreur "Configuration Firebase manquante"**
- Vérifiez que `FIREBASE_PROJECT_ID=moyoo-fleet` est défini
- Vérifiez que toutes les variables du service account sont définies
- Redémarrez le serveur après modification du `.env`

### **Erreur 403 Forbidden**
- Vérifiez que le service account a le rôle "Firebase Admin SDK Administrator Service Agent"
- Vérifiez que Cloud Messaging est activé dans Firebase

### **Erreur 401 Unauthorized**
- Vérifiez que la clé privée est correctement formatée
- Assurez-vous qu'il n'y a pas d'espaces dans la clé privée

## 🎯 **Recommandation :**

**Utilisez la Méthode 1 (Service Account Key)** car elle est :
- Plus sécurisée
- Plus fiable
- Conforme aux standards Google Cloud
- Facile à déployer

## 📋 **Checklist de Configuration :**

- [ ] Ajouter `FIREBASE_PROJECT_ID=moyoo-fleet` dans `.env`
- [ ] Obtenir le Service Account Key depuis Firebase Console
- [ ] Ajouter toutes les variables du service account dans `.env`
- [ ] Redémarrer le serveur Laravel
- [ ] Tester avec `php artisan firebase:test`
- [ ] Configurer l'app mobile pour envoyer les tokens FCM

---

**🎉 Votre configuration Firebase pour MOYOO Fleet est prête !**
