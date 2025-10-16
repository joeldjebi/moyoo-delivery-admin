<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RamassageColis extends Model
{
    use HasFactory;

    protected $table = 'ramassage_colis';

    protected $fillable = [
        'ramassage_id',
        'colis_id',
        'statut_colis',
        'notes_colis',
        'date_ramassage',
        'photo_ramassage'
    ];

    protected $casts = [
        'date_ramassage' => 'datetime'
    ];

    // Relations
    public function ramassage()
    {
        return $this->belongsTo(Ramassage::class);
    }

    public function colis()
    {
        return $this->belongsTo(Colis::class);
    }

    // Scopes
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut_colis', $statut);
    }

    // Accessors
    public function getStatutLabelAttribute()
    {
        $labels = [
            'attendu' => 'Attendu',
            'ramasse' => 'Ramasse',
            'manquant' => 'Manquant',
            'endommage' => 'Endommagé',
            'refuse' => 'Refusé'
        ];

        return $labels[$this->statut_colis] ?? $this->statut_colis;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'attendu' => 'warning',
            'ramasse' => 'success',
            'manquant' => 'danger',
            'endommage' => 'danger',
            'refuse' => 'secondary'
        ];

        return $colors[$this->statut_colis] ?? 'secondary';
    }
}
