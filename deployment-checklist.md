# 📋 Checklist de Déploiement - MOYOO Admin Delivery

## 🚀 Pré-déploiement

### ✅ Vérifications obligatoires
- [ ] Tous les tests passent
- [ ] Code poussé sur GitHub
- [ ] Documentation Swagger à jour
- [ ] Variables d'environnement configurées
- [ ] Base de données prête

### 🔧 Configuration serveur
- [ ] PHP 8.1+ installé
- [ ] Composer installé
- [ ] Node.js et NPM installés
- [ ] Base de données MySQL/PostgreSQL configurée
- [ ] Serveur web (Apache/Nginx) configuré
- [ ] SSL/HTTPS configuré

## 📦 Déploiement

### 1. Récupération du code
```bash
git clone https://github.com/joeldjebi/moyoo-delivery-admin.git
cd moyoo-delivery-admin
```

### 2. Configuration
```bash
# Copier le fichier d'environnement
cp .env.example .env

# Éditer les variables d'environnement
nano .env
```

### 3. Installation des dépendances
```bash
# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Dépendances Node.js
npm ci --production
```

### 4. Compilation des assets
```bash
npm run build
```

### 5. Configuration de l'application
```bash
# Générer la clé d'application
php artisan key:generate

# Migrations de base de données
php artisan migrate --force

# Optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Génération de la documentation
```bash
php artisan l5-swagger:generate
```

## 🔐 Variables d'environnement importantes

```env
APP_NAME="MOYOO Admin Delivery"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moyoo_delivery
DB_USERNAME=...
DB_PASSWORD=...

# Configuration WhatsApp (Wassenger)
WASSENGER_API_URL=https://api.wassenger.com
WASSENGER_API_TOKEN=...

# Configuration Firebase
FIREBASE_SERVER_KEY=...

# Configuration Mailjet
MAILJET_API_KEY=...
MAILJET_SECRET_KEY=...
```

## 🛡️ Sécurité

### ✅ Vérifications de sécurité
- [ ] APP_DEBUG=false en production
- [ ] Mots de passe forts pour la base de données
- [ ] Permissions de fichiers correctes (755 pour les dossiers, 644 pour les fichiers)
- [ ] SSL/HTTPS activé
- [ ] Firewall configuré
- [ ] Sauvegardes automatiques configurées

### 🔒 Permissions
```bash
# Permissions pour Laravel
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📊 Monitoring

### 📈 Métriques à surveiller
- [ ] Utilisation CPU/Mémoire
- [ ] Espace disque
- [ ] Logs d'erreurs
- [ ] Performance de la base de données
- [ ] Temps de réponse de l'API

### 📝 Logs importants
```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs du serveur web
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

## 🚨 En cas de problème

### 🔧 Commandes de dépannage
```bash
# Vérifier le statut de l'application
php artisan about

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Vérifier les routes
php artisan route:list

# Tester la base de données
php artisan migrate:status
```

### 📞 Support
- Documentation: [README.md](./README.md)
- Issues: [GitHub Issues](https://github.com/joeldjebi/moyoo-delivery-admin/issues)
- Contact: [Votre contact]

## ✅ Post-déploiement

### 🧪 Tests de validation
- [ ] Page d'accueil accessible
- [ ] Connexion admin fonctionnelle
- [ ] API Swagger accessible
- [ ] Création de colis fonctionnelle
- [ ] Système de livraison opérationnel
- [ ] Système de reversement fonctionnel
- [ ] Notifications WhatsApp/Firebase actives

### 📋 Maintenance
- [ ] Planification des sauvegardes
- [ ] Monitoring des performances
- [ ] Mise à jour des dépendances
- [ ] Surveillance des logs
- [ ] Tests de récupération

---

**🎉 Félicitations ! Votre application MOYOO Admin Delivery est maintenant déployée !**
