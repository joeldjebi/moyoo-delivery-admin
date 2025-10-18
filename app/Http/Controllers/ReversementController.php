<?php

namespace App\Http\Controllers;

use App\Models\Reversement;
use App\Models\BalanceMarchand;
use App\Models\Marchand;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReversementController extends Controller
{
    public function index()
    {
        $data['title'] = 'Gestion des Reversements';
        $data['menu'] = 'reversements';

        $user = Auth::user();

        // Récupérer les reversements
        $query = Reversement::with(['marchand', 'boutique', 'createdBy', 'validatedBy']);

        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        // Filtres
        if (request('statut')) {
            $query->where('statut', request('statut'));
        }

        if (request('marchand_id')) {
            $query->where('marchand_id', request('marchand_id'));
        }

        if (request('date_debut') && request('date_fin')) {
            $query->whereBetween('created_at', [
                request('date_debut') . ' 00:00:00',
                request('date_fin') . ' 23:59:59'
            ]);
        }

        $perPage = request('per_page', 10);
        $data['reversements'] = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Données pour les filtres
        $data['marchands'] = Marchand::where('entreprise_id', $user->entreprise_id)
            ->orderBy('first_name')
            ->get();

        return view('reversements.index', $data);
    }

    public function create()
    {
        $data['title'] = 'Nouveau Reversement';
        $data['menu'] = 'reversements';

        $user = Auth::user();

        // Récupérer les marchands avec leur balance
        $query = BalanceMarchand::with(['marchand', 'boutique']);

        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $balances = $query->get();

        // Si aucune balance n'existe, créer des entrées pour tous les marchands
        if ($balances->isEmpty()) {
            $this->initializeBalancesForEntreprise($user->entreprise_id);

            // Récupérer les balances nouvellement créées
            $balances = $query->get();
        }

        $data['balances'] = $balances;

        // Si des paramètres sont passés (depuis le dashboard)
        if (request('marchand_id') && request('boutique_id')) {
            $data['selected_marchand_id'] = request('marchand_id');
            $data['selected_boutique_id'] = request('boutique_id');
        }

        return view('reversements.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'marchand_id' => 'required|exists:marchands,id',
            'boutique_id' => 'required|exists:boutiques,id',
            'montant_reverse' => 'required|numeric|min:0.01',
            'mode_reversement' => 'required|in:especes,virement,mobile_money,cheque',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Vérifier la balance disponible
        $balance = BalanceMarchand::where('marchand_id', $request->marchand_id)
            ->where('boutique_id', $request->boutique_id)
            ->where('entreprise_id', $user->entreprise_id)
            ->first();

        if (!$balance || $balance->balance_actuelle < $request->montant_reverse) {
            return back()->withErrors(['error' => 'Balance insuffisante pour ce reversement. Balance disponible: ' . number_format($balance->balance_actuelle ?? 0) . ' FCFA']);
        }

        DB::beginTransaction();
        try {
            // Créer le reversement
            $reversement = Reversement::create([
                'entreprise_id' => $user->entreprise_id,
                'marchand_id' => $request->marchand_id,
                'boutique_id' => $request->boutique_id,
                'montant_reverse' => $request->montant_reverse,
                'mode_reversement' => $request->mode_reversement,
                'reference_reversement' => Reversement::generateReference(),
                'statut' => 'en_attente',
                'notes' => $request->notes,
                'created_by' => $user->id
            ]);

            Log::info('Reversement créé', [
                'reversement_id' => $reversement->id,
                'marchand_id' => $request->marchand_id,
                'boutique_id' => $request->boutique_id,
                'montant' => $request->montant_reverse,
                'created_by' => $user->id
            ]);

            DB::commit();

            return redirect()->route('reversements.index')
                ->with('success', 'Reversement créé avec succès. Référence: ' . $reversement->reference_reversement);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur création reversement', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Erreur lors de la création du reversement: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $data['title'] = 'Détails du Reversement';
        $data['menu'] = 'reversements';

        $user = Auth::user();

        $query = Reversement::with(['marchand', 'boutique', 'createdBy', 'validatedBy']);

        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $data['reversement'] = $query->findOrFail($id);

        return view('reversements.show', $data);
    }

    public function validateReversement($id)
    {
        $user = Auth::user();

        $query = Reversement::query();
        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $reversement = $query->findOrFail($id);

        if ($reversement->statut !== 'en_attente') {
            return back()->withErrors(['error' => 'Ce reversement ne peut pas être validé']);
        }

        DB::beginTransaction();
        try {
            // Mettre à jour le statut
            $reversement->update([
                'statut' => 'valide',
                'date_reversement' => now(),
                'validated_by' => $user->id
            ]);

            // Mettre à jour la balance
            $balance = BalanceMarchand::where('marchand_id', $reversement->marchand_id)
                ->where('boutique_id', $reversement->boutique_id)
                ->where('entreprise_id', $reversement->entreprise_id)
                ->first();

            if ($balance) {
                $balance->subtractReversement($reversement->montant_reverse, $reversement->id);
            }

            Log::info('Reversement validé', [
                'reversement_id' => $reversement->id,
                'montant' => $reversement->montant_reverse,
                'validated_by' => $user->id
            ]);

            DB::commit();

            return back()->with('success', 'Reversement validé avec succès');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur validation reversement', [
                'error' => $e->getMessage(),
                'reversement_id' => $id
            ]);
            return back()->withErrors(['error' => 'Erreur lors de la validation du reversement: ' . $e->getMessage()]);
        }
    }

    public function cancelReversement($id)
    {
        $user = Auth::user();

        $query = Reversement::query();
        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $reversement = $query->findOrFail($id);

        if ($reversement->statut !== 'en_attente') {
            return back()->withErrors(['error' => 'Ce reversement ne peut pas être annulé']);
        }

        $reversement->update([
            'statut' => 'annule',
            'validated_by' => $user->id
        ]);

        Log::info('Reversement annulé', [
            'reversement_id' => $reversement->id,
            'cancelled_by' => $user->id
        ]);

        return back()->with('success', 'Reversement annulé avec succès');
    }

    public function balances()
    {
        $data['title'] = 'Balances des Marchands';
        $data['menu'] = 'balances';

        $user = Auth::user();

        $query = BalanceMarchand::with(['marchand', 'boutique'])
            ->where('balance_actuelle', '>', 0);

        if ($user->user_type !== 'super_admin') {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $data['balances'] = $query->orderBy('balance_actuelle', 'desc')->get();

        // Statistiques
        $data['total_balance'] = $data['balances']->sum('balance_actuelle');
        $data['total_encaisse'] = $data['balances']->sum('montant_encaisse');
        $data['total_reverse'] = $data['balances']->sum('montant_reverse');
        $data['nombre_marchands'] = $data['balances']->count();

        return view('reversements.balances', $data);
    }

    public function historique(Request $request)
    {
        $data['title'] = 'Historique des Balances';
        $data['menu'] = 'historique_balances';

        $user = Auth::user();

        $query = \App\Models\HistoriqueBalance::with(['balanceMarchand.marchand', 'balanceMarchand.boutique', 'createdBy']);

        // Filtre par marchand
        if ($request->filled('marchand_id')) {
            $query->whereHas('balanceMarchand', function($q) use ($request) {
                $q->where('marchand_id', $request->marchand_id);
            });
        }

        // Filtre par type d'opération
        if ($request->filled('type')) {
            $query->where('type_operation', $request->type);
        }

        // Filtre par date
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($request->date_debut)->startOfDay(),
                \Carbon\Carbon::parse($request->date_fin)->endOfDay()
            ]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($user->user_type !== 'super_admin') {
            $query->whereHas('balanceMarchand', function($q) use ($user) {
                $q->where('entreprise_id', $user->entreprise_id);
            });
        }

        $perPage = $request->get('per_page', 20);
        $data['historique'] = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());

        // Données pour les filtres
        $data['marchands'] = Marchand::where('entreprise_id', $user->entreprise_id)
            ->orderBy('first_name')
            ->get();

        $data['selected_marchand_id'] = $request->marchand_id;

        return view('reversements.historique', $data);
    }

    /**
     * Initialiser les balances pour une entreprise
     */
    private function initializeBalancesForEntreprise($entrepriseId)
    {
        $marchands = Marchand::where('entreprise_id', $entrepriseId)->get();
        $boutiques = Boutique::where('entreprise_id', $entrepriseId)->get();

        $created = 0;
        foreach ($marchands as $marchand) {
            foreach ($boutiques as $boutique) {
                // Vérifier si la balance existe déjà
                $existingBalance = BalanceMarchand::where('entreprise_id', $entrepriseId)
                    ->where('marchand_id', $marchand->id)
                    ->where('boutique_id', $boutique->id)
                    ->first();

                if (!$existingBalance) {
                    BalanceMarchand::create([
                        'entreprise_id' => $entrepriseId,
                        'marchand_id' => $marchand->id,
                        'boutique_id' => $boutique->id,
                        'balance_actuelle' => 0,
                        'montant_encaisse' => 0,
                        'montant_reverse' => 0
                    ]);
                    $created++;
                }
            }
        }

        Log::info('Balances initialisées pour entreprise', [
            'entreprise_id' => $entrepriseId,
            'marchands_count' => $marchands->count(),
            'boutiques_count' => $boutiques->count(),
            'balances_created' => $created
        ]);

        return $created;
    }
}
