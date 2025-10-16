<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
        'duree_estimee_minutes',
        'distance_km',
        'actif',
        'created_by'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'duree_estimee_minutes' => 'integer',
        'distance_km' => 'decimal:2',
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

    public function communes()
    {
        return $this->belongsToMany(Commune::class, 'commune_zone')
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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commune_zones()
    {
        return $this->hasMany(Commune_zone::class, 'zone_id');
    }


    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('actif', true);
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
    public function getCommuneNamesAttribute()
    {
        return $this->communes->pluck('libelle')->join(' → ');
    }

    /**
     * Récupérer la liste des communes depuis le champ nom
     */
    public function getCommunesListAttribute()
    {
        if (empty($this->nom)) {
            return [];
        }

        return array_map('trim', explode(';', $this->nom));
    }

    /**
     * Récupérer les communes formatées pour l'affichage
     */
    public function getCommunesFormattedAttribute()
    {
        $communes = $this->getCommunesListAttribute();
        return implode(' → ', $communes);
    }

    /**
     * Récupérer les initiales des communes pour le code
     */
    public function getCommunesInitialesAttribute()
    {
        $communes = $this->getCommunesListAttribute();
        $initiales = '';

        foreach ($communes as $commune) {
            if (!empty(trim($commune))) {
                $initiales .= strtoupper(substr(trim($commune), 0, 1));
            }
        }

        return $initiales;
    }

    /**
     * Vérifier si une commune est dans cette zone
     */
    public function hasCommune($communeName)
    {
        $communes = $this->getCommunesListAttribute();
        return in_array($communeName, $communes);
    }

    /**
     * Ajouter une commune à la zone
     */
    public function addCommune($communeName)
    {
        $communes = $this->getCommunesListAttribute();

        if (!in_array($communeName, $communes)) {
            $communes[] = $communeName;
            $this->nom = implode(';', $communes);
            $this->save();
        }

        return $this;
    }

    /**
     * Supprimer une commune de la zone
     */
    public function removeCommune($communeName)
    {
        $communes = $this->getCommunesListAttribute();
        $communes = array_filter($communes, function($commune) use ($communeName) {
            return trim($commune) !== trim($communeName);
        });

        $this->nom = implode(';', $communes);
        $this->save();

        return $this;
    }

    public function getColisCountAttribute()
    {
        return $this->colis()->count();
    }

    public function getColisEnAttenteCountAttribute()
    {
        return $this->colis()->where('status', 0)->count();
    }
}
