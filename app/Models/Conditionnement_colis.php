<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conditionnement_colis extends Model
{

    protected $table = 'conditionnement_colis';

    protected $fillable = [
        'libelle',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec les colis
     */
    public function colis()
    {
        return $this->hasMany(Colis::class);
    }
}
