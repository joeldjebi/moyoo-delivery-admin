<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type_engin extends Model
{
    use SoftDeletes;

    protected $table = 'type_engins';

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
    public function engins()
    {
        return $this->hasMany(Engin::class);
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
}
