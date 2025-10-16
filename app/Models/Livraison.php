<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Livraison extends Model
{
    use SoftDeletes;

    protected $table = 'livraisons';

    protected $fillable = [
        'entreprise_id',
        'uuid',
        'numero_de_livraison',
        'colis_id',
        'package_colis_id',
        'marchand_id',
        'boutique_id',
        'adresse_de_livraison',
        'status',
        'note_livraison',
        'code_validation',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'status' => 'integer'
    ];

    /**
     * Constantes pour les statuts
     */
    const STATUS_EN_ATTENTE = 0;
    const STATUS_EN_COURS = 1;
    const STATUS_LIVRE = 2;
    const STATUS_ANNULE_CLIENT = 3;
    const STATUS_ANNULE_LIVREUR = 4;
    const STATUS_ANNULE_MARCHAND = 5;

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function colis()
    {
        return $this->belongsTo(Colis::class);
    }

    public function packageColis()
    {
        return $this->belongsTo(PackageColis::class);
    }

    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function codeValidations()
    {
        return $this->hasMany(Code_validation::class);
    }

    public function historiqueLivraisons()
    {
        return $this->hasMany(Historique_livraison::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accesseurs
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_EN_ATTENTE => 'En attente',
            self::STATUS_EN_COURS => 'En cours',
            self::STATUS_LIVRE => 'Livré',
            self::STATUS_ANNULE_CLIENT => 'Annulé par le client',
            self::STATUS_ANNULE_LIVREUR => 'Annulé par le livreur',
            self::STATUS_ANNULE_MARCHAND => 'Annulé par le marchand',
            default => 'Statut inconnu'
        };
    }
}
