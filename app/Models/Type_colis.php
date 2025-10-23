<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type_colis extends Model
{
    use SoftDeletes;

    protected $table = 'type_colis';

    public $timestamps = true;

    protected $fillable = [
        'libelle',
        'created_by',
        'entreprise_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function colis()
    {
        // Note: Cette relation n'est pas utilisée car la table colis n'a pas de colonne type_colis_id
        // return $this->hasMany(Colis::class);
        return $this->hasMany(Colis::class, 'id', 'id')->whereRaw('1=0'); // Relation vide pour éviter les erreurs
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
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
}
