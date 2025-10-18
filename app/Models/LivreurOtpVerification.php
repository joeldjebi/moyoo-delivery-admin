<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LivreurOtpVerification extends Model
{
    protected $table = 'livreur_otp_verifications';

    protected $fillable = [
        'mobile',
        'otp_code',
        'type',
        'is_verified',
        'expires_at',
        'verified_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    /**
     * Scope pour les codes non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope pour les codes non vérifiés
     */
    public function scopeNotVerified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope pour un numéro de téléphone spécifique
     */
    public function scopeForMobile($query, $mobile)
    {
        return $query->where('mobile', $mobile);
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Marquer comme vérifié
     */
    public function markAsVerified()
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now()
        ]);
    }

    /**
     * Relation avec le livreur
     */
    public function livreur()
    {
        return $this->belongsTo(Livreur::class, 'mobile', 'mobile');
    }
}
