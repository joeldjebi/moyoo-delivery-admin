<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'entreprise_id',
        'stock_id',
        'type',
        'quantity',
        'unit_cost',
        'reason',
        'reference',
        'user_id',
        'location',
        'quantity_before',
        'quantity_after'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer'
    ];

    /**
     * Types de mouvements disponibles
     */
    const TYPE_ENTREE = 'entree';
    const TYPE_SORTIE = 'sortie';
    const TYPE_AJUSTEMENT = 'ajustement';
    const TYPE_TRANSFERT = 'transfert';

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation avec le stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par entreprise
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour filtrer par produit
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les entrées
     */
    public function scopeEntries($query)
    {
        return $query->where('type', self::TYPE_ENTREE);
    }

    /**
     * Scope pour les sorties
     */
    public function scopeExits($query)
    {
        return $query->where('type', self::TYPE_SORTIE);
    }

    /**
     * Scope pour les ajustements
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', self::TYPE_AJUSTEMENT);
    }

    /**
     * Accessor pour le type formaté
     */
    public function getFormattedTypeAttribute()
    {
        $types = [
            self::TYPE_ENTREE => 'Entrée',
            self::TYPE_SORTIE => 'Sortie',
            self::TYPE_AJUSTEMENT => 'Ajustement',
            self::TYPE_TRANSFERT => 'Transfert'
        ];

        return $types[$this->type] ?? $this->type;
    }
}
