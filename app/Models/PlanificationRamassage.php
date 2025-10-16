<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanificationRamassage extends Model
{
    use HasFactory;

    protected $table = 'planification_ramassage';

    protected $fillable = [
        'ramassage_id',
        'livreur_id',
        'date_planifiee',
        'heure_debut',
        'heure_fin',
        'zone_ramassage',
        'ordre_visite',
        'statut_planification',
        'notes_planification'
    ];

    protected $casts = [
        'date_planifiee' => 'datetime',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i'
    ];

    // Relations
    public function ramassage()
    {
        return $this->belongsTo(Ramassage::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    // Scopes
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut_planification', $statut);
    }

    public function scopeByLivreur($query, $livreurId)
    {
        return $query->where('livreur_id', $livreurId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date_planifiee', $date);
    }

    // Accessors
    public function getStatutLabelAttribute()
    {
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé'
        ];

        return $labels[$this->statut_planification] ?? $this->statut_planification;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'planifie' => 'info',
            'en_cours' => 'primary',
            'termine' => 'success',
            'annule' => 'danger'
        ];

        return $colors[$this->statut_planification] ?? 'secondary';
    }
}
