<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HistoriqueFraisLivraison;
use App\Models\FraisLivraison;
use App\Models\Entreprise;
use Carbon\Carbon;

class HistoriqueFraisLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $query = HistoriqueFraisLivraison::byEntreprise($entrepriseId)
            ->with(['fraisLivraison', 'user', 'colis.zone.commune', 'livraison']);

        // Filtres
        if ($request->filled('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }

        if ($request->filled('frais_livraison_id')) {
            $query->where('frais_livraison_id', $request->frais_livraison_id);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_operation', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $historique = $query->orderBy('date_operation', 'desc')->paginate(20);

        // Données pour les filtres
        $fraisLivraisons = FraisLivraison::byEntreprise($entrepriseId)->get();
        $users = \App\Models\User::where('entreprise_id', $entrepriseId)->get();

        $data = [
            'title' => 'Historique des Frais de Livraison',
            'menu' => 'historique-frais-livraisons',
            'historique' => $historique,
            'fraisLivraisons' => $fraisLivraisons,
            'users' => $users,
            'filters' => $request->only(['type_operation', 'frais_livraison_id', 'date_debut', 'date_fin', 'user_id'])
        ];

        return view('historique-frais-livraisons.index', $data);
    }

    public function show($id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $historique = HistoriqueFraisLivraison::byEntreprise($entrepriseId)
            ->with(['fraisLivraison', 'user', 'colis.zone.commune', 'livraison'])
            ->findOrFail($id);

        $data = [
            'title' => 'Détails de l\'Historique',
            'menu' => 'historique-frais-livraisons',
            'historique' => $historique
        ];

        return view('historique-frais-livraisons.show', $data);
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $query = $request->get('q');
        $typeOperation = $request->get('type_operation');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $historique = HistoriqueFraisLivraison::byEntreprise($entrepriseId)
            ->with(['fraisLivraison', 'user']);

        if ($query) {
            $historique->where(function($q) use ($query) {
                $q->where('description_operation', 'like', "%{$query}%")
                  ->orWhereHas('fraisLivraison', function($q) use ($query) {
                      $q->where('libelle', 'like', "%{$query}%");
                  })
                  ->orWhereHas('user', function($q) use ($query) {
                      $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                  });
            });
        }

        if ($typeOperation) {
            $historique->where('type_operation', $typeOperation);
        }

        if ($dateDebut && $dateFin) {
            $historique->whereBetween('date_operation', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);
        }

        $results = $historique->orderBy('date_operation', 'desc')->limit(20)->get();

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $query = HistoriqueFraisLivraison::byEntreprise($entrepriseId)
            ->with(['fraisLivraison', 'user', 'colis.zone.commune', 'livraison']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }

        if ($request->filled('frais_livraison_id')) {
            $query->where('frais_livraison_id', $request->frais_livraison_id);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_operation', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $historique = $query->orderBy('date_operation', 'desc')->get();

        // Générer le CSV
        $filename = 'historique_frais_livraisons_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($historique) {
            $file = fopen('php://output', 'w');

            // En-têtes
            fputcsv($file, [
                'ID',
                'Frais de Livraison',
                'Type d\'Opération',
                'Description',
                'Montant Avant',
                'Montant Après',
                'Utilisateur',
                'Date d\'Opération',
                'Colis',
                'Livraison'
            ]);

            // Données
            foreach ($historique as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->fraisLivraison->libelle ?? 'N/A',
                    $item->type_operation_label,
                    $item->description_operation,
                    $item->montant_avant ? number_format($item->montant_avant, 2) . ' FCFA' : 'N/A',
                    $item->montant_apres ? number_format($item->montant_apres, 2) . ' FCFA' : 'N/A',
                    $item->user ? $item->user->first_name . ' ' . $item->user->last_name : 'N/A',
                    $item->date_operation->format('d/m/Y H:i:s'),
                    $item->colis ? $item->colis->code : 'N/A',
                    $item->livraison ? $item->livraison->id : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function statistics(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth());
        $dateFin = $request->get('date_fin', Carbon::now()->endOfMonth());

        $stats = [
            'total_operations' => HistoriqueFraisLivraison::byEntreprise($entrepriseId)
                ->whereBetween('date_operation', [$dateDebut, $dateFin])
                ->count(),
            'par_type' => HistoriqueFraisLivraison::byEntreprise($entrepriseId)
                ->whereBetween('date_operation', [$dateDebut, $dateFin])
                ->selectRaw('type_operation, COUNT(*) as count')
                ->groupBy('type_operation')
                ->get()
                ->pluck('count', 'type_operation'),
            'par_utilisateur' => HistoriqueFraisLivraison::byEntreprise($entrepriseId)
                ->whereBetween('date_operation', [$dateDebut, $dateFin])
                ->with('user')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->user ? $item->user->first_name . ' ' . $item->user->last_name : 'Utilisateur inconnu' => $item->count];
                }),
            'evolution_mensuelle' => $this->getEvolutionMensuelle($entrepriseId, $dateDebut, $dateFin)
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    private function getEvolutionMensuelle($entrepriseId, $dateDebut, $dateFin)
    {
        $evolution = [];
        $current = Carbon::parse($dateDebut);
        $end = Carbon::parse($dateFin);

        while ($current->lte($end)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $count = HistoriqueFraisLivraison::byEntreprise($entrepriseId)
                ->whereBetween('date_operation', [$monthStart, $monthEnd])
                ->count();

            $evolution[] = [
                'month' => $current->format('M Y'),
                'count' => $count
            ];

            $current->addMonth();
        }

        return $evolution;
    }
}
