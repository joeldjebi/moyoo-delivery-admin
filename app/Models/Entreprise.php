<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'adresse',
        'commune_id',
        'statut',
        'logo',
        'created_by',
        'not_update'
    ];

    protected $casts = [
        'statut' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relation avec la commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé l'entreprise
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les historiques d'abonnement
     */
    public function subscriptionHistories()
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    /**
     * Scope pour les entreprises actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }

    /**
     * Scope pour les entreprises inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('statut', 0);
    }

    /**
     * Obtenir l'entreprise de l'utilisateur connecté
     */
    public static function getEntrepriseByUser($userId)
    {
        return self::where('created_by', $userId)->first();
    }

    /**
     * Vérifier si un utilisateur a déjà une entreprise
     */
    public static function hasEntreprise($userId)
    {
        return self::where('created_by', $userId)->exists();
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatutFormattedAttribute()
    {
        return $this->statut == 1 ? 'Actif' : 'Inactif';
    }

    /**
     * Obtenir la classe CSS pour le statut
     */
    public function getStatutClassAttribute()
    {
        return $this->statut == 1 ? 'bg-success' : 'bg-danger';
    }
}
