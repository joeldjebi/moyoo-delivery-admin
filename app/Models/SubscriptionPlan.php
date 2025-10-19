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
        'firebase_notifications',
        'api_access',
        'advanced_reports',
        'priority_support',
        'is_active',
        'sort_order'
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
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
        
        if ($this->max_colis_per_month) {
            $features[] = "Jusqu'à {$this->max_colis_per_month} colis/mois";
        }
        
        if ($this->max_livreurs) {
            $features[] = "Jusqu'à {$this->max_livreurs} livreurs";
        }
        
        if ($this->max_marchands) {
            $features[] = "Jusqu'à {$this->max_marchands} marchands";
        }
        
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
}
