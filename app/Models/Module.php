<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon',
        'category', 'is_active', 'sort_order', 'routes',
        'price', 'currency', 'is_optional'
    ];

    protected $casts = [
        'routes' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
        'is_optional' => 'boolean'
    ];

    /**
     * Relation avec les pricing plans
     */
    public function pricingPlans()
    {
        return $this->belongsToMany(PricingPlan::class, 'pricing_plan_modules')
                    ->withPivot('is_enabled', 'limits')
                    ->withTimestamps();
    }

    /**
     * Scope pour les modules actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Relation avec les entreprises (modules achetés)
     */
    public function entreprises()
    {
        return $this->belongsToMany(\App\Models\Entreprise::class, 'entreprise_modules')
                    ->withPivot('price_paid', 'currency', 'purchased_at', 'expires_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Scope pour les modules optionnels
     */
    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }
}

