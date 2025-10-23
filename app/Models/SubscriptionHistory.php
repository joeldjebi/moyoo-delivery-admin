<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'amount',
        'currency',
        'period',
        'status',
        'starts_at',
        'expires_at',
        'is_trial',
        'payment_method',
        'transaction_id',
        'payment_data'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_trial' => 'boolean',
        'payment_data' => 'array'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le plan de tarification
     */
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Scope pour les abonnements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour trier par date de début
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('starts_at', 'desc');
    }

    /**
     * Accessor pour le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->amount, 0, ',', ' ') . ' ' . $this->currency;
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
