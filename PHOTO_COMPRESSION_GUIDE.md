# Guide de Compression de Photos pour les Ramassages

## 📸 Fonctionnalité de Compression Automatique

Le système permet maintenant aux livreurs d'uploader des photos des colis ramassés avec une **compression automatique** pour optimiser l'espace de stockage et la vitesse de transfert.

## 🎯 Caractéristiques

### Compression Automatique
- **Taille maximale** : 1MB par photo
- **Formats supportés** : JPEG, PNG, GIF, WebP
- **Compression intelligente** : Réduction automatique de la qualité et redimensionnement si nécessaire
- **Préservation de la transparence** : Pour les images PNG

### Optimisations
- **Redimensionnement automatique** : Images redimensionnées à 1920px max si plus grandes
- **Compression itérative** : Ajustement automatique de la qualité pour atteindre la taille cible
- **Noms de fichiers uniques** : Prévention des conflits avec timestamps

## 🚀 APIs Disponibles

### 1. Upload de Photos de Colis
```
POST /api/livreur/ramassages/{id}/upload-photos
```

**Paramètres :**
- `photos[]` : Tableau de fichiers images (max 5 photos)
- `notes_photos` : Notes optionnelles sur les photos

**Exemple de réponse :**
```json
{
  "success": true,
  "message": "Photos uploadées avec succès",
  "data": {
    "ramassage_id": 4,
    "photos_uploaded": 2,
    "photos": [
      {
        "filename": "colis_4_1760354936_1.jpg",
        "url": "http://127.0.0.1:8000/storage/ramassages/photos/colis_4_1760354936_1.jpg",
        "path": "ramassages/photos/colis_4_1760354936_1.jpg",
        "size_kb": 856.5,
        "dimensions": "1920x1080",
        "mime_type": "image/jpeg"
      }
    ]
  }
}
```

### 2. Finalisation de Ramassage avec Photo
```
POST /api/livreur/ramassages/{id}/complete
```

**Paramètres :**
- `photo_ramassage` : Photo du ramassage (optionnel)
- `nombre_colis_reel` : Nombre réel de colis
- `montant_total` : Montant total
- `notes_ramassage` : Notes sur le ramassage

## 📁 Structure des Fichiers

```
storage/app/public/
├── ramassages/
│   ├── photos/           # Photos des colis individuels
│   │   ├── colis_4_1760354936_1.jpg
│   │   └── colis_4_1760354936_2.jpg
│   └── ramassage_4_1760355000.jpg  # Photo du ramassage complet
└── temp/                 # Fichiers temporaires (nettoyés automatiquement)
```

## 🔧 Configuration

### Taille Maximale
La taille maximale par photo est configurée dans `ImageCompressor::compressUploadedFile()` :
```php
$compressedPath = ImageCompressor::compressUploadedFile(
    $photo,
    'ramassages/photos',
    $filename,
    1024 // 1MB max - Modifiable selon les besoins
);
```

### Qualité de Compression
La qualité initiale est de 85% et s'ajuste automatiquement pour atteindre la taille cible.

## 📊 Exemple de Compression

**Image originale :**
- Taille : 2.5MB
- Dimensions : 4000x3000px
- Format : JPEG

**Après compression :**
- Taille : 856KB (compression de ~66%)
- Dimensions : 1920x1440px (redimensionnée)
- Format : JPEG (qualité optimisée)

## 🛡️ Sécurité

- **Validation des types** : Seuls les formats d'image autorisés sont acceptés
- **Taille maximale** : Limite de 10MB par fichier avant compression
- **Noms sécurisés** : Génération de noms de fichiers uniques
- **Nettoyage automatique** : Suppression des fichiers temporaires

## 📝 Notes Automatiques

Le système ajoute automatiquement des informations sur les photos dans les notes du livreur :

```
📸 PHOTOS UPLOADÉES (2 photos):
- Date: 13/10/2025 11:28
- Notes: Photos des 2 colis ramassés
- colis_4_1760354936_1.jpg (856.5KB, 1920x1440)
- colis_4_1760354936_2.jpg (743.2KB, 1920x1080)
```

## 🔍 Monitoring

Les logs de compression sont disponibles dans `storage/logs/laravel.log` :
- Succès de compression
- Erreurs de traitement
- Informations sur les fichiers

## 🚨 Gestion d'Erreurs

Le système gère automatiquement :
- **Fichiers corrompus** : Rejet avec message d'erreur
- **Formats non supportés** : Validation en amont
- **Espace disque insuffisant** : Gestion des exceptions
- **Permissions** : Création automatique des répertoires

## 📱 Utilisation Mobile

Pour les applications mobiles, utilisez `multipart/form-data` :

```javascript
const formData = new FormData();
formData.append('photos[]', photoFile1);
formData.append('photos[]', photoFile2);
formData.append('notes_photos', 'Photos des colis ramassés');

fetch('/api/livreur/ramassages/4/upload-photos', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
});
```

## 🎉 Avantages

1. **Économie d'espace** : Compression automatique jusqu'à 70%
2. **Vitesse de transfert** : Fichiers plus légers
3. **Qualité préservée** : Compression intelligente
4. **Traçabilité** : Historique complet des photos
5. **Flexibilité** : Support de multiples formats
6. **Sécurité** : Validation et nettoyage automatiques
