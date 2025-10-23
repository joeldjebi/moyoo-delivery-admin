<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BalanceMarchand extends Model
{
    protected $table = 'balance_marchands';

    protected $fillable = [
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'montant_encaisse',
        'montant_reverse',
        'balance_actuelle',
        'derniere_mise_a_jour'
    ];

    protected $casts = [
        'montant_encaisse' => 'decimal:2',
        'montant_reverse' => 'decimal:2',
        'balance_actuelle' => 'decimal:2',
        'derniere_mise_a_jour' => 'datetime'
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

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueBalance::class);
    }

    /**
     * Mettre à jour la balance après encaissement
     */
    public function addEncaissement($montant, $colisId = null, $userId = null)
    {
        $balanceAvant = $this->balance_actuelle;
        $this->montant_encaisse += $montant;
        $this->balance_actuelle += $montant;
        $this->derniere_mise_a_jour = now();
        $this->save();

        // Historique
        HistoriqueBalance::create([
            'balance_marchand_id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'type_operation' => 'encaissement',
            'montant' => $montant,
            'balance_avant' => $balanceAvant,
            'balance_apres' => $this->balance_actuelle,
            'description' => 'Encaissement après livraison réussie',
            'reference' => $colisId,
            'created_by' => $userId ?? auth()->id()
        ]);

        \Log::info('Balance mise à jour - Encaissement', [
            'balance_id' => $this->id,
            'marchand_id' => $this->marchand_id,
            'montant' => $montant,
            'balance_avant' => $balanceAvant,
            'balance_apres' => $this->balance_actuelle
        ]);
    }

    /**
     * Mettre à jour la balance après reversement
     */
    public function subtractReversement($montant, $reversementId = null, $userId = null)
    {
        $balanceAvant = $this->balance_actuelle;
        $this->montant_reverse += $montant;
        $this->balance_actuelle -= $montant;
        $this->derniere_mise_a_jour = now();
        $this->save();

        // Historique
        HistoriqueBalance::create([
            'balance_marchand_id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'type_operation' => 'reversement',
            'montant' => $montant,
            'balance_avant' => $balanceAvant,
            'balance_apres' => $this->balance_actuelle,
            'description' => 'Reversement effectué',
            'reference' => $reversementId,
            'created_by' => $userId ?? auth()->id()
        ]);

        \Log::info('Balance mise à jour - Reversement', [
            'balance_id' => $this->id,
            'marchand_id' => $this->marchand_id,
            'montant' => $montant,
            'balance_avant' => $balanceAvant,
            'balance_apres' => $this->balance_actuelle
        ]);
    }

    /**
     * Scopes
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance_actuelle', '>', 0);
    }

    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeByMarchand($query, $marchandId)
    {
        return $query->where('marchand_id', $marchandId);
    }
}
