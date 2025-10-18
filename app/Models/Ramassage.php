<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ramassage extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_ramassage',
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'date_demande',
        'date_planifiee',
        'date_effectuee',
        'statut',
        'adresse_ramassage',
        'contact_ramassage',
        'telephone_contact',
        'nombre_colis_estime',
        'nombre_colis_reel',
        'difference_colis',
        'type_difference',
        'raison_difference',
        'livreur_id',
        'date_debut_ramassage',
        'date_fin_ramassage',
        'photo_ramassage',
        'notes',
        'notes_livreur',
        'notes_ramassage',
        'colis_data',
        'montant_total',
        'raison_annulation',
        'commentaire_annulation',
        'date_annulation',
        'annule_par'
    ];

    protected $casts = [
        'date_demande' => 'datetime',
        'date_planifiee' => 'datetime',
        'date_effectuee' => 'date',
        'date_debut_ramassage' => 'datetime',
        'date_fin_ramassage' => 'datetime',
        'date_annulation' => 'datetime',
        'colis_data' => 'array',
        'montant_total' => 'decimal:2'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    public function livreurAnnuleur()
    {
        return $this->belongsTo(Livreur::class, 'annule_par');
    }

    // Colis liés via la table pivot (liaison simple)
    public function colisLies()
    {
        return $this->belongsToMany(Colis::class, 'ramassage_colis')
                    ->withTimestamps();
    }

    // Colis intégrés (dans colis_data JSON)
    public function getColisIntegresAttribute()
    {
        return $this->colis_data ? json_decode($this->colis_data, true) : [];
    }

    // Relation legacy (pour compatibilité)
    public function colis()
    {
        return $this->colisLies();
    }

    public function planifications()
    {
        return $this->hasMany(PlanificationRamassage::class);
    }

    // Scopes
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeByMarchand($query, $marchandId)
    {
        return $query->where('marchand_id', $marchandId);
    }

    public function scopeByBoutique($query, $boutiqueId)
    {
        return $query->where('boutique_id', $boutiqueId);
    }

    // Accessors
    public function getStatutLabelAttribute()
    {
        $labels = [
            'demande' => 'Demande',
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'demande' => 'warning',
            'planifie' => 'info',
            'en_cours' => 'primary',
            'termine' => 'success',
            'annule' => 'danger'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }
}
