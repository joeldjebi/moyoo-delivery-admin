<?php

namespace App\Http\Controllers;

use App\Models\Colis;
use App\Models\PackageColis;
use App\Models\Zone;
use App\Models\Commune;
use App\Models\Livreur;
use App\Models\Engin;
use App\Models\Type_colis;
use App\Models\Conditionnement_colis;
use App\Models\Poid;
use App\Models\Mode_livraison;
use App\Models\Delais;
use App\Models\Marchand;
use App\Models\Boutique;
use App\Models\BalanceMarchand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ramassage;
use App\Models\Temp;
use App\Traits\SendsFirebaseNotifications;
use App\Notifications\NewColisNotification;

class ColisController extends Controller
{
    use SendsFirebaseNotifications;
    /**
     * Récupérer l'ID de l'entreprise de l'utilisateur connecté
     */
    private function getEntrepriseId()
    {
        $user = Auth::user();
        if (!$user) {
            return 1; // Valeur par défaut
        }

        // Récupérer l'entreprise via la table entreprises où created_by = user_id
        $entreprise = DB::table('entreprises')
            ->where('created_by', $user->id)
            ->first();

        return $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $entrepriseId = $this->getEntrepriseId();
            $data['menu'] = 'colis';
            $data['title'] = 'Liste des Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Construire la requête avec les filtres et toutes les relations nécessaires
            $query = Colis::with([
                'zone', // zone() pointe maintenant vers Commune
                'commune',
                'livreur',
                'engin.typeEngin', // Pour le calcul du coût
                'entreprise', // Pour le calcul du coût
                'poids',
                'modeLivraison', // Pour le calcul du coût
                'temp', // Pour le calcul du coût
                'ramassages.marchand' // Pour afficher les ramassages liés
            ]);

            // Filtre par recherche (code, client, téléphone)
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('uuid', 'like', "%{$search}%")
                      ->orWhereHas('zone', function($subQ) use ($search) {
                          $subQ->where('nom', 'like', "%{$search}%");
                      })
                      ->orWhereHas('commune', function($subQ) use ($search) {
                          $subQ->where('libelle', 'like', "%{$search}%");
                      });
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filtre par zone
            if ($request->filled('zone_id')) {
                $query->where('zone_id', $request->get('zone_id'));
            }

            // Filtre par livreur
            if ($request->filled('livreur_id')) {
                $query->where('livreur_id', $request->get('livreur_id'));
            }

            $data['colis'] = $query->where('entreprise_id', $entrepriseId)
            ->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

            // Ajouter les données nécessaires pour les filtres
            $data['zones'] = Commune::orderBy('libelle')->get();
            $data['livreurs'] = Livreur::where('status', 'actif')->orderBy('first_name')->get();
            $data['engins'] = Engin::where('status', 'actif')->orderBy('libelle')->get();

            return view('colis.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des colis: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Erreur lors de la récupération des colis: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Ajouter un Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Récupérer les données nécessaires filtrées par entreprise
            $data['marchands'] = Marchand::where('entreprise_id', $entrepriseId)->orderBy('first_name')->get();
            $data['boutiques'] = Boutique::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['zones'] = Zone::where('actif', true)->where('entreprise_id', $entrepriseId)->orderBy('nom')->get();
            $data['livreurs'] = Livreur::where('status', 'actif')
                ->where('entreprise_id', $entrepriseId)
                ->with('engin.typeEngin')
                ->orderBy('last_name')->get();
            $data['type_colis'] = Type_colis::orderBy('libelle')->get();
            $data['conditionnement_colis'] = Conditionnement_colis::orderBy('libelle')->get();
            $data['poids'] = Poid::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['mode_livraisons'] = Mode_livraison::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['delais'] = Delais::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['temps'] = Temp::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['communes'] = Commune::orderBy('libelle')->get();
            // Les ramassages seront chargés dynamiquement par boutique via AJAX
            $data['ramassages'] = collect();


            return view('colis.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('=== DÉBUT CRÉATION COLIS ===');
            Log::info('Données reçues:', $request->all());

            $user = Auth::user();
            if(empty($user)){
                Log::error('Utilisateur non authentifié');
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            Log::info('Utilisateur authentifié:', ['user_id' => $user->id, 'user_name' => $user->name]);

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();
            Log::info('Entreprise ID récupéré:', ['entreprise_id' => $entrepriseId]);

            // Validation des données générales
            $request->validate([
                'nombre_colis' => 'required|integer|min:1|max:20',
                'marchand_id' => 'required|exists:marchands,id',
                'boutique_id' => 'required|exists:boutiques,id',
                'livreur_id' => 'required|exists:livreurs,id',
                'engin_id' => 'required|exists:engins,id',
                'ramassage_id' => 'nullable|exists:ramassages,id',
                'colis' => 'required|array|min:1',
                'colis.*.nom_client' => 'required|string|max:255',
                'colis.*.telephone_client' => 'required|string|max:20',
                'colis.*.adresse_client' => 'required|string|max:500',
                'colis.*.montant_a_encaisse' => 'nullable|integer|min:0',
                'colis.*.prix_de_vente' => 'nullable|integer|min:0',
                'colis.*.numero_facture' => 'nullable|string|max:255',
                'colis.*.note_client' => 'nullable|string|max:1000',
                'colis.*.commune_id' => 'required|exists:communes,id',
                'colis.*.type_colis_id' => 'required|exists:type_colis,id',
                'colis.*.conditionnement_colis_id' => 'required|exists:conditionnement_colis,id',
                'colis.*.poids_id' => 'required|exists:poids,id',
                'colis.*.delai_id' => 'required|exists:delais,id',
                'colis.*.mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'colis.*.temp_id' => 'required|exists:temps,id'
            ], [
                'livreur_id.required' => 'Le livreur est obligatoire.',
                'livreur_id.exists' => 'Le livreur sélectionné n\'existe pas.',
                'engin_id.required' => 'L\'engin du livreur est obligatoire.',
                'engin_id.exists' => 'L\'engin sélectionné n\'existe pas.',
                'marchand_id.required' => 'Le marchand est obligatoire.',
                'boutique_id.required' => 'La boutique est obligatoire.',
                'nombre_colis.required' => 'Le nombre de colis est obligatoire.',
                'nombre_colis.min' => 'Vous devez créer au moins 1 colis.',
                'nombre_colis.max' => 'Vous ne pouvez pas créer plus de 20 colis à la fois.',
                'colis.required' => 'Les données des colis sont obligatoires.',
                'colis.min' => 'Vous devez créer au moins 1 colis.',
                'colis.*.nom_client.required' => 'Le nom du client est obligatoire pour chaque colis.',
                'colis.*.telephone_client.required' => 'Le téléphone du client est obligatoire pour chaque colis.',
                'colis.*.adresse_client.required' => 'L\'adresse du client est obligatoire pour chaque colis.',
                'colis.*.commune_id.required' => 'La zone de livraison est obligatoire pour chaque colis.',
                'colis.*.poids_id.required' => 'Le poids est obligatoire pour chaque colis.',
                'colis.*.mode_livraison_id.required' => 'Le mode de livraison est obligatoire pour chaque colis.',
                'colis.*.temp_id.required' => 'La période est obligatoire pour chaque colis.'
            ]);

            Log::info('Validation réussie');

            // Récupérer les communes depuis les formulaires de colis
            $communesSelected = [];
            foreach ($request->colis as $colisData) {
                if (!empty($colisData['commune_id'])) {
                    $communesSelected[] = $colisData['commune_id'];
                }
            }
            $communesSelected = array_unique($communesSelected); // Supprimer les doublons

            if (empty($communesSelected)) {
                return redirect()->back()
                    ->with('error', 'Veuillez sélectionner une zone de livraison pour chaque colis.')
                    ->withInput();
            }

            Log::info('Communes sélectionnées:', $communesSelected);

            DB::beginTransaction();
            Log::info('Transaction de base de données démarrée');

            // 1. Créer le package de colis (sans colis_ids pour l'instant)
            Log::info('Création du package de colis...');
            $packageData = [
                'entreprise_id' => $entrepriseId,
                'numero_package' => PackageColis::generatePackageNumber(),
                'marchand_id' => $request->marchand_id,
                'boutique_id' => $request->boutique_id,
                'nombre_colis' => $request->nombre_colis,
                'communes_selected' => $communesSelected,
                'colis_ids' => [], // Sera mis à jour après création des colis
                'livreur_id' => $request->livreur_id,
                'engin_id' => $request->engin_id,
                'statut' => 'en_attente',
                'created_by' => $user->id
            ];

            Log::info('Données du package:', $packageData);

            $packageColis = PackageColis::create($packageData);
            Log::info('Package créé avec succès:', ['package_id' => $packageColis->id]);

            $createdColis = [];
            $createdLivraisons = [];
            $createdZones = [];
            $colisIndex = 0;

            // 2. Traiter chaque colis avec la commune sélectionnée dans le formulaire
            Log::info('Début de la création des colis...');
            foreach ($request->colis as $index => $colisData) {
                Log::info("Traitement du colis {$index}:", $colisData);

                // Récupérer la commune directement depuis le formulaire
                $communeId = $colisData['commune_id'];
                Log::info("Commune ID pour le colis {$index}: {$communeId}");

                if (!$communeId) {
                    Log::error("Commune ID manquant pour le colis {$index}");
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Veuillez sélectionner une zone de livraison pour le colis " . ($index + 1))
                        ->withInput();
                }

                $colisIndex++;

                // Récupérer les informations de la commune
                $commune = Commune::find($communeId);
                if (!$commune) {
                    Log::error("Commune non trouvée pour l'ID {$communeId}");
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Commune introuvable pour le colis " . ($index + 1))
                        ->withInput();
                }
                Log::info("Commune trouvée:", ['commune_id' => $commune->id, 'commune_name' => $commune->libelle]);

                // Créer ou récupérer la zone de livraison pour cette commune
                Log::info("Création/récupération de la zone pour la commune {$commune->libelle}");
                $zone = Zone::firstOrCreate(
                    ['nom' => $commune->libelle, 'entreprise_id' => $entrepriseId],
                    [
                        'nom' => $commune->libelle,
                        'entreprise_id' => $entrepriseId,
                        'actif' => true,
                        'created_by' => $user->id
                    ]
                );
                Log::info("Zone créée/récupérée:", ['zone_id' => $zone->id, 'zone_name' => $zone->nom]);

                if (!in_array($zone->id, $createdZones)) {
                    $createdZones[] = $zone->id;
                }

                // Récupérer l'adresse de la boutique pour l'adresse de ramassage
                $boutique = Boutique::find($request->boutique_id);
                $adresseRamassage = $boutique ? $boutique->adresse : '';

                // Générer le numéro de ramassage automatiquement
                $numeroRamassage = 'RAM-' . str_pad(DB::table('commune_zone')->count() + 1, 6, '0', STR_PAD_LEFT);

                // Créer l'entrée dans commune_zone (sans commune_id car nullable)
                Log::info("Création de l'entrée commune_zone pour le colis {$index}");
                $communeZoneData = [
                    'entreprise_id' => $entrepriseId,
                    'zone_id' => $zone->id,
                    'commune_id' => null, // Nullable comme demandé
                    'ordre' => $colisIndex,
                    'nom_client' => $colisData['nom_client'],
                    'telephone_client' => $this->cleanPhoneNumber($colisData['telephone_client']),
                    'adresse_client' => $colisData['adresse_client'],
                    'marchand_id' => $request->marchand_id,
                    'boutique_id' => $request->boutique_id,
                    'montant_a_encaisse' => $colisData['montant_a_encaisse'] ?? 0,
                    'prix_de_vente' => $colisData['prix_de_vente'] ?? 0,
                    'numero_facture' => $colisData['numero_facture'] ?? null,
                    'type_colis_id' => $colisData['type_colis_id'] ?? null,
                    'conditionnement_colis_id' => $colisData['conditionnement_colis_id'] ?? null,
                    'poids_id' => $colisData['poids_id'] ?? null,
                    'delai_id' => $colisData['delai_id'] ?? null,
                    'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null,
                    'numero_de_ramassage' => $numeroRamassage,
                    'adresse_de_ramassage' => $adresseRamassage,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                Log::info("Données commune_zone:", $communeZoneData);

                $communeZone = \DB::table('commune_zone')->insertGetId($communeZoneData);
                Log::info("Entrée commune_zone créée avec l'ID: {$communeZone}");

                // Créer le colis avec référence au package
                Log::info("Création du colis pour l'index {$index}");
                $colisCreateData = [
                    'entreprise_id' => $entrepriseId,
                    'uuid' => \Str::uuid(),
                    'code' => $this->generateColisCode($zone->id, $communeId),
                    'montant_a_encaisse' => $colisData['montant_a_encaisse'] ?? 0,
                    'prix_de_vente' => $colisData['prix_de_vente'] ?? 0,
                    'numero_facture' => $colisData['numero_facture'] ?? '',
                    'nom_client' => $colisData['nom_client'],
                    'telephone_client' => $this->cleanPhoneNumber($colisData['telephone_client']),
                    'adresse_client' => $colisData['adresse_client'],
                    'note_client' => $colisData['note_client'] ?? '',
                    'numero_de_ramassage' => '',
                    'adresse_de_ramassage' => '',
                    'status' => 0, // En attente
                    'zone_id' => $zone->id,
                    'commune_id' => $communeId, // Renseigner la commune pour le calcul des coûts
                    'package_colis_id' => $packageColis->id, // Référence au package
                    'livreur_id' => $request->livreur_id,
                    'engin_id' => $request->engin_id,
                    'poids_id' => $colisData['poids_id'] ?? null, // Ajouter le poids
                    'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null, // Ajouter le mode de livraison
                    'temp_id' => $colisData['temp_id'] ?? null, // Ajouter la période temporelle
                    'created_by' => $user->id
                ];

                Log::info("Données du colis à créer:", $colisCreateData);

                $colis = Colis::create($colisCreateData);
                Log::info("Colis créé avec succès:", ['colis_id' => $colis->id, 'colis_code' => $colis->code]);

                // Envoyer une notification à l'admin
                $admin = \App\Models\User::where('entreprise_id', $entrepriseId)
                    ->whereIn('user_type', ['admin', 'entreprise_user'])
                    ->first();

                if ($admin) {
                    $admin->notify(new NewColisNotification($colis));
                }

                // Envoyer une notification Firebase au livreur
                try {
                    $livreur = Livreur::find($request->livreur_id);
                    if ($livreur && $livreur->fcm_token) {
                        $this->sendColisCreatedNotification($livreur, $colis);
                        Log::info("Notification Firebase envoyée au livreur", [
                            'livreur_id' => $livreur->id,
                            'colis_id' => $colis->id,
                            'colis_code' => $colis->code
                        ]);
                    } else {
                        Log::warning("Impossible d'envoyer la notification Firebase", [
                            'livreur_id' => $request->livreur_id,
                            'livreur_found' => $livreur ? 'yes' : 'no',
                            'fcm_token' => $livreur ? ($livreur->fcm_token ? 'yes' : 'no') : 'no'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'envoi de la notification Firebase", [
                        'colis_id' => $colis->id,
                        'error' => $e->getMessage()
                    ]);
                }

                $createdColis[] = $colis;

                // Créer automatiquement la livraison pour ce colis
                $livraison = $colis->createLivraison($request->marchand_id, $request->boutique_id);
                $createdLivraisons[] = $livraison->id;
            }

            // 3. Mettre à jour le package avec les IDs des colis créés
            $colisIds = array_map(function($colis) {
                return $colis->id;
            }, $createdColis);

            $packageColis->update([
                'colis_ids' => $colisIds
            ]);

            DB::commit();

            Log::info('Package de colis créé avec succès', [
                'package_id' => $packageColis->id,
                'numero_package' => $packageColis->numero_package,
                'nombre_colis' => count($createdColis),
                'colis_ids' => $colisIds,
                'communes_selected' => $communesSelected,
                'zones_created' => $createdZones,
                'user_id' => $user->id
            ]);

            // Si un ramassage est sélectionné, lier les colis créés au ramassage
            if ($request->ramassage_id) {
                $ramassage = Ramassage::find($request->ramassage_id);
                if ($ramassage) {
                    foreach ($createdColis as $colis) {
                        $ramassage->colisLies()->attach($colis->id);
                    }

                    Log::info('Colis liés au ramassage', [
                        'ramassage_id' => $ramassage->id,
                        'code_ramassage' => $ramassage->code_ramassage,
                        'colis_count' => count($createdColis),
                        'user_id' => $user->id
                    ]);
                }
            }

            Log::info('=== CRÉATION TERMINÉE AVEC SUCCÈS ===');
            Log::info('Package créé:', ['package_id' => $packageColis->id, 'numero_package' => $packageColis->numero_package]);
            Log::info('Colis créés:', ['count' => count($createdColis), 'colis_ids' => array_map(fn($c) => $c->id, $createdColis)]);
            Log::info('Zones créées:', ['count' => count($createdZones), 'zone_ids' => $createdZones]);
            Log::info('Livraisons créées:', ['count' => count($createdLivraisons), 'livraison_ids' => $createdLivraisons]);

            $message = 'Package ' . $packageColis->numero_package . ' créé avec succès : ' . count($createdColis) . ' colis pour ' . count($communesSelected) . ' commune(s)';
            if (count($createdZones) > 0) {
                $message .= ' - ' . count($createdZones) . ' zone(s) de livraison créée(s)';
            }
            if (count($createdLivraisons) > 0) {
                $message .= ' et ' . count($createdLivraisons) . ' livraison(s) assignée(s)';
            }
            if ($request->ramassage_id) {
                $message .= ' - Colis liés au ramassage';
            }

            Log::info('Message de succès:', ['message' => $message]);
            Log::info('=== FIN CRÉATION COLIS ===');

            return redirect()->route('colis.index')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== ERREUR LORS DE LA CRÉATION DU COLIS ===');
            Log::error('Message d\'erreur: ' . $e->getMessage());
            Log::error('Fichier: ' . $e->getFile());
            Log::error('Ligne: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN ERREUR ===');
            return redirect()->back()
                           ->with('error', 'Erreur lors de la création du colis: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Générer un code de colis
     */
    private function generateColisCode($zoneId, $communeId)
    {
        $zone = Zone::find($zoneId);
        $commune = Commune::find($communeId);

        $zoneInitiales = $zone ? substr(str_replace(' ', '', $zone->nom), 0, 3) : 'ZON';
        $communeInitiales = $commune ? substr(str_replace(' ', '', $commune->libelle), 0, 3) : 'COM';

        $count = Colis::count() + 1;
        $numero = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "CLIS-{$numero}-{$zoneInitiales}{$communeInitiales}";
    }

    /**
     * Générer un numéro de livraison
     */
    private function generateLivraisonNumber()
    {
        $count = \DB::table('livraisons')->count() + 1;
        return 'LIV-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Générer un code de validation
     */
    private function generateValidationCode()
    {
        return strtoupper(substr(md5(uniqid()), 0, 8));
    }

    /**
     * Récupérer les boutiques d'un marchand (AJAX)
     */
    public function getBoutiquesByMarchand($marchandId)
    {
        try {
            $boutiques = Boutique::where('marchand_id', $marchandId)
                ->orderBy('libelle')
                ->get(['id', 'libelle', 'adresse']);

            return response()->json([
                'success' => true,
                'boutiques' => $boutiques
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des boutiques'
            ], 500);
        }
    }

    /**
     * Récupérer les communes d'une zone (AJAX)
     */
    public function getCommunesByZone($zoneId)
    {
        try {
            $communes = Zone::find($zoneId)
                ->communes()
                ->orderBy('commune_zone.ordre')
                ->get(['communes.id', 'communes.libelle']);

            return response()->json($communes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des communes'], 500);
        }
    }

    public function getEnginsByLivreur(Livreur $livreur)
    {
        try {
            // Récupérer l'engin du livreur
            $engin = $livreur->engin;

            return response()->json([
                'success' => true,
                'engin' => $engin,
                'livreur' => [
                    'id' => $livreur->id,
                    'first_name' => $livreur->first_name,
                    'last_name' => $livreur->last_name,
                    'telephone' => $livreur->telephone
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'engin du livreur: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'engin du livreur'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Détails du Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['colis'] = $colis->load([
                'zone',
                'commune',
                'livreur',
                'engin.typeEngin', // Pour le calcul du coût
                'marchand',
                'boutique',
                'entreprise', // Pour le calcul du coût
                'poids', // Pour le calcul du coût
                'modeLivraison', // Pour le calcul du coût
                'temp', // Pour le calcul du coût
                'ramassages.marchand', // Pour afficher les ramassages liés
                'commune_zone.typeColis',
                'commune_zone.conditionnementColis',
                'commune_zone.modeLivraison',
                'commune_zone.poids',
                'commune_zone.delai'
            ]);
            // dd($data['colis']);
            return view('colis.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du colis: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            dd($e);
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du colis: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Modifier le Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['colis'] = $colis->load(['zone', 'commune', 'livreur', 'engin', 'marchand', 'boutique', 'poids', 'modeLivraison', 'temp', 'entreprise']);

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            $data['marchands'] = Marchand::where('entreprise_id', $entrepriseId)->orderBy('first_name')->get();
            $data['communes'] = Commune::orderBy('libelle')->get();
            $data['livreurs'] = Livreur::where('status', 'actif')
                ->where('entreprise_id', $entrepriseId)
                ->with('engin.typeEngin')
                ->orderBy('last_name')->get();
            $data['engins'] = Engin::where('status', 'actif')
                ->where('entreprise_id', $entrepriseId)
                ->with('typeEngin')
                ->orderBy('libelle')->get();
            $data['type_colis'] = Type_colis::orderBy('libelle')->get();
            $data['conditionnement_colis'] = Conditionnement_colis::orderBy('libelle')->get();
            $data['poids'] = Poid::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['delais'] = Delais::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['mode_livraisons'] = Mode_livraison::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();
            $data['temps'] = Temp::where('entreprise_id', $entrepriseId)->orderBy('libelle')->get();

            return view('colis.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Modifier le Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données
            $request->validate([
                'marchand_id' => 'required|exists:marchands,id',
                'boutique_id' => 'required|exists:boutiques,id',
                'livreur_id' => 'nullable|exists:livreurs,id',
                'engin_id' => 'nullable|exists:engins,id',
                'note_client' => 'nullable|string|max:1000',
                'instructions_livraison' => 'nullable|string|max:1000',
                'colis' => 'required|array|min:1',
                'colis.*.nom_client' => 'required|string|max:255',
                'colis.*.telephone_client' => 'required|string|max:20',
                'colis.*.adresse_client' => 'required|string|max:500',
                'colis.*.montant_a_encaisse' => 'nullable|numeric|min:0',
                'colis.*.prix_de_vente' => 'nullable|numeric|min:0',
                'colis.*.numero_facture' => 'nullable|string|max:255',
                'colis.*.note_client' => 'nullable|string|max:1000',
                'colis.*.livreur_id' => 'nullable|exists:livreurs,id',
                'colis.*.engin_id' => 'nullable|exists:engins,id',
                'colis.*.type_colis_id' => 'nullable|exists:type_colis,id',
                'colis.*.conditionnement_colis_id' => 'nullable|exists:conditionnement_colis,id',
                'colis.*.poids_id' => 'required|exists:poids,id',
                'colis.*.delai_id' => 'nullable|exists:delais,id',
                'colis.*.mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'colis.*.temp_id' => 'required|exists:temps,id',
                'colis.*.commune_id' => 'required|exists:communes,id'
            ]);

            // Récupérer les communes sélectionnées depuis les formulaires de colis
            $communesSelected = [];
            foreach ($request->colis as $colisData) {
                if (!empty($colisData['commune_id'])) {
                    $communesSelected[] = $colisData['commune_id'];
                }
            }

            if (empty($communesSelected)) {
                return redirect()->back()
                    ->withErrors(['colis' => 'Veuillez sélectionner une zone de livraison pour chaque colis.'])
                               ->withInput();
            }

            DB::beginTransaction();

            // Mettre à jour le colis avec les nouvelles informations
            $colis->update([
                'note_client' => $request->note_client,
                'instructions_livraison' => $request->instructions_livraison,
                'livreur_id' => $request->livreur_id,
                'engin_id' => $request->engin_id
            ]);

            // Supprimer les anciens enregistrements commune_zone pour toutes les zones qui seront utilisées
            $zonesToDelete = [];
            foreach ($request->colis as $index => $colisData) {
                $communeId = $colisData['commune_id'] ?? null;
                if ($communeId) {
                    $commune = Commune::find($communeId);
                    if ($commune) {
                        $entrepriseId = $this->getEntrepriseId();
                        $zone = Zone::firstOrCreate(
                            ['nom' => $commune->libelle, 'entreprise_id' => $entrepriseId],
                            [
                                'nom' => $commune->libelle,
                                'entreprise_id' => $entrepriseId,
                                'actif' => true,
                                'created_by' => Auth::id()
                            ]
                        );
                        $zonesToDelete[] = $zone->id;
                    }
                }
            }

            // Supprimer les enregistrements commune_zone pour toutes les zones concernées
            if (!empty($zonesToDelete)) {
                DB::table('commune_zone')->whereIn('zone_id', $zonesToDelete)->delete();
            }

            // Récupérer le package_colis_id du colis original
            $packageColisId = $colis->package_colis_id;

            // Traiter chaque colis du formulaire
            foreach ($request->colis as $index => $colisData) {
                $communeId = $colisData['commune_id'] ?? null;

                if ($communeId) {
                    // Récupérer les informations de la commune
                    $commune = Commune::find($communeId);
                    if (!$commune) {
                        DB::rollBack();
                        return redirect()->back()
                                       ->with('error', "Commune introuvable pour le colis " . ($index + 1))
                                       ->withInput();
                    }

                    // Créer ou récupérer la zone de livraison pour cette commune
                    $entrepriseId = $this->getEntrepriseId();
                    $zone = Zone::firstOrCreate(
                        ['nom' => $commune->libelle, 'entreprise_id' => $entrepriseId],
                        [
                            'nom' => $commune->libelle,
                            'entreprise_id' => $entrepriseId,
                            'actif' => true,
                            'created_by' => Auth::id()
                        ]
                    );

                    if ($index == 1) {
                        // Sauvegarder les anciennes valeurs pour détecter les changements
                        $oldLivreurId = $colis->livreur_id;
                        $oldEnginId = $colis->engin_id;

                        // Mettre à jour le colis existant (premier formulaire)
                        $colis->update([
                            'zone_id' => $zone->id,
                            'commune_id' => $communeId,
                            'livreur_id' => $colisData['livreur_id'] ?? $request->livreur_id,
                            'engin_id' => $colisData['engin_id'] ?? $request->engin_id,
                            'note_client' => $colisData['note_client'] ?? '',
                            'poids_id' => $colisData['poids_id'] ?? null,
                            'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null,
                            'temp_id' => $colisData['temp_id'] ?? null
                        ]);

                        // Envoyer une notification Firebase si le livreur a changé
                        try {
                            $newLivreurId = $colisData['livreur_id'] ?? $request->livreur_id;
                            if ($newLivreurId && $newLivreurId != $oldLivreurId) {
                                $livreur = Livreur::find($newLivreurId);
                                if ($livreur && $livreur->fcm_token) {
                                    $changes = [
                                        'livreur_id' => ['old' => $oldLivreurId, 'new' => $newLivreurId],
                                        'engin_id' => ['old' => $oldEnginId, 'new' => $colisData['engin_id'] ?? $request->engin_id]
                                    ];
                                    $this->sendColisUpdatedNotification($livreur, $colis, $changes);
                                    Log::info("Notification Firebase de mise à jour envoyée au livreur", [
                                        'livreur_id' => $livreur->id,
                                        'colis_id' => $colis->id,
                                        'colis_code' => $colis->code,
                                        'changes' => $changes
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Erreur lors de l'envoi de la notification Firebase de mise à jour", [
                                'colis_id' => $colis->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    } else {
                        // Récupérer l'entreprise de l'utilisateur
                        $entreprise = Entreprise::getEntrepriseByUser(Auth::id());
                        if (!$entreprise) {
                            DB::rollBack();
                            return redirect()->back()
                                ->with('error', 'Aucune entreprise trouvée pour cet utilisateur. Veuillez créer une entreprise d\'abord.')
                                ->withInput();
                        }

                        // Créer de nouveaux colis pour les formulaires supplémentaires
                        $newColis = Colis::create([
                            'entreprise_id' => $entreprise->id,
                            'uuid' => \Illuminate\Support\Str::uuid(),
                            'code' => $this->generateColisCode($zone->id, $communeId),
                            'status' => 0,
                            'note_client' => $colisData['note_client'] ?? '',
                            'instructions_livraison' => $request->instructions_livraison,
                            'zone_id' => $zone->id,
                            'commune_id' => $communeId,
                            'package_colis_id' => $packageColisId, // Même package que le colis original
                            'livreur_id' => $colisData['livreur_id'] ?? $request->livreur_id,
                            'engin_id' => $colisData['engin_id'] ?? $request->engin_id,
                            'poids_id' => $colisData['poids_id'] ?? null, // Ajouter le poids
                            'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null, // Ajouter le mode de livraison
                            'temp_id' => $colisData['temp_id'] ?? null, // Ajouter la période temporelle
                            'created_by' => Auth::id()
                        ]);

                        // Envoyer une notification Firebase au livreur pour le nouveau colis
                        try {
                            $livreurId = $colisData['livreur_id'] ?? $request->livreur_id;
                            if ($livreurId) {
                                $livreur = Livreur::find($livreurId);
                                if ($livreur && $livreur->fcm_token) {
                                    $this->sendColisCreatedNotification($livreur, $newColis);
                                    Log::info("Notification Firebase envoyée au livreur pour nouveau colis", [
                                        'livreur_id' => $livreur->id,
                                        'colis_id' => $newColis->id,
                                        'colis_code' => $newColis->code
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Erreur lors de l'envoi de la notification Firebase pour nouveau colis", [
                                'colis_id' => $newColis->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Récupérer l'adresse de la boutique pour l'adresse de ramassage
                    $boutique = Boutique::find($request->boutique_id);
                    $adresseRamassage = $boutique ? $boutique->adresse : '';

                    // Générer le numéro de ramassage automatiquement
                    $numeroRamassage = 'RAM-' . str_pad(DB::table('commune_zone')->count() + 1, 6, '0', STR_PAD_LEFT);

                    // Récupérer l'ID de l'entreprise de l'utilisateur connecté
                    $entrepriseId = $this->getEntrepriseId();

                    // Créer l'enregistrement commune_zone pour chaque colis
                    DB::table('commune_zone')->insert([
                        'entreprise_id' => $entrepriseId,
                        'zone_id' => $zone->id,
                        'commune_id' => $communeId,
                        'ordre' => $index,
                        'nom_client' => $colisData['nom_client'],
                        'telephone_client' => $this->cleanPhoneNumber($colisData['telephone_client']),
                        'adresse_client' => $colisData['adresse_client'],
                        'marchand_id' => $request->marchand_id,
                        'boutique_id' => $request->boutique_id,
                        'montant_a_encaisse' => $colisData['montant_a_encaisse'],
                        'prix_de_vente' => $colisData['prix_de_vente'],
                        'numero_facture' => $colisData['numero_facture'],
                        'type_colis_id' => $colisData['type_colis_id'] ?? null,
                        'conditionnement_colis_id' => $colisData['conditionnement_colis_id'] ?? null,
                        'poids_id' => $colisData['poids_id'] ?? null,
                        'delai_id' => $colisData['delai_id'] ?? null,
                        'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null,
                        'numero_de_ramassage' => $numeroRamassage,
                        'adresse_de_ramassage' => $adresseRamassage,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Mettre à jour le package avec tous les colis (y compris les nouveaux créés)
            if ($packageColisId) {
                $package = PackageColis::find($packageColisId);
                if ($package) {
                    $allColisIds = Colis::where('package_colis_id', $packageColisId)->pluck('id')->toArray();
                    $package->update([
                        'colis_ids' => $allColisIds,
                        'nombre_colis' => count($allColisIds)
                    ]);
                }
            }

            // Mettre à jour la livraison associée au colis principal
            $colis->updateLivraison();

            DB::commit();

            Log::info('Colis mis à jour avec succès', [
                'colis_id' => $colis->id,
                'code' => $colis->code,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('colis.show', $colis->id)
                           ->with('success', "Colis mis à jour avec succès. Code: {$colis->code}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du colis: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la mise à jour du colis.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Supprimer le Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier si le colis peut être supprimé
            if ($colis->status === Colis::STATUS_EN_COURS) {
                return redirect()->back()
                               ->with('error', 'Impossible de supprimer un colis en cours de livraison.');
            }

            if ($colis->status === Colis::STATUS_LIVRE) {
                return redirect()->back()
                               ->with('error', 'Impossible de supprimer un colis déjà livré.');
            }

            DB::beginTransaction();

            // Supprimer le colis (soft delete)
            $colis->delete();

            DB::commit();

            Log::info('Colis supprimé avec succès', [
                'colis_id' => $colis->id,
                'code' => $colis->code,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('colis.index')
                           ->with('success', "Colis supprimé avec succès. Code: {$colis->code}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du colis: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression du colis.');
        }
    }

    /**
     * Toggle the status of the colis
     */
    public function toggleStatus(Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Changer le Statut du Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $newStatus = $colis->status === Colis::STATUS_EN_ATTENTE
                        ? Colis::STATUS_EN_COURS
                        : Colis::STATUS_EN_ATTENTE;

            $colis->update(['status' => $newStatus]);

            $statusLabel = $colis->status_label;

            Log::info("Statut du colis changé", [
                'colis_id' => $colis->id,
                'code' => $colis->code,
                'new_status' => $statusLabel,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                           ->with('success', "Statut du colis changé: {$statusLabel}");

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du changement de statut.');
        }
    }

    /**
     * Assign a livreur to the colis
     */
    public function assignLivreur(Request $request, Colis $colis)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Assigner un Livreur au Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $request->validate([
                'livreur_id' => 'nullable|exists:livreurs,id',
                'engin_id' => 'nullable|exists:engins,id'
            ]);

            $colis->update([
                'livreur_id' => $request->livreur_id,
                'engin_id' => $request->engin_id,
                'status' => Colis::STATUS_EN_COURS
            ]);

            Log::info('Livreur assigné au colis', [
                'colis_id' => $colis->id,
                'code' => $colis->code,
                'livreur_id' => $request->livreur_id,
                'engin_id' => $request->engin_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                           ->with('success', 'Livreur assigné avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'assignation du livreur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'assignation du livreur.');
        }
    }

    /**
     * Assigner un livreur à plusieurs colis (assignation en masse)
     */
    public function showAssignPage(Request $request)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Assignation en masse des Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer les IDs des colis depuis la requête
            $colisIds = $request->get('colis_ids', '');

            if (empty($colisIds)) {
                return redirect()->route('colis.index')
                    ->with('error', 'Aucun colis sélectionné pour l\'assignation.');
            }

            // Convertir la chaîne d'IDs en tableau
            $colisIdsArray = array_filter(explode(',', $colisIds));

            // Récupérer les colis sélectionnés
            $data['selectedColis'] = Colis::whereIn('id', $colisIdsArray)
                ->where('status', Colis::STATUS_EN_ATTENTE)

                ->with(['zone', 'commune', 'poids', 'modeLivraison', 'temp'])
                ->get();

            if ($data['selectedColis']->count() !== count($colisIdsArray)) {
                return redirect()->route('colis.index')
                    ->with('error', 'Certains colis ne peuvent pas être assignés (statut incorrect - seuls les colis en attente peuvent être assignés).');
            }

            // Récupérer les livreurs disponibles avec leurs engins
            $data['livreurs'] = Livreur::where('status', 'actif')
                ->with('engin.typeEngin')
                ->orderBy('first_name')
                ->get();

            $data['colisIds'] = $colisIds;

            return view('colis.assign', $data);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la page d\'assignation: ' . $e->getMessage());
            return redirect()->route('colis.index')
                ->with('error', 'Erreur lors du chargement de la page d\'assignation.');
        }
    }

    public function bulkAssignLivreur(Request $request)
    {
        try {
            $data['menu'] = 'colis';
            $data['title'] = 'Assignation en masse des Colis';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $request->validate([
                'colis_ids' => 'required|string',
                'livreur_id' => 'nullable|exists:livreurs,id',
                'engin_id' => 'nullable|exists:engins,id'
            ]);

            // Convertir la chaîne d'IDs en tableau
            $colisIds = array_filter(explode(',', $request->colis_ids));

            if (empty($colisIds)) {
                return redirect()->back()->with('error', 'Aucun colis sélectionné.');
            }

            // Vérifier que tous les colis existent et sont assignables
            $colis = Colis::whereIn('id', $colisIds)
                         ->where('status', Colis::STATUS_EN_ATTENTE)

                         ->get();

            if ($colis->count() !== count($colisIds)) {
                return redirect()->back()->with('error', 'Certains colis ne peuvent pas être assignés (statut incorrect - seuls les colis en attente peuvent être assignés).');
            }

            DB::beginTransaction();

            // Assigner le livreur à tous les colis
            $updatedCount = 0;
            foreach ($colis as $colisItem) {
                $colisItem->update([
                    'livreur_id' => $request->livreur_id,
                    'engin_id' => $request->engin_id,
                    'status' => Colis::STATUS_EN_COURS
                ]);
                $updatedCount++;
            }

            DB::commit();

            Log::info('Assignation en masse réussie', [
                'user_id' => $data['user']->id,
                'livreur_id' => $request->livreur_id,
                'engin_id' => $request->engin_id,
                'colis_count' => $updatedCount,
                'colis_ids' => $colisIds
            ]);

            return redirect()->route('colis.index')
                           ->with('success', "{$updatedCount} colis ont été assignés avec succès au livreur.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation en masse: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'assignation en masse des colis.');
        }
    }

    /**
     * Mark colis as delivered
     */
    public function markAsDelivered(Colis $colis)
    {
        try {
            if ($colis->status !== Colis::STATUS_EN_COURS) {
                return redirect()->back()
                               ->with('error', 'Seuls les colis en cours peuvent être marqués comme livrés.');
            }

            DB::beginTransaction();

            // Marquer le colis comme livré
            $colis->update(['status' => Colis::STATUS_LIVRE]);

            // Mettre à jour l'historique de livraison
            $livraison = \App\Models\Historique_livraison::where('colis_id', $colis->id)->first();
            if ($livraison) {
                $livraison->update(['status' => 'livre']);
            }

            // Mettre à jour la balance du marchand
            $this->updateMarchandBalance($colis);

            Log::info('Colis marqué comme livré', [
                'colis_id' => $colis->id,
                'code' => $colis->code,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->back()
                           ->with('success', 'Colis marqué comme livré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage comme livré: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du marquage comme livré.');
        }
    }

    /**
     * Search colis
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');

            if (empty($query)) {
                return redirect()->route('colis.index');
            }

            $colis = Colis::where('code', 'like', "%{$query}%")
                         ->orWhere('uuid', 'like', "%{$query}%")
                         ->orWhereHas('zone', function($q) use ($query) {
                             $q->where('nom', 'like', "%{$query}%");
                         })
                         ->orWhereHas('commune', function($q) use ($query) {
                             $q->where('libelle', 'like', "%{$query}%");
                         })
                         ->with(['zone', 'commune', 'livreur', 'engin'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

            $data = [
                'colis' => $colis,
                'query' => $query,
                'menu' => 'colis'
            ];

            return view('colis.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la recherche.');
        }
    }

    /**
     * Filter colis by status
     */
    public function filterByStatus(Request $request)
    {
        try {
            $status = $request->get('status');

            if (!in_array($status, [0, 1, 2, 3, 4, 5])) {
                return redirect()->route('colis.index');
            }

            $colis = Colis::where('status', $status)
                         ->with(['zone', 'commune', 'livreur', 'engin'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

            $statusLabel = match($status) {
                0 => 'En attente',
                1 => 'En cours',
                2 => 'Livré',
                3 => 'Annulé par le client',
                4 => 'Annulé par le livreur',
                5 => 'Annulé par le marchand',
                default => 'Statut inconnu'
            };

            $data = [
                'colis' => $colis,
                'filter_status' => $status,
                'status_label' => $statusLabel,
                'menu' => 'colis'
            ];

            return view('colis.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du filtrage: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du filtrage.');
        }
    }



    /**
     * Assign multiple colis to a livreur across different zones
     */
    public function assignMultipleColis(Request $request)
    {
        try {
            $request->validate([
                'colis_ids' => 'required|array|min:1',
                'colis_ids.*' => 'exists:colis,id',
                'livreur_id' => 'nullable|exists:livreurs,id',
                'engin_id' => 'nullable|exists:engins,id'
            ]);

            DB::beginTransaction();

            $colis = Colis::whereIn('id', $request->colis_ids)
                         ->where('status', Colis::STATUS_EN_ATTENTE)
                         ->get();

            if ($colis->isEmpty()) {
                return redirect()->back()
                               ->with('error', 'Aucun colis en attente trouvé.');
            }

            // Grouper par zone pour optimiser les tournées
            $colisByZone = $colis->groupBy('zone_id');

            foreach ($colisByZone as $zoneId => $zoneColis) {
                // Calculer l'ordre optimal pour cette zone
                $this->optimizeDeliveryOrder($zoneColis, $zoneId);

                // Assigner le livreur
                foreach ($zoneColis as $colisItem) {
                    $colisItem->update([
                        'livreur_id' => $request->livreur_id,
                        'engin_id' => $request->engin_id,
                        'status' => Colis::STATUS_EN_COURS
                    ]);
                }
            }

            DB::commit();

            Log::info('Colis multiples assignés', [
                'colis_count' => $colis->count(),
                'zones_count' => $colisByZone->count(),
                'livreur_id' => $request->livreur_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                           ->with('success', "{$colis->count()} colis assignés au livreur dans {$colisByZone->count()} zones.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation multiple: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'assignation multiple.');
        }
    }

    /**
     * Optimize delivery order for colis in a zone
     */
    private function optimizeDeliveryOrder($colis, $zoneId)
    {
        $zone = Zone::find($zoneId);
        if (!$zone) return;

        // Récupérer l'ordre des communes dans la zone
        $communeOrder = $zone->communes()->orderBy('commune_zone.ordre')->pluck('commune_id')->toArray();

        // Grouper les colis par commune
        $colisByCommune = $colis->groupBy('commune_id');

        $order = 1;

        // Assigner l'ordre selon l'ordre des communes dans la zone
        foreach ($communeOrder as $communeId) {
            if (isset($colisByCommune[$communeId])) {
                foreach ($colisByCommune[$communeId] as $colisItem) {
                    $colisItem->update(['ordre_livraison' => $order++]);
                }
            }
        }
    }

    /**
     * Get colis available for assignment (en attente)
     */
    public function getAvailableColis(Request $request)
    {
        try {
            $colis = Colis::where('status', Colis::STATUS_EN_ATTENTE)

                         ->with(['zone', 'commune'])
                         ->orderBy('created_at', 'asc')
                         ->get();

            // Grouper par zone pour faciliter l'affichage
            $colisByZone = $colis->groupBy('zone_id');

            return response()->json([
                'colis' => $colis,
                'colis_by_zone' => $colisByZone,
                'total_count' => $colis->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des colis disponibles: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des colis.'], 500);
        }
    }

    /**
     * Get livreur's current assignments across zones
     */
    public function getLivreurAssignments(Livreur $livreur)
    {
        try {
            $colis = Colis::where('livreur_id', $livreur->id)
                         ->whereIn('status', [Colis::STATUS_EN_COURS, Colis::STATUS_EN_ATTENTE])
                         ->with(['zone', 'commune'])
                         ->orderBy('zone_id')
                         ->orderBy('ordre_livraison')
                         ->get();

            // Grouper par zone
            $assignmentsByZone = $colis->groupBy('zone_id');

            // Calculer les statistiques
            $stats = [
                'total_colis' => $colis->count(),
                'zones_count' => $assignmentsByZone->count(),
                'estimated_duration' => $this->calculateEstimatedDuration($assignmentsByZone),
                'estimated_distance' => $this->calculateEstimatedDistance($assignmentsByZone)
            ];

            return response()->json([
                'assignments' => $assignmentsByZone,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des assignations: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des assignations.'], 500);
        }
    }

    /**
     * Calculate estimated duration for multiple zones
     */
    private function calculateEstimatedDuration($assignmentsByZone)
    {
        $totalDuration = 0;

        foreach ($assignmentsByZone as $zoneId => $colis) {
            $zone = Zone::find($zoneId);
            if ($zone && $zone->duree_estimee_minutes) {
                $totalDuration += $zone->duree_estimee_minutes;
            }
        }

        return $totalDuration;
    }

    /**
     * Calculate estimated distance for multiple zones
     */
    private function calculateEstimatedDistance($assignmentsByZone)
    {
        $totalDistance = 0;

        foreach ($assignmentsByZone as $zoneId => $colis) {
            $zone = Zone::find($zoneId);
            if ($zone && $zone->distance_km) {
                $totalDistance += $zone->distance_km;
            }
        }

        return $totalDistance;
    }

    /**
     * Optimize delivery routes for a livreur
     */
    public function optimizeDeliveryRoutes(Livreur $livreur)
    {
        try {
            $colis = Colis::where('livreur_id', $livreur->id)
                         ->where('status', Colis::STATUS_EN_COURS)
                         ->with(['zone', 'commune'])
                         ->get();

            // Grouper par zone
            $colisByZone = $colis->groupBy('zone_id');

            $optimizedRoutes = [];

            foreach ($colisByZone as $zoneId => $zoneColis) {
                $zone = Zone::find($zoneId);

                // Trier par ordre des communes dans la zone
                $sortedColis = $this->sortColisByZoneOrder($zoneColis, $zone);

                $optimizedRoutes[] = [
                    'zone' => $zone,
                    'colis' => $sortedColis,
                    'estimated_duration' => $zone->duree_estimee_minutes,
                    'estimated_distance' => $zone->distance_km
                ];
            }

            return response()->json([
                'livreur' => $livreur,
                'routes' => $optimizedRoutes,
                'total_colis' => $colis->count(),
                'total_zones' => $colisByZone->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation des tournées: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'optimisation des tournées.'], 500);
        }
    }

    /**
     * Sort colis by zone order
     */
    private function sortColisByZoneOrder($colis, $zone)
    {
        if (!$zone) return $colis;

        $communeOrder = $zone->communes()->orderBy('commune_zone.ordre')->pluck('commune_id')->toArray();

        return $colis->sortBy(function($colis) use ($communeOrder) {
            $index = array_search($colis->commune_id, $communeOrder);
            return $index !== false ? $index : 999;
        });
    }

    /**
     * Get colis by livreur and zone
     */
    public function getColisByLivreurAndZone(Request $request)
    {
        try {
            $livreurId = $request->get('livreur_id');
            $zoneId = $request->get('zone_id');

            $query = Colis::with(['zone', 'commune']);

            if ($livreurId) {
                $query->where('livreur_id', $livreurId);
            }

            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }

            $colis = $query->orderBy('zone_id')
                          ->orderBy('ordre_livraison')
                          ->get();

            // Grouper par zone
            $colisByZone = $colis->groupBy('zone_id');

            return response()->json([
                'colis' => $colis,
                'colis_by_zone' => $colisByZone,
                'total_count' => $colis->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des colis par livreur et zone: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des colis.'], 500);
        }
    }

    /**
     * Bulk update colis status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'colis_ids' => 'required|array|min:1',
                'colis_ids.*' => 'exists:colis,id',
                'status' => 'required|integer|in:0,1,2,3,4,5'
            ]);

            DB::beginTransaction();

            $colis = Colis::whereIn('id', $request->colis_ids)->get();

            foreach ($colis as $colisItem) {
                $colisItem->update(['status' => $request->status]);
            }

            DB::commit();

            $statusLabel = match($request->status) {
                0 => 'En attente',
                1 => 'En cours',
                2 => 'Livré',
                3 => 'Annulé par le client',
                4 => 'Annulé par le livreur',
                5 => 'Annulé par le marchand',
                default => 'Statut inconnu'
            };

            Log::info('Statut de colis multiples mis à jour', [
                'colis_count' => $colis->count(),
                'new_status' => $statusLabel,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                           ->with('success', "Statut de {$colis->count()} colis mis à jour: {$statusLabel}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour en masse: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour en masse.');
        }
    }

    /**
     * Afficher la liste des packages de colis
     */
    public function packages()
    {
        try {
            $data['menu'] = 'colis';
            $data['packages'] = PackageColis::with(['marchand', 'boutique', 'livreur', 'engin', 'createdBy', 'colis'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('colis.packages', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des packages: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage des packages.');
        }
    }

    /**
     * Afficher les détails d'un package
     */
    public function showPackage($id)
    {
        try {
            $data['menu'] = 'colis';
            $data['package'] = PackageColis::with(['marchand', 'boutique', 'livreur', 'engin', 'createdBy', 'colis.zone', 'colis.commune', 'colis.commune_zone'])
                ->findOrFail($id);

            return view('colis.package-details', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du package: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Package introuvable.');
        }
    }

    /**
     * Créer des colis pour plusieurs boutiques (Workflow par étapes)
     * Option 1 : Workflow par Étapes + Option 3 : Packages Séparés
     */
    public function storeMultiBoutiques(Request $request)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données multi-boutiques
            $request->validate([
                'marchand_id' => 'required|exists:marchands,id',
                'ramassage_id' => 'nullable|exists:ramassages,id',
                'boutiques' => 'required|array|min:1|max:10',
                'boutiques.*.boutique_id' => 'required|exists:boutiques,id',
                'boutiques.*.nombre_colis' => 'required|integer|min:1|max:20',
                'boutiques.*.livreur_id' => 'required|exists:livreurs,id',
                'boutiques.*.engin_id' => 'required|exists:engins,id',
                'boutiques.*.colis' => 'required|array|min:1',
                'boutiques.*.colis.*.nom_client' => 'required|string|max:255',
                'boutiques.*.colis.*.telephone_client' => 'required|string|max:20',
                'boutiques.*.colis.*.adresse_client' => 'required|string|max:500',
                'boutiques.*.colis.*.commune_id' => 'required|exists:communes,id',
                'boutiques.*.colis.*.montant_a_encaisse' => 'nullable|integer|min:0',
                'boutiques.*.colis.*.prix_de_vente' => 'nullable|integer|min:0',
                'boutiques.*.colis.*.numero_facture' => 'nullable|string|max:255',
                'boutiques.*.colis.*.note_client' => 'nullable|string|max:1000',
                'boutiques.*.colis.*.type_colis_id' => 'nullable|exists:type_colis,id',
                'boutiques.*.colis.*.conditionnement_colis_id' => 'nullable|exists:conditionnement_colis,id',
                'boutiques.*.colis.*.poids_id' => 'required|exists:poids,id',
                'boutiques.*.colis.*.delai_id' => 'nullable|exists:delais,id',
                'boutiques.*.colis.*.mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'boutiques.*.colis.*.temp_id' => 'required|exists:temps,id'
            ], [
                'boutiques.*.livreur_id.required' => 'Le livreur est obligatoire pour chaque boutique.',
                'boutiques.*.livreur_id.exists' => 'Le livreur sélectionné n\'existe pas.',
                'boutiques.*.engin_id.required' => 'L\'engin du livreur est obligatoire pour chaque boutique.',
                'boutiques.*.engin_id.exists' => 'L\'engin sélectionné n\'existe pas.',
                'marchand_id.required' => 'Le marchand est obligatoire.',
                'boutiques.required' => 'Au moins une boutique est obligatoire.',
                'boutiques.min' => 'Vous devez sélectionner au moins une boutique.',
                'boutiques.max' => 'Vous ne pouvez pas sélectionner plus de 10 boutiques.',
                'boutiques.*.boutique_id.required' => 'La boutique est obligatoire.',
                'boutiques.*.nombre_colis.required' => 'Le nombre de colis est obligatoire pour chaque boutique.',
                'boutiques.*.nombre_colis.min' => 'Vous devez créer au moins 1 colis par boutique.',
                'boutiques.*.nombre_colis.max' => 'Vous ne pouvez pas créer plus de 20 colis par boutique.',
                'boutiques.*.colis.required' => 'Les données des colis sont obligatoires pour chaque boutique.',
                'boutiques.*.colis.min' => 'Vous devez créer au moins 1 colis par boutique.',
                'boutiques.*.colis.*.nom_client.required' => 'Le nom du client est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.telephone_client.required' => 'Le téléphone du client est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.adresse_client.required' => 'L\'adresse du client est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.commune_id.required' => 'La zone de livraison est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.poids_id.required' => 'Le poids est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.mode_livraison_id.required' => 'Le mode de livraison est obligatoire pour chaque colis.',
                'boutiques.*.colis.*.temp_id.required' => 'La période est obligatoire pour chaque colis.'
            ]);

            DB::beginTransaction();

            $createdPackages = [];
            $totalColisCreated = 0;

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Traiter chaque boutique séparément (Option 3 : Packages Séparés)
            foreach ($request->boutiques as $boutiqueIndex => $boutiqueData) {

                // Récupérer les communes uniques utilisées dans les colis de cette boutique
                $communesUsed = collect($boutiqueData['colis'])->pluck('commune_id')->unique()->filter()->toArray();

                if (empty($communesUsed)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Veuillez sélectionner au moins une commune pour la boutique " . ($boutiqueIndex + 1))
                        ->withInput();
                }

                // 1. Créer un package séparé pour chaque boutique
                $packageColis = PackageColis::create([
                    'entreprise_id' => $entrepriseId,
                    'numero_package' => PackageColis::generatePackageNumber(),
                    'marchand_id' => $request->marchand_id,
                    'boutique_id' => $boutiqueData['boutique_id'],
                    'nombre_colis' => $boutiqueData['nombre_colis'],
                    'communes_selected' => implode(',', $communesUsed),
                    'colis_ids' => [], // Sera mis à jour après création des colis
                    'livreur_id' => $boutiqueData['livreur_id'],
                    'engin_id' => $boutiqueData['engin_id'],
                    'statut' => 'en_attente',
                    'created_by' => $user->id
                ]);

                $createdColis = [];
                $createdLivraisons = [];
                $createdZones = [];
                $colisIndex = 0;

                // 2. Traiter chaque colis de cette boutique
                foreach ($boutiqueData['colis'] as $index => $colisData) {
                    // Utiliser directement la commune sélectionnée pour ce colis
                    $communeId = $colisData['commune_id'];

                    if (!$communeId) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', "Erreur de répartition pour le colis " . ($index + 1) . " de la boutique " . ($boutiqueIndex + 1))
                            ->withInput();
                    }

                    $colisIndex++;

                    // Récupérer les informations de la commune
                    $commune = Commune::find($communeId);
                    if (!$commune) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', "Commune introuvable pour le colis " . ($index + 1) . " de la boutique " . ($boutiqueIndex + 1))
                            ->withInput();
                    }

                    // Créer ou récupérer la zone de livraison pour cette commune
                    $entrepriseId = $this->getEntrepriseId();
                    $zone = Zone::firstOrCreate(
                        ['nom' => $commune->libelle, 'entreprise_id' => $entrepriseId],
                        [
                            'nom' => $commune->libelle,
                            'entreprise_id' => $entrepriseId,
                            'actif' => true,
                            'created_by' => $user->id
                        ]
                    );

                    if (!in_array($zone->id, $createdZones)) {
                        $createdZones[] = $zone->id;
                    }

                    // Récupérer l'adresse de la boutique pour l'adresse de ramassage
                    $boutique = Boutique::find($boutiqueData['boutique_id']);
                    $adresseRamassage = $boutique ? $boutique->adresse : '';

                    // Générer le numéro de ramassage automatiquement
                    $numeroRamassage = 'RAM-' . str_pad(DB::table('commune_zone')->count() + 1, 6, '0', STR_PAD_LEFT);

                    // Créer l'entrée dans commune_zone
                    $communeZone = \DB::table('commune_zone')->insertGetId([
                        'entreprise_id' => $entrepriseId,
                        'zone_id' => $zone->id,
                        'commune_id' => null, // Nullable comme demandé
                        'ordre' => $colisIndex,
                        'nom_client' => $colisData['nom_client'],
                        'telephone_client' => $this->cleanPhoneNumber($colisData['telephone_client']),
                        'adresse_client' => $colisData['adresse_client'],
                        'marchand_id' => $request->marchand_id,
                        'boutique_id' => $boutiqueData['boutique_id'],
                        'montant_a_encaisse' => $colisData['montant_a_encaisse'] ?? null,
                        'prix_de_vente' => $colisData['prix_de_vente'] ?? null,
                        'numero_facture' => $colisData['numero_facture'] ?? null,
                        'type_colis_id' => $colisData['type_colis_id'] ?? null,
                        'conditionnement_colis_id' => $colisData['conditionnement_colis_id'] ?? null,
                        'poids_id' => $colisData['poids_id'] ?? null,
                        'delai_id' => $colisData['delai_id'] ?? null,
                        'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null,
                        'numero_de_ramassage' => $numeroRamassage,
                        'adresse_de_ramassage' => $adresseRamassage,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Créer le colis avec référence au package de cette boutique
                    $colis = Colis::create([
                        'entreprise_id' => $entrepriseId,
                        'uuid' => \Str::uuid(),
                        'code' => $this->generateColisCode($zone->id, $communeId),
                        'nom_client' => $colisData['nom_client'],
                        'telephone_client' => $this->cleanPhoneNumber($colisData['telephone_client']),
                        'adresse_client' => $colisData['adresse_client'],
                        'montant_a_encaisse' => $colisData['montant_a_encaisse'] ?? 0,
                        'prix_de_vente' => $colisData['prix_de_vente'] ?? 0,
                        'numero_facture' => $colisData['numero_facture'] ?? '',
                        'note_client' => $colisData['note_client'] ?? '',
                        'status' => 0, // En attente
                        'zone_id' => $zone->id,
                        'commune_id' => $communeId,
                        'package_colis_id' => $packageColis->id, // Référence au package de cette boutique
                        'livreur_id' => $boutiqueData['livreur_id'],
                        'engin_id' => $boutiqueData['engin_id'],
                        'poids_id' => $colisData['poids_id'] ?? null,
                        'mode_livraison_id' => $colisData['mode_livraison_id'] ?? null,
                        'temp_id' => $colisData['temp_id'] ?? null,
                        'created_by' => $user->id
                    ]);

                    $createdColis[] = $colis;
                    $totalColisCreated++;

                    // Créer automatiquement la livraison pour ce colis
                    $livraison = $colis->createLivraison();
                    $createdLivraisons[] = $livraison->id;
                }

                // 3. Mettre à jour le package avec les IDs des colis créés pour cette boutique
                $colisIds = array_map(function($colis) {
                    return $colis->id;
                }, $createdColis);

                $packageColis->update([
                    'colis_ids' => $colisIds
                ]);

                $createdPackages[] = [
                    'package' => $packageColis,
                    'colis_count' => count($createdColis),
                    'livraisons_count' => count($createdLivraisons),
                    'zones_count' => count($createdZones)
                ];
            }

            DB::commit();

            // Log de l'opération réussie
            Log::info('Packages multi-boutiques créés avec succès', [
                'marchand_id' => $request->marchand_id,
                'packages_count' => count($createdPackages),
                'total_colis' => $totalColisCreated,
                'user_id' => $user->id
            ]);

            // Si un ramassage est sélectionné, lier tous les colis créés au ramassage
            if ($request->ramassage_id) {
                $ramassage = Ramassage::find($request->ramassage_id);
                if ($ramassage) {
                    foreach ($allCreatedColis as $colis) {
                        $ramassage->colisLies()->attach($colis->id);
                    }

                    Log::info('Colis multi-boutiques liés au ramassage', [
                        'ramassage_id' => $ramassage->id,
                        'code_ramassage' => $ramassage->code_ramassage,
                        'colis_count' => count($allCreatedColis),
                        'user_id' => $user->id
                    ]);
                }
            }

            // Construire le message de succès
            $message = count($createdPackages) . ' packages créés avec succès pour ' . count($request->boutiques) . ' boutique(s) : ';
            $message .= $totalColisCreated . ' colis au total';
            if ($request->ramassage_id) {
                $message .= ' - Colis liés au ramassage';
            }

            return redirect()->route('colis.packages')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création multi-boutiques: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la création des colis multi-boutiques.')
                           ->withInput();
        }
    }

    /**
     * Mettre à jour la balance du marchand après livraison
     */
    private function updateMarchandBalance($colis)
    {
        try {
            // Vérifier que le colis a les informations nécessaires
            if (!$colis->marchand_id || !$colis->boutique_id || !$colis->entreprise_id) {
                Log::warning('Colis sans informations complètes pour la balance', [
                    'colis_id' => $colis->id,
                    'marchand_id' => $colis->marchand_id,
                    'boutique_id' => $colis->boutique_id,
                    'entreprise_id' => $colis->entreprise_id
                ]);
                return;
            }

            // Récupérer ou créer la balance du marchand
            $balance = BalanceMarchand::firstOrCreate(
                [
                    'marchand_id' => $colis->marchand_id,
                    'boutique_id' => $colis->boutique_id,
                    'entreprise_id' => $colis->entreprise_id
                ],
                [
                    'montant_encaisse' => 0,
                    'montant_reverse' => 0,
                    'balance_actuelle' => 0
                ]
            );

            // Ajouter le montant encaissé
            $montantEncaisse = $colis->montant_a_encaisse ?? 0;

            if ($montantEncaisse > 0) {
                $balance->addEncaissement($montantEncaisse, $colis->id);

                Log::info('Balance marchand mise à jour', [
                    'colis_id' => $colis->id,
                    'marchand_id' => $colis->marchand_id,
                    'boutique_id' => $colis->boutique_id,
                    'montant_encaisse' => $montantEncaisse,
                    'nouvelle_balance' => $balance->balance_actuelle
                ]);
            } else {
                Log::warning('Montant à encaisser nul ou vide', [
                    'colis_id' => $colis->id,
                    'montant_a_encaisse' => $colis->montant_a_encaisse
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la balance marchand', [
                'colis_id' => $colis->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Ne pas faire échouer la transaction pour cette erreur
            // La livraison doit pouvoir continuer même si la balance échoue
        }
    }

    /**
     * Nettoyer et formater le numéro de téléphone avec l'indicatif +225
     */
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone)) {
            return $phone;
        }

        // Supprimer tous les espaces et caractères non numériques
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Si le numéro commence déjà par 225, le retourner tel quel
        if (strpos($cleanPhone, '225') === 0) {
            return $cleanPhone;
        }

        // Si le numéro commence par 0, le remplacer par 225
        if (strpos($cleanPhone, '0') === 0) {
            return '225' . substr($cleanPhone, 1);
        }

        // Si le numéro ne commence ni par 0 ni par 225, ajouter 225
        return '225' . $cleanPhone;
    }

    /**
     * Envoyer une notification Firebase au livreur lors de la création d'un colis
     */
    private function sendColisCreatedNotification($livreur, $colis)
    {
        try {
            // Vérifier que le livreur a un token FCM
            if (!$livreur->fcm_token) {
                Log::warning("Token FCM manquant pour le livreur", [
                    'livreur_id' => $livreur->id,
                    'livreur_name' => $livreur->first_name . ' ' . $livreur->last_name
                ]);
                return false;
            }

            // Utiliser le trait SendsFirebaseNotifications
            $result = $this->sendNewColisNotification($livreur, $colis);

            if ($result['success']) {
                Log::info("Notification Firebase envoyée au livreur", [
                    'livreur_id' => $livreur->id,
                    'colis_id' => $colis->id,
                    'colis_code' => $colis->code
                ]);
                return true;
            } else {
                Log::warning("Échec envoi notification Firebase", [
                    'livreur_id' => $livreur->id,
                    'colis_id' => $colis->id,
                    'error' => $result['message'] ?? 'Erreur inconnue'
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de la notification Firebase", [
                'livreur_id' => $livreur->id,
                'colis_id' => $colis->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
