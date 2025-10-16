<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boutique extends Model
{
    use SoftDeletes;

    protected $table = 'boutiques';

    protected $fillable = [
        'entreprise_id',
        'libelle',
        'mobile',
        'adresse',
        'adresse_gps',
        'cover_image',
        'marchand_id',
        'status',
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

    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
    }

    public function colis()
    {
        return $this->hasManyThrough(
            Colis::class,
            Commune_zone::class,
            'marchand_id', // Clé étrangère dans commune_zone
            'zone_id',     // Clé étrangère dans colis
            'marchand_id', // Clé locale dans boutiques
            'zone_id'      // Clé locale dans commune_zone
        );
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
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'active' ? 'Actif' : 'Inactif';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active' ? 'success' : 'danger';
    }
}
