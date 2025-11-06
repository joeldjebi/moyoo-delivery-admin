<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ramassage;
use App\Models\RamassageColis;
use App\Models\PlanificationRamassage;
use App\Models\Entreprise;
use App\Models\Marchand;
use App\Models\Boutique;
use App\Models\Livreur;
use App\Models\Colis;
use App\Models\Poid;
use App\Models\Commune;
use App\Models\Type_colis;
use App\Models\Conditionnement_colis;
use App\Models\Delais;
use App\Models\Mode_livraison;
use App\Models\Temp;
use App\Traits\SendsFirebaseNotifications;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RamassageController extends Controller
{
    use SendsFirebaseNotifications;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['menu'] = 'ramassages';
        $data['title'] = 'Gestion des Ramassages';
        $data['user'] = auth()->user();
        $entrepriseId = auth()->user()->entreprise_id;

        // Si l'utilisateur n'a pas d'entreprise_id, chercher par created_by
        if (!$entrepriseId) {
            $entreprise = Entreprise::where('created_by', auth()->id())->first();
            $entrepriseId = $entreprise ? $entreprise->id : null;
        }

        $ramassages = Ramassage::with(['marchand', 'boutique', 'planifications.livreur'])
            ->byEntreprise($entrepriseId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data['ramassages'] = $ramassages;
        return view('ramassages.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entrepriseId = auth()->user()->entreprise_id;

        if (!$entrepriseId) {
            $entreprise = Entreprise::where('created_by', auth()->id())->first();
            $entrepriseId = $entreprise ? $entreprise->id : null;
        }

        $marchands = Marchand::where('entreprise_id', $entrepriseId)->get();
        $livreurs = Livreur::where('entreprise_id', $entrepriseId)->get();

        $data = [
            'title' => 'Nouveau Ramassage',
            'menu' => 'ramassages',
            'marchands' => $marchands,
            'livreurs' => $livreurs
        ];
        return view('ramassages.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'marchand_id' => 'required|exists:marchands,id',
            'boutique_id' => 'required|exists:boutiques,id',
            'date_demande' => 'required|date',
            'heure_demande' => 'required|date_format:H:i',
            'adresse_ramassage' => 'required|string|max:500',
            'contact_ramassage' => 'required|string|max:255',
            'nombre_colis_estime' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'colis_data' => 'nullable|array',
            'colis_data.*.client' => 'required|string|max:255',
            'colis_data.*.telephone_client' => 'required|string|max:20',
            'colis_data.*.adresse_client' => 'required|string|max:500',
            'colis_data.*.commune_id' => 'required|exists:communes,id',
            'colis_data.*.type_colis_id' => 'required|exists:type_colis,id',
            'colis_data.*.poids_id' => 'required|exists:poids,id',
            'colis_data.*.conditionnement_colis_id' => 'required|exists:conditionnement_colis,id',
            'colis_data.*.delai_id' => 'required|exists:delais,id',
            'colis_data.*.mode_livraison_id' => 'required|exists:mode_livraisons,id',
            'colis_data.*.temp_id' => 'required|exists:temps,id',
            'colis_data.*.valeur' => 'nullable|numeric|min:0',
            'colis_data.*.notes' => 'nullable|string|max:1000'
        ]);

        $entrepriseId = auth()->user()->entreprise_id;

        if (!$entrepriseId) {
            $entreprise = Entreprise::where('created_by', auth()->id())->first();
            $entrepriseId = $entreprise ? $entreprise->id : null;
        }

        // Générer un code unique pour le ramassage
        $codeRamassage = 'RAMS-' . strtoupper(Str::random(8));

        // Calculer le montant total des colis
        $montantTotal = 0;
        $colisData = [];

        // Traiter les données des colis
        if ($request->has('colis_data') && is_array($request->colis_data)) {
            foreach ($request->colis_data as $colisInfo) {
                $colisData[] = [
                    'client' => $colisInfo['client'],
                    'telephone_client' => $colisInfo['telephone_client'],
                    'adresse_client' => $colisInfo['adresse_client'],
                    'commune_id' => $colisInfo['commune_id'],
                    'type_colis_id' => $colisInfo['type_colis_id'],
                    'poids_id' => $colisInfo['poids_id'],
                    'conditionnement_colis_id' => $colisInfo['conditionnement_colis_id'],
                    'delai_id' => $colisInfo['delai_id'],
                    'mode_livraison_id' => $colisInfo['mode_livraison_id'],
                    'temp_id' => $colisInfo['temp_id'],
                    'valeur' => $colisInfo['valeur'] ?? 0,
                    'notes' => $colisInfo['notes'] ?? null
                ];
                $montantTotal += $colisInfo['valeur'] ?? 0;
            }
        }

        // Combiner la date et l'heure pour créer un datetime
        $dateTimeDemande = $request->date_demande . ' ' . $request->heure_demande;

        $ramassage = Ramassage::create([
            'code_ramassage' => $codeRamassage,
            'entreprise_id' => $entrepriseId,
            'marchand_id' => $request->marchand_id,
            'boutique_id' => $request->boutique_id,
            'date_demande' => $dateTimeDemande,
            'statut' => 'demande',
            'adresse_ramassage' => $request->adresse_ramassage,
            'contact_ramassage' => $request->contact_ramassage,
            'telephone_contact' => $request->telephone_contact ?? $request->contact_ramassage ?? null,
            'nombre_colis_estime' => $request->nombre_colis_estime,
            'nombre_colis_reel' => count($colisData),
            'notes' => $request->notes,
            'colis_data' => $colisData, // Laravel convertira automatiquement en JSON grâce au cast 'array'
            'montant_total' => $montantTotal
        ]);

        return redirect()->route('ramassages.show', $ramassage->id)
            ->with('success', 'Demande de ramassage créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ramassage = Ramassage::with([
            'marchand',
            'boutique',
            'planifications.livreur',
            'colisLies.zone',
            'colisLies.commune'
        ])->findOrFail($id);

        // Récupérer l'adresse GPS de la boutique
        $adresseGpsBoutique = $ramassage->boutique->adresse_gps ?? '';

        // Récupérer les livreurs pour la planification
        $entrepriseId = auth()->user()->entreprise_id;
        if (!$entrepriseId) {
            $entreprise = Entreprise::where('created_by', auth()->id())->first();
            $entrepriseId = $entreprise ? $entreprise->id : null;
        }
        $livreurs = Livreur::where('entreprise_id', $entrepriseId)->get();

        // Précharger les données des colis pour éviter les requêtes dans la vue
        $colisDataArray = is_string($ramassage->colis_data) ? json_decode($ramassage->colis_data, true) : $ramassage->colis_data;
        $communes = collect();
        $types = collect();
        $poids = collect();
        $conditionnements = collect();
        $delais = collect();
        $modesLivraison = collect();
        $periodes = collect();

        if ($colisDataArray && is_array($colisDataArray)) {
            // Extraire tous les IDs uniques
            $communeIds = collect($colisDataArray)->pluck('commune_id')->filter()->unique()->values()->toArray();
            $typeIds = collect($colisDataArray)->pluck('type_colis_id')->filter()->unique()->values()->toArray();
            $poidsIds = collect($colisDataArray)->pluck('poids_id')->filter()->unique()->values()->toArray();
            $conditionnementIds = collect($colisDataArray)->pluck('conditionnement_colis_id')->filter()->unique()->values()->toArray();
            $delaiIds = collect($colisDataArray)->pluck('delai_id')->filter()->unique()->values()->toArray();
            $modeIds = collect($colisDataArray)->pluck('mode_livraison_id')->filter()->unique()->values()->toArray();
            $periodeIds = collect($colisDataArray)->pluck('temp_id')->filter()->unique()->values()->toArray();

            // Récupérer toutes les données en une seule fois
            $communes = Commune::whereIn('id', $communeIds)->get()->keyBy('id');
            $types = Type_colis::whereIn('id', $typeIds)->get()->keyBy('id');
            $poids = Poid::whereIn('id', $poidsIds)->get()->keyBy('id');
            $conditionnements = Conditionnement_colis::whereIn('id', $conditionnementIds)->get()->keyBy('id');
            $delais = Delais::whereIn('id', $delaiIds)->get()->keyBy('id');
            $modesLivraison = Mode_livraison::whereIn('id', $modeIds)->get()->keyBy('id');
            $periodes = Temp::whereIn('id', $periodeIds)->get()->keyBy('id');
        }

        // Parser les notes du livreur pour extraire les photos et les notes
        $photosColis = [];
        $notesLivreurText = '';

        if ($ramassage->notes_livreur) {
            $notesLines = explode("\n", $ramassage->notes_livreur);
            $inPhotosSection = false;
            $inNotesSection = false;
            $photoDate = null;

            foreach ($notesLines as $originalLine) {
                $line = trim($originalLine);

                // Détecter le début de la section photos
                if (strpos($line, '📸 PHOTOS DES COLIS RAMASSÉS') !== false) {
                    $inPhotosSection = true;
                    $inNotesSection = false;
                    continue;
                }

                // Détecter la date dans la section photos
                if ($inPhotosSection && strpos($line, 'Date:') !== false) {
                    $photoDate = trim(str_replace('Date:', '', $line));
                    continue;
                }

                // Détecter le début de la section notes du livreur
                if (strpos($line, 'Notes livreur:') !== false) {
                    $inPhotosSection = false;
                    $inNotesSection = true;
                    $noteText = trim(str_replace('Notes livreur:', '', $line));
                    if (!empty($noteText)) {
                        $notesLivreurText = $noteText;
                    }
                    continue;
                }

                // Si on est dans la section photos, détecter les fichiers
                if ($inPhotosSection) {
                    if (strpos($line, '-') === 0 || strpos($line, 'colis_') === 0) {
                        // C'est un nom de fichier photo
                        $filename = trim(str_replace('-', '', $line));
                        // Vérifier si le fichier existe
                        $filePath = storage_path('app/public/ramassages/photos/' . $filename);
                        $photosColis[] = [
                            'filename' => $filename,
                            'date' => $photoDate,
                            'url' => asset('storage/ramassages/photos/' . $filename),
                            'exists' => file_exists($filePath)
                        ];
                    }
                    continue;
                }

                // Si on est dans la section notes, ajouter les lignes
                if ($inNotesSection) {
                    if (!empty($line)) {
                        $notesLivreurText .= ($notesLivreurText ? "\n" : '') . $line;
                    }
                    continue;
                }

                // Si on n'est ni dans photos ni dans notes, ce sont des notes générales
                if (!$inPhotosSection && !$inNotesSection && !empty($line)) {
                    $notesLivreurText .= ($notesLivreurText ? "\n" : '') . $line;
                }
            }

            // Si on n'a pas trouvé de notes séparées mais qu'on a des photos, nettoyer
            if (empty($notesLivreurText) && count($photosColis) > 0) {
                // Les notes sont probablement dans la section photos
                $notesLivreurText = '';
            }

            // Si aucune section n'a été détectée, utiliser tout le texte comme notes
            if (empty($photosColis) && empty($notesLivreurText)) {
                $notesLivreurText = $ramassage->notes_livreur;
            }
        }

        $data = [
            'title' => 'Détails du Ramassage',
            'menu' => 'ramassages',
            'ramassage' => $ramassage,
            'livreurs' => $livreurs,
            'colisDataArray' => $colisDataArray,
            'communes' => $communes,
            'communesPlanification' => $communes, // Communes pour les planifications (basées sur les colis)
            'types' => $types,
            'poids' => $poids,
            'conditionnements' => $conditionnements,
            'delais' => $delais,
            'modesLivraison' => $modesLivraison,
            'periodes' => $periodes,
            'adresseGpsBoutique' => $adresseGpsBoutique,
            'photosColis' => $photosColis,
            'notesLivreurText' => trim($notesLivreurText)
        ];
        return view('ramassages.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ramassage = Ramassage::findOrFail($id);

        $entrepriseId = auth()->user()->entreprise_id;

        if (!$entrepriseId) {
            $entreprise = Entreprise::where('created_by', auth()->id())->first();
            $entrepriseId = $entreprise ? $entreprise->id : null;
        }

        $marchands = Marchand::where('entreprise_id', $entrepriseId)->get();
        $livreurs = Livreur::where('entreprise_id', $entrepriseId)->get();

        // Précharger les données des colis existants
        $colisDataArray = is_string($ramassage->colis_data) ? json_decode($ramassage->colis_data, true) : $ramassage->colis_data;

        $data = [
            'title' => 'Modifier le Ramassage',
            'menu' => 'ramassages',
            'ramassage' => $ramassage,
            'marchands' => $marchands,
            'livreurs' => $livreurs,
            'colisDataArray' => $colisDataArray
        ];
        return view('ramassages.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $ramassage = Ramassage::findOrFail($id);

        $request->validate([
            'marchand_id' => 'required|exists:marchands,id',
            'boutique_id' => 'required|exists:boutiques,id',
            'date_demande' => 'required|date',
            'heure_demande' => 'required|date_format:H:i',
            'date_planifiee' => 'nullable|date',
            'heure_planifiee' => 'nullable|date_format:H:i',
            'statut' => 'required|in:demande,planifie,en_cours,termine,annule',
            'adresse_ramassage' => 'required|string|max:500',
            'contact_ramassage' => 'required|string|max:255',
            'nombre_colis_estime' => 'required|integer|min:1',
            'nombre_colis_reel' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'montant_total' => 'nullable|numeric|min:0',
            'colis_data' => 'nullable|array',
            'colis_data.*.client' => 'required|string|max:255',
            'colis_data.*.telephone_client' => 'required|string|max:20',
            'colis_data.*.adresse_client' => 'required|string|max:500',
            'colis_data.*.commune_id' => 'required|exists:communes,id',
            'colis_data.*.type_colis_id' => 'required|exists:type_colis,id',
            'colis_data.*.poids_id' => 'required|exists:poids,id',
            'colis_data.*.conditionnement_colis_id' => 'required|exists:conditionnement_colis,id',
            'colis_data.*.delai_id' => 'required|exists:delais,id',
            'colis_data.*.mode_livraison_id' => 'required|exists:mode_livraisons,id',
            'colis_data.*.temp_id' => 'required|exists:temps,id',
            'colis_data.*.valeur' => 'nullable|numeric|min:0',
            'colis_data.*.notes' => 'nullable|string|max:1000'
        ]);

        // Calculer le montant total des colis
        $montantTotal = 0;
        $colisData = [];

        // Traiter les données des colis
        if ($request->has('colis_data') && is_array($request->colis_data)) {
            foreach ($request->colis_data as $colisInfo) {
                $colisData[] = [
                    'client' => $colisInfo['client'],
                    'telephone_client' => $colisInfo['telephone_client'],
                    'adresse_client' => $colisInfo['adresse_client'],
                    'commune_id' => $colisInfo['commune_id'],
                    'type_colis_id' => $colisInfo['type_colis_id'],
                    'poids_id' => $colisInfo['poids_id'],
                    'conditionnement_colis_id' => $colisInfo['conditionnement_colis_id'],
                    'delai_id' => $colisInfo['delai_id'],
                    'mode_livraison_id' => $colisInfo['mode_livraison_id'],
                    'temp_id' => $colisInfo['temp_id'],
                    'valeur' => $colisInfo['valeur'] ?? 0,
                    'notes' => $colisInfo['notes'] ?? null
                ];
                $montantTotal += $colisInfo['valeur'] ?? 0;
            }
        }

        // Combiner les dates et heures pour créer des datetime
        $dateTimeDemande = $request->date_demande . ' ' . $request->heure_demande;
        $dateTimePlanifiee = null;
        if ($request->date_planifiee && $request->heure_planifiee) {
            $dateTimePlanifiee = $request->date_planifiee . ' ' . $request->heure_planifiee;
        }

        $ramassage->update([
            'marchand_id' => $request->marchand_id,
            'boutique_id' => $request->boutique_id,
            'date_demande' => $dateTimeDemande,
            'date_planifiee' => $dateTimePlanifiee,
            'statut' => $request->statut,
            'adresse_ramassage' => $request->adresse_ramassage,
            'contact_ramassage' => $request->contact_ramassage,
            'telephone_contact' => $request->telephone_contact ?? $request->contact_ramassage ?? null,
            'nombre_colis_estime' => $request->nombre_colis_estime,
            'nombre_colis_reel' => count($colisData),
            'notes' => $request->notes,
            'colis_data' => $colisData, // Laravel convertira automatiquement en JSON grâce au cast 'array'
            'montant_total' => $montantTotal
        ]);

        return redirect()->route('ramassages.show', $ramassage->id)
            ->with('success', 'Ramassage mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ramassage = Ramassage::findOrFail($id);
        $ramassage->delete();

        return redirect()->route('ramassages.index')
            ->with('success', 'Ramassage supprimé avec succès.');
    }

    /**
     * Planifier un ramassage
     */
    public function planifier(Request $request, $id)
    {
        $ramassage = Ramassage::findOrFail($id);

        $request->validate([
            'livreur_id' => 'required|exists:livreurs,id',
            'date_planifiee' => 'required|date|after_or_equal:today',
            'heure_planifiee' => 'required|date_format:H:i',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'adresse_ramassage' => 'required|string|max:500',
            'notes_planification' => 'nullable|string|max:1000'
        ]);

        // Combiner la date et l'heure pour créer un datetime
        $dateTimePlanifiee = $request->date_planifiee . ' ' . $request->heure_planifiee;

        // Créer la planification
        PlanificationRamassage::create([
            'ramassage_id' => $ramassage->id,
            'livreur_id' => $request->livreur_id,
            'entreprise_id' => $ramassage->entreprise_id,
            'date_planifiee' => $dateTimePlanifiee,
            'heure_debut' => $request->heure_debut,
            'heure_fin' => $request->heure_fin,
            'zone_ramassage' => $request->adresse_ramassage,
            'statut_planification' => 'planifie',
            'notes_planification' => $request->notes_planification
        ]);

        // Mettre à jour le statut du ramassage
        $ramassage->update([
            'statut' => 'planifie',
            'date_planifiee' => $dateTimePlanifiee
        ]);

        // Envoyer une notification au livreur
        $livreur = Livreur::find($request->livreur_id);
        if ($livreur) {
            // Notification Firebase
            if ($livreur->fcm_token) {
                $notificationResult = $this->sendNewRamassageNotification($livreur, $ramassage);

                // Log du résultat de la notification Firebase
                if ($notificationResult['success']) {
                    \Log::info('Notification Firebase ramassage envoyée avec succès', [
                        'livreur_id' => $livreur->id,
                        'ramassage_id' => $ramassage->id
                    ]);
                } else {
                    \Log::warning('Échec envoi notification Firebase ramassage', [
                        'livreur_id' => $livreur->id,
                        'ramassage_id' => $ramassage->id,
                        'error' => $notificationResult['message']
                    ]);
                }
            } else {
                \Log::warning('FCM token manquant pour le livreur', [
                    'livreur_id' => $livreur->id,
                    'ramassage_id' => $ramassage->id
                ]);
            }

            // Notification WhatsApp
            $this->sendRamassageWhatsAppNotification($livreur, $ramassage, $request);
        }

        return redirect()->route('ramassages.show', $ramassage->id)
            ->with('success', 'Ramassage planifié avec succès.');
    }

    /**
     * Ajouter des colis à un ramassage
     */
    public function ajouterColis(Request $request, $id)
    {
        $ramassage = Ramassage::findOrFail($id);

        $request->validate([
            'colis_ids' => 'required|array',
            'colis_ids.*' => 'exists:colis,id'
        ]);

        foreach ($request->colis_ids as $colisId) {
            RamassageColis::create([
                'ramassage_id' => $ramassage->id,
                'colis_id' => $colisId,
                'statut_colis' => 'attendu'
            ]);
        }

        return redirect()->route('ramassages.show', $ramassage->id)
            ->with('success', 'Colis ajoutés au ramassage avec succès.');
    }

    /**
     * Mettre à jour le statut d'un colis dans un ramassage
     */
    public function updateStatutColis(Request $request, $id, $colisId)
    {
        $request->validate([
            'statut_colis' => 'required|in:en_attente,recupere,refuse,livre',
            'notes_colis' => 'nullable|string|max:500',
            'photo_ramassage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        $ramassageColis = RamassageColis::where('ramassage_id', $id)
            ->where('colis_id', $colisId)
            ->firstOrFail();

        $updateData = [
            'statut_colis' => $request->statut_colis,
            'notes_colis' => $request->notes_colis,
            'date_ramassage' => in_array($request->statut_colis, ['recupere', 'refuse']) ? now() : $ramassageColis->date_ramassage
        ];

        // Gestion de la photo
        if ($request->hasFile('photo_ramassage')) {
            $photo = $request->file('photo_ramassage');
            $photoName = 'ramassage_' . $id . '_colis_' . $colisId . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('ramassages/photos', $photoName, 'public');
            $updateData['photo_ramassage'] = $photoPath;
        }

        $ramassageColis->update($updateData);

        return redirect()->route('ramassages.show', $id)
            ->with('success', 'Statut du colis mis à jour avec succès.');
    }

    /**
     * API pour récupérer les colis disponibles pour un ramassage
     */
    public function getColisDisponibles(Request $request)
    {
        $marchandId = $request->get('marchand_id');
        $boutiqueId = $request->get('boutique_id');

        $colis = Colis::where('marchand_id', $marchandId)
            ->where('boutique_id', $boutiqueId)
            ->where('status', 0) // Colis en attente de ramassage
            ->whereDoesntHave('ramassages') // Pas déjà dans un ramassage
            ->get();

        return response()->json($colis);
    }

    /**
     * Récupérer les boutiques d'un marchand
     */
    public function getBoutiquesByMarchand($marchandId)
    {
        try {
            $boutiques = Boutique::where('marchand_id', $marchandId)
                ->select('id', 'libelle', 'mobile', 'adresse', 'adresse_gps')
                ->get();

            return response()->json(['boutiques' => $boutiques]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des boutiques'], 500);
        }
    }

    /**
     * Récupérer les communes
     */
    public function getCommunes()
    {
        try {
            $communes = Commune::select('id', 'libelle')->get();
            return response()->json(['communes' => $communes]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des communes'], 500);
        }
    }

    /**
     * Récupérer les types de colis
     */
    public function getTypesColis()
    {
        try {
            $types = Type_colis::select('id', 'libelle')->get();
            return response()->json(['types' => $types]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des types de colis'], 500);
        }
    }

    /**
     * Récupérer les poids
     */
    public function getPoids()
    {
        try {
            $poids = Poid::select('id', 'libelle')->get();
            return response()->json(['poids' => $poids]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des poids'], 500);
        }
    }

    /**
     * Récupérer les conditionnements
     */
    public function getConditionnements()
    {
        try {
            $conditionnements = Conditionnement_colis::select('id', 'libelle')->get();
            return response()->json(['conditionnements' => $conditionnements]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des conditionnements'], 500);
        }
    }

    /**
     * Récupérer les délais
     */
    public function getDelais()
    {
        try {
            $delais = Delais::select('id', 'libelle')->get();
            return response()->json(['delais' => $delais]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des délais'], 500);
        }
    }

    /**
     * Récupérer les modes de livraison
     */
    public function getModesLivraison()
    {
        try {
            $modes = Mode_livraison::select('id', 'libelle')->get();
            return response()->json(['modes' => $modes]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des modes de livraison'], 500);
        }
    }

    /**
     * Récupérer les périodes
     */
    public function getPeriodes()
    {
        try {
            $periodes = Temp::select('id', 'libelle')->get();
            return response()->json(['periodes' => $periodes]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des périodes'], 500);
        }
    }

    /**
     * Récupérer les données des colis d'un ramassage
     */
    public function getColisData($id)
    {
        try {
            $ramassage = Ramassage::findOrFail($id);

            // Décoder les données des colis (gérer les chaînes JSON échappées)
            $colisData = $ramassage->colis_data;
            if (is_string($colisData)) {
                // Décoder la chaîne JSON échappée
                $colisData = json_decode(stripslashes($colisData), true);
            }

            if (!$colisData || !is_array($colisData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée de colis trouvée pour ce ramassage'
                ]);
            }

            // Normaliser les données pour correspondre au format attendu par le formulaire
            // Le ramassage stocke 'client' mais le formulaire attend 'nom_client'
            $normalizedColisData = array_map(function($colis) {
                // Si 'client' existe mais pas 'nom_client', utiliser 'client' comme 'nom_client'
                if (isset($colis['client']) && !isset($colis['nom_client'])) {
                    $colis['nom_client'] = $colis['client'];
                }
                // Si 'nom_client' existe mais pas 'client', utiliser 'nom_client' comme 'client'
                if (isset($colis['nom_client']) && !isset($colis['client'])) {
                    $colis['client'] = $colis['nom_client'];
                }
                return $colis;
            }, $colisData);

            return response()->json([
                'success' => true,
                'colisData' => $normalizedColisData,
                'ramassage' => [
                    'id' => $ramassage->id,
                    'code_ramassage' => $ramassage->code_ramassage,
                    'marchand_id' => $ramassage->marchand_id,
                    'marchand' => $ramassage->marchand ? $ramassage->marchand->first_name . ' ' . $ramassage->marchand->last_name : 'N/A',
                    'boutique_id' => $ramassage->boutique_id,
                    'boutique' => $ramassage->boutique ? $ramassage->boutique->libelle : 'N/A'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des données du ramassage'
            ], 500);
        }
    }

    /**
     * Mettre à jour une planification de ramassage
     */
    public function updatePlanification(Request $request, $planificationId)
    {
        try {
            $request->validate([
                'livreur_id' => 'required|exists:livreurs,id',
                'date_planifiee' => 'required|date',
                'heure_planifiee' => 'required|date_format:H:i',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i|after:heure_debut',
                'zone_ramassage' => 'required|string|max:255',
                'statut_planification' => 'required|in:planifie,en_cours,termine,annule',
                'notes_planification' => 'nullable|string'
            ]);

            $planification = PlanificationRamassage::findOrFail($planificationId);

            // Combiner la date et l'heure pour créer un datetime
            $dateTimePlanifiee = $request->date_planifiee . ' ' . $request->heure_planifiee;

            $planification->update([
                'livreur_id' => $request->livreur_id,
                'date_planifiee' => $dateTimePlanifiee,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'zone_ramassage' => $request->zone_ramassage,
                'statut_planification' => $request->statut_planification,
                'notes_planification' => $request->notes_planification
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Planification mise à jour avec succès',
                'planification' => $planification
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la planification'
            ], 500);
        }
    }

    /**
     * Envoyer une notification WhatsApp pour un nouveau ramassage
     */
    private function sendRamassageWhatsAppNotification($livreur, $ramassage, $request)
    {
        try {
            $entrepriseName = auth()->user()->entreprise ? auth()->user()->entreprise->name : 'MOYOO';

            $message = "🚚 MOYOO - Nouveau Ramassage Assigné\n\n";
            $message .= "Bonjour {$livreur->first_name},\n\n";
            $message .= "Un nouveau ramassage vous a été assigné :\n\n";
            $message .= "📦 Code Ramassage : {$ramassage->code_ramassage}\n";
            $message .= "🏪 Boutique : {$ramassage->boutique->libelle}\n";
            $message .= "👤 Marchand : {$ramassage->marchand->first_name} {$ramassage->marchand->last_name}\n";
            $message .= "📅 Date : " . \Carbon\Carbon::parse($request->date_planifiee)->format('d/m/Y') . "\n";
            $message .= "🕐 Heure : {$request->heure_planifiee}\n";
            $message .= "📍 Adresse : {$request->adresse_ramassage}\n";
            $message .= "📦 Colis estimés : {$ramassage->nombre_colis_estime}\n\n";

            if (!empty($request->notes_planification)) {
                $message .= "📝 Notes : {$request->notes_planification}\n\n";
            }

            $message .= "⚠️ IMPORTANT :\n";
            $message .= "• Arrivez à l'heure prévue\n";
            $message .= "• Vérifiez l'adresse avant de partir\n";
            $message .= "• Contactez le marchand si nécessaire\n\n";
            $message .= "Bonne journée de ramassage !\n\n";
            $message .= "Cordialement,\nL'équipe {$entrepriseName}";

            $result = $this->sendWhatsAppMessageInternal($livreur->mobile, $message);

            if ($result['success']) {
                \Log::info('Notification WhatsApp ramassage envoyée avec succès', [
                    'livreur_id' => $livreur->id,
                    'ramassage_id' => $ramassage->id,
                    'mobile' => $livreur->mobile
                ]);
            } else {
                \Log::warning('Échec envoi notification WhatsApp ramassage', [
                    'livreur_id' => $livreur->id,
                    'ramassage_id' => $ramassage->id,
                    'mobile' => $livreur->mobile,
                    'error' => $result['error'] ?? 'Erreur inconnue'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de la notification WhatsApp ramassage', [
                'livreur_id' => $livreur->id,
                'ramassage_id' => $ramassage->id,
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
        $apiUrl = env('WASSENGER_API_URL', 'https://api.wassenger.com/v1/messages');
        $token = env('WASSENGER_TOKEN', '11aa75a1de8f22a6c05e5b49eeb309b48329258699f05e419624bff1d0fcc9940058293b92a6fc95');

        $data = [
            'phone' => $phone,
            'message' => $message
        ];

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

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

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
}
