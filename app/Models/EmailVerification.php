<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    protected $table = 'email_verifications';

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified',
        'user_data'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
        'user_data' => 'array'
    ];

    /**
     * Vérifier si l'OTP est expiré
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Vérifier si l'OTP est valide
     */
    public function isValid($otp)
    {
        return !$this->isExpired() &&
               $this->otp === $otp &&
               !$this->verified;
    }

    /**
     * Marquer comme vérifié
     */
    public function markAsVerified()
    {
        $this->update(['verified' => true]);
    }

    /**
     * Générer un OTP de 6 chiffres
     */
    public static function generateOTP()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Créer une nouvelle vérification
     */
    public static function createVerification($email, $userData = null)
    {
        // Supprimer les anciennes vérifications pour cet email
        self::where('email', $email)->delete();

        return self::create([
            'email' => $email,
            'otp' => self::generateOTP(),
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes d'expiration
            'user_data' => $userData
        ]);
    }

    /**
     * Scope pour les vérifications non expirées
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope pour les vérifications non vérifiées
     */
    public function scopeNotVerified($query)
    {
        return $query->where('verified', false);
    }
}
