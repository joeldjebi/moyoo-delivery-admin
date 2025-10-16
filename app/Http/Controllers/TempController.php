<?php

namespace App\Http\Controllers;

use App\Models\Temp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TempController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data['menu'] = 'temps';
            $data['title'] = 'Gestion des Périodes Temporelles';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Construire la requête avec les filtres
            $query = Temp::with(['createdBy']);

            // Filtre par statut
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->get('is_active'));
            }

            // Filtre par type
            if ($request->filled('type')) {
                $type = $request->get('type');
                switch ($type) {
                    case 'weekend':
                        $query->weekend();
                        break;
                    case 'holiday':
                        $query->holiday();
                        break;
                    case 'working':
                        $query->workingHours();
                        break;
                }
            }

            $data['temps'] = $query->orderBy('libelle')->paginate(15)->appends($request->query());

            return view('temps.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des périodes temporelles: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la récupération des périodes temporelles.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'temps';
            $data['title'] = 'Nouvelle Période Temporelle';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            return view('temps.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du formulaire.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'heure_debut' => 'nullable|date_format:H:i',
                'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',
                'is_weekend' => 'boolean',
                'is_holiday' => 'boolean',
                'is_active' => 'boolean'
            ]);

            DB::beginTransaction();

            // Créer la période temporelle
            $temp = Temp::create([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'is_weekend' => $request->boolean('is_weekend'),
                'is_holiday' => $request->boolean('is_holiday'),
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $user->id
            ]);

            DB::commit();

            Log::info('Période temporelle créée avec succès', [
                'temp_id' => $temp->id,
                'libelle' => $temp->libelle,
                'user_id' => $user->id
            ]);

            return redirect()->route('temps.index')
                           ->with('success', 'Période temporelle créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la période temporelle: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la création de la période temporelle.')
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Temp $temp)
    {
        try {
            $data['menu'] = 'temps';
            $data['title'] = 'Détails de la Période Temporelle';
            $data['temp'] = $temp->load(['createdBy']);

            return view('temps.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la période temporelle: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage de la période temporelle.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Temp $temp)
    {
        try {
            $data['menu'] = 'temps';
            $data['title'] = 'Modifier la Période Temporelle';
            $data['temp'] = $temp;

            return view('temps.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du formulaire.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Temp $temp)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'heure_debut' => 'nullable|date_format:H:i',
                'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',
                'is_weekend' => 'boolean',
                'is_holiday' => 'boolean',
                'is_active' => 'boolean'
            ]);

            DB::beginTransaction();

            // Mettre à jour la période temporelle
            $temp->update([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'is_weekend' => $request->boolean('is_weekend'),
                'is_holiday' => $request->boolean('is_holiday'),
                'is_active' => $request->boolean('is_active')
            ]);

            DB::commit();

            Log::info('Période temporelle mise à jour avec succès', [
                'temp_id' => $temp->id,
                'libelle' => $temp->libelle,
                'user_id' => $user->id
            ]);

            return redirect()->route('temps.index')
                           ->with('success', 'Période temporelle mise à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la période temporelle: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la mise à jour de la période temporelle.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Temp $temp)
    {
        try {
            DB::beginTransaction();

            $temp->delete();

            DB::commit();

            Log::info('Période temporelle supprimée avec succès', [
                'temp_id' => $temp->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('temps.index')
                           ->with('success', 'Période temporelle supprimée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la période temporelle: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression de la période temporelle.');
        }
    }

    /**
     * API pour obtenir la période temporelle actuelle
     */
    public function getCurrent()
    {
        try {
            $currentTemp = Temp::getCurrentTemp();

            return response()->json([
                'success' => true,
                'temp' => $currentTemp,
                'message' => 'Période temporelle actuelle récupérée avec succès.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la période actuelle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la période actuelle.'
            ], 500);
        }
    }
}
