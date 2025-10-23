<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ramassage;
use App\Models\RamassageColis;
use App\Models\Colis;
use App\Models\PlanificationRamassage;
use App\Helpers\ImageCompressor;
use App\Notifications\PickupCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Ramassage Livreur",
 *     description="Endpoints de gestion des ramassages pour les livreurs"
 * )
 */
class LivreurRamassageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/livreur/ramassages",
     *     summary="Liste des ramassages assignés au livreur",
     *     description="Récupère la liste des ramassages assignés au livreur connecté",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut (planifie, en_cours, termine)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"planifie", "en_cours", "termine"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des ramassages récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ramassages récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code_ramassage", type="string", example="RAM-000001"),
     *                     @OA\Property(property="statut", type="string", example="planifie"),
     *                     @OA\Property(property="date_planifiee", type="string", format="date", example="2025-10-13"),
     *                     @OA\Property(property="adresse_ramassage", type="string", example="123 Rue de la Paix"),
     *                     @OA\Property(property="contact_ramassage", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="telephone_contact", type="string", example="0123456789"),
     *                     @OA\Property(property="nombre_colis_estime", type="integer", example=5),
     *                     @OA\Property(property="nombre_colis_reel", type="integer", example=0),
     *                     @OA\Property(property="montant_total", type="number", format="float", example=50000.00),
     *                     @OA\Property(
     *                         property="marchand",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom_complet", type="string", example="Marchand Test")
     *                     ),
     *                     @OA\Property(
     *                         property="boutique",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="libelle", type="string", example="Boutique Centre")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="statistiques",
     *                 type="object",
     *                 @OA\Property(property="colis_termines", type="integer", example=5, description="Nombre de ramassages terminés"),
     *                 @OA\Property(property="colis_en_attente", type="integer", example=3, description="Nombre de ramassages en attente (planifiés)"),
     *                 @OA\Property(property="colis_en_cours", type="integer", example=2, description="Nombre de ramassages en cours"),
     *                 @OA\Property(property="colis_annules", type="integer", example=1, description="Nombre de ramassages annulés"),
     *                 @OA\Property(property="total", type="integer", example=11, description="Total des ramassages"),
     *                 @OA\Property(property="montant_total_encaisse", type="number", format="float", example=125000.00, description="Montant total encaissé des ramassages terminés")
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
    public function getRamassagesAssignes(Request $request)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            $query = Ramassage::with(['marchand', 'boutique', 'livreur'])
                ->whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id);
                });

            // Filtrer par statut si fourni
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            $ramassages = $query->orderBy('date_planifiee', 'desc')->get();

            // Calculer les statistiques des colis par statut
            $stats = [
                'termine' => 0,
                'en_attente' => 0,
                'en_cours' => 0,
                'annule' => 0
            ];

            $montantTotalEncaisse = 0;

            foreach ($ramassages as $ramassage) {
                switch ($ramassage->statut) {
                    case 'termine':
                        $stats['termine']++;
                        $montantTotalEncaisse += (float) $ramassage->montant_total;
                        break;
                    case 'planifie':
                        $stats['en_attente']++;
                        break;
                    case 'en_cours':
                        $stats['en_cours']++;
                        break;
                    case 'annule':
                        $stats['annule']++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Ramassages récupérés avec succès',
                'data' => $ramassages,
                'statistiques' => [
                    'colis_termines' => $stats['termine'],
                    'colis_en_attente' => $stats['en_attente'],
                    'colis_en_cours' => $stats['en_cours'],
                    'colis_annules' => $stats['annule'],
                    'total' => array_sum($stats),
                    'montant_total_encaisse' => $montantTotalEncaisse
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des ramassages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/ramassages/{id}/details",
     *     summary="Détails d'un ramassage",
     *     description="Récupère les détails complets d'un ramassage spécifique",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ramassage",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du ramassage récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du ramassage récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code_ramassage", type="string", example="RAM-000001"),
     *                 @OA\Property(property="statut", type="string", example="planifie"),
     *                 @OA\Property(property="date_planifiee", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="adresse_ramassage", type="string", example="123 Rue de la Paix"),
     *                 @OA\Property(property="contact_ramassage", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="telephone_contact", type="string", example="0123456789"),
     *                 @OA\Property(property="nombre_colis_estime", type="integer", example=5),
     *                 @OA\Property(property="nombre_colis_reel", type="integer", example=0),
     *                 @OA\Property(property="montant_total", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="notes", type="string", example="Notes spéciales"),
     *                 @OA\Property(
     *                     property="marchand",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom_complet", type="string", example="Marchand Test")
     *                 ),
     *                 @OA\Property(
     *                     property="boutique",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="libelle", type="string", example="Boutique Centre")
     *                 ),
     *                 @OA\Property(
     *                     property="colis",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="code", type="string", example="CLIS-000001"),
     *                         @OA\Property(property="nom_client", type="string", example="Client Test"),
     *                         @OA\Property(property="telephone_client", type="string", example="0123456789"),
     *                         @OA\Property(property="adresse_client", type="string", example="Adresse Client"),
     *                         @OA\Property(property="montant_a_encaisse", type="number", format="float", example=10000.00),
     *                         @OA\Property(property="prix_de_vente", type="number", format="float", example=10000.00)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ramassage non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ramassage non trouvé")
     *         )
     *     )
     * )
     */
    public function getRamassageDetails($id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            $ramassage = Ramassage::with(['marchand', 'boutique', 'livreur', 'colisLies'])
                ->whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id);
                })
                ->findOrFail($id);

            // Si colis_data est null, créer des données par défaut basées sur le ramassage
            if ($ramassage->colis_data === null) {
                // Créer des données de colis par défaut basées sur les informations du ramassage
                $colisData = [];
                $nombreColis = $ramassage->nombre_colis_reel > 0 ? $ramassage->nombre_colis_reel : $ramassage->nombre_colis_estime;

                for ($i = 1; $i <= $nombreColis; $i++) {
                    $colisData[] = [
                        'id' => null, // Pas d'ID car ce sont des colis virtuels
                        'code' => 'VIRTUAL-' . $ramassage->code_ramassage . '-' . $i,
                        'nom_client' => 'Client ' . $i,
                        'telephone_client' => $ramassage->contact_ramassage,
                        'adresse_client' => $ramassage->adresse_ramassage,
                        'montant_a_encaisse' => $ramassage->montant_total / $nombreColis,
                        'prix_de_vente' => $ramassage->montant_total / $nombreColis,
                        'note_client' => 'Colis virtuel généré automatiquement',
                        'instructions_livraison' => 'Instructions de livraison par défaut',
                        'commune_id' => 1, // Valeur par défaut
                        'livreur_id' => $ramassage->livreur_id,
                        'engin_id' => 1, // Valeur par défaut
                        'poids_id' => 1, // Valeur par défaut
                        'mode_livraison_id' => 1, // Valeur par défaut
                        'temp_id' => 1, // Valeur par défaut
                        'created_at' => $ramassage->created_at,
                        'updated_at' => $ramassage->updated_at
                    ];
                }

                // Mettre à jour le champ colis_data
                $ramassage->colis_data = json_encode($colisData);
                $ramassage->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Détails du ramassage récupérés avec succès',
                'data' => $ramassage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ramassage non trouvé ou erreur: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/ramassages/{id}/start",
     *     summary="Démarrer un ramassage",
     *     description="Marque un ramassage comme en cours et met à jour la planification",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ramassage",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ramassage démarré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ramassage démarré avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="statut", type="string", example="en_cours")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous avez déjà un ramassage en cours. Terminez-le avant d'en démarrer un nouveau."),
     *             @OA\Property(
     *                 property="active_pickups",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RAMS-ABC123"),
     *                     @OA\Property(property="marchand", type="string", example="John Doe"),
     *                     @OA\Property(property="boutique", type="string", example="Boutique Central")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function startRamassage($id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            // Vérifier si le livreur a déjà un ramassage en cours
            if (!$livreur->canStartPickup()) {
                $activePickups = $livreur->getActivePickups();
                \Log::warning("Tentative de démarrage de ramassage avec ramassage en cours", [
                    'livreur_id' => $livreur->id,
                    'livreur_name' => $livreur->first_name . ' ' . $livreur->last_name,
                    'ramassage_id' => $id,
                    'active_pickups_count' => $activePickups->count(),
                    'active_pickups' => $activePickups->pluck('id')->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà un ramassage en cours. Terminez-le avant d\'en démarrer un nouveau.',
                    'active_pickups' => $activePickups->map(function($ramassage) {
                        return [
                            'id' => $ramassage->id,
                            'code' => $ramassage->code_ramassage,
                            'marchand' => $ramassage->marchand ? $ramassage->marchand->first_name . ' ' . $ramassage->marchand->last_name : 'N/A',
                            'boutique' => $ramassage->boutique ? $ramassage->boutique->libelle : 'N/A'
                        ];
                    })
                ], 400);
            }

            DB::beginTransaction();

            // Vérifier que le ramassage est assigné au livreur et en statut planifié
            $ramassage = Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                $q->where('livreur_id', $livreur->id)
                  ->where('statut_planification', 'planifie');
            })->where('statut', 'planifie')->findOrFail($id);

            // Mettre à jour le statut du ramassage et enregistrer la date de début
            $ramassage->update([
                'statut' => 'en_cours',
                'date_debut_ramassage' => now(),
                'livreur_id' => $livreur->id
            ]);

            // Mettre à jour la planification
            $planification = $ramassage->planifications()
                ->where('livreur_id', $livreur->id)
                ->first();

            if ($planification) {
                $planification->update(['statut_planification' => 'en_cours']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ramassage démarré avec succès',
                'data' => [
                    'id' => $ramassage->id,
                    'statut' => 'en_cours'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Ce ramassage n\'est pas disponible pour le démarrage'
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/ramassages/{id}/complete",
     *     summary="Finaliser un ramassage",
     *     description="Marque un ramassage comme terminé avec les détails de ramassage",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ramassage",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nombre_colis_reel", type="integer", example=2, description="Nombre réel de colis ramassés"),
     *                 @OA\Property(property="notes_ramassage", type="string", example="Notes sur le ramassage", description="Notes du livreur (optionnel)"),
     *                 @OA\Property(property="raison_difference", type="string", example="Client n'avait qu'un seul colis prêt", description="Raison de la différence entre colis estimés et réels (optionnel)"),
     *                 @OA\Property(
     *                     property="photos_colis",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Photos des colis ramassés (obligatoire - une photo par colis)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ramassage finalisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ramassage finalisé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="statut", type="string", example="termine"),
     *                 @OA\Property(property="date_effectuee", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="nombre_colis_estime", type="integer", example=2),
     *                 @OA\Property(property="nombre_colis_reel", type="integer", example=2),
     *                 @OA\Property(
     *                     property="photos_colis",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="filename", type="string", example="colis_1_1760355000_1.jpg"),
     *                         @OA\Property(property="url", type="string", example="http://192.168.1.5:8000/storage/ramassages/photos/colis_1_1760355000_1.jpg"),
     *                         @OA\Property(property="path", type="string", example="ramassages/photos/colis_1_1760355000_1.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="difference_info",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="colis_estimes", type="integer", example=2),
     *                     @OA\Property(property="colis_reels", type="integer", example=1),
     *                     @OA\Property(property="difference", type="integer", example=-1),
     *                     @OA\Property(property="type_difference", type="string", example="moins"),
     *                     @OA\Property(property="raison", type="string", example="Client n'avait qu'un seul colis prêt")
     *                 )
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
    public function completeRamassage($id, Request $request)
    {
        // Gérer les requêtes multipart/form-data
        $data = $request->all();

        // Si les données sont vides (problème avec multipart/form-data),
        // essayer de récupérer les données via input()
        if (empty($data)) {
            $data = [
                'nombre_colis_reel' => $request->input('nombre_colis_reel'),
                'notes_ramassage' => $request->input('notes_ramassage'),
                'raison_difference' => $request->input('raison_difference')
            ];
        }

        // Validation des données textuelles
        $validator = Validator::make($data, [
            'nombre_colis_reel' => 'required|integer|min:0',
            'notes_ramassage' => 'nullable|string|max:500',
            'raison_difference' => 'nullable|string|max:500'
        ]);

        // Validation des fichiers
        $fileValidator = Validator::make($request->all(), [
            'photos_colis' => 'required|array|min:1',
            'photos_colis.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        if ($validator->fails() || $fileValidator->fails()) {
            $errors = $validator->errors()->merge($fileValidator->errors());
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $errors
            ], 422);
        }

        try {
            $livreur = Auth::guard('livreur')->user();

            DB::beginTransaction();

            // Vérifier que le ramassage est en cours et assigné au livreur
            $ramassage = Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                $q->where('livreur_id', $livreur->id)
                  ->where('statut_planification', 'en_cours');
            })->where('statut', 'en_cours')->findOrFail($id);

            // Gérer l'upload des photos de colis (obligatoires)
            $photosColis = $request->file('photos_colis');
            $uploadedPhotosColis = [];

            foreach ($photosColis as $index => $photoColis) {
                $filenameColis = 'colis_' . $ramassage->id . '_' . time() . '_' . ($index + 1) . '.' . $photoColis->getClientOriginalExtension();

                $compressedPath = ImageCompressor::compressUploadedFile(
                    $photoColis,
                    'ramassages/photos',
                    $filenameColis,
                    1024 // 1MB max
                );

                if ($compressedPath) {
                    $uploadedPhotosColis[] = [
                        'filename' => $filenameColis,
                        'url' => asset('storage/' . $compressedPath),
                        'path' => $compressedPath
                    ];
                }
            }

            if (count($uploadedPhotosColis) !== (int)$data['nombre_colis_reel']) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Le nombre de photos de colis doit correspondre au nombre de colis récupérés'
                ], 422);
            }

            // Calculer la différence entre estimé et réel
            $difference = $data['nombre_colis_reel'] - $ramassage->nombre_colis_estime;
            $typeDifference = $difference != 0 ? ($difference > 0 ? 'plus' : 'moins') : null;

            // Préparer les notes avec les informations sur les photos
            $currentNotes = $ramassage->notes_livreur ?? '';
            $photoNotes = "\n\n📸 PHOTOS DES COLIS RAMASSÉS (" . count($uploadedPhotosColis) . " photos):\n";
            $photoNotes .= "- Date: " . now()->format('d/m/Y H:i') . "\n";

            foreach ($uploadedPhotosColis as $photo) {
                $photoNotes .= "- {$photo['filename']}\n";
            }

            if (!empty($data['notes_ramassage'])) {
                $photoNotes .= "\nNotes livreur: " . $data['notes_ramassage'] . "\n";
            }

            // Mettre à jour le ramassage avec les nouveaux champs
            $updateData = [
                'statut' => 'termine',
                'date_effectuee' => now()->toDateString(),
                'date_fin_ramassage' => now(),
                'livreur_id' => $livreur->id,
                'nombre_colis_reel' => $data['nombre_colis_reel'],
                'notes_ramassage' => $data['notes_ramassage'] ?? null,
                'notes_livreur' => $currentNotes . $photoNotes
            ];

            // Ajouter les informations de différence si nécessaire
            if ($difference != 0) {
                $updateData['difference_colis'] = $difference;
                $updateData['type_difference'] = $typeDifference;
                $updateData['raison_difference'] = $data['raison_difference'] ?? null;
            }


            $ramassage->update($updateData);

            // Mettre à jour la planification
            $planification = $ramassage->planifications()
                ->where('livreur_id', $livreur->id)
                ->first();

            if ($planification) {
                $planification->update(['statut_planification' => 'termine']);
            }

            DB::commit();

            // Envoyer une notification à l'admin
            $this->sendRamassageCompletedNotificationToAdmin($ramassage, $livreur);

            // Envoyer une notification en base de données
            $admin = \App\Models\User::where('entreprise_id', $ramassage->entreprise_id)
                ->where('user_type', 'admin')
                ->first();

            if ($admin) {
                $admin->notify(new PickupCompletedNotification($ramassage, $livreur));
            }

            // Préparer les informations de différence pour la réponse
            $differenceInfo = null;
            if ($difference != 0) {
                $differenceInfo = [
                    'colis_estimes' => $ramassage->nombre_colis_estime,
                    'colis_reels' => $data['nombre_colis_reel'],
                    'difference' => $difference,
                    'type_difference' => $difference > 0 ? 'plus' : 'moins',
                    'raison' => $data['raison_difference'] ?? null
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Ramassage finalisé avec succès',
                'data' => [
                    'id' => $ramassage->id,
                    'statut' => 'termine',
                    'date_effectuee' => $ramassage->date_effectuee,
                    'nombre_colis_estime' => $ramassage->nombre_colis_estime,
                    'nombre_colis_reel' => $data['nombre_colis_reel'],
                    'photos_colis' => $uploadedPhotosColis,
                    'difference_info' => $differenceInfo
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/livreur/ramassages/stats/daily",
     *     summary="Statistiques quotidiennes de ramassage",
     *     description="Récupère les statistiques de ramassage du livreur pour la journée",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statistiques récupérées avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="date", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="ramassages_planifies", type="integer", example=3),
     *                 @OA\Property(property="ramassages_en_cours", type="integer", example=1),
     *                 @OA\Property(property="ramassages_termines", type="integer", example=2),
     *                 @OA\Property(property="total_colis_ramasses", type="integer", example=15),
     *                 @OA\Property(property="montant_total_ramasse", type="number", format="float", example=150000.00)
     *             )
     *         )
     *     )
     * )
     */
    public function getDailyStats()
    {
        try {
            $livreur = Auth::guard('livreur')->user();
            $today = now()->toDateString();

            $stats = [
                'date' => $today,
                'ramassages_planifies' => Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id)
                      ->where('date_planifiee', $today);
                })->where('statut', 'planifie')->count(),

                'ramassages_en_cours' => Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id)
                      ->where('date_planifiee', $today);
                })->where('statut', 'en_cours')->count(),

                'ramassages_termines' => Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id)
                      ->where('date_planifiee', $today);
                })->where('statut', 'termine')->count(),

                'total_colis_ramasses' => Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id)
                      ->where('date_planifiee', $today);
                })->where('statut', 'termine')->sum('nombre_colis_reel'),

                'montant_total_ramasse' => Ramassage::whereHas('planifications', function ($q) use ($livreur) {
                    $q->where('livreur_id', $livreur->id)
                      ->where('date_planifiee', $today);
                })->where('statut', 'termine')->sum('montant_total')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/ramassages/{id}/cancel",
     *     summary="Annuler un ramassage",
     *     description="Permet au livreur d'annuler un ramassage avec une raison",
     *     tags={"Ramassage Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du ramassage à annuler",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"raison"},
     *             @OA\Property(property="raison", type="string", example="Problème technique avec le véhicule", description="Raison de l'annulation"),
     *             @OA\Property(property="commentaire", type="string", example="Véhicule en panne, impossible de se déplacer", description="Commentaire supplémentaire (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ramassage annulé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ramassage annulé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="statut", type="string", example="annule"),
     *                 @OA\Property(property="raison_annulation", type="string", example="Problème technique avec le véhicule")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ramassage non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ramassage non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé à annuler ce ramassage",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas autorisé à annuler ce ramassage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ramassage déjà terminé ou annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ce ramassage ne peut pas être annulé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function cancelRamassage(Request $request, $id)
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            // Validation des données
            $validator = Validator::make($request->all(), [
                'raison' => 'required|string|max:500',
                'commentaire' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer le ramassage
            $ramassage = Ramassage::find($id);

            if (!$ramassage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ramassage non trouvé'
                ], 404);
            }

            // Vérifier que le livreur est assigné à ce ramassage
            $planification = PlanificationRamassage::where('ramassage_id', $ramassage->id)
                                                  ->where('livreur_id', $livreur->id)
                                                  ->first();

            if (!$planification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à annuler ce ramassage'
                ], 403);
            }

            // Vérifier que le ramassage peut être annulé
            if (in_array($ramassage->statut, ['termine', 'annule'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce ramassage ne peut pas être annulé (déjà terminé ou annulé)'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Mettre à jour le statut du ramassage
                $ramassage->update([
                    'statut' => 'annule',
                    'raison_annulation' => $request->raison,
                    'commentaire_annulation' => $request->commentaire,
                    'date_annulation' => now(),
                    'annule_par' => $livreur->id
                ]);

                // Mettre à jour la planification
                $planification->update([
                    'statut' => 'annule',
                    'date_annulation' => now()
                ]);

                // Si le ramassage était en cours, remettre les colis en attente
                if ($ramassage->statut === 'en_cours') {
                    $colisIds = RamassageColis::where('ramassage_id', $ramassage->id)
                                             ->pluck('colis_id')
                                             ->toArray();

                    if (!empty($colisIds)) {
                        Colis::whereIn('id', $colisIds)
                             ->update([
                                 'status' => Colis::STATUS_EN_ATTENTE,
                                 'updated_at' => now()
                             ]);
                    }
                }

                DB::commit();

                // Log de l'annulation
                \Log::info('Ramassage annulé par le livreur', [
                    'ramassage_id' => $ramassage->id,
                    'livreur_id' => $livreur->id,
                    'raison' => $request->raison,
                    'commentaire' => $request->commentaire,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ramassage annulé avec succès',
                    'data' => [
                        'id' => $ramassage->id,
                        'statut' => $ramassage->statut,
                        'raison_annulation' => $ramassage->raison_annulation,
                        'date_annulation' => $ramassage->date_annulation
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation du ramassage', [
                'ramassage_id' => $id,
                'livreur_id' => $livreur->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du ramassage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer une notification à l'admin lors de la fin d'un ramassage
     */
    private function sendRamassageCompletedNotificationToAdmin($ramassage, $livreur)
    {
        try {
            // Récupérer l'admin de l'entreprise
            $admin = \App\Models\User::where('entreprise_id', $ramassage->entreprise_id)
                ->where('user_type', 'admin')
                ->whereNotNull('fcm_token')
                ->first();

            if (!$admin) {
                \Log::warning('Aucun admin trouvé avec un token FCM pour l\'entreprise', [
                    'entreprise_id' => $ramassage->entreprise_id,
                    'ramassage_id' => $ramassage->id
                ]);
                return;
            }

            // Utiliser le service Firebase
            $firebaseService = new \App\Services\ServiceAccountFirebaseService();
            $result = $firebaseService->sendRamassageCompletedNotificationToAdmin($ramassage, $livreur, $admin->fcm_token);

            if ($result['success']) {
                \Log::info('Notification de ramassage terminé envoyée à l\'admin', [
                    'admin_id' => $admin->id,
                    'ramassage_id' => $ramassage->id,
                    'livreur_id' => $livreur->id
                ]);
            } else {
                \Log::warning('Échec envoi notification ramassage terminé à l\'admin', [
                    'admin_id' => $admin->id,
                    'ramassage_id' => $ramassage->id,
                    'error' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de notification à l\'admin', [
                'ramassage_id' => $ramassage->id,
                'livreur_id' => $livreur->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
