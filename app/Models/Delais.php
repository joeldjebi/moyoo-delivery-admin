<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delais extends Model
{
    use SoftDeletes;

    protected $table = 'delais';

    protected $fillable = [
        'entreprise_id',
        'libelle',
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

    // Note: Les délais ne sont pas directement liés aux colis via une clé étrangère
    // Les colis utilisent temp_id qui fait référence à la table temps (créneaux horaires)
    // public function colis()
    // {
    //     return $this->hasMany(Colis::class, 'delai_id');
    // }

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

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
