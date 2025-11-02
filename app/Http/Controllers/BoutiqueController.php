<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Colis;
use App\Models\Marchand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BoutiqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Récupérer les boutiques des marchands de l'entreprise de l'utilisateur connecté
        $boutiques = Boutique::with(['marchand', 'user'])
            ->whereHas('marchand', function($query) use ($user) {
                $query->where('entreprise_id', $user->entreprise_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = [
            'title' => 'Gestion des Boutiques',
            'menu' => 'boutiques',
            'user' => $user,
            'boutiques' => $boutiques
        ];

        return view('boutique.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Récupérer les marchands actifs de l'utilisateur connecté
        $marchands = Marchand::where('entreprise_id', $user->entreprise_id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $data = [
            'title' => 'Ajouter une Boutique',
            'menu' => 'boutiques',
            'user' => $user,
            'marchands' => $marchands
        ];

        return view('boutique.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            Log::warning('Tentative de création de boutique sans authentification', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        Log::info('Début de création de boutique', [
            'user_id' => $user->id,
            'entreprise_id' => $user->entreprise_id,
            'ip' => $request->ip(),
            'request_data' => $request->except(['_token'])
        ]);

        try {
            // Validation des données
            Log::info('Début de validation des données', [
                'user_id' => $user->id,
                'raw_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'libelle' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'adresse' => 'nullable|string|max:500',
                'adresse_gps' => 'nullable|url|max:500',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'marchand_id' => 'required|exists:marchands,id',
                'status' => 'required|in:active,inactive'
            ], [
                'libelle.required' => 'Le libellé de la boutique est requis.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, chiffres, espaces, tirets et points.',
                'mobile.required' => 'Le numéro de téléphone de la boutique est requis.',
                'mobile.regex' => 'Veuillez entrer un numéro de téléphone valide (ex: +225 07 12 34 56 78).',
                'adresse_gps.url' => 'Veuillez entrer un lien Google Maps valide.',
                'cover_image.image' => 'Le fichier doit être une image.',
                'cover_image.mimes' => 'L\'image doit être au format JPG, PNG ou GIF.',
                'cover_image.max' => 'L\'image ne doit pas dépasser 2MB.',
                'marchand_id.required' => 'Veuillez sélectionner un marchand.',
                'marchand_id.exists' => 'Le marchand sélectionné n\'existe pas.',
                'status.required' => 'Veuillez sélectionner un statut.',
                'status.in' => 'Le statut doit être actif ou inactif.'
            ]);

            // Vérifier que le marchand appartient à l'utilisateur connecté
            $marchand = Marchand::where('id', $validatedData['marchand_id'])
                ->where('entreprise_id', $user->entreprise_id)
                ->first();

            if (!$marchand) {
                Log::warning('Marchand non trouvé ou non autorisé', [
                    'user_id' => $user->id,
                    'marchand_id' => $validatedData['marchand_id'],
                    'entreprise_id' => $user->entreprise_id
                ]);
                throw ValidationException::withMessages([
                    'marchand_id' => ['Vous ne pouvez pas créer une boutique pour ce marchand.']
                ]);
            }

            Log::info('Marchand trouvé et autorisé', [
                'user_id' => $user->id,
                'marchand_id' => $marchand->id,
                'marchand_name' => $marchand->first_name . ' ' . $marchand->last_name,
                'entreprise_id' => $marchand->entreprise_id
            ]);

            // Vérifier qu'il n'y a pas déjà une boutique avec le même nom pour ce marchand
            Log::info('Vérification des doublons de boutique', [
                'user_id' => $user->id,
                'marchand_id' => $validatedData['marchand_id'],
                'libelle' => $validatedData['libelle'],
                'entreprise_id' => $user->entreprise_id
            ]);

            $existingBoutique = Boutique::where('marchand_id', $validatedData['marchand_id'])
                ->where('libelle', $validatedData['libelle'])
                ->where('entreprise_id', $user->entreprise_id)
                ->first();

            if ($existingBoutique) {
                Log::warning('Boutique avec le même nom existe déjà', [
                    'user_id' => $user->id,
                    'existing_boutique_id' => $existingBoutique->id,
                    'marchand_id' => $validatedData['marchand_id'],
                    'libelle' => $validatedData['libelle']
                ]);
                throw ValidationException::withMessages([
                    'libelle' => ['Une boutique avec ce nom existe déjà pour ce marchand.']
                ]);
            }

            Log::info('Aucun doublon trouvé, procédure de création', [
                'user_id' => $user->id,
                'marchand_id' => $validatedData['marchand_id'],
                'libelle' => $validatedData['libelle']
            ]);

            DB::beginTransaction();
            Log::info('Transaction DB démarrée', ['user_id' => $user->id]);

            // Gérer l'upload de l'image
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                Log::info('Upload d\'image détecté', [
                    'user_id' => $user->id,
                    'file_name' => $request->file('cover_image')->getClientOriginalName(),
                    'file_size' => $request->file('cover_image')->getSize()
                ]);
                $coverImage = $request->file('cover_image');
                $coverImagePath = $coverImage->store('boutiques', 'public');
                Log::info('Image uploadée avec succès', [
                    'user_id' => $user->id,
                    'image_path' => $coverImagePath
                ]);
            } else {
                Log::info('Aucune image à uploader', ['user_id' => $user->id]);
            }

            // Créer la boutique
            Log::info('Création de la boutique en cours', [
                'user_id' => $user->id,
                'boutique_data' => [
                    'libelle' => $validatedData['libelle'],
                    'marchand_id' => $validatedData['marchand_id'],
                    'status' => $validatedData['status'],
                    'entreprise_id' => $user->entreprise_id,
                    'created_by' => $user->id
                ]
            ]);

            $boutique = Boutique::create([
                'libelle' => $validatedData['libelle'],
                'mobile' => $validatedData['mobile'],
                'adresse' => $validatedData['adresse'] ?? null,
                'adresse_gps' => $validatedData['adresse_gps'] ?? null,
                'cover_image' => $coverImagePath,
                'marchand_id' => $validatedData['marchand_id'],
                'status' => $validatedData['status'],
                'entreprise_id' => $user->entreprise_id,
                'created_by' => $user->id
            ]);

            Log::info('Boutique créée avec succès', [
                'user_id' => $user->id,
                'boutique_id' => $boutique->id,
                'boutique_libelle' => $boutique->libelle
            ]);

            DB::commit();
            Log::info('Transaction DB commitée avec succès', ['user_id' => $user->id]);

            // Log de l'action
            Log::info('Boutique créée avec succès', [
                'boutique_id' => $boutique->id,
                'libelle' => $boutique->libelle,
                'marchand_id' => $boutique->marchand_id,
                'entreprise_id' => $user->entreprise_id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('boutiques.index')
                ->with('success', 'Boutique créée avec succès !');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Erreur de validation lors de la création de boutique', [
                'user_id' => $user->id,
                'validation_errors' => $e->errors(),
                'input_data' => $request->except(['_token']),
                'ip' => $request->ip()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la boutique', [
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'input_data' => $request->except(['_token']),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création de la boutique.', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $boutique->load(['marchand', 'user']);

        // Récupérer les colis de cette boutique via la table livraisons
        $colis = Colis::join('livraisons', 'colis.id', '=', 'livraisons.colis_id')
                     ->where('livraisons.boutique_id', $boutique->id)
                     ->where('colis.entreprise_id', $user->entreprise_id)
                     ->select('colis.*')
                     ->orderBy('colis.created_at', 'desc')
                     ->get();

        // Statistiques des colis par statut
        $colisStats = [
            'total' => $colis->count(),
            'en_attente' => $colis->where('status', Colis::STATUS_EN_ATTENTE)->count(),
            'en_cours' => $colis->where('status', Colis::STATUS_EN_COURS)->count(),
            'livres' => $colis->where('status', Colis::STATUS_LIVRE)->count(),
            'annules' => $colis->whereIn('status', [
                Colis::STATUS_ANNULE_CLIENT,
                Colis::STATUS_ANNULE_LIVREUR,
                Colis::STATUS_ANNULE_MARCHAND
            ])->count()
        ];

        $data = [
            'title' => 'Détails de la Boutique',
            'menu' => 'boutiques',
            'user' => $user,
            'boutique' => $boutique,
            'colis' => $colis,
            'colisStats' => $colisStats
        ];

        return view('boutique.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que la boutique appartient à un marchand de l'utilisateur connecté
        if ($boutique->marchand->entreprise_id !== $user->entreprise_id) {
            return redirect()->route('boutiques.index')
                ->with('error', 'Vous n\'avez pas accès à cette boutique.');
        }

        // Récupérer les marchands actifs de l'utilisateur connecté
        $marchands = Marchand::where('entreprise_id', $user->entreprise_id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $data = [
            'title' => 'Modifier la Boutique',
            'menu' => 'boutiques',
            'user' => $user,
            'boutique' => $boutique,
            'marchands' => $marchands
        ];

        return view('boutique.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que la boutique appartient à un marchand de l'utilisateur connecté
        if ($boutique->marchand->entreprise_id !== $user->entreprise_id) {
            return redirect()->route('boutiques.index')
                ->with('error', 'Vous n\'avez pas accès à cette boutique.');
        }

        try {
            // Validation des données
            $validatedData = $request->validate([
                'libelle' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'adresse' => 'nullable|string|max:500',
                'adresse_gps' => 'nullable|url|max:500',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'marchand_id' => 'required|exists:marchands,id',
                'status' => 'required|in:active,inactive'
            ], [
                'libelle.required' => 'Le libellé de la boutique est requis.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, chiffres, espaces, tirets et points.',
                'mobile.required' => 'Le numéro de téléphone de la boutique est requis.',
                'mobile.regex' => 'Veuillez entrer un numéro de téléphone valide (ex: +225 07 12 34 56 78).',
                'adresse_gps.url' => 'Veuillez entrer un lien Google Maps valide.',
                'cover_image.image' => 'Le fichier doit être une image.',
                'cover_image.mimes' => 'L\'image doit être au format JPG, PNG ou GIF.',
                'cover_image.max' => 'L\'image ne doit pas dépasser 2MB.',
                'marchand_id.required' => 'Veuillez sélectionner un marchand.',
                'marchand_id.exists' => 'Le marchand sélectionné n\'existe pas.',
                'status.required' => 'Veuillez sélectionner un statut.',
                'status.in' => 'Le statut doit être actif ou inactif.'
            ]);

            // Vérifier que le marchand appartient à l'utilisateur connecté
            $marchand = Marchand::where('id', $validatedData['marchand_id'])
                ->where('entreprise_id', $user->entreprise_id)
                ->first();

            if (!$marchand) {
                throw ValidationException::withMessages([
                    'marchand_id' => ['Vous ne pouvez pas assigner cette boutique à ce marchand.']
                ]);
            }

            DB::beginTransaction();

            // Gérer l'upload de l'image
            $coverImagePath = $boutique->cover_image; // Garder l'ancienne image par défaut
            if ($request->hasFile('cover_image')) {
                // Supprimer l'ancienne image si elle existe
                if ($boutique->cover_image && \Storage::disk('public')->exists($boutique->cover_image)) {
                    \Storage::disk('public')->delete($boutique->cover_image);
                }

                $coverImage = $request->file('cover_image');
                $coverImagePath = $coverImage->store('boutiques', 'public');
            }

            // Mettre à jour la boutique
            $boutique->update([
                'libelle' => $validatedData['libelle'],
                'mobile' => $validatedData['mobile'],
                'adresse' => $validatedData['adresse'],
                'adresse_gps' => $validatedData['adresse_gps'],
                'cover_image' => $coverImagePath,
                'marchand_id' => $validatedData['marchand_id'],
                'status' => $validatedData['status']
            ]);

            DB::commit();

            // Log de l'action
            Log::info('Boutique modifiée avec succès', [
                'boutique_id' => $boutique->id,
                'libelle' => $boutique->libelle,
                'marchand_id' => $boutique->marchand_id,
                'updated_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('boutiques.index')
                ->with('success', 'Boutique modifiée avec succès !');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification de la boutique', [
                'error' => $e->getMessage(),
                'boutique_id' => $boutique->id,
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la modification de la boutique.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que la boutique appartient à un marchand de l'utilisateur connecté
        if ($boutique->marchand->entreprise_id !== $user->entreprise_id) {
            return redirect()->route('boutiques.index')
                ->with('error', 'Vous n\'avez pas accès à cette boutique.');
        }

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des colis associés
            if ($boutique->colis()->count() > 0) {
                DB::rollBack();
                return redirect()->route('boutiques.index')
                    ->with('error', 'Impossible de supprimer cette boutique car elle contient des colis.');
            }

            $boutiqueId = $boutique->id;
            $boutiqueLibelle = $boutique->libelle;

            // Supprimer la boutique (soft delete)
            $boutique->delete();

            DB::commit();

            // Log de l'action
            Log::info('Boutique supprimée avec succès', [
                'boutique_id' => $boutiqueId,
                'libelle' => $boutiqueLibelle,
                'deleted_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('boutiques.index')
                ->with('success', 'Boutique supprimée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la boutique', [
                'error' => $e->getMessage(),
                'boutique_id' => $boutique->id,
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('boutiques.index')
                ->with('error', 'Une erreur est survenue lors de la suppression de la boutique.');
        }
    }

    /**
     * Search boutiques
     */
    public function search(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $user = Auth::user();
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $boutiques = Boutique::with(['marchand'])
            ->whereHas('marchand', function($q) use ($user) {
                $q->where('entreprise_id', $user->entreprise_id);
            })
            ->where(function($q) use ($query) {
                $q->where('libelle', 'like', "%{$query}%")
                  ->orWhere('adresse', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($boutique) {
                return [
                    'id' => $boutique->id,
                    'libelle' => $boutique->libelle,
                    'marchand' => $boutique->marchand->full_name,
                    'adresse' => $boutique->adresse
                ];
            });

        return response()->json($boutiques);
    }

    /**
     * Toggle boutique status
     */
    public function toggleStatus(Boutique $boutique)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que la boutique appartient à un marchand de l'utilisateur connecté
        if ($boutique->marchand->entreprise_id !== $user->entreprise_id) {
            return redirect()->route('boutiques.index')
                ->with('error', 'Vous n\'avez pas accès à cette boutique.');
        }

        try {
            $oldStatus = $boutique->status;
            $newStatus = $boutique->status === 'active' ? 'inactive' : 'active';

            $boutique->update(['status' => $newStatus]);

            // Log de l'action
            Log::info('Statut de la boutique modifié', [
                'boutique_id' => $boutique->id,
                'libelle' => $boutique->libelle,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => $user->id,
                'ip' => request()->ip()
            ]);

            $statusLabel = $newStatus === 'active' ? 'activée' : 'désactivée';
            return redirect()->route('boutiques.index')
                ->with('success', "Boutique {$statusLabel} avec succès !");

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut de la boutique', [
                'error' => $e->getMessage(),
                'boutique_id' => $boutique->id,
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('boutiques.index')
                ->with('error', 'Une erreur est survenue lors du changement de statut.');
        }
    }

    /**
     * Afficher l'historique des colis d'une boutique
     */
    public function colisHistory(Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les colis de la boutique avec pagination
        $colis = $boutique->colis()
            ->with(['conditionnementColis', 'poids', 'modeLivraison'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculer les statistiques des colis
        $totalColis = $boutique->colis()->count();
        $colisEnAttente = $boutique->colis()->where('status', 0)->count();
        $colisEnCours = $boutique->colis()->where('status', 1)->count();
        $colisLivre = $boutique->colis()->where('status', 2)->count();
        $colisAnnule = $boutique->colis()->whereIn('status', [3, 4, 5])->count();

        $data = [
            'title' => 'Historique des Colis - ' . $boutique->libelle,
            'menu' => 'boutiques',
            'user' => $user,
            'boutique' => $boutique,
            'colis' => $colis,
            'stats' => [
                'total' => $totalColis,
                'en_attente' => $colisEnAttente,
                'en_cours' => $colisEnCours,
                'livre' => $colisLivre,
                'annule' => $colisAnnule
            ]
        ];

        return view('boutique.colis-history', $data);
    }

    /**
     * Afficher l'historique des livraisons d'une boutique
     */
    public function livraisonsHistory(Boutique $boutique)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Récupérer les livraisons des colis de cette boutique via le marchand
        $livraisons = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
                $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                    $subQuery->where('marchand_id', $boutique->marchand_id);
                });
            })
            ->with(['colis.conditionnementColis', 'colis.poids', 'colis.livreur', 'colis.commune_zone'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculer les statistiques des livraisons
        $totalLivraisons = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
            $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                $subQuery->where('marchand_id', $boutique->marchand_id);
            });
        })->count();

        $livraisonsEnAttente = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
            $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                $subQuery->where('marchand_id', $boutique->marchand_id);
            });
        })->where('status', 0)->count();

        $livraisonsEnCours = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
            $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                $subQuery->where('marchand_id', $boutique->marchand_id);
            });
        })->where('status', 1)->count();

        $livraisonsLivre = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
            $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                $subQuery->where('marchand_id', $boutique->marchand_id);
            });
        })->where('status', 2)->count();

        $livraisonsAnnule = \App\Models\Livraison::whereHas('colis', function($query) use ($boutique) {
            $query->whereHas('commune_zone', function($subQuery) use ($boutique) {
                $subQuery->where('marchand_id', $boutique->marchand_id);
            });
        })->whereIn('status', [3, 4, 5])->count();

        $data = [
            'title' => 'Historique des Livraisons - ' . $boutique->libelle,
            'menu' => 'boutiques',
            'user' => $user,
            'boutique' => $boutique,
            'livraisons' => $livraisons,
            'stats' => [
                'total' => $totalLivraisons,
                'en_attente' => $livraisonsEnAttente,
                'en_cours' => $livraisonsEnCours,
                'livre' => $livraisonsLivre,
                'annule' => $livraisonsAnnule
            ]
        ];

        return view('boutique.livraisons-history', $data);
    }
}
