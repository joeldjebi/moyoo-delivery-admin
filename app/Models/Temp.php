<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Temp extends Model
{
    use SoftDeletes;

    protected $table = 'temps';

    protected $fillable = [
        'entreprise_id',
        'libelle',
        'description',
        'heure_debut',
        'heure_fin',
        'is_weekend',
        'is_holiday',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'is_weekend' => 'boolean',
        'is_holiday' => 'boolean',
        'is_active' => 'boolean',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tarifLivraisons()
    {
        return $this->hasMany(TarifLivraison::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWeekend($query)
    {
        return $query->where('is_weekend', true);
    }

    public function scopeHoliday($query)
    {
        return $query->where('is_holiday', true);
    }

    public function scopeWorkingHours($query)
    {
        return $query->where('is_weekend', false)->where('is_holiday', false);
    }

    /**
     * Méthode pour déterminer la période temporelle actuelle
     */
    public static function getCurrentTemp()
    {
        $now = now();
        $currentTime = $now->format('H:i:s');
        $isWeekend = $now->isWeekend();
        $isHoliday = false; // Vous pouvez ajouter une logique pour les jours fériés

        // Chercher d'abord les périodes spéciales (week-end, jours fériés)
        if ($isWeekend) {
            $temp = self::active()->weekend()->first();
            if ($temp) return $temp;
        }

        if ($isHoliday) {
            $temp = self::active()->holiday()->first();
            if ($temp) return $temp;
        }

        // Chercher une période basée sur l'heure
        $temp = self::active()
                   ->workingHours()
                   ->where('heure_debut', '<=', $currentTime)
                   ->where('heure_fin', '>=', $currentTime)
                   ->first();

        // Si aucune période spécifique n'est trouvée, retourner la première période active
        return $temp ?: self::active()->first();
    }

    /**
     * Accesseur pour le format d'heure
     */
    public function getHeureDebutFormattedAttribute()
    {
        return $this->heure_debut ? $this->heure_debut->format('H:i') : null;
    }

    public function getHeureFinFormattedAttribute()
    {
        return $this->heure_fin ? $this->heure_fin->format('H:i') : null;
    }

    /**
     * Accesseur pour le type de période
     */
    public function getTypeAttribute()
    {
        if ($this->is_holiday) return 'Jour férié';
        if ($this->is_weekend) return 'Week-end';
        return 'Heures ouvrables';
    }
}
