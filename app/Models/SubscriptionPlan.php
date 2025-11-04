<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'duration_days',
        'features',
        'max_colis_per_month',
        'max_livreurs',
        'max_marchands',
        'whatsapp_notifications',
        'whatsapp_sms_limit',
        'firebase_notifications',
        'api_access',
        'advanced_reports',
        'priority_support',
        'is_active',
        'sort_order',
        'entreprise_id',
        'pricing_plan_id',
        'started_at',
        'expires_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'whatsapp_notifications' => 'boolean',
        'firebase_notifications' => 'boolean',
        'api_access' => 'boolean',
        'advanced_reports' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];


    /**
     * Relation avec les utilisateurs
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec le plan de tarification
     */
    public function pricingPlan()
    {
        return $this->belongsTo(\App\Models\PricingPlan::class, 'pricing_plan_id');
    }

    /**
     * Scope pour les plans actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Scope pour filtrer par entreprise
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Accessor pour le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Accessor pour les fonctionnalités formatées
     */
    public function getFormattedFeaturesAttribute()
    {
        $features = [];

        // Fonctionnalités de base
        $features[] = "Accès à la plateforme";

        // Limites
        if ($this->max_colis_per_month) {
            $features[] = "Jusqu'à {$this->max_colis_per_month} colis/mois";
        } else {
            $features[] = "Colis illimités";
        }

        if ($this->max_livreurs) {
            $features[] = "Jusqu'à {$this->max_livreurs} livreurs";
        } else {
            $features[] = "Livreurs illimités";
        }

        if ($this->max_marchands) {
            $features[] = "Jusqu'à {$this->max_marchands} marchands";
        } else {
            $features[] = "Marchands illimités";
        }

        // Fonctionnalités avancées
        if ($this->whatsapp_notifications) {
            $features[] = "Notifications WhatsApp";
        }

        if ($this->firebase_notifications) {
            $features[] = "Notifications Push";
        }

        if ($this->api_access) {
            $features[] = "Accès API";
        }

        if ($this->advanced_reports) {
            $features[] = "Rapports avancés";
        }

        if ($this->priority_support) {
            $features[] = "Support prioritaire";
        }

        // Ajouter les fonctionnalités du champ features si elles existent
        if ($this->features && is_array($this->features)) {
            $features = array_merge($features, $this->features);
        }

        return $features;
    }

    /**
     * Vérifier si le plan est gratuit
     */
    public function isFree()
    {
        return $this->price == 0;
    }

    /**
     * Vérifier si le plan est premium
     */
    public function isPremium()
    {
        return $this->price > 0;
    }

    /**
     * Calculer la date d'expiration basée sur le type de plan
     */
    public function calculateExpirationDate()
    {
        if (!$this->started_at) {
            return null;
        }

        // Pour les plans Premium (30 jours)
        if ($this->name === 'Premium' || $this->duration_days == 30) {
            return $this->started_at->addDays(30);
        }

        // Pour les plans Premium Annuel (365 jours)
        if ($this->name === 'Premium Annuel' || $this->duration_days == 365) {
            return $this->started_at->addDays(365);
        }

        // Par défaut, utiliser duration_days
        return $this->started_at->addDays($this->duration_days);
    }

    /**
     * Démarrer l'abonnement avec calcul automatique de l'expiration
     */
    public function startSubscription()
    {
        // Vérifier s'il y a déjà un abonnement actif du même plan
        $existingActiveSubscription = self::where('entreprise_id', $this->entreprise_id)
            ->where('is_active', true)
            ->where('pricing_plan_id', $this->pricing_plan_id)
            ->where('id', '!=', $this->id)
            ->first();

        if ($existingActiveSubscription) {
            // Extension d'abonnement existant
            $this->extendExistingSubscription($existingActiveSubscription);
        } else {
            // Nouvel abonnement - désactiver les autres abonnements actifs
            self::where('entreprise_id', $this->entreprise_id)
                ->where('is_active', true)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            $this->started_at = now();
            $this->expires_at = $this->calculateExpirationDate();
            $this->is_active = true;
            $this->save();
        }
    }

    /**
     * Étendre un abonnement existant
     */
    public function extendExistingSubscription($existingSubscription)
    {
        // Calculer la nouvelle date d'expiration
        $currentExpiration = $existingSubscription->expires_at ?? now();
        $extensionDays = $this->calculateExtensionDays();
        $newExpiration = $currentExpiration->addDays($extensionDays);

        // Mettre à jour l'abonnement existant
        $existingSubscription->expires_at = $newExpiration;
        $existingSubscription->save();

        // Marquer ce nouvel abonnement comme inactif (il sert juste pour l'extension)
        $this->is_active = false;
        $this->started_at = now();
        $this->expires_at = $newExpiration;
        $this->save();

        return $existingSubscription;
    }

    /**
     * Calculer le nombre de jours d'extension
     */
    public function calculateExtensionDays()
    {
        if ($this->name === 'Premium' || $this->duration_days == 30) {
            return 30;
        }

        if ($this->name === 'Premium Annuel' || $this->duration_days == 365) {
            return 365;
        }

        return $this->duration_days;
    }

    /**
     * Vérifier si l'abonnement est expiré
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifier si l'abonnement est actif (non expiré)
     */
    public function isActiveSubscription()
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Vérifier s'il y a déjà un abonnement actif pour une entreprise
     */
    public static function hasActiveSubscription($entrepriseId)
    {
        return self::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Obtenir l'abonnement actif d'une entreprise
     */
    public static function getActiveSubscription($entrepriseId)
    {
        return self::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with('pricingPlan')
            ->first();
    }

    /**
     * Désactiver un abonnement
     */
    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }

    /**
     * Calculer la durée réelle de l'abonnement
     */
    public function getRealDurationDays()
    {
        if (!$this->started_at || !$this->expires_at) {
            return $this->duration_days; // Retourner la durée du plan si les dates ne sont pas définies
        }

        return $this->started_at->diffInDays($this->expires_at);
    }

    /**
     * Calculer les jours restants
     */
    public function getRemainingDays()
    {
        if (!$this->expires_at) {
            return 0;
        }

        $now = now();
        if ($now->greaterThan($this->expires_at)) {
            return 0; // Expiré
        }

        return ceil($now->diffInDays($this->expires_at));
    }
}
