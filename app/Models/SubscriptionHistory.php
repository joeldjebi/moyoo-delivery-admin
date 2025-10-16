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
        'start_date',
        'end_date',
        'status',
        'features',
        'payment_method',
        'transaction_id',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
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
     * Scope pour une entreprise spécifique
     */
    public function scopeForEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour trier par date de début
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('start_date', 'desc');
    }

    /**
     * Accessor pour le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
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
        return $this->end_date < now()->toDateString();
    }
}
