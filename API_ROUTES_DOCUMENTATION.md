# Documentation des Routes API - Livreur

## Problème résolu ✅

Les routes API ont été corrigées dans la documentation Swagger pour correspondre aux routes réelles définies dans `routes/api.php`.

## Routes API Livreur

### 🔐 Authentification
```bash
# Connexion
POST /api/livreur/login
{
  "mobile": "1234567890",
  "password": "password123"
}

# Rafraîchir le token
POST /api/livreur/refresh

# Déconnexion (nécessite authentification)
POST /api/livreur/logout
```

### 👤 Profil
```bash
# Obtenir le profil
GET /api/livreur/profile

# Mettre à jour le profil
POST /api/livreur/profile

# Changer le mot de passe
POST /api/livreur/change-password
```

### 📦 Livraisons et Colis

#### Liste des colis assignés
```bash
GET /api/livreur/colis-assignes?statut=en_attente
```

#### Détails d'un colis
```bash
GET /api/livreur/colis/{id}/details
```

#### ⚠️ CORRECTION : Démarrer une livraison
```bash
# ❌ INCORRECT (erreur 404)
POST /api/livreur/colis/1/start

# ✅ CORRECT
POST /api/livreur/colis/1/start-delivery
```

#### ⚠️ CORRECTION : Terminer une livraison
```bash
# ❌ INCORRECT (erreur 404)
POST /api/livreur/colis/1/complete

# ✅ CORRECT
POST /api/livreur/colis/1/complete-delivery
{
  "photo_proof": "base64_image_data",
  "code_validation": "123456",
  "note_livraison": "Livré avec succès",
  "signature_data": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "latitude": 5.359952,
  "longitude": -4.008256
}
```

#### ⚠️ CORRECTION : Annuler une livraison
```bash
# ❌ INCORRECT (erreur 404)
POST /api/livreur/colis/1/cancel

# ✅ CORRECT
POST /api/livreur/colis/1/cancel-delivery
{
  "motif_annulation": "Client absent",
  "note_livraison": "Client non joignable"
}
```

#### Statistiques quotidiennes
```bash
GET /api/livreur/colis/stats
```

### 🚚 Ramassages

#### Liste des ramassages assignés
```bash
GET /api/livreur/ramassages
```

#### Détails d'un ramassage
```bash
GET /api/livreur/ramassages/{id}/details
```

#### Démarrer un ramassage
```bash
POST /api/livreur/ramassages/{id}/start
```

#### Terminer un ramassage
```bash
POST /api/livreur/ramassages/{id}/complete
{
  "photos_colis": ["base64_image1", "base64_image2"],
  "notes_ramassage": "Ramassage effectué avec succès",
  "difference_info": {
    "type": "plus",
    "nombre": 2,
    "raison": "Colis supplémentaires trouvés"
  }
}
```

#### Statistiques quotidiennes des ramassages
```bash
GET /api/livreur/ramassages/stats/daily
```

## Exemples de requêtes cURL

### Démarrer une livraison (CORRECT)
```bash
curl -X 'POST' \
  'http://192.168.1.5:8000/api/livreur/colis/1/start-delivery' \
  -H 'accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \
  -H 'Content-Type: application/json' \
  -d '{}'
```

### Démarrer un ramassage (CORRECT)
```bash
curl -X 'POST' \
  'http://192.168.1.5:8000/api/livreur/ramassages/1/start' \
  -H 'accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \
  -H 'Content-Type: application/json' \
  -d '{}'
```

### Terminer une livraison (CORRECT)
```bash
curl -X 'POST' \
  'http://192.168.1.5:8000/api/livreur/colis/1/complete-delivery' \
  -H 'accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \
  -H 'Content-Type: multipart/form-data' \
  -F 'code_validation=ABC123' \
  -F 'photo_proof=@photo.jpg' \
  -F 'signature_data=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...' \
  -F 'note_livraison=Livraison effectuée avec succès' \
  -F 'latitude=5.359952' \
  -F 'longitude=-4.008256'
```

### Annuler une livraison (CORRECT)
```bash
curl -X 'POST' \
  'http://192.168.1.5:8000/api/livreur/colis/1/cancel-delivery' \
  -H 'accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \
  -H 'Content-Type: application/json' \
  -d '{
    "motif_annulation": "Client absent",
    "note_livraison": "Client non joignable"
  }'
```

## Corrections apportées

### 1. Documentation Swagger mise à jour
- **LivreurDeliveryController.php** : 
  - Route `start-delivery` corrigée de `POST /start` vers `POST /start-delivery`
  - Route `complete-delivery` corrigée de `POST /complete` vers `POST /complete-delivery`
  - Route `cancel-delivery` corrigée de `POST /cancel` vers `POST /cancel-delivery`

### 2. Méthodes HTTP correctes
- **Démarrer livraison** : `POST` (action)
- **Terminer livraison** : `POST` (action avec données)
- **Démarrer ramassage** : `POST` (action)
- **Annuler livraison** : `POST` (action avec données)

### 3. Documentation régénérée
- La documentation Swagger a été régénérée avec les routes corrigées
- Toutes les URLs d'exemple utilisent maintenant `http://192.168.1.5:8000`

## Accès à la documentation

**Documentation Swagger :** http://192.168.1.5:8000/api/documentation

## Dépannage

Si vous obtenez encore une erreur 404 :

1. Vérifiez que vous utilisez la bonne méthode HTTP (`POST` pour toutes les actions)
2. Vérifiez que l'URL est correcte :
   - `/start-delivery` et non `/start`
   - `/complete-delivery` et non `/complete`
   - `/cancel-delivery` et non `/cancel`
3. Vérifiez que votre token JWT est valide
4. Vérifiez que le colis/ramassage existe et est assigné au livreur

## Notes importantes

- Toutes les routes (sauf login/refresh) nécessitent un token JWT valide
- Le token doit être inclus dans l'en-tête `Authorization: Bearer YOUR_TOKEN`
- Les routes sont protégées par le middleware `auth:livreur`
- Les colis/ramassages doivent être assignés au livreur connecté
