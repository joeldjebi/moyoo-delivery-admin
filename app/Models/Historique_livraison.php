<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historique_livraison extends Model
{
    use SoftDeletes;

    protected $table = 'historique_livraisons';

    protected $fillable = [
        'entreprise_id',
        'package_colis_id',
        'livraison_id',
        'status',
        'colis_id',
        'livreur_id',
        'montant_a_encaisse',
        'prix_de_vente',
        'montant_de_la_livraison',
        'created_by',
        'code_validation_utilise',
        'photo_proof_path',
        'signature_data',
        'note_livraison',
        'motif_annulation',
        'date_livraison_effective',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'date_livraison_effective' => 'datetime'
    ];

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function packageColis()
    {
        return $this->belongsTo(PackageColis::class);
    }

    public function livraison()
    {
        return $this->belongsTo(Livraison::class);
    }

    public function colis()
    {
        return $this->belongsTo(Colis::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'livre' => 'Livré',
            'annule_client' => 'Annulé par le client',
            'annule_livreur' => 'Annulé par le livreur',
            'annule_marchand' => 'Annulé par le marchand'
        ];

        return $statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badgeClasses = [
            'en_attente' => 'bg-warning',
            'en_cours' => 'bg-info',
            'livre' => 'bg-success',
            'annule_client' => 'bg-danger',
            'annule_livreur' => 'bg-danger',
            'annule_marchand' => 'bg-danger'
        ];

        return $badgeClasses[$this->status] ?? 'bg-secondary';
    }
}
