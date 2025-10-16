<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoriqueFraisLivraison extends Model
{
    use HasFactory;

    protected $fillable = [
        'frais_livraison_id',
        'colis_id',
        'livraison_id',
        'type_operation',
        'montant_avant',
        'montant_apres',
        'description_operation',
        'donnees_avant',
        'donnees_apres',
        'entreprise_id',
        'user_id',
        'date_operation'
    ];

    protected $casts = [
        'montant_avant' => 'decimal:2',
        'montant_apres' => 'decimal:2',
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
        'date_operation' => 'datetime'
    ];

    // Relations
    public function fraisLivraison()
    {
        return $this->belongsTo(FraisLivraison::class);
    }

    public function colis()
    {
        return $this->belongsTo(Colis::class);
    }

    public function livraison()
    {
        return $this->belongsTo(Historique_livraison::class, 'livraison_id');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeByTypeOperation($query, $type)
    {
        return $query->where('type_operation', $type);
    }

    public function scopeByDateRange($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_operation', [$dateDebut, $dateFin]);
    }

    // Accessors
    public function getTypeOperationLabelAttribute()
    {
        $types = [
            'creation' => 'Création',
            'modification' => 'Modification',
            'suppression' => 'Suppression',
            'application' => 'Application'
        ];
        return $types[$this->type_operation] ?? $this->type_operation;
    }

    public function getTypeOperationColorAttribute()
    {
        $colors = [
            'creation' => 'success',
            'modification' => 'warning',
            'suppression' => 'danger',
            'application' => 'info'
        ];
        return $colors[$this->type_operation] ?? 'secondary';
    }
}
