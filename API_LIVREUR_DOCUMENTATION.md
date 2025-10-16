# 🚚 API Livreur - Documentation

## 📋 Vue d'ensemble

Cette API permet aux livreurs de s'authentifier et de gérer leurs livraisons via une application mobile. Elle utilise JWT (JSON Web Token) pour l'authentification sécurisée.

## 🔐 Authentification

### Base URL
```
http://127.0.0.1:8000/api/livreur
```

### Endpoints d'authentification

#### 1. Connexion
**POST** `/login`

**Paramètres :**
```json
{
    "mobile": "1234567890",
    "password": "password123"
}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600,
        "livreur": {
            "id": 5,
            "nom_complet": "Test Livreur",
            "mobile": "1234567890",
            "email": null,
            "status": "actif",
            "photo": null,
            "engin": {
                "id": 1,
                "nom": null,
                "type": "Moto"
            },
            "zone_activite": null
        }
    }
}
```

**Réponse d'erreur (401) :**
```json
{
    "success": false,
    "message": "Identifiants incorrects"
}
```

**Réponse d'erreur (403) :**
```json
{
    "success": false,
    "message": "Votre compte est inactif. Contactez votre administrateur."
}
```

#### 2. Déconnexion
**POST** `/logout`

**Headers requis :**
```
Authorization: Bearer {token}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Déconnexion réussie"
}
```

#### 3. Rafraîchir le token
**POST** `/refresh`

**Headers requis :**
```
Authorization: Bearer {token}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Token rafraîchi avec succès",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

## 👤 Gestion du profil

### Endpoints du profil

#### 1. Obtenir le profil
**GET** `/profile`

**Headers requis :**
```
Authorization: Bearer {token}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Profil récupéré avec succès",
    "data": {
        "id": 5,
        "nom_complet": "Test Livreur",
        "first_name": "Test",
        "last_name": "Livreur",
        "mobile": "1234567890",
        "email": null,
        "adresse": null,
        "permis": null,
        "status": "actif",
        "photo": null,
        "engin": {
            "id": 1,
            "nom": null,
            "type": "Moto"
        },
        "zone_activite": null,
        "communes": [],
        "created_at": "2025-10-12T00:17:38.000000Z",
        "updated_at": "2025-10-12T00:17:38.000000Z"
    }
}
```

#### 2. Mettre à jour le profil
**PUT** `/profile`

**Headers requis :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Paramètres (optionnels) :**
```json
{
    "first_name": "Nouveau Prénom",
    "last_name": "Nouveau Nom",
    "email": "nouveau@email.com",
    "adresse": "Nouvelle adresse",
    "photo": "fichier_image" // Multipart form data
}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Profil mis à jour avec succès",
    "data": {
        "id": 5,
        "nom_complet": "Nouveau Prénom Nouveau Nom",
        "first_name": "Nouveau Prénom",
        "last_name": "Nouveau Nom",
        "mobile": "1234567890",
        "email": "nouveau@email.com",
        "adresse": "Nouvelle adresse",
        "photo": "http://127.0.0.1:8000/storage/livreurs/photo.jpg"
    }
}
```

#### 3. Changer le mot de passe
**POST** `/change-password`

**Headers requis :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Paramètres :**
```json
{
    "current_password": "ancien_mot_de_passe",
    "new_password": "nouveau_mot_de_passe",
    "new_password_confirmation": "nouveau_mot_de_passe"
}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Mot de passe modifié avec succès"
}
```

## 🔒 Sécurité

### Headers d'authentification
Toutes les routes protégées nécessitent le header suivant :
```
Authorization: Bearer {jwt_token}
```

### Gestion des erreurs JWT
- **Token expiré (401)** : Le token a expiré, utilisez `/refresh` pour en obtenir un nouveau
- **Token invalide (401)** : Le token fourni n'est pas valide
- **Token manquant (401)** : Aucun token fourni dans les headers

### Durée de vie du token
- **Durée par défaut** : 1 heure (3600 secondes)
- **Rafraîchissement** : Utilisez l'endpoint `/refresh` avant expiration

## 📱 Utilisation dans l'application mobile

### Flux d'authentification recommandé

1. **Connexion initiale**
   ```javascript
   const response = await fetch('/api/livreur/login', {
       method: 'POST',
       headers: {
           'Content-Type': 'application/json',
       },
       body: JSON.stringify({
           mobile: '1234567890',
           password: 'password123'
       })
   });
   
   const data = await response.json();
   if (data.success) {
       // Stocker le token
       localStorage.setItem('jwt_token', data.data.token);
       localStorage.setItem('livreur_data', JSON.stringify(data.data.livreur));
   }
   ```

2. **Utilisation du token pour les requêtes**
   ```javascript
   const token = localStorage.getItem('jwt_token');
   const response = await fetch('/api/livreur/profile', {
       headers: {
           'Authorization': `Bearer ${token}`,
           'Content-Type': 'application/json',
       }
   });
   ```

3. **Gestion de l'expiration**
   ```javascript
   // Intercepter les erreurs 401 et rafraîchir le token
   if (response.status === 401) {
       const refreshResponse = await fetch('/api/livreur/refresh', {
           method: 'POST',
           headers: {
               'Authorization': `Bearer ${token}`,
           }
       });
       
       if (refreshResponse.ok) {
           const refreshData = await refreshResponse.json();
           localStorage.setItem('jwt_token', refreshData.data.token);
           // Retry la requête originale
       } else {
           // Rediriger vers la page de connexion
       }
   }
   ```

## 🧪 Tests

### Test de connexion
```bash
curl -X POST http://127.0.0.1:8000/api/livreur/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "mobile": "1234567890",
    "password": "password123"
  }'
```

### Test du profil
```bash
curl -X GET http://127.0.0.1:8000/api/livreur/profile \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {votre_token}"
```

## 📋 Prochaines étapes

Les endpoints suivants seront ajoutés dans les prochaines versions :

- **Colis assignés** : `GET /colis-assignes`
- **Détails d'un colis** : `GET /colis/{id}/details`
- **Démarrer une livraison** : `PUT /colis/{id}/start-delivery`
- **Compléter une livraison** : `PUT /colis/{id}/complete-delivery`
- **Annuler une livraison** : `PUT /colis/{id}/cancel-delivery`
- **Mise à jour de localisation** : `POST /location/update`
- **Statistiques quotidiennes** : `GET /stats/daily`
- **Historique des livraisons** : `GET /delivery-history`

## 🐛 Dépannage

### Erreurs courantes

1. **"Token invalide"** : Vérifiez que le token est correctement formaté dans le header Authorization
2. **"Identifiants incorrects"** : Vérifiez le mobile et le mot de passe
3. **"Compte inactif"** : Le livreur doit être actif dans la base de données
4. **"Token expiré"** : Utilisez l'endpoint `/refresh` pour obtenir un nouveau token

### Logs
Les erreurs sont loggées dans `storage/logs/laravel.log` pour le débogage.
