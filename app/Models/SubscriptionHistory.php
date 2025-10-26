<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    protected $fillable = [
        'entreprise_id',
        'pricing_plan_id',
        'plan_name',
        'price',
        'currency',
        'status',
        'payment_method',
        'transaction_id'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Relation avec le plan de tarification
     */
    public function pricingPlan()
    {
        return $this->belongsTo(\App\Models\PricingPlan::class, 'pricing_plan_id');
    }

    /**
     * Scope pour les abonnements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour une entreprise spécifique
     */
    public function scopeForEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour trier par date de création
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor pour le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Accessor pour le statut formaté
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Actif',
            'expired' => 'Expiré',
            'cancelled' => 'Annulé',
            'pending' => 'En attente',
            default => 'Inconnu'
        };
    }

    /**
     * Accessor pour vérifier si l'abonnement est expiré
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at < now();
    }

    /**
     * Vérifier si l'abonnement est actif
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }
}
