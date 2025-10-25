<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivreurLocation extends Model
{
    protected $fillable = [
        'livreur_id',
        'entreprise_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'heading',
        'timestamp',
        'status',
        'ramassage_id',
        'historique_livraison_id'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'timestamp' => 'datetime'
    ];

    /**
     * Relation avec le livreur
     */
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class, 'livreur_id');
    }

    /**
     * Relation avec le ramassage
     */
    public function ramassage(): BelongsTo
    {
        return $this->belongsTo(Ramassage::class, 'ramassage_id');
    }

    /**
     * Relation avec l'historique de livraison
     */
    public function historiqueLivraison(): BelongsTo
    {
        return $this->belongsTo(HistoriqueLivraison::class, 'historique_livraison_id');
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Calculer la distance entre deux points
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // Rayon de la Terre en mètres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $R * $c;
    }

    /**
     * Scope pour les positions récentes
     */
    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('timestamp', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope pour un livreur spécifique
     */
    public function scopeForLivreur($query, $livreurId)
    {
        return $query->where('livreur_id', $livreurId);
    }

    /**
     * Scope pour une entreprise spécifique
     */
    public function scopeForEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }
}
