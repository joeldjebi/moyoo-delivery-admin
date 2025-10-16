<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune_zone extends Model
{
    protected $table = 'commune_zone';

    protected $fillable = [
        'entreprise_id',
        'zone_id',
        'commune_id',
        'ordre',
        'nom_client',
        'telephone_client',
        'adresse_client',
        'marchand_id',
        'boutique_id',
        'montant_a_encaisse',
        'prix_de_vente',
        'numero_facture',
        'type_colis_id',
        'conditionnement_colis_id',
        'poids_id',
        'mode_livraison_id',
        'delai_id',
        'numero_de_ramassage',
        'adresse_de_ramassage'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function typeColis()
    {
        return $this->belongsTo(Type_colis::class, 'type_colis_id');
    }

    public function conditionnementColis()
    {
        return $this->belongsTo(Conditionnement_colis::class, 'conditionnement_colis_id');
    }

    public function modeLivraison()
    {
        return $this->belongsTo(Mode_livraison::class, 'mode_livraison_id');
    }

    public function poids()
    {
        return $this->belongsTo(Poid::class, 'poids_id');
    }

    public function delai()
    {
        return $this->belongsTo(Delais::class, 'delai_id');
    }

    public function marchand()
    {
        return $this->belongsTo(Marchand::class, 'marchand_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class, 'boutique_id');
    }
}