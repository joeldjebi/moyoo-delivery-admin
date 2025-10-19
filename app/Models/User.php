<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'entreprise_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'password',
        'role',
        'user_type',
        'permissions',
        'status',
        'created_by',
        'subscription_plan_id',
        'subscription_started_at',
        'subscription_expires_at',
        'subscription_status',
        'is_trial',
        'trial_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'subscription_started_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'trial_expires_at' => 'datetime',
            'is_trial' => 'boolean',
        ];
    }

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Scopes pour l'isolation multi-tenant
     */
    public function scopeForEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId)
                    ->where('user_type', '!=', 'super_admin');
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('user_type', 'super_admin');
    }

    public function scopeEntrepriseAdmins($query)
    {
        return $query->where('user_type', 'entreprise_admin');
    }

    public function scopeEntrepriseUsers($query)
    {
        return $query->where('user_type', 'entreprise_user');
    }

    /**
     * Méthodes utilitaires
     */
    public function isSuperAdmin()
    {
        return $this->user_type === 'super_admin';
    }

    public function isEntrepriseAdmin()
    {
        return $this->user_type === 'entreprise_admin';
    }

    public function isEntrepriseUser()
    {
        return $this->user_type === 'entreprise_user';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Récupérer les permissions du rôle depuis la base de données
        $rolePermissions = \App\Models\RolePermission::getPermissionsForRole($this->role);

        // Vérifier les permissions personnalisées
        $customPermissions = $this->permissions ?? [];

        // Combiner les permissions du rôle et les permissions personnalisées
        $allPermissions = array_merge($rolePermissions, $customPermissions);

        return in_array($permission, $allPermissions);
    }

    /**
     * Obtenir les permissions par défaut selon le rôle
     */
    public function getRolePermissions()
    {
        $permissions = [];

        switch ($this->role) {
            case 'admin':
                $permissions = [
                    'users.create',
                    'users.read',
                    'users.update',
                    'users.delete',
                    'colis.create',
                    'colis.read',
                    'colis.update',
                    'colis.delete',
                    'livreurs.create',
                    'livreurs.read',
                    'livreurs.update',
                    'livreurs.delete',
                    'marchands.create',
                    'marchands.read',
                    'marchands.update',
                    'marchands.delete',
                    'reports.read',
                    'settings.read',
                    'settings.update'
                ];
                break;

            case 'manager':
                $permissions = [
                    'colis.create',
                    'colis.read',
                    'colis.update',
                    'colis.delete',
                    'livreurs.create',
                    'livreurs.read',
                    'livreurs.update',
                    'livreurs.delete',
                    'marchands.create',
                    'marchands.read',
                    'marchands.update',
                    'marchands.delete',
                    'reports.read'
                ];
                break;

            case 'user':
                $permissions = [
                    'colis.create',
                    'colis.read',
                    'livreurs.read',
                    'marchands.read'
                ];
                break;
        }

        return $permissions;
    }

    /**
     * Obtenir toutes les permissions disponibles
     */
    public static function getAllAvailablePermissions()
    {
        return [
            'users.create' => 'Créer des utilisateurs',
            'users.read' => 'Voir les utilisateurs',
            'users.update' => 'Modifier les utilisateurs',
            'users.delete' => 'Supprimer les utilisateurs',
            'colis.create' => 'Créer des colis',
            'colis.read' => 'Voir les colis',
            'colis.update' => 'Modifier les colis',
            'colis.delete' => 'Supprimer les colis',
            'livreurs.create' => 'Créer des livreurs',
            'livreurs.read' => 'Voir les livreurs',
            'livreurs.update' => 'Modifier les livreurs',
            'livreurs.delete' => 'Supprimer les livreurs',
            'marchands.create' => 'Créer des marchands',
            'marchands.read' => 'Voir les marchands',
            'marchands.update' => 'Modifier les marchands',
            'marchands.delete' => 'Supprimer les marchands',
            'reports.read' => 'Voir les rapports',
            'settings.read' => 'Voir les paramètres',
            'settings.update' => 'Modifier les paramètres'
        ];
    }

    /**
     * Permissions par rôle - Utilise le nouveau système de permissions
     */
    public function canManageUsers()
    {
        return $this->hasPermission('users.read');
    }

    public function canCreateUsers()
    {
        return $this->hasPermission('users.create');
    }

    public function canEditUsers()
    {
        return $this->hasPermission('users.update');
    }

    public function canDeleteUsers()
    {
        return $this->hasPermission('users.delete');
    }

    public function canManageColis()
    {
        return $this->hasPermission('colis.read');
    }

    public function canCreateColis()
    {
        return $this->hasPermission('colis.create');
    }

    public function canEditColis()
    {
        return $this->hasPermission('colis.update');
    }

    public function canDeleteColis()
    {
        return $this->hasPermission('colis.delete');
    }

    public function canManageLivreurs()
    {
        return $this->hasPermission('livreurs.read');
    }

    public function canCreateLivreurs()
    {
        return $this->hasPermission('livreurs.create');
    }

    public function canEditLivreurs()
    {
        return $this->hasPermission('livreurs.update');
    }

    public function canDeleteLivreurs()
    {
        return $this->hasPermission('livreurs.delete');
    }

    public function canManageMarchands()
    {
        return $this->hasPermission('marchands.read');
    }

    public function canCreateMarchands()
    {
        return $this->hasPermission('marchands.create');
    }

    public function canEditMarchands()
    {
        return $this->hasPermission('marchands.update');
    }

    public function canDeleteMarchands()
    {
        return $this->hasPermission('marchands.delete');
    }

    public function canViewReports()
    {
        return $this->hasPermission('reports.read');
    }

    public function canManageSettings()
    {
        return $this->hasPermission('settings.read');
    }

    public function canUpdateSettings()
    {
        return $this->hasPermission('settings.update');
    }

    public function canViewDashboard()
    {
        return true; // Tous les utilisateurs peuvent voir le dashboard
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function canEditUser(User $targetUser)
    {
        // Un utilisateur d'entreprise ne peut jamais modifier un super admin
        if ($targetUser->isSuperAdmin() && !$this->isSuperAdmin()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isEntrepriseAdmin() && $targetUser->entreprise_id === $this->entreprise_id) {
            return true;
        }

        return false;
    }

    public function canDeleteUser(User $targetUser)
    {
        // Un utilisateur d'entreprise ne peut jamais supprimer un super admin
        if ($targetUser->isSuperAdmin() && !$this->isSuperAdmin()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isEntrepriseAdmin() &&
            $targetUser->entreprise_id === $this->entreprise_id &&
            $targetUser->id !== $this->id) {
            return true;
        }

        return false;
    }

    /**
     * Méthodes pour la gestion des abonnements
     */

    /**
     * Vérifier si l'utilisateur a un abonnement actif
     */
    public function hasActiveSubscription()
    {
        return $this->subscription_status === 'active' &&
               $this->subscription_expires_at &&
               $this->subscription_expires_at->isFuture() &&
               !$this->is_trial;
    }

    /**
     * Vérifier si l'utilisateur est en période d'essai
     */
    public function isOnTrial()
    {
        return $this->is_trial &&
               $this->trial_expires_at &&
               $this->trial_expires_at->isFuture();
    }

    /**
     * Vérifier si l'utilisateur peut utiliser une fonctionnalité
     */
    public function canUseFeature($feature)
    {
        if (!$this->subscriptionPlan) {
            return false;
        }

        return $this->subscriptionPlan->$feature ?? false;
    }

    /**
     * Vérifier si l'utilisateur a atteint sa limite de colis
     */
    public function hasReachedColisLimit()
    {
        if (!$this->subscriptionPlan || !$this->subscriptionPlan->max_colis_per_month) {
            return false;
        }

        $currentMonthColis = \App\Models\Colis::whereHas('packageColis', function($query) {
            $query->whereHas('communeZone', function($q) {
                $q->where('entreprise_id', $this->entreprise_id);
            });
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();

        return $currentMonthColis >= $this->subscriptionPlan->max_colis_per_month;
    }

    /**
     * Assigner un plan d'abonnement à l'utilisateur
     */
    public function assignSubscriptionPlan($planId, $isTrial = true)
    {
        $plan = \App\Models\SubscriptionPlan::find($planId);

        if (!$plan) {
            return false;
        }

        $this->update([
            'subscription_plan_id' => $planId,
            'subscription_started_at' => now(),
            'subscription_expires_at' => $isTrial ? now()->addMonth() : now()->addDays($plan->duration_days),
            'subscription_status' => 'active',
            'is_trial' => $isTrial,
            'trial_expires_at' => $isTrial ? now()->addMonth() : null,
        ]);

        return true;
    }

    /**
     * Obtenir le statut de l'abonnement formaté
     */
    public function getSubscriptionStatusAttribute()
    {
        if ($this->isOnTrial()) {
            return 'Période d\'essai';
        }

        if ($this->hasActiveSubscription()) {
            return 'Actif';
        }

        if ($this->subscription_status === 'expired') {
            return 'Expiré';
        }

        if ($this->subscription_status === 'cancelled') {
            return 'Annulé';
        }

        return 'Inactif';
    }
}