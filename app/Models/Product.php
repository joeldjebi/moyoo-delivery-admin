<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'description',
        'category_id',
        'entreprise_id',
        'price',
        'currency',
        'unit',
        'image',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Boot method pour générer automatiquement le SKU si non fourni
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec les stocks
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Relation avec les mouvements de stock
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Obtenir le stock total pour une entreprise
     */
    public function getTotalStock($entrepriseId = null)
    {
        $entrepriseId = $entrepriseId ?? $this->entreprise_id;

        return $this->stocks()
            ->where('entreprise_id', $entrepriseId)
            ->sum('quantity');
    }

    /**
     * Obtenir le stock pour un emplacement spécifique
     */
    public function getStockByLocation($location, $entrepriseId = null)
    {
        $entrepriseId = $entrepriseId ?? $this->entreprise_id;

        return $this->stocks()
            ->where('entreprise_id', $entrepriseId)
            ->where('location', $location)
            ->first();
    }

    /**
     * Scope pour les produits actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour filtrer par entreprise
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Accessor pour le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }
}
