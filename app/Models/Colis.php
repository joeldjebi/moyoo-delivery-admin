<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Colis extends Model
{
    use SoftDeletes;

    protected $table = 'colis';

    protected $fillable = [
        'entreprise_id',
        'uuid',
        'code',
        'status',
        'nom_client',
        'telephone_client',
        'adresse_client',
        'montant_a_encaisse',
        'prix_de_vente',
        'numero_facture',
        'note_client',
        'instructions_livraison',
        'zone_id',
        'commune_id',
        'package_colis_id',
        'ordre_livraison',
        'date_livraison_prevue',
        'livreur_id',
        'engin_id',
        'poids_id',
        'mode_livraison_id',
        'temp_id',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'status' => 'integer',
        'date_livraison_prevue' => 'datetime',
        'ordre_livraison' => 'integer'
    ];

    /**
     * Boot method to generate UUID and code automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($colis) {
            if (empty($colis->uuid)) {
                $colis->uuid = Str::uuid();
            }

            if (empty($colis->code)) {
                $colis->code = $colis->generateCode();
            }
        });
    }

    /**
     * Constantes pour les statuts
     */
    const STATUS_EN_ATTENTE = 0;
    const STATUS_EN_COURS = 1;
    const STATUS_LIVRE = 2;
    const STATUS_ANNULE_CLIENT = 3;
    const STATUS_ANNULE_LIVREUR = 4;
    const STATUS_ANNULE_MARCHAND = 5;

    /**
     * Relations
     */
    public function zone()
    {
        return $this->belongsTo(Commune::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    public function engin()
    {
        return $this->belongsTo(Engin::class);
    }

    public function poids()
    {
        return $this->belongsTo(Poid::class, 'poids_id');
    }

    public function modeLivraison()
    {
        return $this->belongsTo(Mode_livraison::class, 'mode_livraison_id');
    }

    public function temp()
    {
        return $this->belongsTo(Temp::class, 'temp_id');
    }

    public function marchand()
    {
        return $this->hasOneThrough(
            Marchand::class,
            Commune_zone::class,
            'zone_id',     // Clé locale dans commune_zone
            'id',          // Clé locale dans marchands
            'zone_id',     // Clé étrangère dans colis
            'marchand_id'  // Clé étrangère dans commune_zone
        );
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function packageColis()
    {
        return $this->belongsTo(PackageColis::class);
    }

    public function livraisons()
    {
        return $this->hasMany(Livraison::class);
    }

    // Ramassages liés via la table pivot
    public function ramassages()
    {
        return $this->belongsToMany(Ramassage::class, 'ramassage_colis')
                    ->withTimestamps();
    }

    // Ramassage principal (le plus récent)
    public function ramassagePrincipal()
    {
        return $this->ramassages()->latest()->first();
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class)->latest();
    }

    public function historiqueLivraisons()
    {
        return $this->hasMany(Historique_livraison::class);
    }
    public function conditionnementColis()
    {
        return $this->belongsTo(Conditionnement_colis::class, 'conditionnement_colis_id');
    }
    // Note: Les colis n'ont pas de relation directe avec la table delais
    // Ils utilisent temp_id qui fait référence à la table temps (créneaux horaires)
    // public function delai()
    // {
    //     return $this->belongsTo(Delais::class, 'delai_id');
    // }

    public function commune_zone()
    {
        return $this->belongsTo(Commune_zone::class, 'zone_id', 'zone_id');
    }

    // Relations via commune_zone
    public function typeColis()
    {
        return $this->hasOneThrough(
            Type_colis::class,
            Commune_zone::class,
            'zone_id',     // Clé locale dans commune_zone
            'id',          // Clé locale dans type_colis
            'zone_id',     // Clé étrangère dans colis
            'type_colis_id' // Clé étrangère dans commune_zone
        );
    }

    public function delai()
    {
        return $this->hasOneThrough(
            Delais::class,
            Commune_zone::class,
            'zone_id',     // Clé locale dans commune_zone
            'id',          // Clé locale dans delais
            'zone_id',     // Clé étrangère dans colis
            'delai_id'     // Clé étrangère dans commune_zone
        );
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('status', self::STATUS_EN_ATTENTE);
    }

    public function scopeEnCours($query)
    {
        return $query->where('status', self::STATUS_EN_COURS);
    }

    public function scopeLivre($query)
    {
        return $query->where('status', self::STATUS_LIVRE);
    }

    public function scopeParZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeParCommune($query, $communeId)
    {
        return $query->where('commune_id', $communeId);
    }

    public function scopeParLivreur($query, $livreurId)
    {
        return $query->where('livreur_id', $livreurId);
    }

    /**
     * Créer automatiquement une livraison pour ce colis
     */
    public function createLivraison($marchandId = null, $boutiqueId = null)
    {
        $livraison = Livraison::create([
            'entreprise_id' => $this->entreprise_id,
            'uuid' => Str::uuid(),
            'numero_de_livraison' => 'LIV-' . strtoupper(Str::random(8)),
            'colis_id' => $this->id,
            'package_colis_id' => $this->package_colis_id,
            'marchand_id' => $marchandId,
            'boutique_id' => $boutiqueId,
            'adresse_de_livraison' => $this->adresse_client,
            'status' => Livraison::STATUS_EN_ATTENTE,
            'note_livraison' => $this->instructions_livraison,
            'code_validation' => str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT),
            'created_by' => auth()->user()->id ?? 1
        ]);

        // Créer automatiquement l'historique de livraison
        $this->createHistoriqueLivraison($livraison);

        return $livraison;
    }

    /**
     * Créer automatiquement l'historique de livraison
     */
    public function createHistoriqueLivraison($livraison)
    {
        // Récupérer les montants directement depuis le colis
        $montantEncaisse = $this->montant_a_encaisse ?? 0;
        $prixVente = $this->prix_de_vente ?? 0;

        // Calculer le montant de livraison en utilisant la méthode du modèle
        $montantLivraison = $this->calculateDeliveryCost();

        // Convertir en entier (la colonne est de type integer)
        $montantLivraison = (int) round((float) $montantLivraison);

        // Créer l'historique de livraison
        \App\Models\Historique_livraison::create([
            'entreprise_id' => $this->entreprise_id,
            'package_colis_id' => $this->package_colis_id,
            'livraison_id' => $livraison->id,
            'status' => 'en_attente',
            'colis_id' => $this->id,
            'livreur_id' => $this->livreur_id,
            'montant_a_encaisse' => $montantEncaisse,
            'prix_de_vente' => $prixVente,
            'montant_de_la_livraison' => $montantLivraison,
            'created_by' => auth()->user()->id ?? 1
        ]);
    }

    /**
     * Mettre à jour la livraison associée
     */
    public function updateLivraison()
    {
        $livraison = $this->livraison;
        if ($livraison) {
            $livraison->update([
                'adresse_de_livraison' => $this->adresse_client,
                'note_livraison' => $this->instructions_livraison,
                'status' => $this->status == self::STATUS_EN_ATTENTE ? Livraison::STATUS_EN_ATTENTE :
                           ($this->status == self::STATUS_EN_COURS ? Livraison::STATUS_EN_COURS :
                           ($this->status == self::STATUS_LIVRE ? Livraison::STATUS_LIVRE : $livraison->status))
            ]);

            // Mettre à jour l'historique de livraison
            $this->updateHistoriqueLivraison($livraison);
        }
    }

    /**
     * Mettre à jour l'historique de livraison
     */
    public function updateHistoriqueLivraison($livraison)
    {
        // Récupérer les montants directement depuis le colis
        $montantEncaisse = $this->montant_a_encaisse ?? 0;
        $prixVente = $this->prix_de_vente ?? 0;

        // Calculer le montant de livraison en utilisant la méthode du modèle
        $montantLivraison = $this->calculateDeliveryCost();

        // Convertir en entier (la colonne est de type integer)
        $montantLivraison = (int) round((float) $montantLivraison);

        // Mapper le statut du colis vers le statut de l'historique
        $statusHistorique = match($this->status) {
            self::STATUS_EN_ATTENTE => 'en_attente',
            self::STATUS_EN_COURS => 'en_cours',
            self::STATUS_LIVRE => 'livre',
            self::STATUS_ANNULE_CLIENT => 'annule_client',
            self::STATUS_ANNULE_LIVREUR => 'annule_livreur',
            self::STATUS_ANNULE_MARCHAND => 'annule_marchand',
            default => 'en_attente'
        };

        // Chercher l'historique existant ou en créer un nouveau
        $historique = \App\Models\Historique_livraison::where('colis_id', $this->id)->first();

        if ($historique) {
            // Mettre à jour l'historique existant
            $historique->update([
                'status' => $statusHistorique,
                'livreur_id' => $this->livreur_id,
                'montant_a_encaisse' => $montantEncaisse,
                'prix_de_vente' => $prixVente,
                'montant_de_la_livraison' => $montantLivraison,
            ]);
        } else {
            // Créer un nouvel historique si il n'existe pas
            \App\Models\Historique_livraison::create([
                'entreprise_id' => $this->entreprise_id,
                'package_colis_id' => $this->package_colis_id,
                'livraison_id' => $livraison->id,
                'status' => $statusHistorique,
                'colis_id' => $this->id,
                'livreur_id' => $this->livreur_id,
                'montant_a_encaisse' => $montantEncaisse,
                'prix_de_vente' => $prixVente,
                'montant_de_la_livraison' => $montantLivraison,
                'created_by' => auth()->user()->id ?? 1
            ]);
        }
    }

    /**
     * Accesseurs
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_EN_ATTENTE => 'En attente',
            self::STATUS_EN_COURS => 'En cours',
            self::STATUS_LIVRE => 'Livré',
            self::STATUS_ANNULE_CLIENT => 'Annulé par le client',
            self::STATUS_ANNULE_LIVREUR => 'Annulé par le livreur',
            self::STATUS_ANNULE_MARCHAND => 'Annulé par le marchand',
            default => 'Statut inconnu'
        };
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Méthodes utilitaires
     */
    public function peutEtreOptimise()
    {
        return $this->status === self::STATUS_EN_ATTENTE &&
               $this->zone_id !== null &&
               $this->livreur_id === null;
    }

    public function estDansLaMemeZone($autreColis)
    {
        return $this->zone_id === $autreColis->zone_id;
    }

    public function calculerOrdreLivraison()
    {
        if (!$this->zone_id) {
            return null;
        }

        // Calculer l'ordre basé sur la commune dans la zone
        $commune = $this->zone; // zone_id pointe vers commune_id

        if ($commune) {
            // Récupérer l'ordre depuis commune_zone
            $communeZone = DB::table('commune_zone')
                ->where('commune_id', $commune->id)
                ->orderBy('ordre')
                ->first();

            return $communeZone ? $communeZone->ordre : null;
        }

        return null;
    }

    /**
     * Accesseurs pour récupérer les informations depuis la zone
     */
    public function getNomClientAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['nom_client'] ?? $this->getZoneAttributeValue('nom_client');
    }

    public function getTelephoneClientAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['telephone_client'] ?? $this->getZoneAttributeValue('telephone_client');
    }

    public function getAdresseClientAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['adresse_client'] ?? $this->getZoneAttributeValue('adresse_client');
    }

    public function getMarchandIdAttribute()
    {
        return $this->getZoneAttributeValue('marchand_id');
    }

    public function getBoutiqueIdAttribute()
    {
        return $this->getZoneAttributeValue('boutique_id');
    }

    public function getMontantAEncaisseAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['montant_a_encaisse'] ?? $this->getZoneAttributeValue('montant_a_encaisse');
    }

    public function getPrixDeVenteAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['prix_de_vente'] ?? $this->getZoneAttributeValue('prix_de_vente');
    }

    public function getNumeroFactureAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['numero_facture'] ?? $this->getZoneAttributeValue('numero_facture');
    }

    public function getTypeColisIdAttribute()
    {
        return $this->getZoneAttributeValue('type_colis_id');
    }

    public function getConditionnementColisIdAttribute()
    {
        return $this->getZoneAttributeValue('conditionnement_colis_id');
    }

    public function getPoidsIdAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['poids_id'] ?? $this->getZoneAttributeValue('poids_id');
    }

    public function getModeLivraisonIdAttribute()
    {
        // Retourner la valeur de la table colis si elle existe, sinon chercher dans commune_zone
        return $this->attributes['mode_livraison_id'] ?? $this->getZoneAttributeValue('mode_livraison_id');
    }

    public function getDelaiIdAttribute()
    {
        return $this->getZoneAttributeValue('delai_id');
    }

    public function getNumeroDeRamassageAttribute()
    {
        return $this->getZoneAttributeValue('numero_de_ramassage');
    }

    public function getAdresseDeRamassageAttribute()
    {
        return $this->getZoneAttributeValue('adresse_de_ramassage');
    }

    /**
     * Méthode utilitaire pour récupérer un attribut depuis la zone
     */
    public function getZoneAttributeValue($attribute)
    {
        if (!$this->zone_id) {
            return null;
        }

        // D'abord, essayer de récupérer directement depuis commune_zone
        $communeZone = DB::table('commune_zone')
            ->where('zone_id', $this->zone_id)
            ->orderBy('ordre')
            ->first();

        if ($communeZone && $communeZone->$attribute !== null) {
            return $communeZone->$attribute;
        }

        // Si pas trouvé et que commune_id existe, essayer la relation pivot
        if ($this->commune_id) {
            $pivot = DB::table('commune_zone')
                ->where('commune_id', $this->commune_id)
                ->where('zone_id', $this->zone_id)
                ->first();
            return $pivot ? $pivot->$attribute : null;
        }

        return null;
    }

    /**
     * Générer le code du colis avec la nomenclature : CLIS-XXXXXX-INITIALES_ZONE
     */
    public function generateCode()
    {
        // Récupérer le prochain numéro séquentiel
        $lastColis = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastColis ? $lastColis->id + 1 : 1;
        $sequentialNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // Récupérer les initiales de la zone
        $zoneInitiales = '';
        if ($this->zone_id) {
            $zone = Zone::find($this->zone_id);
            if ($zone) {
                $zoneInitiales = $zone->communes_initiales;
            }
        }

        // Si pas d'initiales, utiliser "GEN" par défaut
        if (empty($zoneInitiales)) {
            $zoneInitiales = 'GEN';
        }

        return "CLIS-{$sequentialNumber}-{$zoneInitiales}";
    }

    /**
     * Appliquer les valeurs par défaut de la zone
     */
    public function appliquerValeursParDefaut()
    {
        if ($this->marchand_par_defaut && !$this->marchand_id) {
            $this->marchand_id = $this->marchand_par_defaut;
        }

        if ($this->client_par_defaut && !$this->nom_client) {
            $this->nom_client = $this->client_par_defaut;
        }

        $this->ordre_livraison = $this->calculerOrdreLivraison();
    }

    /**
     * Calculer le coût de livraison pour ce colis
     */
    public function calculateDeliveryCost()
    {
        if (!$this->commune_id || !$this->engin_id || !$this->poids_id) {
            return 0;
        }

        // Récupérer le type d'engin et le mode de livraison
        $engin = $this->engin;
        $modeLivraison = $this->modeLivraison;

        if (!$engin || !$modeLivraison) {
            return 0;
        }

        // Utiliser la période temporelle du colis ou déterminer la période actuelle
        $tempId = $this->temp_id;
        if (!$tempId) {
            $temp = \App\Models\Temp::getCurrentTemp();
            $tempId = $temp ? $temp->id : null;
        }

        // Récupérer l'entreprise du colis
        if (!$this->entreprise_id) {
            return 0; // Pas d'entreprise = pas de tarif
        }

        $entreprise = $this->entreprise;
        if (!$entreprise) {
            return 0; // Entreprise introuvable
        }

        return \App\Models\TarifLivraison::calculateDeliveryCost(
            $this->entreprise_id,
            $entreprise->commune_id,
            $this->commune_id,
            $engin->type_engin_id,
            $modeLivraison->id,
            $this->poids_id,
            $tempId
        );
    }

    /**
     * Accesseur pour le coût de livraison formaté
     */
    public function getDeliveryCostFormattedAttribute()
    {
        $cost = $this->calculateDeliveryCost();
        return number_format($cost, 0, ',', ' ') . ' FCFA';
    }
}
