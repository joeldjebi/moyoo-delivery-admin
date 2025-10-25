# MOYOO Delivery - Système de Géolocalisation

## 🚀 Description

Système de géolocalisation en temps réel pour la gestion des livreurs avec suivi conditionnel automatique.

## ✨ Fonctionnalités

### 📱 Suivi Conditionnel Automatique
- **Activation automatique** du GPS lors du démarrage d'une mission
- **Désactivation automatique** à la fin de la mission
- **Respect de la vie privée** des livreurs

### 🖥️ Interface Admin Complète
- **Vue d'ensemble** de tous les livreurs en mission
- **Sélection individuelle** de livreurs
- **Filtrage par type** de mission (livraison/ramassage)
- **Centrage sur livreur** spécifique
- **Supervision en temps réel**

### 🔧 Technologies Utilisées
- **Laravel 12** - Framework PHP
- **Socket.IO** - Communication temps réel
- **Leaflet + OpenStreetMap** - Cartes interactives
- **MySQL** - Base de données
- **JWT Authentication** - Authentification sécurisée

## 🛠️ Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Configuration

1. **Cloner le repository**
```bash
git clone https://github.com/votre-username/moyoo-delivery.git
cd moyoo-delivery
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configuration de la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moyoo_delivery
DB_USERNAME=root
DB_PASSWORD=
```

5. **Migration et seeders**
```bash
php artisan migrate
php artisan db:seed
```

## 🚀 Démarrage

### Démarrage automatique
```bash
./start-moyoo-system.sh
```

### Démarrage manuel

1. **Serveur Laravel**
```bash
php artisan serve --host=192.168.1.6 --port=8000
```

2. **Serveur Socket.IO**
```bash
node socket-server.js
```

3. **Assets frontend**
```bash
npm run dev
```

## 📱 Interfaces

### Interface Admin
- **URL** : `http://192.168.1.6:8000/location/admin-monitor`
- **Fonction** : Supervision de tous les livreurs en mission
- **Fonctionnalités** :
  - Sélection de livreurs individuels
  - Filtrage par type de mission
  - Centrage sur livreur spécifique
  - Statistiques en temps réel

### Dashboard Principal
- **URL** : `http://192.168.1.6:8000/dashboard`
- **Fonction** : Tableau de bord principal

## 🔌 API Endpoints

### Authentification
- `POST /api/livreur/login` - Connexion livreur
- `POST /api/livreur/logout` - Déconnexion livreur

### Géolocalisation
- `POST /api/livreur/location/update` - Mise à jour position
- `GET /api/livreur/location/status` - Statut de localisation

### Missions
- `POST /api/livreur/colis/{id}/start-delivery` - Démarrer livraison
- `POST /api/livreur/colis/{id}/complete-delivery` - Terminer livraison
- `POST /api/livreur/ramassages/{id}/start` - Démarrer ramassage
- `POST /api/livreur/ramassages/{id}/complete` - Terminer ramassage

## 🗺️ Configuration Socket.IO

### Serveur Socket.IO
- **Port** : 3000
- **URL** : `http://192.168.1.6:3000`
- **Fonction** : Communication temps réel

### Configuration CORS
```javascript
cors: {
    origin: "http://192.168.1.6:8000",
    methods: ["GET", "POST"]
}
```

## 📊 Base de Données

### Tables Principales
- `livreurs` - Informations des livreurs
- `livreur_locations` - Positions GPS
- `livreur_location_status` - Statuts de localisation
- `colis` - Livraisons
- `ramassages` - Ramassages

### Migrations
```bash
php artisan migrate
```

## 🔒 Sécurité

### Authentification
- **JWT Tokens** pour l'authentification API
- **CSRF Protection** pour les formulaires web
- **Middleware d'authentification** sur toutes les routes

### Suivi Conditionnel
- **Activation automatique** uniquement pendant les missions
- **Désactivation automatique** à la fin des missions
- **Respect de la vie privée** des livreurs

## 📱 Application Mobile

### Configuration Socket.IO
```javascript
const socket = io('http://192.168.1.6:3000', {
    auth: {
        token: 'JWT_TOKEN'
    }
});
```

### Envoi de Position
```javascript
socket.emit('location:update', {
    latitude: position.latitude,
    longitude: position.longitude,
    accuracy: position.accuracy,
    speed: position.speed
});
```

## 🚀 Déploiement

### Production
1. **Variables d'environnement**
```env
APP_ENV=production
APP_DEBUG=false
```

2. **Optimisation**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Assets**
```bash
npm run build
```

## 📝 Documentation API

### Swagger
- **URL** : `http://192.168.1.6:8000/api/documentation`
- **Génération** : `php artisan l5-swagger:generate`

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou problème, ouvrez une issue sur GitHub.

---

**MOYOO Delivery** - Système de géolocalisation intelligent pour la logistique moderne.