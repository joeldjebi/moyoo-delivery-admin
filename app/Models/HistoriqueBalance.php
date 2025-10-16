<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueBalance extends Model
{
    protected $table = 'historique_balance';

    protected $fillable = [
        'balance_marchand_id',
        'type_operation',
        'montant',
        'balance_avant',
        'balance_apres',
        'description',
        'reference',
        'created_by'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'balance_avant' => 'decimal:2',
        'balance_apres' => 'decimal:2'
    ];

    /**
     * Relations
     */
    public function balanceMarchand(): BelongsTo
    {
        return $this->belongsTo(BalanceMarchand::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_operation', $type);
    }

    public function scopeEncaissements($query)
    {
        return $query->where('type_operation', 'encaissement');
    }

    public function scopeReversements($query)
    {
        return $query->where('type_operation', 'reversement');
    }

    public function scopeAjustements($query)
    {
        return $query->where('type_operation', 'ajustement');
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type_operation) {
            'encaissement' => 'Encaissement',
            'reversement' => 'Reversement',
            'ajustement' => 'Ajustement',
            default => 'Inconnu'
        };
    }

    public function getTypeColorAttribute()
    {
        return match($this->type_operation) {
            'encaissement' => 'success',
            'reversement' => 'info',
            'ajustement' => 'warning',
            default => 'secondary'
        };
    }
}
