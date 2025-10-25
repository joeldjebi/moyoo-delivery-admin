<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Livreur extends Authenticatable implements JWTSubject
{
    use SoftDeletes;

    protected $table = 'livreurs';

    protected $fillable = [
        'entreprise_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'mobile',
        'adresse',
        'status',
        'engin_id',
        'zone_activite_id',
        'photo',
        'permis',
        'fcm_token',
        'fcm_token_updated_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'fcm_token_updated_at' => 'datetime'
    ];

    // Accesseurs pour maintenir la compatibilité
    public function getNomAttribute()
    {
        return $this->last_name;
    }

    public function getPrenomAttribute()
    {
        return $this->first_name;
    }

    public function getTelephoneAttribute()
    {
        return $this->mobile;
    }

    public function getActifAttribute()
    {
        return $this->status === 'actif';
    }

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function colis()
    {
        return $this->hasMany(Colis::class);
    }

    public function historiqueLivraisons()
    {
        return $this->hasMany(Historique_livraison::class);
    }

    public function engin()
    {
        return $this->belongsTo(Engin::class);
    }

    public function engins()
    {
        return $this->hasMany(Engin::class);
    }

    public function zoneActivite()
    {
        return $this->belongsTo(Commune::class, 'zone_activite_id');
    }

    public function communes()
    {
        return $this->belongsToMany(Commune::class, 'livreur_commune');
    }

    public function livraisons()
    {
        return $this->hasManyThrough(Livraison::class, Colis::class, 'livreur_id', 'colis_id');
    }

    public function ramassages()
    {
        return $this->hasMany(Ramassage::class, 'livreur_id');
    }

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('status', 'inactif');
    }

    public function scopeParZone($query, $zoneId)
    {
        return $query->where('zone_activite_id', $zoneId);
    }

    public function scopeParCommune($query, $communeId)
    {
        return $query->whereHas('communes', function($q) use ($communeId) {
            $q->where('commune_id', $communeId);
        });
    }

    public function scopeParEngin($query, $enginId)
    {
        return $query->where('engin_id', $enginId);
    }

    /**
     * Accesseurs supplémentaires
     */
    public function getNomCompletAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatutLabelAttribute()
    {
        return $this->status === 'actif' ? 'Actif' : 'Inactif';
    }

    public function getStatutBadgeAttribute()
    {
        return $this->status === 'actif' ? 'success' : 'secondary';
    }

    /**
     * JWT Methods
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'type' => 'livreur',
            'entreprise_id' => $this->entreprise_id,
            'status' => $this->status
        ];
    }

    /**
     * Authentification par mobile
     */
    public function findForPassport($mobile)
    {
        return $this->where('mobile', $mobile)->first();
    }

    /**
     * Vérifier si le livreur a des livraisons en cours
     */
    public function hasActiveDeliveries()
    {
        return $this->colis()
            ->where('status', \App\Models\Colis::STATUS_EN_COURS)
            ->exists();
    }

    /**
     * Vérifier si le livreur a des ramassages en cours
     */
    public function hasActivePickups()
    {
        return \App\Models\Ramassage::where('livreur_id', $this->id)
            ->where('statut', 'en_cours')
            ->exists();
    }

    /**
     * Obtenir les livraisons en cours du livreur
     */
    public function getActiveDeliveries()
    {
        return $this->colis()
            ->where('status', \App\Models\Colis::STATUS_EN_COURS)
            ->with(['livraison', 'commune_zone'])
            ->get();
    }

    /**
     * Obtenir les ramassages en cours du livreur
     */
    public function getActivePickups()
    {
        return \App\Models\Ramassage::where('livreur_id', $this->id)
            ->where('statut', 'en_cours')
            ->with(['marchand', 'boutique'])
            ->get();
    }

    /**
     * Vérifier si le livreur peut démarrer une nouvelle livraison
     */
    public function canStartDelivery()
    {
        return !$this->hasActiveDeliveries();
    }

    /**
     * Vérifier si le livreur peut démarrer un nouveau ramassage
     */
    public function canStartPickup()
    {
        return !$this->hasActivePickups();
    }

    /**
     * Relation avec les positions de localisation
     */
    public function locations()
    {
        return $this->hasMany(LivreurLocation::class, 'livreur_id');
    }

    /**
     * Dernière position du livreur
     */
    public function lastLocation()
    {
        return $this->hasOne(LivreurLocation::class, 'livreur_id')->latest('timestamp');
    }

    /**
     * Statut de localisation du livreur
     */
    public function locationStatus()
    {
        return $this->hasOne(LivreurLocationStatus::class, 'livreur_id');
    }
}
