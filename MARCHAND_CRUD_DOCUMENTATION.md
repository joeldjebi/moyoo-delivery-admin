# CRUD Marchand - Documentation

## Vue d'ensemble

Le CRUD (Create, Read, Update, Delete) pour la table `marchands` a été entièrement implémenté dans le contrôleur `MarchandController.php` avec toutes les fonctionnalités nécessaires pour gérer les marchands.

## Structure de la table

### **📋 Champs de la table `marchands`**

| **Champ** | **Type** | **Description** | **Contraintes** |
|-----------|----------|-----------------|-----------------|
| `id` | bigint(20) | Identifiant unique | PRIMARY KEY, AUTO_INCREMENT |
| `first_name` | varchar(255) | Prénom du marchand | NOT NULL |
| `last_name` | varchar(255) | Nom du marchand | NOT NULL |
| `mobile` | varchar(255) | Numéro de téléphone | NOT NULL |
| `email` | varchar(255) | Adresse email | NULLABLE, UNIQUE |
| `adresse` | varchar(255) | Adresse physique | NULLABLE |
| `status` | varchar(255) | Statut du marchand | NOT NULL, DEFAULT 'active' |
| `commune_id` | bigint(20) | ID de la commune | NOT NULL, FOREIGN KEY |
| `created_by` | varchar(255) | ID de l'utilisateur créateur | NOT NULL |
| `deleted_at` | timestamp | Date de suppression (soft delete) | NULLABLE |
| `created_at` | timestamp | Date de création | NULLABLE |
| `updated_at` | timestamp | Date de modification | NULLABLE |

## Modèle Marchand

### **🔧 Fonctionnalités du modèle**

#### **1. Relations**
```php
// Relation avec la commune
public function commune()
{
    return $this->belongsTo(Commune::class);
}

// Relation avec les colis
public function colis()
{
    return $this->hasMany(Colis::class);
}

// Relation avec les boutiques
public function boutiques()
{
    return $this->hasMany(Boutique::class);
}

// Relation avec l'utilisateur créateur
public function user()
{
    return $this->belongsTo(User::class, 'created_by');
}
```

#### **2. Accessors**
```php
// Nom complet
public function getFullNameAttribute()
{
    return $this->first_name . ' ' . $this->last_name;
}
```

#### **3. Scopes**
```php
// Marchands actifs
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

// Marchands inactifs
public function scopeInactive($query)
{
    return $query->where('status', 'inactive');
}
```

## Contrôleur MarchandController

### **📝 Méthodes implémentées**

#### **1. index() - Liste des marchands**
- **Route** : `GET /marchands`
- **Fonctionnalités** :
  - Pagination (15 éléments par page)
  - Chargement des relations (commune)
  - Tri par date de création (plus récent en premier)
  - Filtrage par utilisateur connecté

#### **2. create() - Formulaire de création**
- **Route** : `GET /marchands/create`
- **Fonctionnalités** :
  - Chargement des communes actives
  - Tri des communes par nom

#### **3. store() - Création d'un marchand**
- **Route** : `POST /marchands`
- **Validation** :
  - Prénom : requis, lettres et espaces uniquement
  - Nom : requis, lettres et espaces uniquement
  - Téléphone : requis, format valide (10-15 caractères)
  - Email : optionnel, format email, unique
  - Adresse : optionnel, max 500 caractères
  - Commune : requis, doit exister
  - Statut : requis, 'active' ou 'inactive'

#### **4. show() - Détails d'un marchand**
- **Route** : `GET /marchands/{marchand}`
- **Fonctionnalités** :
  - Chargement des relations (commune, colis, boutiques)
  - Vérification des permissions

#### **5. edit() - Formulaire de modification**
- **Route** : `GET /marchands/{marchand}/edit`
- **Fonctionnalités** :
  - Pré-remplissage des champs
  - Chargement des communes actives

#### **6. update() - Modification d'un marchand**
- **Route** : `PUT /marchands/{marchand}`
- **Validation** : Identique à store() avec exception pour l'email unique

#### **7. destroy() - Suppression d'un marchand**
- **Route** : `DELETE /marchands/{marchand}`
- **Fonctionnalités** :
  - Vérification des contraintes (colis et boutiques associés)
  - Soft delete
  - Logs de suppression

#### **8. search() - Recherche de marchands**
- **Route** : `GET /marchands-search`
- **Fonctionnalités** :
  - Recherche par nom, prénom, téléphone, email
  - Limite de 10 résultats
  - Retour JSON

#### **9. toggleStatus() - Changement de statut**
- **Route** : `PATCH /marchands/{marchand}/toggle-status`
- **Fonctionnalités** :
  - Basculement entre actif/inactif
  - Logs de modification

### **🔒 Sécurité implémentée**

#### **1. Authentification**
- Vérification de l'utilisateur connecté sur toutes les méthodes
- Redirection vers login si non authentifié

#### **2. Autorisation**
- Vérification que le marchand appartient à l'utilisateur connecté
- Protection contre l'accès aux données d'autres utilisateurs

#### **3. Validation des données**
- Validation stricte côté serveur
- Messages d'erreur personnalisés en français
- Protection contre les injections

#### **4. Logs de sécurité**
- Enregistrement de toutes les actions (création, modification, suppression)
- Traçabilité des opérations avec IP et utilisateur

## Routes

### **📋 Routes définies**

```php
// Routes resource (CRUD standard)
Route::resource('marchands', MarchandController::class);

// Routes supplémentaires
Route::get('/marchands-search', [MarchandController::class, 'search'])->name('marchands.search');
Route::patch('/marchands/{marchand}/toggle-status', [MarchandController::class, 'toggleStatus'])->name('marchands.toggle-status');
```

### **🎯 Routes générées**

| **Méthode** | **URI** | **Nom** | **Action** |
|-------------|---------|---------|------------|
| GET | `/marchands` | `marchands.index` | Liste des marchands |
| GET | `/marchands/create` | `marchands.create` | Formulaire de création |
| POST | `/marchands` | `marchands.store` | Création d'un marchand |
| GET | `/marchands/{marchand}` | `marchands.show` | Détails d'un marchand |
| GET | `/marchands/{marchand}/edit` | `marchands.edit` | Formulaire de modification |
| PUT | `/marchands/{marchand}` | `marchands.update` | Modification d'un marchand |
| DELETE | `/marchands/{marchand}` | `marchands.destroy` | Suppression d'un marchand |
| GET | `/marchands-search` | `marchands.search` | Recherche de marchands |
| PATCH | `/marchands/{marchand}/toggle-status` | `marchands.toggle-status` | Changement de statut |

## Vues

### **🎨 Vues créées**

#### **1. index.blade.php - Liste des marchands**
- **Fonctionnalités** :
  - Tableau responsive avec pagination
  - Recherche en temps réel
  - Actions par dropdown (voir, modifier, activer/désactiver, supprimer)
  - Affichage des avatars avec initiales
  - Badges pour le statut et la commune
  - Messages de succès/erreur

#### **2. create.blade.php - Création d'un marchand**
- **Fonctionnalités** :
  - Formulaire complet avec validation côté client
  - Champs : prénom, nom, téléphone, email, commune, statut, adresse
  - Validation en temps réel
  - Messages d'erreur contextuels
  - Boutons d'action (annuler, enregistrer)

#### **3. edit.blade.php - Modification d'un marchand**
- **Fonctionnalités** :
  - Formulaire pré-rempli avec les données existantes
  - Même validation que la création
  - Boutons d'action (annuler, mettre à jour)

#### **4. show.blade.php - Détails d'un marchand**
- **Fonctionnalités** :
  - Affichage complet des informations
  - Statistiques (nombre de colis, boutiques, ancienneté)
  - Actions rapides (modifier, activer/désactiver, supprimer)
  - Liste des colis récents
  - Informations générales (dates, créateur)

### **💻 JavaScript intégré**

#### **1. Validation côté client**
- Vérification des champs requis
- Validation du format email
- Validation du format téléphone
- Messages d'alerte personnalisés

#### **2. Fonctionnalités UX**
- Auto-dismiss des alertes après 5 secondes
- Recherche avec bouton et touche Entrée
- Confirmation de suppression

## Validation

### **✅ Règles de validation**

#### **1. Création (store)**
```php
'first_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
'last_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
'mobile' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:15',
'email' => 'nullable|email|max:255|unique:marchands,email',
'adresse' => 'nullable|string|max:500',
'commune_id' => 'required|exists:communes,id',
'status' => 'required|in:active,inactive'
```

#### **2. Modification (update)**
```php
// Même validation que store() avec exception pour l'email unique
'email' => 'nullable|email|max:255|unique:marchands,email,' . $marchand->id,
```

### **📝 Messages d'erreur personnalisés**

- **Prénom/Nom** : "Le prénom ne peut contenir que des lettres et espaces."
- **Téléphone** : "Le format du numéro de téléphone est invalide."
- **Commune** : "Veuillez sélectionner une commune."
- **Email** : "Cette adresse email est déjà utilisée."
- **Statut** : "Le statut doit être actif ou inactif."

## Gestion des erreurs

### **🛡️ Protection contre les erreurs**

#### **1. Transactions de base de données**
- Utilisation de `DB::beginTransaction()` et `DB::commit()`
- Rollback automatique en cas d'erreur

#### **2. Gestion des exceptions**
- Try-catch sur toutes les opérations critiques
- Logs détaillés des erreurs
- Messages d'erreur génériques pour l'utilisateur

#### **3. Vérifications de contraintes**
- Vérification avant suppression (colis et boutiques associés)
- Messages d'erreur explicites

## Logs et traçabilité

### **📊 Logs implémentés**

#### **1. Création**
```php
Log::info('Marchand créé avec succès', [
    'marchand_id' => $marchand->id,
    'nom' => $marchand->full_name,
    'created_by' => $user->id,
    'ip' => $request->ip()
]);
```

#### **2. Modification**
```php
Log::info('Marchand modifié avec succès', [
    'marchand_id' => $marchand->id,
    'nom' => $marchand->full_name,
    'updated_by' => $user->id,
    'ip' => $request->ip()
]);
```

#### **3. Suppression**
```php
Log::info('Marchand supprimé avec succès', [
    'marchand_id' => $marchandId,
    'nom' => $marchandName,
    'deleted_by' => $user->id,
    'ip' => request()->ip()
]);
```

## Utilisation

### **🎯 Pour l'utilisateur**

#### **1. Créer un marchand**
1. Aller sur `/marchands`
2. Cliquer sur "Ajouter un Marchand"
3. Remplir le formulaire
4. Cliquer sur "Enregistrer"

#### **2. Modifier un marchand**
1. Aller sur `/marchands`
2. Cliquer sur "Modifier" dans le dropdown
3. Modifier les informations
4. Cliquer sur "Mettre à jour"

#### **3. Voir les détails**
1. Aller sur `/marchands`
2. Cliquer sur "Voir" dans le dropdown

#### **4. Changer le statut**
1. Aller sur `/marchands`
2. Cliquer sur "Activer/Désactiver" dans le dropdown

#### **5. Supprimer un marchand**
1. Aller sur `/marchands`
2. Cliquer sur "Supprimer" dans le dropdown
3. Confirmer la suppression

### **🔧 Pour le développeur**

#### **1. Ajouter de nouveaux champs**
1. Modifier la migration
2. Mettre à jour le modèle (fillable)
3. Ajouter la validation dans le contrôleur
4. Mettre à jour les vues

#### **2. Modifier les règles de validation**
```php
// Dans store() et update()
$request->validate([
    'nouveau_champ' => 'required|string|max:255',
    // ... autres règles
]);
```

#### **3. Ajouter de nouvelles relations**
```php
// Dans le modèle Marchand
public function nouvelleRelation()
{
    return $this->hasMany(NouveauModele::class);
}
```

## Avantages de l'implémentation

### **✅ Fonctionnalités complètes**
- CRUD complet avec toutes les opérations
- Validation robuste côté client et serveur
- Interface utilisateur intuitive et responsive
- Gestion d'erreurs complète

### **✅ Sécurité renforcée**
- Authentification et autorisation
- Validation stricte des données
- Protection contre les injections
- Logs de sécurité détaillés

### **✅ Performance optimisée**
- Pagination pour les grandes listes
- Chargement des relations avec `with()`
- Requêtes optimisées
- Cache des communes

### **✅ Maintenabilité**
- Code modulaire et réutilisable
- Documentation complète
- Logs détaillés
- Structure claire et organisée

Le CRUD Marchand est maintenant **entièrement fonctionnel** et prêt à l'utilisation ! 🎯
