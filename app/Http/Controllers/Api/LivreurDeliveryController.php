<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\Historique_livraison;
use App\Models\Livraison;
use App\Models\PackageColis;
use App\Models\Commune;
use App\Models\BalanceMarchand;
use App\Models\Marchand;
use App\Helpers\ImageCompressor;
use App\Traits\SendsFirebaseNotifications;
use App\Notifications\DeliveryCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LivreurDeliveryController extends Controller
{
    use SendsFirebaseNotifications;
    /**
     * @OA\Get(
     *     path="/api/livreur/colis-assignes",
     *     summary="Liste des colis assignés au livreur",
     *     description="Récupère la liste des colis assignés au livreur connecté avec filtrage par statut",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut du colis",
     *         required=false,
     *         @OA\Schema(type="string", enum={"en_attente", "en_cours", "livre", "annule"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des colis assignés récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Colis assignés récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="CLIS-000001"),
     *                     @OA\Property(property="status", type="integer", example=0),
     *                     @OA\Property(property="nom_client", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="telephone_client", type="string", example="0123456789"),
     *                     @OA\Property(property="adresse_client", type="string", example="123 Rue de la Paix"),
     *                     @OA\Property(property="montant_a_encaisse", type="number", format="float", example=50000.00),
     *                     @OA\Property(property="prix_de_vente", type="number", format="float", example=45000.00),
     *                     @OA\Property(property="date_livraison_prevue", type="string", format="date", example="2025-10-13"),
     *                     @OA\Property(property="ordre_livraison", type="integer", example=1),
     *                     @OA\Property(property="livreur_id", type="integer", example=5),
     *                     @OA\Property(property="engin_id", type="integer", example=1),
     *                     @OA\Property(property="poids_id", type="integer", example=4),
     *                     @OA\Property(property="mode_livraison_id", type="integer", example=2),
     *                     @OA\Property(property="temp_id", type="integer", example=2),
     *                     @OA\Property(
     *                         property="commune",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Cocody")
     *                     ),
     *                     @OA\Property(
     *                         property="livreur",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="nom_complet", type="string", example="Jean Dupont")
     *                     ),
     *                     @OA\Property(
     *                         property="engin",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="libelle", type="string", example="Moto")
     *                     ),
     *                     @OA\Property(
     *                         property="poids",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=4),
     *                         @OA\Property(property="libelle", type="string", example="5-10 kg")
     *                     ),
     *                     @OA\Property(
     *                         property="modeLivraison",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="libelle", type="string", example="Express")
     *                     ),
     *                     @OA\Property(
     *                         property="temp",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="libelle", type="string", example="Urgent")
     *                     ),
     *                     @OA\Property(
     *                         property="livraison",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="numero_de_livraison", type="string", example="LIV-000001"),
     *                         @OA\Property(property="code_validation", type="string", example="12345")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="statistiques",
     *                 type="object",
     *                 @OA\Property(property="colis_en_attente", type="integer", example=5, description="Nombre de colis en attente"),
     *                 @OA\Property(property="colis_en_cours", type="integer", example=3, description="Nombre de colis en cours de livraison"),
     *                 @OA\Property(property="colis_livres", type="integer", example=12, description="Nombre de colis livrés"),
     *                 @OA\Property(property="colis_annules", type="integer", example=1, description="Nombre de colis annulés"),
     *                 @OA\Property(property="total", type="integer", example=21, description="Total des colis"),
     *                 @OA\Property(property="montant_total_encaisse", type="number", format="float", example=1250000.00, description="Montant total à encaisser")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function getColisAssignes(Request $request)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            $query = Colis::with(['commune', 'livraison', 'packageColis', 'livreur', 'engin', 'poids', 'modeLivraison', 'temp'])
                ->where('livreur_id', $livreur->id);

            // Filtrer par statut si fourni
            if ($request->has('statut')) {
                $statutMap = [
                    'en_attente' => 0,
                    'en_cours' => 1,
                    'livre' => 2,
                    'annule' => [3, 4, 5] // Annulé par client, livreur ou marchand
                ];

                if (isset($statutMap[$request->statut])) {
                    if (is_array($statutMap[$request->statut])) {
                        $query->whereIn('status', $statutMap[$request->statut]);
                    } else {
                        $query->where('status', $statutMap[$request->statut]);
                    }
                }
            }

            $colis = $query->orderBy('created_at', 'desc')
                          ->orderBy('ordre_livraison', 'desc')
                          ->orderBy('date_livraison_prevue', 'desc')
                          ->get();

            // Calculer les statistiques
            $stats = [
                'en_attente' => 0,
                'en_cours' => 0,
                'livre' => 0,
                'annule' => 0
            ];

            $montantTotalEncaisse = 0;

            // Formater les colis selon le modèle Flutter
            $formattedColis = $colis->map(function ($colisItem) use (&$stats, &$montantTotalEncaisse) {
                // Calculer les statistiques
                switch ($colisItem->status) {
                    case 0: // en_attente
                        $stats['en_attente']++;
                        break;
                    case 1: // en_cours
                        $stats['en_cours']++;
                        break;
                    case 2: // livre
                        $stats['livre']++;
                        $montantTotalEncaisse += (int) ($colisItem->montant_a_encaisse ?? 0);
                        break;
                    case 3:
                    case 4:
                    case 5: // annulé
                        $stats['annule']++;
                        break;
                }

                // Formater le colis selon le modèle Flutter
                return $this->formatColisForFlutter($colisItem);
            });

            return response()->json([
                'success' => true,
                'message' => 'Colis assignés récupérés avec succès',
                'data' => $formattedColis->values()->all(),
                'statistiques' => [
                    'colis_en_attente' => (int) $stats['en_attente'],
                    'colis_en_cours' => (int) $stats['en_cours'],
                    'colis_livres' => (int) $stats['livre'],
                    'colis_annules' => (int) $stats['annule'],
                    'total' => (int) array_sum($stats),
                    'montant_total_encaisse' => (int) $montantTotalEncaisse
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des colis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formater un colis selon le modèle Flutter
     */
    private function formatColisForFlutter($colis)
    {
        // Formater Commune
        $commune = $colis->commune ? [
            'id' => (int) $colis->commune->id,
            'entreprise_id' => (int) ($colis->commune->entreprise_id ?? 0),
            'libelle' => (string) ($colis->commune->libelle ?? ''),
            'ville_id' => (int) ($colis->commune->ville_id ?? 0),
            'deleted_at' => $colis->commune->deleted_at ? $colis->commune->deleted_at->toIso8601String() : null,
            'created_at' => $colis->commune->created_at ? $colis->commune->created_at->toIso8601String() : '',
            'updated_at' => $colis->commune->updated_at ? $colis->commune->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'libelle' => '',
            'ville_id' => 0,
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater Livraison
        $livraison = $colis->livraison ? [
            'id' => (int) $colis->livraison->id,
            'entreprise_id' => (int) ($colis->livraison->entreprise_id ?? 0),
            'uuid' => (string) ($colis->livraison->uuid ?? ''),
            'numero_de_livraison' => (string) ($colis->livraison->numero_de_livraison ?? ''),
            'colis_id' => (int) ($colis->livraison->colis_id ?? 0),
            'package_colis_id' => (int) ($colis->livraison->package_colis_id ?? 0),
            'marchand_id' => (int) ($colis->livraison->marchand_id ?? 0),
            'boutique_id' => (int) ($colis->livraison->boutique_id ?? 0),
            'adresse_de_livraison' => (string) ($colis->livraison->adresse_de_livraison ?? ''),
            'status' => (int) ($colis->livraison->status ?? 0),
            'note_livraison' => $colis->livraison->note_livraison ? (string) $colis->livraison->note_livraison : null,
            'code_validation' => (string) ($colis->livraison->code_validation ?? ''),
            'created_by' => (string) ($colis->livraison->created_by ?? ''),
            'deleted_at' => $colis->livraison->deleted_at ? $colis->livraison->deleted_at->toIso8601String() : null,
            'created_at' => $colis->livraison->created_at ? $colis->livraison->created_at->toIso8601String() : '',
            'updated_at' => $colis->livraison->updated_at ? $colis->livraison->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'uuid' => '',
            'numero_de_livraison' => '',
            'colis_id' => 0,
            'package_colis_id' => 0,
            'marchand_id' => 0,
            'boutique_id' => 0,
            'adresse_de_livraison' => '',
            'status' => 0,
            'note_livraison' => null,
            'code_validation' => '',
            'created_by' => '',
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater PackageColis
        $packageColis = $colis->packageColis ? [
            'id' => (int) $colis->packageColis->id,
            'entreprise_id' => (int) ($colis->packageColis->entreprise_id ?? 0),
            'numero_package' => (string) ($colis->packageColis->numero_package ?? ''),
            'marchand_id' => (int) ($colis->packageColis->marchand_id ?? 0),
            'boutique_id' => (int) ($colis->packageColis->boutique_id ?? 0),
            'nombre_colis' => (int) ($colis->packageColis->nombre_colis ?? 0),
            'communes_selected' => is_array($colis->packageColis->communes_selected)
                ? array_map('strval', $colis->packageColis->communes_selected)
                : [],
            'colis_ids' => is_array($colis->packageColis->colis_ids)
                ? array_map('intval', $colis->packageColis->colis_ids)
                : [],
            'livreur_id' => (int) ($colis->packageColis->livreur_id ?? 0),
            'engin_id' => (int) ($colis->packageColis->engin_id ?? 0),
            'statut' => (string) ($colis->packageColis->statut ?? ''),
            'created_by' => (int) ($colis->packageColis->created_by ?? 0),
            'created_at' => $colis->packageColis->created_at ? $colis->packageColis->created_at->toIso8601String() : '',
            'updated_at' => $colis->packageColis->updated_at ? $colis->packageColis->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'numero_package' => '',
            'marchand_id' => 0,
            'boutique_id' => 0,
            'nombre_colis' => 0,
            'communes_selected' => [],
            'colis_ids' => [],
            'livreur_id' => 0,
            'engin_id' => 0,
            'statut' => '',
            'created_by' => 0,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater Livreur
        $livreur = $colis->livreur ? [
            'id' => (int) $colis->livreur->id,
            'entreprise_id' => (int) ($colis->livreur->entreprise_id ?? 0),
            'first_name' => (string) ($colis->livreur->first_name ?? ''),
            'last_name' => (string) ($colis->livreur->last_name ?? ''),
            'mobile' => (string) ($colis->livreur->mobile ?? ''),
            'email' => (string) ($colis->livreur->email ?? ''),
            'status' => (string) ($colis->livreur->status ?? ''),
            'email_verified_at' => $colis->livreur->email_verified_at ? $colis->livreur->email_verified_at->toIso8601String() : null,
            'engin_id' => (int) ($colis->livreur->engin_id ?? 0),
            'photo' => (string) ($colis->livreur->photo ?? ''),
            'permis' => (string) ($colis->livreur->permis ?? ''),
            'adresse' => (string) ($colis->livreur->adresse ?? ''),
            'zone_activite_id' => $colis->livreur->zone_activite_id ? (int) $colis->livreur->zone_activite_id : null,
            'password' => (string) ($colis->livreur->password ?? ''),
            'created_by' => (string) ($colis->livreur->created_by ?? ''),
            'updated_by' => (string) ($colis->livreur->updated_by ?? ''),
            'deleted_by' => $colis->livreur->deleted_by ? (string) $colis->livreur->deleted_by : null,
            'remember_token' => $colis->livreur->remember_token ? (string) $colis->livreur->remember_token : null,
            'created_at' => $colis->livreur->created_at ? $colis->livreur->created_at->toIso8601String() : '',
            'updated_at' => $colis->livreur->updated_at ? $colis->livreur->updated_at->toIso8601String() : '',
            'deleted_at' => $colis->livreur->deleted_at ? $colis->livreur->deleted_at->toIso8601String() : null,
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'first_name' => '',
            'last_name' => '',
            'mobile' => '',
            'email' => '',
            'status' => '',
            'email_verified_at' => null,
            'engin_id' => 0,
            'photo' => '',
            'permis' => '',
            'adresse' => '',
            'zone_activite_id' => null,
            'password' => '',
            'created_by' => '',
            'updated_by' => '',
            'deleted_by' => null,
            'remember_token' => null,
            'created_at' => '',
            'updated_at' => '',
            'deleted_at' => null,
        ];

        // Formater Engin
        $engin = $colis->engin ? [
            'id' => (int) $colis->engin->id,
            'entreprise_id' => (int) ($colis->engin->entreprise_id ?? 0),
            'libelle' => (string) ($colis->engin->libelle ?? ''),
            'matricule' => (string) ($colis->engin->matricule ?? ''),
            'marque' => (string) ($colis->engin->marque ?? ''),
            'modele' => (string) ($colis->engin->modele ?? ''),
            'couleur' => (string) ($colis->engin->couleur ?? ''),
            'immatriculation' => (string) ($colis->engin->immatriculation ?? ''),
            'etat' => (string) ($colis->engin->etat ?? ''),
            'status' => (string) ($colis->engin->status ?? ''),
            'type_engin_id' => (int) ($colis->engin->type_engin_id ?? 0),
            'created_by' => (string) ($colis->engin->created_by ?? ''),
            'created_at' => $colis->engin->created_at ? $colis->engin->created_at->toIso8601String() : '',
            'updated_at' => $colis->engin->updated_at ? $colis->engin->updated_at->toIso8601String() : '',
            'deleted_at' => $colis->engin->deleted_at ? $colis->engin->deleted_at->toIso8601String() : null,
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'libelle' => '',
            'matricule' => '',
            'marque' => '',
            'modele' => '',
            'couleur' => '',
            'immatriculation' => '',
            'etat' => '',
            'status' => '',
            'type_engin_id' => 0,
            'created_by' => '',
            'created_at' => '',
            'updated_at' => '',
            'deleted_at' => null,
        ];

        // Formater Poids
        $poids = $colis->poids ? [
            'id' => (int) $colis->poids->id,
            'libelle' => (string) ($colis->poids->libelle ?? ''),
            'created_by' => (string) ($colis->poids->created_by ?? ''),
            'entreprise_id' => (int) ($colis->poids->entreprise_id ?? 0),
            'deleted_at' => $colis->poids->deleted_at ? $colis->poids->deleted_at->toIso8601String() : null,
            'created_at' => $colis->poids->created_at ? $colis->poids->created_at->toIso8601String() : '',
            'updated_at' => $colis->poids->updated_at ? $colis->poids->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'libelle' => '',
            'created_by' => '',
            'entreprise_id' => 0,
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater ModeLivraison
        $modeLivraison = $colis->modeLivraison ? [
            'id' => (int) $colis->modeLivraison->id,
            'libelle' => (string) ($colis->modeLivraison->libelle ?? ''),
            'description' => (string) ($colis->modeLivraison->description ?? ''),
            'created_by' => (string) ($colis->modeLivraison->created_by ?? ''),
            'entreprise_id' => (int) ($colis->modeLivraison->entreprise_id ?? 0),
            'deleted_at' => $colis->modeLivraison->deleted_at ? $colis->modeLivraison->deleted_at->toIso8601String() : null,
            'created_at' => $colis->modeLivraison->created_at ? $colis->modeLivraison->created_at->toIso8601String() : '',
            'updated_at' => $colis->modeLivraison->updated_at ? $colis->modeLivraison->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'libelle' => '',
            'description' => '',
            'created_by' => '',
            'entreprise_id' => 0,
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater Temp
        $temp = $colis->temp ? [
            'id' => (int) $colis->temp->id,
            'entreprise_id' => (int) ($colis->temp->entreprise_id ?? 0),
            'libelle' => (string) ($colis->temp->libelle ?? ''),
            'description' => (string) ($colis->temp->description ?? ''),
            'heure_debut' => (string) ($colis->temp->heure_debut ?? ''),
            'heure_fin' => (string) ($colis->temp->heure_fin ?? ''),
            'is_weekend' => (bool) ($colis->temp->is_weekend ?? false),
            'is_holiday' => (bool) ($colis->temp->is_holiday ?? false),
            'is_active' => (bool) ($colis->temp->is_active ?? true),
            'created_by' => (int) ($colis->temp->created_by ?? 0),
            'deleted_at' => $colis->temp->deleted_at ? $colis->temp->deleted_at->toIso8601String() : null,
            'created_at' => $colis->temp->created_at ? $colis->temp->created_at->toIso8601String() : '',
            'updated_at' => $colis->temp->updated_at ? $colis->temp->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'libelle' => '',
            'description' => '',
            'heure_debut' => '',
            'heure_fin' => '',
            'is_weekend' => false,
            'is_holiday' => false,
            'is_active' => true,
            'created_by' => 0,
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater le Colis principal
        return [
            'id' => (int) $colis->id,
            'entreprise_id' => (int) ($colis->entreprise_id ?? 0),
            'package_colis_id' => (int) ($colis->package_colis_id ?? 0),
            'uuid' => (string) ($colis->uuid ?? ''),
            'code' => (string) ($colis->code ?? ''),
            'status' => (int) ($colis->status ?? 0),
            'nom_client' => (string) ($colis->nom_client ?? ''),
            'telephone_client' => (string) ($colis->telephone_client ?? ''),
            'adresse_client' => (string) ($colis->adresse_client ?? ''),
            'montant_a_encaisse' => (int) ($colis->montant_a_encaisse ?? 0),
            'prix_de_vente' => (int) ($colis->prix_de_vente ?? 0),
            'numero_facture' => (string) ($colis->numero_facture ?? ''),
            'note_client' => (string) ($colis->note_client ?? ''),
            'instructions_livraison' => $colis->instructions_livraison ? (string) $colis->instructions_livraison : null,
            'zone_id' => (int) ($colis->zone_id ?? 0),
            'commune_id' => (int) ($colis->commune_id ?? 0),
            'ordre_livraison' => $colis->ordre_livraison ? (int) $colis->ordre_livraison : null,
            'date_livraison_prevue' => $colis->date_livraison_prevue ? $colis->date_livraison_prevue->toIso8601String() : null,
            'livreur_id' => (int) ($colis->livreur_id ?? 0),
            'engin_id' => (int) ($colis->engin_id ?? 0),
            'poids_id' => (int) ($colis->poids_id ?? 0),
            'mode_livraison_id' => (int) ($colis->mode_livraison_id ?? 0),
            'temp_id' => (int) ($colis->temp_id ?? 0),
            'created_by' => (string) ($colis->created_by ?? ''),
            'deleted_at' => $colis->deleted_at ? $colis->deleted_at->toIso8601String() : null,
            'created_at' => $colis->created_at ? $colis->created_at->toIso8601String() : '',
            'updated_at' => $colis->updated_at ? $colis->updated_at->toIso8601String() : '',
            'commune' => $commune,
            'livraison' => $livraison,
            'package_colis' => $packageColis,
            'livreur' => $livreur,
            'engin' => $engin,
            'poids' => $poids,
            'mode_livraison' => $modeLivraison,
            'temp' => $temp,
        ];
    }

    /**
     * Formater un colis détaillé selon le modèle Flutter
     */
    private function formatColisDetailForFlutter($colis, $historique)
    {
        // Récupérer commune_zone pour accéder aux relations hasOneThrough
        // Utiliser la relation chargée si disponible, sinon la requête
        $communeZone = $colis->commune_zone;
        if (!$communeZone) {
            $communeZone = \App\Models\Commune_zone::where('zone_id', $colis->zone_id)
                ->where('entreprise_id', $colis->entreprise_id)
                ->with(['typeColis', 'delai', 'marchand'])
                ->first();
        }

        // Formater HistoriqueLivraison
        $historiqueLivraison = $historique ? [
            'id' => (int) $historique->id,
            'entreprise_id' => (int) ($historique->entreprise_id ?? 0),
            'package_colis_id' => (int) ($historique->package_colis_id ?? 0),
            'livraison_id' => (int) ($historique->livraison_id ?? 0),
            'status' => (string) ($historique->status ?? ''),
            'code_validation_utilise' => $historique->code_validation_utilise ? (string) $historique->code_validation_utilise : null,
            'photo_proof_path' => $historique->photo_proof_path ? (string) $historique->photo_proof_path : null,
            'signature_data' => $historique->signature_data ? (string) $historique->signature_data : null,
            'note_livraison' => $historique->note_livraison ? (string) $historique->note_livraison : null,
            'motif_annulation' => $historique->motif_annulation ? (string) $historique->motif_annulation : null,
            'date_livraison_effective' => $historique->date_livraison_effective ? (\Carbon\Carbon::parse($historique->date_livraison_effective))->toIso8601String() : null,
            'latitude' => $historique->latitude ? (float) $historique->latitude : null,
            'longitude' => $historique->longitude ? (float) $historique->longitude : null,
            'colis_id' => (int) ($historique->colis_id ?? 0),
            'livreur_id' => (int) ($historique->livreur_id ?? 0),
            'montant_a_encaisse' => (int) round((float) ($historique->montant_a_encaisse ?? 0)),
            'prix_de_vente' => (int) round((float) ($historique->prix_de_vente ?? 0)),
            'montant_de_la_livraison' => (int) round((float) ($historique->montant_de_la_livraison ?? 0)),
            'created_by' => (string) ($historique->created_by ?? ''),
            'deleted_at' => $historique->deleted_at ? $historique->deleted_at->toIso8601String() : null,
            'created_at' => $historique->created_at ? $historique->created_at->toIso8601String() : '',
            'updated_at' => $historique->updated_at ? $historique->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'package_colis_id' => 0,
            'livraison_id' => 0,
            'status' => '',
            'code_validation_utilise' => null,
            'photo_proof_path' => null,
            'signature_data' => null,
            'note_livraison' => null,
            'motif_annulation' => null,
            'date_livraison_effective' => null,
            'latitude' => null,
            'longitude' => null,
            'colis_id' => 0,
            'livreur_id' => 0,
            'montant_a_encaisse' => 0,
            'prix_de_vente' => 0,
            'montant_de_la_livraison' => 0,
            'created_by' => '',
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater LivraisonDetail (similaire à Livraison mais avec plus de détails)
        $livraisonDetail = $colis->livraison ? [
            'id' => (int) $colis->livraison->id,
            'entreprise_id' => (int) ($colis->livraison->entreprise_id ?? 0),
            'uuid' => (string) ($colis->livraison->uuid ?? ''),
            'numero_de_livraison' => (string) ($colis->livraison->numero_de_livraison ?? ''),
            'colis_id' => (int) ($colis->livraison->colis_id ?? 0),
            'package_colis_id' => (int) ($colis->livraison->package_colis_id ?? 0),
            'marchand_id' => (int) ($colis->livraison->marchand_id ?? 0),
            'boutique_id' => (int) ($colis->livraison->boutique_id ?? 0),
            'adresse_de_livraison' => (string) ($colis->livraison->adresse_de_livraison ?? ''),
            'status' => (int) ($colis->livraison->status ?? 0),
            'note_livraison' => $colis->livraison->note_livraison ? (string) $colis->livraison->note_livraison : null,
            'code_validation' => (string) ($colis->livraison->code_validation ?? ''),
            'created_by' => (string) ($colis->livraison->created_by ?? ''),
            'deleted_at' => $colis->livraison->deleted_at ? $colis->livraison->deleted_at->toIso8601String() : null,
            'created_at' => $colis->livraison->created_at ? $colis->livraison->created_at->toIso8601String() : '',
            'updated_at' => $colis->livraison->updated_at ? $colis->livraison->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'uuid' => '',
            'numero_de_livraison' => '',
            'colis_id' => 0,
            'package_colis_id' => 0,
            'marchand_id' => 0,
            'boutique_id' => 0,
            'adresse_de_livraison' => '',
            'status' => 0,
            'note_livraison' => null,
            'code_validation' => '',
            'created_by' => '',
            'deleted_at' => null,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater TypeColis (via commune_zone)
        $typeColis = null;
        if ($communeZone && $communeZone->typeColis) {
            $typeColis = [
                'id' => (int) $communeZone->typeColis->id,
                'libelle' => (string) ($communeZone->typeColis->libelle ?? ''),
                'created_by' => (string) ($communeZone->typeColis->created_by ?? ''),
                'deleted_at' => $communeZone->typeColis->deleted_at ? $communeZone->typeColis->deleted_at->toIso8601String() : null,
                'created_at' => $communeZone->typeColis->created_at ? $communeZone->typeColis->created_at->toIso8601String() : '',
                'updated_at' => $communeZone->typeColis->updated_at ? $communeZone->typeColis->updated_at->toIso8601String() : '',
                'laravel_through_key' => (int) ($communeZone->id ?? 0),
            ];
        } else {
            $typeColis = [
                'id' => 0,
                'libelle' => '',
                'created_by' => '',
                'deleted_at' => null,
                'created_at' => '',
                'updated_at' => '',
                'laravel_through_key' => 0,
            ];
        }

        // Formater ConditionnementColis
        $conditionnementColis = $colis->conditionnementColis ? [
            'id' => (int) $colis->conditionnementColis->id,
            'entreprise_id' => (int) ($colis->conditionnementColis->entreprise_id ?? 0),
            'libelle' => (string) ($colis->conditionnementColis->libelle ?? ''),
            'created_by' => (int) ($colis->conditionnementColis->created_by ?? 0),
            'created_at' => $colis->conditionnementColis->created_at ? $colis->conditionnementColis->created_at->toIso8601String() : '',
            'updated_at' => $colis->conditionnementColis->updated_at ? $colis->conditionnementColis->updated_at->toIso8601String() : '',
        ] : [
            'id' => 0,
            'entreprise_id' => 0,
            'libelle' => '',
            'created_by' => 0,
            'created_at' => '',
            'updated_at' => '',
        ];

        // Formater Delai (via commune_zone)
        $delai = null;
        if ($communeZone && $communeZone->delai) {
            $delai = [
                'id' => (int) $communeZone->delai->id,
                'entreprise_id' => (int) ($communeZone->delai->entreprise_id ?? 0),
                'libelle' => (string) ($communeZone->delai->libelle ?? ''),
                'created_by' => (string) ($communeZone->delai->created_by ?? ''),
                'deleted_at' => $communeZone->delai->deleted_at ? $communeZone->delai->deleted_at->toIso8601String() : null,
                'created_at' => $communeZone->delai->created_at ? $communeZone->delai->created_at->toIso8601String() : '',
                'updated_at' => $communeZone->delai->updated_at ? $communeZone->delai->updated_at->toIso8601String() : '',
                'laravel_through_key' => (int) ($communeZone->id ?? 0),
            ];
        } else {
            $delai = [
                'id' => 0,
                'entreprise_id' => 0,
                'libelle' => '',
                'created_by' => '',
                'deleted_at' => null,
                'created_at' => '',
                'updated_at' => '',
                'laravel_through_key' => 0,
            ];
        }

        // Formater Marchand (via commune_zone)
        $marchand = null;
        if ($communeZone && $communeZone->marchand) {
            $marchand = [
                'id' => (int) $communeZone->marchand->id,
                'entreprise_id' => (int) ($communeZone->marchand->entreprise_id ?? 0),
                'first_name' => (string) ($communeZone->marchand->first_name ?? ''),
                'last_name' => (string) ($communeZone->marchand->last_name ?? ''),
                'mobile' => (string) ($communeZone->marchand->mobile ?? ''),
                'email' => (string) ($communeZone->marchand->email ?? ''),
                'adresse' => (string) ($communeZone->marchand->adresse ?? ''),
                'status' => (string) ($communeZone->marchand->status ?? ''),
                'commune_id' => (int) ($communeZone->marchand->commune_id ?? 0),
                'created_by' => (string) ($communeZone->marchand->created_by ?? ''),
                'deleted_at' => $communeZone->marchand->deleted_at ? $communeZone->marchand->deleted_at->toIso8601String() : null,
                'created_at' => $communeZone->marchand->created_at ? $communeZone->marchand->created_at->toIso8601String() : '',
                'updated_at' => $communeZone->marchand->updated_at ? $communeZone->marchand->updated_at->toIso8601String() : '',
                'laravel_through_key' => (int) ($communeZone->id ?? 0),
            ];
        } else {
            // Essayer de récupérer depuis packageColis si disponible
            if ($colis->packageColis && $colis->packageColis->marchand) {
                $marchandModel = $colis->packageColis->marchand;
                $marchand = [
                    'id' => (int) $marchandModel->id,
                    'entreprise_id' => (int) ($marchandModel->entreprise_id ?? 0),
                    'first_name' => (string) ($marchandModel->first_name ?? ''),
                    'last_name' => (string) ($marchandModel->last_name ?? ''),
                    'mobile' => (string) ($marchandModel->mobile ?? ''),
                    'email' => (string) ($marchandModel->email ?? ''),
                    'adresse' => (string) ($marchandModel->adresse ?? ''),
                    'status' => (string) ($marchandModel->status ?? ''),
                    'commune_id' => (int) ($marchandModel->commune_id ?? 0),
                    'created_by' => (string) ($marchandModel->created_by ?? ''),
                    'deleted_at' => $marchandModel->deleted_at ? $marchandModel->deleted_at->toIso8601String() : null,
                    'created_at' => $marchandModel->created_at ? $marchandModel->created_at->toIso8601String() : '',
                    'updated_at' => $marchandModel->updated_at ? $marchandModel->updated_at->toIso8601String() : '',
                    'laravel_through_key' => (int) ($communeZone ? $communeZone->id : 0),
                ];
            } else {
                $marchand = [
                    'id' => 0,
                    'entreprise_id' => 0,
                    'first_name' => '',
                    'last_name' => '',
                    'mobile' => '',
                    'email' => '',
                    'adresse' => '',
                    'status' => '',
                    'commune_id' => 0,
                    'created_by' => '',
                    'deleted_at' => null,
                    'created_at' => '',
                    'updated_at' => '',
                    'laravel_through_key' => 0,
                ];
            }
        }

        // Formater Boutique
        $boutique = null;
        if ($colis->boutique) {
            $boutique = [
                'id' => (int) $colis->boutique->id,
                'entreprise_id' => (int) ($colis->boutique->entreprise_id ?? 0),
                'libelle' => (string) ($colis->boutique->libelle ?? ''),
                'mobile' => (string) ($colis->boutique->mobile ?? ''),
                'adresse' => (string) ($colis->boutique->adresse ?? ''),
                'adresse_gps' => (string) ($colis->boutique->adresse_gps ?? ''),
                'cover_image' => (string) ($colis->boutique->cover_image ?? ''),
                'marchand_id' => (int) ($colis->boutique->marchand_id ?? 0),
                'status' => (string) ($colis->boutique->status ?? ''),
                'created_by' => (string) ($colis->boutique->created_by ?? ''),
                'deleted_at' => $colis->boutique->deleted_at ? $colis->boutique->deleted_at->toIso8601String() : null,
                'created_at' => $colis->boutique->created_at ? $colis->boutique->created_at->toIso8601String() : '',
                'updated_at' => $colis->boutique->updated_at ? $colis->boutique->updated_at->toIso8601String() : '',
            ];
        } else {
            // Essayer de récupérer depuis packageColis si disponible
            if ($colis->packageColis && $colis->packageColis->boutique) {
                $boutiqueModel = $colis->packageColis->boutique;
                $boutique = [
                    'id' => (int) $boutiqueModel->id,
                    'entreprise_id' => (int) ($boutiqueModel->entreprise_id ?? 0),
                    'libelle' => (string) ($boutiqueModel->libelle ?? ''),
                    'mobile' => (string) ($boutiqueModel->mobile ?? ''),
                    'adresse' => (string) ($boutiqueModel->adresse ?? ''),
                    'adresse_gps' => (string) ($boutiqueModel->adresse_gps ?? ''),
                    'cover_image' => (string) ($boutiqueModel->cover_image ?? ''),
                    'marchand_id' => (int) ($boutiqueModel->marchand_id ?? 0),
                    'status' => (string) ($boutiqueModel->status ?? ''),
                    'created_by' => (string) ($boutiqueModel->created_by ?? ''),
                    'deleted_at' => $boutiqueModel->deleted_at ? $boutiqueModel->deleted_at->toIso8601String() : null,
                    'created_at' => $boutiqueModel->created_at ? $boutiqueModel->created_at->toIso8601String() : '',
                    'updated_at' => $boutiqueModel->updated_at ? $boutiqueModel->updated_at->toIso8601String() : '',
                ];
            } else {
                $boutique = [
                    'id' => 0,
                    'entreprise_id' => 0,
                    'libelle' => '',
                    'mobile' => '',
                    'adresse' => '',
                    'adresse_gps' => '',
                    'cover_image' => '',
                    'marchand_id' => 0,
                    'status' => '',
                    'created_by' => '',
                    'deleted_at' => null,
                    'created_at' => '',
                    'updated_at' => '',
                ];
            }
        }

        // Utiliser formatColisForFlutter pour les objets communs
        $baseColis = $this->formatColisForFlutter($colis);

        // Ajouter les objets spécifiques aux détails
        return array_merge($baseColis, [
            'historique_livraison' => $historiqueLivraison,
            'livraison' => $livraisonDetail,
            'type_colis' => $typeColis,
            'conditionnement_colis' => $conditionnementColis,
            'delai' => $delai,
            'marchand' => $marchand,
            'boutique' => $boutique,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/colis/{id}/details",
     *     summary="Détails d'un colis",
     *     description="Récupère les détails complets d'un colis spécifique",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du colis",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du colis récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du colis récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="CLIS-000001"),
     *                 @OA\Property(property="status", type="integer", example=0),
     *                 @OA\Property(property="nom_client", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="telephone_client", type="string", example="0123456789"),
     *                 @OA\Property(property="adresse_client", type="string", example="123 Rue de la Paix"),
     *                 @OA\Property(property="montant_a_encaisse", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="prix_de_vente", type="number", format="float", example=45000.00),
     *                 @OA\Property(property="note_client", type="string", example="Fragile"),
     *                 @OA\Property(property="instructions_livraison", type="string", example="Sonner 2 fois"),
     *                 @OA\Property(property="date_livraison_prevue", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="ordre_livraison", type="integer", example=1),
                 *                 @OA\Property(
                 *                     property="commune",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="nom", type="string", example="Cocody")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="temp",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=2),
                 *                     @OA\Property(property="entreprise_id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="Nuit (18h-6h)"),
                 *                     @OA\Property(property="description", type="string", example="Période de nuit"),
                 *                     @OA\Property(property="heure_debut", type="string", example="18:00"),
                 *                     @OA\Property(property="heure_fin", type="string", example="06:00")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="mode_livraison",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=2),
                 *                     @OA\Property(property="libelle", type="string", example="Livraison express"),
                 *                     @OA\Property(property="description", type="string", example="Dans la journée ou en 2–6 heures")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="poids",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="1 Kg")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="type_colis",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="Document")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="conditionnement_colis",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="Enveloppe")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="delai",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="24h")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="livreur",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=5),
                 *                     @OA\Property(property="nom", type="string", example="Jean"),
                 *                     @OA\Property(property="prenom", type="string", example="Dupont"),
                 *                     @OA\Property(property="telephone", type="string", example="0123456789")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="engin",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="libelle", type="string", example="Moto"),
                 *                     @OA\Property(
                 *                         property="type_engin",
                 *                         type="object",
                 *                         @OA\Property(property="id", type="integer", example=1),
                 *                         @OA\Property(property="libelle", type="string", example="Moto")
                 *                     )
                 *                 ),
                 *                 @OA\Property(
                 *                     property="marchand",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="nom", type="string", example="Boutique Test"),
                 *                     @OA\Property(property="telephone", type="string", example="0123456789")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="boutique",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="nom", type="string", example="Boutique Centre"),
                 *                     @OA\Property(property="adresse", type="string", example="123 Rue Commerce")
                 *                 ),
                 *                 @OA\Property(
                 *                     property="livraison",
                 *                     type="object",
                 *                     @OA\Property(property="id", type="integer", example=1),
                 *                     @OA\Property(property="numero_de_livraison", type="string", example="LIV-000001"),
                 *                     @OA\Property(property="code_validation", type="string", example="12345")
                 *                 ),
     *                 @OA\Property(
     *                     property="historique_livraison",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="en_attente"),
     *                     @OA\Property(property="date_livraison_effective", type="string", format="date-time", example="2025-10-13T14:30:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colis non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Colis non trouvé")
     *         )
     *     )
     * )
     */
    public function getColisDetails($id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            $colis = Colis::with([
                'commune',
                'livraison',
                'packageColis.marchand',
                'packageColis.boutique',
                'temp',
                'modeLivraison',
                'poids',
                'typeColis',
                'conditionnementColis',
                'delai',
                'livreur',
                'engin.typeEngin',
                'marchand',
                'boutique',
                'commune_zone.typeColis',
                'commune_zone.delai',
                'commune_zone.marchand'
            ])
                ->where('id', $id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if (!$colis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Colis non trouvé'
                ], 404);
            }

            // Récupérer l'historique de livraison
            $historique = Historique_livraison::where('colis_id', $colis->id)
                ->where('livreur_id', $livreur->id)
                ->latest()
                ->first();

            // Formater le colis selon le modèle Flutter
            $formattedColis = $this->formatColisDetailForFlutter($colis, $historique);

            return response()->json([
                'success' => true,
                'message' => 'Détails du colis récupérés avec succès',
                'data' => $formattedColis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/colis/{id}/start-delivery",
     *     summary="Démarrer une livraison",
     *     description="Marque un colis comme étant en cours de livraison",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du colis",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livraison démarrée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Livraison démarrée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="integer", example=1),
     *                 @OA\Property(property="status_label", type="string", example="En cours")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous avez déjà une livraison en cours. Terminez-la avant d'en démarrer une nouvelle."),
     *             @OA\Property(
     *                 property="active_deliveries",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="CLIS-000001"),
     *                     @OA\Property(property="client", type="string", example="John Doe"),
     *                     @OA\Property(property="adresse", type="string", example="Cocody, Deux Plateaux")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function startDelivery($id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            // Vérifier si le livreur a déjà une livraison en cours
            if (!$livreur->canStartDelivery()) {
                $activeDeliveries = $livreur->getActiveDeliveries();
                \Log::warning("Tentative de démarrage de livraison avec livraison en cours", [
                    'livreur_id' => $livreur->id,
                    'livreur_name' => $livreur->first_name . ' ' . $livreur->last_name,
                    'colis_id' => $id,
                    'active_deliveries_count' => $activeDeliveries->count(),
                    'active_deliveries' => $activeDeliveries->pluck('id')->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà une livraison en cours. Terminez-la avant d\'en démarrer une nouvelle.',
                    'active_deliveries' => $activeDeliveries->map(function($colis) {
                        return [
                            'id' => $colis->id,
                            'code' => $colis->code,
                            'client' => $colis->nom_client,
                            'adresse' => $colis->adresse_client
                        ];
                    })
                ], 400);
            }

            $colis = Colis::where('id', $id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if (!$colis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Colis non trouvé'
                ], 404);
            }

            if ($colis->status !== 0) { // Pas en attente
                return response()->json([
                    'success' => false,
                    'message' => 'Ce colis n\'est pas disponible pour la livraison'
                ], 400);
            }

            DB::beginTransaction();

            // Mettre à jour le statut du colis
            $colis->update(['status' => 1]); // En cours

            // Créer une livraison si elle n'existe pas
            $livraison = $colis->livraison;
            if (!$livraison) {
                $livraison = Livraison::create([
                    'entreprise_id' => $livreur->entreprise_id,
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'numero_de_livraison' => 'LIV-' . str_pad($colis->id, 6, '0', STR_PAD_LEFT),
                    'colis_id' => $colis->id,
                    'package_colis_id' => $colis->package_colis_id,
                    'marchand_id' => $colis->packageColis->marchand_id ?? 1,
                    'boutique_id' => $colis->packageColis->boutique_id ?? 1,
                    'adresse_de_livraison' => $colis->adresse_client,
                    'status' => 1, // En cours
                    'code_validation' => str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT),
                    'created_by' => $livreur->id
                ]);
            }

            // Créer ou mettre à jour l'historique de livraison
            // Convertir les montants en entiers (les colonnes sont de type integer)
            $montantEncaisse = (int) ($colis->montant_a_encaisse ?? 0);
            $prixVente = (int) ($colis->prix_de_vente ?? 0);
            $montantLivraison = (int) round((float) $colis->calculateDeliveryCost());

            $historique = Historique_livraison::updateOrCreate(
                [
                    'colis_id' => $colis->id,
                    'livreur_id' => $livreur->id
                ],
                [
                    'entreprise_id' => $livreur->entreprise_id,
                    'package_colis_id' => $colis->package_colis_id,
                    'livraison_id' => $livraison->id,
                    'status' => 'en_cours',
                    'montant_a_encaisse' => $montantEncaisse,
                    'prix_de_vente' => $prixVente,
                    'montant_de_la_livraison' => $montantLivraison,
                    'created_by' => $livreur->id
                ]
            );

            // Envoyer le code de validation par WhatsApp au livreur
            $this->sendValidationCodeWhatsApp($livreur, $livraison, $colis);

            // Activer le suivi GPS pour cette livraison
            $this->activateLocationTracking($livreur, 'delivery', $colis->id, $historique->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison démarrée avec succès',
                'data' => [
                    'id' => $colis->id,
                    'status' => $colis->status,
                    'status_label' => 'En cours'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage de la livraison: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/colis/{id}/complete-delivery",
     *     summary="Finaliser une livraison",
     *     description="Finalise une livraison avec les preuves (code de validation, photo, signature, GPS)",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du colis",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="code_validation", type="string", example="12345", description="Code de validation du colis (5 chiffres)"),
     *                 @OA\Property(property="photo_proof", type="string", format="binary", description="Photo de preuve de livraison"),
     *                 @OA\Property(property="signature_data", type="string", example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...", description="Signature du client (base64)"),
     *                 @OA\Property(property="note_livraison", type="string", example="Livraison effectuée avec succès", description="Note du livreur"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=5.359952, description="Latitude GPS"),
     *                 @OA\Property(property="longitude", type="number", format="float", example=-4.008256, description="Longitude GPS")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livraison finalisée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Livraison finalisée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="integer", example=2),
     *                 @OA\Property(property="status_label", type="string", example="Livré"),
     *                 @OA\Property(property="date_livraison_effective", type="string", format="date-time", example="2025-10-13T14:30:00Z"),
     *                 @OA\Property(property="photo_proof_url", type="string", example="http://192.168.1.2:8000/storage/livraisons/proofs/colis_1_1760357729.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données de validation invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function completeDelivery(Request $request, $id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            // Validation des données
            $validator = Validator::make($request->all(), [
                'code_validation' => 'required|string|size:5|regex:/^[0-9]{5}$/',
                'photo_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'signature_data' => 'nullable|string',
                'note_livraison' => 'nullable|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $colis = Colis::where('id', $id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if (!$colis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Colis non trouvé'
                ], 404);
            }

            if ($colis->status !== 1) { // Pas en cours
                return response()->json([
                    'success' => false,
                    'message' => 'Ce colis n\'est pas en cours de livraison'
                ], 400);
            }

            DB::beginTransaction();

            // Traitement de la photo de preuve
            $photoPath = null;
            if ($request->hasFile('photo_proof')) {
                $photo = $request->file('photo_proof');
                $filename = 'colis_' . $colis->id . '_' . time() . '.jpg';
                $photoPath = ImageCompressor::compressUploadedFile($photo, 'livraisons/proofs', $filename, 1024); // 1MB max
            }

            // Note: Le statut sera mis à jour après le commit de la transaction
            // pour éviter les problèmes de cache ou de transaction

            // Récupérer ou créer la livraison
            $livraison = $colis->livraison;
            if (!$livraison) {
                // Créer la livraison si elle n'existe pas
                $livraison = Livraison::create([
                    'entreprise_id' => $colis->entreprise_id,
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'numero_de_livraison' => 'LIV-' . str_pad($colis->id, 6, '0', STR_PAD_LEFT),
                    'colis_id' => $colis->id,
                    'package_colis_id' => $colis->package_colis_id,
                    'marchand_id' => $colis->packageColis->marchand_id ?? null,
                    'boutique_id' => $colis->packageColis->boutique_id ?? null,
                    'adresse_de_livraison' => $colis->adresse_client,
                    'status' => Livraison::STATUS_LIVRE, // 2 = Livré
                    'code_validation' => $request->code_validation,
                    'note_livraison' => $request->note_livraison,
                    'created_by' => $livreur->id
                ]);
            } else {
                // Mettre à jour le statut de la livraison existante
                $livraison->update([
                    'status' => Livraison::STATUS_LIVRE, // 2 = Livré
                    'note_livraison' => $request->note_livraison ?? $livraison->note_livraison
                ]);
                $livraison->refresh(); // Rafraîchir le modèle
            }

            // Récupérer ou créer l'historique de livraison
            $historique = Historique_livraison::where('colis_id', $colis->id)
                ->where('livreur_id', $livreur->id)
                ->first();

            // Si l'historique n'existe pas, le créer
            if (!$historique) {
                // Convertir les montants en entiers
                $montantEncaisse = (int) ($colis->montant_a_encaisse ?? 0);
                $prixVente = (int) ($colis->prix_de_vente ?? 0);
                $montantLivraison = (int) round((float) $colis->calculateDeliveryCost());

                $historique = Historique_livraison::create([
                    'entreprise_id' => $colis->entreprise_id,
                    'package_colis_id' => $colis->package_colis_id,
                    'livraison_id' => $livraison ? $livraison->id : null,
                    'status' => 'livre',
                    'colis_id' => $colis->id,
                    'livreur_id' => $livreur->id,
                    'montant_a_encaisse' => $montantEncaisse,
                    'prix_de_vente' => $prixVente,
                    'montant_de_la_livraison' => $montantLivraison,
                    'created_by' => $livreur->id,
                    'code_validation_utilise' => $request->code_validation,
                    'date_livraison_effective' => now(),
                    'note_livraison' => $request->note_livraison,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'photo_proof_path' => $photoPath,
                    'signature_data' => $request->signature_data
                ]);
            } else {
                // Mettre à jour l'historique existant
                $updateData = [
                    'status' => 'livre',
                    'code_validation_utilise' => $request->code_validation,
                    'date_livraison_effective' => now(),
                    'note_livraison' => $request->note_livraison,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ];

                if ($photoPath) {
                    $updateData['photo_proof_path'] = $photoPath;
                }

                if ($request->signature_data) {
                    $updateData['signature_data'] = $request->signature_data;
                }

                $historique->update($updateData);
            }

            $historique->refresh();

            // Vérifier et créer l'entrée dans balance_marchands si nécessaire
            $this->ensureBalanceMarchandExists($colis);

            // Mettre à jour le statut du colis AVANT le commit pour qu'il soit inclus dans la transaction
            // Utiliser DB::table() pour forcer la mise à jour sans passer par Eloquent
            DB::table('colis')->where('id', $colis->id)->update(['status' => 2, 'updated_at' => now()]);

            DB::commit();

            // Rafraîchir le colis et ses relations après le commit pour s'assurer que toutes les données sont à jour
            $colis = Colis::with(['livraison', 'packageColis'])->find($colis->id); // Recharger complètement depuis la base

            // Vérifier que le statut est bien mis à jour
            if ($colis->status !== 2) {
                \Log::warning('Statut du colis non mis à jour correctement après commit', [
                    'colis_id' => $colis->id,
                    'status_actuel' => $colis->status,
                    'status_attendu' => 2
                ]);
                // Forcer la mise à jour une dernière fois (hors transaction)
                DB::table('colis')->where('id', $colis->id)->update(['status' => 2, 'updated_at' => now()]);
                $colis = Colis::find($colis->id);
            }

            \Log::info('Colis statut mis à jour après commit', [
                'colis_id' => $colis->id,
                'status' => $colis->status,
                'status_expected' => 2,
                'updated_at' => $colis->updated_at
            ]);

            // Envoyer une notification au marchand
            // Récupérer le marchand depuis le packageColis ou la livraison
            $marchandId = $colis->packageColis->marchand_id ?? ($livraison ? $livraison->marchand_id : null);
            $marchand = $marchandId ? Marchand::find($marchandId) : null;
            if ($marchand && $marchand->fcm_token) {
                $notificationResult = $this->sendColisDeliveredNotification($marchand, $colis);

                // Log du résultat de la notification
                if ($notificationResult['success']) {
                    \Log::info('Notification colis livré envoyée avec succès', [
                        'marchand_id' => $marchand->id,
                        'colis_id' => $colis->id
                    ]);
                } else {
                    \Log::warning('Échec envoi notification colis livré', [
                        'marchand_id' => $marchand->id,
                        'colis_id' => $colis->id,
                        'error' => $notificationResult['message']
                    ]);
                }
            }

            // Envoyer une notification à l'admin
            $this->sendDeliveryCompletedNotificationToAdmin($colis, $livreur);

            // Envoyer une notification en base de données
            $admin = \App\Models\User::where('entreprise_id', $colis->entreprise_id)
                ->where('user_type', 'admin')
                ->first();

            if ($admin) {
                $admin->notify(new DeliveryCompletedNotification($colis, $livreur));
            }

            // S'assurer que le statut est bien 2 (Livré) dans la réponse
            // Forcer le statut à 2 car la livraison est complétée
            $finalStatus = 2; // Statut "Livré"

            \Log::info('Réponse completeDelivery', [
                'colis_id' => $colis->id,
                'status_dans_colis' => $colis->status,
                'status_dans_reponse' => $finalStatus,
                'status_attendu' => 2
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Livraison finalisée avec succès',
                'data' => [
                    'id' => $colis->id,
                    'status' => $finalStatus, // Statut "Livré" (2)
                    'status_label' => 'Livré',
                    'date_livraison_effective' => $historique->date_livraison_effective,
                    'photo_proof_url' => $photoPath ? Storage::url($photoPath) : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/colis/{id}/cancel-delivery",
     *     summary="Annuler une livraison",
     *     description="Annule une livraison avec motif d'annulation",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du colis",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif_annulation", type="string", example="Client absent", description="Motif de l'annulation"),
     *             @OA\Property(property="note_livraison", type="string", example="Client non joignable", description="Note du livreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Livraison annulée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Livraison annulée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="integer", example=4),
     *                 @OA\Property(property="status_label", type="string", example="Annulé par le livreur")
     *             )
     *         )
     *     )
     * )
     */
    public function cancelDelivery(Request $request, $id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            // Validation des données
            $validator = Validator::make($request->all(), [
                'motif_annulation' => 'required|string|max:255',
                'note_livraison' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $colis = Colis::where('id', $id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if (!$colis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Colis non trouvé'
                ], 404);
            }

            if (!in_array($colis->status, [0, 1])) { // Pas en attente ou en cours
                return response()->json([
                    'success' => false,
                    'message' => 'Ce colis ne peut pas être annulé'
                ], 400);
            }

            DB::beginTransaction();

            // Mettre à jour le statut du colis (annulé par le livreur)
            $colis->update(['status' => 4]);

            // Mettre à jour l'historique de livraison
            $historique = Historique_livraison::where('colis_id', $colis->id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if ($historique) {
                $historique->update([
                    'status' => 'annule',
                    'motif_annulation' => $request->motif_annulation,
                    'note_livraison' => $request->note_livraison
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison annulée avec succès',
                'data' => [
                    'id' => $colis->id,
                    'status' => $colis->status,
                    'status_label' => 'Annulé par le livreur'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/delivery/stats",
     *     summary="Statistiques de livraison",
     *     description="Récupère les statistiques de livraison du livreur pour une période donnée",
     *     tags={"Livraison Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date_debut",
     *         in="query",
     *         description="Date de début (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_fin",
     *         in="query",
     *         description="Date de fin (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistiques récupérées avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="periode", type="object",
     *                     @OA\Property(property="debut", type="string", format="date", example="2025-10-01"),
     *                     @OA\Property(property="fin", type="string", format="date", example="2025-10-31")
     *                 ),
     *                 @OA\Property(property="statistiques", type="object",
     *                     @OA\Property(property="total_colis", type="integer", example=50),
     *                     @OA\Property(property="colis_livres", type="integer", example=45),
     *                     @OA\Property(property="colis_annules", type="integer", example=5),
     *                     @OA\Property(property="taux_reussite", type="number", format="float", example=90.0),
     *                     @OA\Property(property="montant_total_encaisse", type="number", format="float", example=2250000.00)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getDailyStats(Request $request)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
            $dateFin = $request->get('date_fin', now()->endOfMonth()->toDateString());

            // Statistiques des colis
            $totalColis = Colis::where('livreur_id', $livreur->id)
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->count();

            $colisLivres = Colis::where('livreur_id', $livreur->id)
                ->where('status', 2) // Livré
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->count();

            $colisAnnules = Colis::where('livreur_id', $livreur->id)
                ->whereIn('status', [3, 4, 5]) // Annulé
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->count();

            $montantTotalEncaisse = Colis::where('livreur_id', $livreur->id)
                ->where('status', 2) // Livré
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->sum('montant_a_encaisse');

            $tauxReussite = $totalColis > 0 ? ($colisLivres / $totalColis) * 100 : 0;

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => [
                    'periode' => [
                        'debut' => $dateDebut,
                        'fin' => $dateFin
                    ],
                    'statistiques' => [
                        'total_colis' => $totalColis,
                        'colis_livres' => $colisLivres,
                        'colis_annules' => $colisAnnules,
                        'taux_reussite' => round($tauxReussite, 2),
                        'montant_total_encaisse' => (float) $montantTotalEncaisse
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer le code de validation par WhatsApp au livreur
     */
    private function sendValidationCodeWhatsApp($livreur, $livraison, $colis)
    {
        try {
            // Récupérer le nom de l'entreprise
            $entrepriseName = $livreur->entreprise ? $livreur->entreprise->name : 'MOYOO';

            // Construire le message
            $message = "🚚 MOYOO - Code de Validation de Livraison\n\n";
            $message .= "Bonjour {$livreur->first_name},\n\n";
            $message .= "Vous avez démarré une nouvelle livraison :\n";
            $message .= "📦 Colis : {$colis->code}\n";
            $message .= "🏠 Adresse : {$colis->adresse_client}\n";
            $message .= "👤 Client : {$colis->nom_client}\n";
            $message .= "📱 Téléphone : {$colis->telephone_client}\n\n";
            $message .= "🔐 CODE DE VALIDATION (5 chiffres) : {$livraison->code_validation}\n\n";
            $message .= "⚠️ IMPORTANT :\n";
            $message .= "• Utilisez ce code pour finaliser la livraison\n";
            $message .= "• Ne partagez jamais ce code avec le client\n";
            $message .= "• Le code est valide uniquement pour cette livraison\n\n";
            $message .= "Bonne livraison !\n\n";
            $message .= "Cordialement,\nL'équipe {$entrepriseName}";

            // Envoyer le message
            $result = $this->sendWhatsAppMessageInternal($livreur->mobile, $message);

            if ($result['success']) {
                \Log::info('Code de validation envoyé par WhatsApp', [
                    'livreur_id' => $livreur->id,
                    'livraison_id' => $livraison->id,
                    'colis_id' => $colis->id,
                    'code_validation' => $livraison->code_validation,
                    'mobile' => $livreur->mobile
                ]);
            } else {
                \Log::warning('Échec envoi code de validation WhatsApp', [
                    'livreur_id' => $livreur->id,
                    'livraison_id' => $livraison->id,
                    'colis_id' => $colis->id,
                    'error' => $result['error'] ?? 'Erreur inconnue'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du code de validation WhatsApp', [
                'livreur_id' => $livreur->id,
                'livraison_id' => $livraison->id,
                'colis_id' => $colis->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Envoyer un message WhatsApp via l'API Wassenger
     */
    private function sendWhatsAppMessageInternal($phone, $message)
    {
        // Configuration de l'API Wassenger
        $apiUrl = env('WASSENGER_API_URL', 'https://api.wassenger.com/v1/messages');
        $token = env('WASSENGER_TOKEN', '11aa75a1de8f22a6c05e5b49eeb309b48329258699f05e419624bff1d0fcc9940058293b92a6fc95');

        // Préparer les données
        $data = [
            'phone' => $phone,
            'message' => $message
        ];

        // Configuration cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Exécuter la requête
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Analyser la réponse
        if ($error) {
            return [
                'success' => false,
                'error' => 'Erreur cURL: ' . $error,
                'response' => null
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'response' => $responseData
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Erreur HTTP: ' . $httpCode,
                'response' => $responseData
            ];
        }
    }

    /**
     * Vérifier et créer l'entrée dans balance_marchands si nécessaire
     */
    private function ensureBalanceMarchandExists($colis)
    {
        try {
            // Récupérer les informations depuis la table livraisons
            $livraison = \App\Models\Livraison::where('colis_id', $colis->id)->first();
            \Log::info('Livraison trouvée pour le colis', [
                'livraison' => $livraison
            ]);
            if (!$livraison) {
                \Log::warning('Aucune livraison trouvée pour le colis', [
                    'colis_id' => $colis->id
                ]);
                return;
            }
            \Log::info('Livraison trouvée pour le colis', [
                'livraison_id' => $livraison->id,
                'colis_id' => $colis->id
            ]);

            // Récupérer ou créer la balance du marchand
            $balance = BalanceMarchand::firstOrCreate(
                [
                    'entreprise_id' => $livraison->entreprise_id,
                    'marchand_id' => $livraison->marchand_id,
                    'boutique_id' => $livraison->boutique_id
                ],
                [
                    'montant_encaisse' => 0,
                    'montant_reverse' => 0,
                    'balance_actuelle' => 0,
                    'derniere_mise_a_jour' => now()
                ]
            );

            // Ajouter le montant encaissé du colis
            $montantEncaisse = $colis->montant_a_encaisse ?? 0;

            if ($montantEncaisse > 0) {
                $balance->addEncaissement($montantEncaisse, $colis->id);

                \Log::info('Balance marchand mise à jour après livraison', [
                    'colis_id' => $colis->id,
                    'livraison_id' => $livraison->id,
                    'marchand_id' => $livraison->marchand_id,
                    'boutique_id' => $livraison->boutique_id,
                    'montant_encaisse' => $montantEncaisse,
                    'nouvelle_balance' => $balance->balance_actuelle
                ]);
            } else {
                \Log::warning('Montant à encaisser nul ou vide', [
                    'colis_id' => $colis->id,
                    'montant_a_encaisse' => $colis->montant_a_encaisse
                ]);
            }

            \Log::info('Entrée balance_marchands mise à jour', [
                    'colis_id' => $colis->id,
                    'livraison_id' => $livraison->id,
                    'balance_id' => $balance->id,
                    'entreprise_id' => $livraison->entreprise_id,
                    'marchand_id' => $livraison->marchand_id,
                    'boutique_id' => $livraison->boutique_id
                ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification/création de balance_marchands', [
                'colis_id' => $colis->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Envoyer une notification à l'admin lors de la fin d'une livraison
     */
    private function sendDeliveryCompletedNotificationToAdmin($colis, $livreur)
    {
        try {
            // Récupérer l'admin de l'entreprise
            $admin = \App\Models\User::where('entreprise_id', $colis->entreprise_id)
                ->where('user_type', 'admin')
                ->whereNotNull('fcm_token')
                ->first();

            if (!$admin) {
                \Log::warning('Aucun admin trouvé avec un token FCM pour l\'entreprise', [
                    'entreprise_id' => $colis->entreprise_id,
                    'colis_id' => $colis->id
                ]);
                return;
            }

            // Utiliser le service Firebase
            $firebaseService = new \App\Services\ServiceAccountFirebaseService();
            $result = $firebaseService->sendDeliveryCompletedNotificationToAdmin($colis, $livreur, $admin->fcm_token);

            if ($result['success']) {
                \Log::info('Notification de livraison terminée envoyée à l\'admin', [
                    'admin_id' => $admin->id,
                    'colis_id' => $colis->id,
                    'livreur_id' => $livreur->id
                ]);
            } else {
                \Log::warning('Échec envoi notification livraison terminée à l\'admin', [
                    'admin_id' => $admin->id,
                    'colis_id' => $colis->id,
                    'error' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de notification à l\'admin', [
                'colis_id' => $colis->id,
                'livreur_id' => $livreur->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Activer le suivi GPS pour une mission
     */
    private function activateLocationTracking($livreur, $missionType, $missionId, $historiqueId = null)
    {
        try {
            // Mettre à jour le statut de localisation
            \App\Models\LivreurLocationStatus::updateStatus(
                $livreur->id,
                'active',
                null,
                $livreur->entreprise_id
            );

            \Log::info('Suivi GPS activé pour livreur', [
                'livreur_id' => $livreur->id,
                'mission_type' => $missionType,
                'mission_id' => $missionId,
                'historique_id' => $historiqueId
            ]);

            // Notifier le serveur Socket.IO (optionnel)
            // Ici on pourrait envoyer une notification au serveur Socket.IO
            // pour informer que le livreur est maintenant en mission

        } catch (\Exception $e) {
            \Log::error('Erreur activation suivi GPS', [
                'livreur_id' => $livreur->id,
                'mission_type' => $missionType,
                'error' => $e->getMessage()
            ]);
        }
    }
}
