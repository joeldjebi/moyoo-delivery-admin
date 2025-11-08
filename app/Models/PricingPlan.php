<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'period',
        'features',
        'is_popular',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relation avec les historiques d'abonnement
     */
    public function subscriptionHistories()
    {
        return $this->hasMany(SubscriptionHistory::class);
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
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Accessor pour la période formatée
     */
    public function getFormattedPeriodAttribute()
    {
        return $this->period === 'month' ? 'mois' : 'année';
    }

    /**
     * Relation avec les modules
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'pricing_plan_modules')
                    ->withPivot('is_enabled', 'limits')
                    ->withTimestamps();
    }

    /**
     * Vérifier si le plan a un module activé
     */
    public function hasModule($moduleSlug)
    {
        return $this->modules()
            ->where('slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Obtenir les limites d'un module
     */
    public function getModuleLimits($moduleSlug)
    {
        $module = $this->modules()
            ->where('slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->first();

        if (!$module) {
            return null;
        }

        return json_decode($module->pivot->limits, true);
    }
}
