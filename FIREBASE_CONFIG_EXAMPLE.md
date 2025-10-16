# 🔥 Configuration Firebase - Exemple

## Variables d'environnement à ajouter dans votre fichier `.env`

```env
# Configuration Firebase pour les notifications push
FIREBASE_SERVER_KEY=AAAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## 📋 Étapes pour obtenir votre Server Key

1. **Allez sur [Firebase Console](https://console.firebase.google.com/)**
2. **Sélectionnez votre projet** (ou créez-en un nouveau)
3. **Allez dans Paramètres du projet** (icône d'engrenage)
4. **Cliquez sur l'onglet "Cloud Messaging"**
5. **Copiez la "Server Key"** (Legacy Server Key)

## 🧪 Test de la configuration

```bash
# Test simple avec un token FCM
php artisan firebase:test --token=YOUR_FCM_TOKEN

# Test avec un livreur spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=livreur --id=1

# Test avec un marchand spécifique
php artisan firebase:test --token=YOUR_FCM_TOKEN --type=marchand --id=1
```

## ⚠️ Important

- **Ne partagez jamais votre Server Key** publiquement
- **Gardez-la secrète** dans votre fichier `.env`
- **Redémarrez votre serveur** après avoir ajouté la variable
- **Testez toujours** avec la commande `firebase:test` avant de déployer

## 🔧 Dépannage

### Erreur "Firebase Server Key non configuré"
- Vérifiez que `FIREBASE_SERVER_KEY` est bien défini dans `.env`
- Redémarrez le serveur Laravel
- Vérifiez qu'il n'y a pas d'espaces autour de la valeur

### Erreur 401 Unauthorized
- Vérifiez que la Server Key est correcte
- Assurez-vous que le projet Firebase est actif
- Vérifiez que Cloud Messaging est activé dans Firebase

### Notifications non reçues
- Vérifiez que le token FCM est valide
- Testez avec la commande `firebase:test`
- Vérifiez les logs dans `storage/logs/laravel.log`
