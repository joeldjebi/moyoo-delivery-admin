<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marchand extends Model
{
    use SoftDeletes;

    protected $table = 'marchands';

    protected $fillable = [
        'entreprise_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'adresse',
        'status',
        'commune_id',
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

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function colis()
    {
        return $this->hasManyThrough(
            Colis::class,
            Commune_zone::class,
            'marchand_id', // Clé étrangère dans commune_zone
            'zone_id',     // Clé étrangère dans colis
            'id',          // Clé locale dans marchands
            'zone_id'      // Clé locale dans commune_zone (zone_id)
        );
    }

    public function boutiques()
    {
        return $this->hasMany(Boutique::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
