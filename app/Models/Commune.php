<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commune extends Model
{
    use SoftDeletes;

    protected $table = 'communes';

    protected $fillable = [
        'entreprise_id',
        'libelle',
        'nom',
        'code',
        'ville_id',
        'status',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'commune_zone')
                    ->withPivot([
                        'ordre', 'nom_client', 'telephone_client', 'adresse_client',
                        'marchand_id', 'boutique_id', 'montant_a_encaisse', 'prix_de_vente',
                        'numero_facture', 'type_colis_id', 'conditionnement_colis_id',
                        'poids_id', 'mode_livraison_id', 'delai_id',
                        'numero_de_ramassage', 'adresse_de_ramassage'
                    ])
                    ->orderBy('commune_zone.ordre');
    }

    public function colis()
    {
        return $this->hasMany(Colis::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Accessors
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Méthodes utilitaires
     */
    public function getZoneNamesAttribute()
    {
        return $this->zones->pluck('nom')->join(', ');
    }

    public function getColisCountAttribute()
    {
        return $this->colis()->count();
    }
}
