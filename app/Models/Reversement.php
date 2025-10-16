<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reversement extends Model
{
    protected $fillable = [
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'montant_reverse',
        'mode_reversement',
        'reference_reversement',
        'statut',
        'date_reversement',
        'notes',
        'justificatif_path',
        'created_by',
        'validated_by'
    ];

    protected $casts = [
        'montant_reverse' => 'decimal:2',
        'date_reversement' => 'datetime'
    ];

    /**
     * Relations
     */
    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class);
    }

    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function balanceMarchand()
    {
        return BalanceMarchand::where('marchand_id', $this->marchand_id)
                              ->where('boutique_id', $this->boutique_id)
                              ->where('entreprise_id', $this->entreprise_id)
                              ->first();
    }

    /**
     * Générer une référence unique
     */
    public static function generateReference()
    {
        return 'REV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Scopes
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }

    /**
     * Accessors
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'En Attente',
            'valide' => 'Validé',
            'annule' => 'Annulé',
            default => 'Inconnu'
        };
    }

    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'valide' => 'success',
            'annule' => 'danger',
            default => 'secondary'
        };
    }

    public function getModeLabelAttribute()
    {
        return match($this->mode_reversement) {
            'especes' => 'Espèces',
            'virement' => 'Virement',
            'mobile_money' => 'Mobile Money',
            'cheque' => 'Chèque',
            default => 'Inconnu'
        };
    }
}
