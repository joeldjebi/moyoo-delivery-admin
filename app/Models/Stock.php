<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'entreprise_id',
        'quantity',
        'min_quantity',
        'max_quantity',
        'unit_cost',
        'location'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'unit_cost' => 'decimal:2'
    ];

    /**
     * Relation avec le produit
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec les mouvements de stock
     */
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Vérifier si le stock est en alerte (en dessous du minimum)
     */
    public function isLowStock()
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Vérifier si le stock est au maximum
     */
    public function isMaxStock()
    {
        if ($this->max_quantity === null) {
            return false;
        }
        return $this->quantity >= $this->max_quantity;
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
     * Scope pour les stocks en alerte
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    /**
     * Scope pour filtrer par emplacement
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}
