<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class EntrepriseController extends Controller
{
    /**
     * Afficher les détails de l'entreprise de l'utilisateur connecté
     */
    public function index()
    {
        $data['menu'] = 'entreprise';
        $data['entreprise'] = Entreprise::getEntrepriseByUser(Auth::id());

        if (!$data['entreprise']) {
            return redirect()->route('entreprise.create');
        }

        // Récupérer les statistiques des colis pour l'entreprise
        $entrepriseId = $data['entreprise']->id;

        $data['stats'] = [
            'total_colis' => \App\Models\Colis::where('entreprise_id', $entrepriseId)->count(),
            'colis_livres' => \App\Models\Colis::where('entreprise_id', $entrepriseId)
                ->where('status', \App\Models\Colis::STATUS_LIVRE)->count(),
            'colis_en_cours' => \App\Models\Colis::where('entreprise_id', $entrepriseId)
                ->where('status', \App\Models\Colis::STATUS_EN_COURS)->count(),
            'colis_en_attente' => \App\Models\Colis::where('entreprise_id', $entrepriseId)
                ->where('status', \App\Models\Colis::STATUS_EN_ATTENTE)->count(),
        ];

        return view('entreprise.index', $data);
    }

    /**
     * Afficher le formulaire de création d'entreprise
     */
    public function create()
    {
        // Vérifier si l'utilisateur a déjà une entreprise
        if (Entreprise::hasEntreprise(Auth::id())) {
            return redirect()->route('entreprise.index')->with('error', 'Vous avez déjà une entreprise enregistrée.');
        }

        $data['menu'] = 'entreprise';
        $data['communes'] = Commune::orderBy('libelle')->get();

        return view('entreprise.create', $data);
    }

    /**
     * Enregistrer une nouvelle entreprise
     */
    public function store(Request $request)
    {
        try {
            // Vérifier si l'utilisateur a déjà une entreprise
            if (Entreprise::hasEntreprise(Auth::id())) {
                return redirect()->route('entreprise.index')->with('error', 'Vous avez déjà une entreprise enregistrée.');
            }

            $request->validate([
                'name' => 'required|string|max:200',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:200|unique:entreprises,email',
                'adresse' => 'required|string|max:500',
                'commune_id' => 'required|exists:communes,id',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            $entreprise = Entreprise::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'adresse' => $request->adresse,
                'commune_id' => $request->commune_id,
                'statut' => 1, // Actif par défaut
                'logo' => $logoPath,
                'created_by' => Auth::id()
            ]);

            Log::info('Entreprise créée', [
                'entreprise_id' => $entreprise->id,
                'user_id' => Auth::id(),
                'commune_id' => $request->commune_id
            ]);

            // Générer automatiquement les tarifs pour cette entreprise
            try {
                // Passer l'ID via la config pour le seeder
                \Config::set('seed.entreprise_id', $entreprise->id);
                Artisan::call('db:seed', ['--class' => 'EntrepriseTarifSeeder']);
                Log::info('Tarifs générés automatiquement à la création de l\'entreprise', [
                    'entreprise_id' => $entreprise->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur génération automatique des tarifs', [
                    'entreprise_id' => $entreprise->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('entreprise.index')->with('success', 'Entreprise créée avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'entreprise', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->withInput()->with('error', 'Erreur lors de la création de l\'entreprise : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition de l'entreprise
     */
    public function edit()
    {
        $data['menu'] = 'entreprise';
        $data['entreprise'] = Entreprise::getEntrepriseByUser(Auth::id());

        if (!$data['entreprise']) {
            return redirect()->route('entreprise.create');
        }

        $data['communes'] = Commune::orderBy('libelle')->get();

        return view('entreprise.edit', $data);
    }

    /**
     * Mettre à jour l'entreprise
     */
    public function update(Request $request)
    {
        try {
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());

            if (!$entreprise) {
                return redirect()->route('entreprise.create');
            }

            $request->validate([
                'name' => 'required|string|max:200',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:200|unique:entreprises,email,' . $entreprise->id,
                'adresse' => 'required|string|max:500',
                'commune_id' => 'required|exists:communes,id',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $logoPath = $entreprise->logo;
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
                if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            $entreprise->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'adresse' => $request->adresse,
                'commune_id' => $request->commune_id,
                'logo' => $logoPath,
                'not_update' => 1
            ]);

            Log::info('Entreprise mise à jour', [
                'entreprise_id' => $entreprise->id,
                'user_id' => Auth::id(),
                'commune_id' => $request->commune_id
            ]);

            return redirect()->route('entreprise.index')->with('success', 'Entreprise mise à jour avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'entreprise', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour de l\'entreprise : ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver l'entreprise
     */
    public function toggleStatus()
    {
        try {
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());

            if (!$entreprise) {
                return redirect()->route('entreprise.create');
            }

            $entreprise->update([
                'statut' => $entreprise->statut == 1 ? 0 : 1
            ]);

            $status = $entreprise->statut == 1 ? 'activée' : 'désactivée';

            return redirect()->route('entreprise.index')->with('success', "Entreprise {$status} avec succès !");

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut de l\'entreprise', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erreur lors du changement de statut : ' . $e->getMessage());
        }
    }

    /**
     * Régénérer les tarifs basés sur la commune de départ de l'entreprise
     */
    public function regenerateTarifs()
    {
        try {
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());

            if (!$entreprise) {
                return redirect()->route('entreprise.create');
            }

            // Exécuter le seeder pour regénérer les tarifs pour l'entreprise courante
            \Config::set('seed.entreprise_id', $entreprise->id);
            Artisan::call('db:seed', ['--class' => 'EntrepriseTarifSeeder']);

            Log::info('Tarifs régénérés', [
                'entreprise_id' => $entreprise->id,
                'user_id' => Auth::id(),
                'commune_depart_id' => $entreprise->commune_id
            ]);

            return redirect()->route('entreprise.index')->with('success', 'Tarifs de livraison régénérés avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la régénération des tarifs', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erreur lors de la régénération des tarifs : ' . $e->getMessage());
        }
    }

    /**
     * Upload du logo de l'entreprise
     */
    public function uploadLogo(Request $request)
    {
        try {
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());

            if (!$entreprise) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entreprise non trouvée'
                ], 404);
            }

            // Gérer la suppression du logo
            if ($request->has('_method') && $request->input('_method') === 'DELETE') {
                // Supprimer le logo existant
                if ($entreprise->logo && Storage::disk('public')->exists($entreprise->logo)) {
                    Storage::disk('public')->delete($entreprise->logo);
                }

                $entreprise->update([
                    'logo' => null
                ]);

                Log::info('Logo de l\'entreprise supprimé', [
                    'entreprise_id' => $entreprise->id,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Logo supprimé avec succès'
                ]);
            }

            // Upload du nouveau logo
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            // Supprimer l'ancien logo s'il existe
            if ($entreprise->logo && Storage::disk('public')->exists($entreprise->logo)) {
                Storage::disk('public')->delete($entreprise->logo);
            }

            // Upload du nouveau logo
            $logoPath = $request->file('logo')->store('logos', 'public');

            // Mettre à jour l'entreprise
            $entreprise->update([
                'logo' => $logoPath
            ]);

            Log::info('Logo de l\'entreprise mis à jour', [
                'entreprise_id' => $entreprise->id,
                'user_id' => Auth::id(),
                'logo_path' => $logoPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logo mis à jour avec succès',
                'logo_url' => asset('storage/' . $logoPath)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'upload du logo', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du logo : ' . $e->getMessage()
            ], 500);
        }
    }
}
