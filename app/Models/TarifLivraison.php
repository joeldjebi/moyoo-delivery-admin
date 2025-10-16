<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarifLivraison extends Model
{
    use SoftDeletes;

    protected $table = 'tarif_livraisons';

    protected $fillable = [
        'entreprise_id',
        'commune_depart_id',
        'commune_id',
        'type_engin_id',
        'mode_livraison_id',
        'poids_id',
        'temp_id',
        'amount',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function communeDepart()
    {
        return $this->belongsTo(Commune::class, 'commune_depart_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function typeEngin()
    {
        return $this->belongsTo(Type_engin::class);
    }

    public function modeLivraison()
    {
        return $this->belongsTo(Mode_livraison::class);
    }

    public function poids()
    {
        return $this->belongsTo(Poid::class);
    }

    public function temp()
    {
        return $this->belongsTo(Temp::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Méthode pour calculer le coût de livraison
     */
    public static function calculateDeliveryCost($entrepriseId, $communeDepartId, $communeId, $typeEnginId, $modeLivraisonId, $poidsId, $tempId = null)
    {
        \Log::info('🔍 RECHERCHE TARIF', [
            'entreprise_id' => $entrepriseId,
            'commune_depart_id' => $communeDepartId,
            'commune_id' => $communeId,
            'type_engin_id' => $typeEnginId,
            'mode_livraison_id' => $modeLivraisonId,
            'poids_id' => $poidsId,
            'temp_id' => $tempId
        ]);

        $query = self::active()
                    ->where('entreprise_id', $entrepriseId)
                    ->where('commune_depart_id', $communeDepartId)
                    ->where('commune_id', $communeId)
                    ->where('type_engin_id', $typeEnginId)
                    ->where('mode_livraison_id', $modeLivraisonId)
                    ->where('poids_id', $poidsId);

        if ($tempId) {
            $query->where('temp_id', $tempId);
        }

        \Log::info('📋 REQUÊTE SQL', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $tarif = $query->first();

        \Log::info('💰 TARIF TROUVÉ', [
            'tarif' => $tarif ? $tarif->toArray() : null,
            'amount' => $tarif ? $tarif->amount : 0
        ]);

        return $tarif ? $tarif->amount : 0;
    }

    /**
     * Méthode pour obtenir tous les tarifs disponibles pour une combinaison
     */
    public static function getAvailableTarifs($communeId, $typeEnginId, $modeLivraisonId, $poidsId)
    {
        return self::active()
                  ->with(['temp'])
                  ->where('commune_id', $communeId)
                  ->where('type_engin_id', $typeEnginId)
                  ->where('mode_livraison_id', $modeLivraisonId)
                  ->where('poids_id', $poidsId)
                  ->get();
    }
}
