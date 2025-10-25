<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivreurLocationStatus extends Model
{
    protected $table = 'livreur_location_status';

    protected $fillable = [
        'livreur_id',
        'entreprise_id',
        'status',
        'socket_id',
        'last_updated'
    ];

    protected $casts = [
        'last_updated' => 'datetime'
    ];

    /**
     * Relation avec le livreur
     */
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class, 'livreur_id');
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Scope pour les livreurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    /**
     * Mettre à jour le statut d'un livreur
     */
    public static function updateStatus($livreurId, $status, $socketId = null, $entrepriseId = null)
    {
        $data = [
            'status' => $status,
            'socket_id' => $socketId,
            'last_updated' => now()
        ];

        if ($entrepriseId) {
            $data['entreprise_id'] = $entrepriseId;
        }

        return self::updateOrCreate(
            ['livreur_id' => $livreurId],
            $data
        );
    }
}
