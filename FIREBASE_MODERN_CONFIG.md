# 🔥 Configuration Firebase Moderne (2024)

## ⚠️ **Important : Server Key Dépréciée**

Firebase a déprécié la **Server Key (Legacy)**. Voici les nouvelles méthodes recommandées :

## 🚀 **Méthode 1 : Service Account Key (Recommandée)**

### 1. **Obtenir le Service Account Key**

1. **Allez sur [Firebase Console](https://console.firebase.google.com/)**
2. **Sélectionnez votre projet**
3. **Allez dans Paramètres du projet** (icône d'engrenage)
4. **Cliquez sur l'onglet "Comptes de service"**
5. **Cliquez sur "Générer une nouvelle clé privée"**
6. **Téléchargez le fichier JSON**

### 2. **Configuration dans .env**

```env
# Configuration Firebase Moderne
FIREBASE_PROJECT_ID=votre-project-id

# Service Account Key (méthode recommandée)
FIREBASE_SA_TYPE=service_account
FIREBASE_SA_PROJECT_ID=votre-project-id
FIREBASE_SA_PRIVATE_KEY_ID=votre-private-key-id
FIREBASE_SA_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nVOTRE_CLE_PRIVEE\n-----END PRIVATE KEY-----\n"
FIREBASE_SA_CLIENT_EMAIL=firebase-adminsdk-xxxxx@votre-project.iam.gserviceaccount.com
FIREBASE_SA_CLIENT_ID=votre-client-id
FIREBASE_SA_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_SA_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_SA_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_SA_CLIENT_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40votre-project.iam.gserviceaccount.com
```

### 3. **Exemple de fichier JSON Service Account**

```json
{
  "type": "service_account",
  "project_id": "votre-project-id",
  "private_key_id": "votre-private-key-id",
  "private_key": "-----BEGIN PRIVATE KEY-----\nVOTRE_CLE_PRIVEE\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@votre-project.iam.gserviceaccount.com",
  "client_id": "votre-client-id",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40votre-project.iam.gserviceaccount.com"
}
```

## 🛠️ **Méthode 2 : Google Cloud CLI (Alternative)**

### 1. **Installer Google Cloud CLI**

```bash
# macOS
brew install google-cloud-sdk

# Ubuntu/Debian
curl https://sdk.cloud.google.com | bash

# Windows
# Téléchargez depuis https://cloud.google.com/sdk/docs/install
```

### 2. **Authentification**

```bash
# Se connecter à Google Cloud
gcloud auth login

# Définir le projet
gcloud config set project VOTRE_PROJECT_ID

# Tester l'authentification
gcloud auth print-access-token
```

### 3. **Configuration dans .env**

```env
# Configuration Firebase avec gcloud CLI
FIREBASE_PROJECT_ID=votre-project-id
# Pas besoin d'autres variables, le service utilisera gcloud CLI automatiquement
```

## 🧪 **Test de la Configuration**

### 1. **Test avec Service Account**

```bash
# Test simple
php artisan firebase:test --token=YOUR_FCM_TOKEN

# Test avec un livreur
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=livreur --id=1
```

### 2. **Test avec gcloud CLI**

```bash
# Vérifier que gcloud est installé
gcloud --version

# Vérifier l'authentification
gcloud auth list

# Tester l'accès à Firebase
gcloud auth print-access-token
```

## 🔧 **Dépannage**

### **Erreur "Configuration Firebase manquante"**
- Vérifiez que `FIREBASE_PROJECT_ID` est défini
- Vérifiez que toutes les variables du service account sont définies
- Redémarrez le serveur après modification du `.env`

### **Erreur "Impossible d'obtenir le token d'accès"**
- Vérifiez que la clé privée est correctement formatée
- Assurez-vous qu'il n'y a pas d'espaces dans la clé privée
- Vérifiez que le service account a les bonnes permissions

### **Erreur 403 Forbidden**
- Vérifiez que le service account a le rôle "Firebase Admin SDK Administrator Service Agent"
- Vérifiez que Cloud Messaging est activé dans Firebase

### **Erreur avec gcloud CLI**
- Vérifiez que gcloud est installé : `gcloud --version`
- Vérifiez l'authentification : `gcloud auth list`
- Vérifiez le projet : `gcloud config get-value project`

## 📱 **Permissions Requises**

Le service account doit avoir ces rôles :
- **Firebase Admin SDK Administrator Service Agent**
- **Cloud Messaging Admin** (optionnel, pour plus de permissions)

## 🚀 **Avantages de la Nouvelle Méthode**

- ✅ **Plus sécurisée** : Pas de clé statique exposée
- ✅ **Plus flexible** : Gestion des permissions granulaire
- ✅ **Conforme** : Suit les standards Google Cloud
- ✅ **Évolutive** : Compatible avec les futures versions Firebase

## 📋 **Migration depuis l'Ancienne Méthode**

1. **Gardez l'ancienne configuration** temporairement
2. **Ajoutez la nouvelle configuration** dans `.env`
3. **Testez avec la nouvelle méthode**
4. **Supprimez l'ancienne configuration** une fois que tout fonctionne

## 🎯 **Recommandation**

**Utilisez la Méthode 1 (Service Account Key)** pour la production car elle est :
- Plus sécurisée
- Plus fiable
- Conforme aux standards Google Cloud
- Facile à déployer

---

**🎉 Votre système Firebase est maintenant à jour avec les dernières recommandations !**
