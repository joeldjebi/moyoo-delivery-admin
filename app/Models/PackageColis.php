<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageColis extends Model
{
    protected $fillable = [
        'entreprise_id',
        'numero_package',
        'marchand_id',
        'boutique_id',
        'nombre_colis',
        'communes_selected',
        'colis_ids',
        'livreur_id',
        'engin_id',
        'statut',
        'created_by'
    ];

    protected $casts = [
        'communes_selected' => 'array',
        'colis_ids' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Relation avec le marchand
     */
    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class);
    }

    /**
     * Relation avec la boutique
     */
    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }

    /**
     * Relation avec le livreur
     */
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    /**
     * Relation avec l'engin
     */
    public function engin(): BelongsTo
    {
        return $this->belongsTo(Engin::class);
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commune_zone()
    {
        return $this->belongsTo(Commune_zone::class);
    }

    /**
     * Relation avec les colis
     */
    public function colis(): HasMany
    {
        return $this->hasMany(Colis::class);
    }

    /**
     * Générer un numéro de package unique
     */
    public static function generatePackageNumber(): string
    {
        $prefix = 'PKG';
        $date = now()->format('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $date . $random;
    }

    /**
     * Récupérer les IDs des colis sous forme de tableau
     */
    public function getColisIdsArray(): array
    {
        if (!$this->colis_ids) {
            return [];
        }

        // Si c'est déjà un tableau, le retourner
        if (is_array($this->colis_ids)) {
            return array_filter($this->colis_ids);
        }

        // Si c'est une chaîne, la convertir en tableau
        if (is_string($this->colis_ids)) {
            // Essayer d'abord de décoder comme JSON
            $decoded = json_decode($this->colis_ids, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_filter($decoded);
            }

            // Sinon, traiter comme une chaîne séparée par des virgules
            return array_filter(explode(',', $this->colis_ids));
        }

        // Si c'est un entier, le convertir en tableau
        if (is_numeric($this->colis_ids)) {
            return [$this->colis_ids];
        }

        return [];
    }

    /**
     * Compter le nombre de colis dans le package
     */
    public function getColisCountAttribute(): int
    {
        return count($this->getColisIdsArray());
    }

    /**
     * Récupérer les colis via leurs IDs stockés
     */
    public function getColisByIds()
    {
        $colisIds = $this->getColisIdsArray();
        if (empty($colisIds)) {
            return collect();
        }

        return Colis::whereIn('id', $colisIds)->get();
    }

    /**
     * Récupérer les codes des colis via leurs IDs stockés
     */
    public function getColisCodes()
    {
        $colisIds = $this->getColisIdsArray();
        if (empty($colisIds)) {
            return [];
        }

        return Colis::whereIn('id', $colisIds)->pluck('code', 'id')->toArray();
    }

    /**
     * Vérifier si un colis appartient à ce package
     */
    public function hasColis($colisId): bool
    {
        $colisIds = $this->getColisIdsArray();
        return in_array($colisId, $colisIds);
    }

    /**
     * Ajouter un colis au package
     */
    public function addColis($colisId): void
    {
        $colisIds = $this->getColisIdsArray();
        if (!in_array($colisId, $colisIds)) {
            $colisIds[] = $colisId;
            $this->update(['colis_ids' => $colisIds]);
        }
    }

    /**
     * Retirer un colis du package
     */
    public function removeColis($colisId): void
    {
        $colisIds = $this->getColisIdsArray();
        $colisIds = array_filter($colisIds, function($id) use ($colisId) {
            return $id != $colisId;
        });
        $this->update(['colis_ids' => array_values($colisIds)]);
    }

    /**
     * Compter le nombre de communes sélectionnées
     */
    public function getCommunesCountAttribute(): int
    {
        if (is_array($this->communes_selected)) {
            return count($this->communes_selected);
        } elseif (is_string($this->communes_selected)) {
            // Si c'est une chaîne JSON, la décoder
            $decoded = json_decode($this->communes_selected, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return count($decoded);
            } else {
                // Sinon, traiter comme une chaîne séparée par des virgules
                $communes = array_filter(explode(',', $this->communes_selected));
                return count($communes);
            }
        }
        return 0;
    }

    /**
     * Récupérer les communes sélectionnées sous forme de tableau
     */
    public function getCommunesArrayAttribute(): array
    {
        if (is_array($this->communes_selected)) {
            return $this->communes_selected;
        } elseif (is_string($this->communes_selected)) {
            // Si c'est une chaîne JSON, la décoder
            $decoded = json_decode($this->communes_selected, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            } else {
                // Sinon, traiter comme une chaîne séparée par des virgules
                return array_filter(explode(',', $this->communes_selected));
            }
        }
        return [];
    }
}
