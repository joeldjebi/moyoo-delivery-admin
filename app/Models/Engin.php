<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Engin extends Model
{
    use SoftDeletes;

    protected $table = 'engins';

    protected $fillable = [
        'entreprise_id',
        'libelle',
        'marque',
        'modele',
        'couleur',
        'immatriculation',
        'etat',
        'status',
        'type_engin_id',
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

    public function typeEngin()
    {
        return $this->belongsTo(Type_engin::class, 'type_engin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Accessors
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
