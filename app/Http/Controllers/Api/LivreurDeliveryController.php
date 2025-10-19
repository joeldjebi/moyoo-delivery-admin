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

            foreach ($colis as $colisItem) {
                switch ($colisItem->status) {
                    case 0: // en_attente
                        $stats['en_attente']++;
                        break;
                    case 1: // en_cours
                        $stats['en_cours']++;
                        break;
                    case 2: // livre
                        $stats['livre']++;
                        $montantTotalEncaisse += (float) $colisItem->montant_a_encaisse;
                        break;
                    case 3:
                    case 4:
                    case 5: // annulé
                        $stats['annule']++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Colis assignés récupérés avec succès',
                'data' => $colis,
                'statistiques' => [
                    'colis_en_attente' => $stats['en_attente'],
                    'colis_en_cours' => $stats['en_cours'],
                    'colis_livres' => $stats['livre'],
                    'colis_annules' => $stats['annule'],
                    'total' => array_sum($stats),
                    'montant_total_encaisse' => $montantTotalEncaisse
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
                'packageColis',
                'temp',
                'modeLivraison',
                'poids',
                'typeColis',
                'conditionnementColis',
                'delai',
                'livreur',
                'engin.typeEngin',
                'marchand',
                'boutique'
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

            $colis->historique_livraison = $historique;

            return response()->json([
                'success' => true,
                'message' => 'Détails du colis récupérés avec succès',
                'data' => $colis
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
                    'montant_a_encaisse' => $colis->montant_a_encaisse,
                    'prix_de_vente' => $colis->prix_de_vente,
                    'montant_de_la_livraison' => $colis->calculateDeliveryCost(),
                    'created_by' => $livreur->id
                ]
            );

            // Envoyer le code de validation par WhatsApp au livreur
            $this->sendValidationCodeWhatsApp($livreur, $livraison, $colis);

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
     *                 @OA\Property(property="photo_proof_url", type="string", example="http://192.168.1.5:8000/storage/livraisons/proofs/colis_1_1760357729.jpg")
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

            // Mettre à jour le statut du colis
            $colis->update(['status' => 2]); // Livré

            // Mettre à jour l'historique de livraison
            $historique = Historique_livraison::where('colis_id', $colis->id)
                ->where('livreur_id', $livreur->id)
                ->first();

            if ($historique) {
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
                $historique->refresh();
            }

            // Vérifier et créer l'entrée dans balance_marchands si nécessaire
            $this->ensureBalanceMarchandExists($colis);

            DB::commit();

            // Envoyer une notification au marchand
            $marchand = Marchand::find($colis->marchand_id);
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

            return response()->json([
                'success' => true,
                'message' => 'Livraison finalisée avec succès',
                'data' => [
                    'id' => $colis->id,
                    'status' => $colis->status,
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
}
