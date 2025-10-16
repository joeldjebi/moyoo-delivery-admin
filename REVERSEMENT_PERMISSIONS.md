# 🔐 Permissions pour le Système de Reversement

## 📋 **Permissions Requises**

Le système de reversement utilise les permissions suivantes qui doivent être configurées dans votre système de gestion des rôles :

### **1. 📖 Lecture des Reversements (`reversements.read`)**
**Routes protégées :**
- `GET /reversements` - Liste des reversements
- `GET /reversements/{id}` - Détails d'un reversement
- `GET /balances` - Dashboard des balances
- `GET /historique-balances` - Historique des mouvements

**Fonctionnalités :**
- Consulter la liste des reversements
- Voir les détails d'un reversement
- Accéder au dashboard des balances
- Consulter l'historique des mouvements

### **2. ➕ Création des Reversements (`reversements.create`)**
**Routes protégées :**
- `GET /reversements/create` - Formulaire de création
- `POST /reversements` - Création d'un reversement

**Fonctionnalités :**
- Accéder au formulaire de création
- Créer de nouveaux reversements
- Sélectionner les marchands et boutiques

### **3. ✏️ Mise à Jour des Reversements (`reversements.update`)**
**Routes protégées :**
- `POST /reversements/{id}/validate` - Validation d'un reversement
- `POST /reversements/{id}/cancel` - Annulation d'un reversement

**Fonctionnalités :**
- Valider les reversements en attente
- Annuler les reversements
- Effectuer les débits de balance

## 🎯 **Configuration des Rôles**

### **Rôle Administrateur**
```php
$adminPermissions = [
    'reversements.read',
    'reversements.create', 
    'reversements.update'
];
```

### **Rôle Gestionnaire Financier**
```php
$financePermissions = [
    'reversements.read',
    'reversements.create',
    'reversements.update'
];
```

### **Rôle Consultant**
```php
$consultantPermissions = [
    'reversements.read'
];
```

### **Rôle Comptable**
```php
$comptablePermissions = [
    'reversements.read',
    'reversements.update'  // Peut valider mais pas créer
];
```

## 🔧 **Exemple de Configuration**

### **Dans un Seeder de Permissions :**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ReversementPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'reversements.read',
            'reversements.create',
            'reversements.update'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
```

### **Attribution des Permissions aux Rôles :**
```php
// Rôle Admin
$adminRole = Role::findByName('admin');
$adminRole->givePermissionTo([
    'reversements.read',
    'reversements.create',
    'reversements.update'
]);

// Rôle Comptable
$comptableRole = Role::findByName('comptable');
$comptableRole->givePermissionTo([
    'reversements.read',
    'reversements.update'
]);
```

## 🛡️ **Sécurité et Contrôle d'Accès**

### **1. Vérification dans les Vues**
```blade
@can('reversements.create')
    <a href="{{ route('reversements.create') }}" class="btn btn-primary">
        Nouveau Reversement
    </a>
@endcan

@can('reversements.update')
    <button type="submit" class="btn btn-success">
        Valider
    </button>
@endcan
```

### **2. Vérification dans les Contrôleurs**
```php
// Dans ReversementController
public function create()
{
    $this->authorize('create', Reversement::class);
    // ... logique de création
}

public function validateReversement($id)
{
    $this->authorize('update', Reversement::class);
    // ... logique de validation
}
```

### **3. Middleware de Groupe**
```php
Route::middleware(['auth', 'permission:reversements.read'])->group(function () {
    // Routes de consultation
});

Route::middleware(['auth', 'permission:reversements.create'])->group(function () {
    // Routes de création
});
```

## 📊 **Matrice des Permissions**

| Fonctionnalité | `reversements.read` | `reversements.create` | `reversements.update` |
|----------------|-------------------|---------------------|---------------------|
| Voir la liste des reversements | ✅ | ❌ | ❌ |
| Voir les détails d'un reversement | ✅ | ❌ | ❌ |
| Accéder au dashboard des balances | ✅ | ❌ | ❌ |
| Consulter l'historique | ✅ | ❌ | ❌ |
| Créer un nouveau reversement | ❌ | ✅ | ❌ |
| Valider un reversement | ❌ | ❌ | ✅ |
| Annuler un reversement | ❌ | ❌ | ✅ |

## 🚀 **Mise en Place**

1. **Créer les permissions** dans votre système de gestion des rôles
2. **Attribuer les permissions** aux rôles appropriés
3. **Tester l'accès** avec différents utilisateurs
4. **Configurer les vues** avec les directives `@can`

## ⚠️ **Important**

- Les permissions sont **cumulatives** : un utilisateur avec `reversements.update` peut aussi lire
- Les **super admins** ont accès à tout par défaut
- Les permissions sont **vérifiées côté serveur** ET côté client
- L'**isolation par entreprise** est maintenue automatiquement
