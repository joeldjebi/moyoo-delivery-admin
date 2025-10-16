<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FraisLivraison extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'description',
        'montant',
        'type_frais',
        'zone_applicable',
        'zones_specifiques',
        'actif',
        'date_debut',
        'date_fin',
        'entreprise_id',
        'created_by'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'zones_specifiques' => 'array',
        'actif' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function historique()
    {
        return $this->hasMany(HistoriqueFraisLivraison::class);
    }

    // Scopes
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type_frais', $type);
    }

    public function scopeByZone($query, $zone)
    {
        return $query->where('zone_applicable', $zone);
    }

    // Accessors
    public function getTypeFraisLabelAttribute()
    {
        $types = [
            'fixe' => 'Frais Fixe',
            'pourcentage' => 'Pourcentage',
            'par_km' => 'Par Kilomètre',
            'par_colis' => 'Par Colis'
        ];
        return $types[$this->type_frais] ?? $this->type_frais;
    }

    public function getZoneApplicableLabelAttribute()
    {
        $zones = [
            'toutes' => 'Toutes les zones',
            'urbain' => 'Zone urbaine',
            'rural' => 'Zone rurale',
            'specifique' => 'Zones spécifiques'
        ];
        return $zones[$this->zone_applicable] ?? $this->zone_applicable;
    }

    public function getStatutLabelAttribute()
    {
        return $this->actif ? 'Actif' : 'Inactif';
    }

    public function getStatutColorAttribute()
    {
        return $this->actif ? 'success' : 'secondary';
    }
}
