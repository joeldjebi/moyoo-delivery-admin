<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ModuleAccessService;

class StockController extends Controller
{
    protected $moduleAccessService;

    public function __construct(ModuleAccessService $moduleAccessService)
    {
        $this->moduleAccessService = $moduleAccessService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] = 'Gestion des Stocks';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès au module
        if (!$this->moduleAccessService->hasAccess($entrepriseId, 'stock_management')) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Le module de gestion de stock n\'est pas disponible dans votre plan actuel.');
        }

        $query = Stock::byEntreprise($entrepriseId)
            ->with(['product.category']);

        // Filtres
        if (request()->has('product_id') && request()->product_id) {
            $query->where('product_id', request()->product_id);
        }

        if (request()->has('location') && request()->location) {
            $query->where('location', 'like', '%' . request()->location . '%');
        }

        if (request()->has('status') && request()->status !== '') {
            switch (request()->status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'normal':
                    $query->whereColumn('quantity', '>', 'min_quantity');
                    break;
                case 'max':
                    $query->where(function($q) {
                        $q->whereNotNull('max_quantity')
                          ->whereColumn('quantity', '>=', 'max_quantity');
                    });
                    break;
            }
        }

        if (request()->has('search') && request()->search) {
            $search = request()->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $data['stocks'] = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $data['products'] = Product::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.stocks.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Créer un Stock';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès au module
        if (!$this->moduleAccessService->hasAccess($entrepriseId, 'stock_management')) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Le module de gestion de stock n\'est pas disponible dans votre plan actuel.');
        }

        $data['products'] = Product::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.stocks.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès au module
        if (!$this->moduleAccessService->hasAccess($entrepriseId, 'stock_management')) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Le module de gestion de stock n\'est pas disponible dans votre plan actuel.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $stock = Stock::create([
                'product_id' => $request->product_id,
                'entreprise_id' => $entrepriseId,
                'quantity' => $request->quantity,
                'min_quantity' => $request->min_quantity ?? 0,
                'max_quantity' => $request->max_quantity,
                'unit_cost' => $request->unit_cost ?? 0,
                'location' => $request->location
            ]);

            DB::commit();

            Log::info('Stock créé avec succès', [
                'stock_id' => $stock->id,
                'product_id' => $stock->product_id,
                'created_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('stocks.index')
                ->with('success', 'Le stock a été créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création du stock', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création du stock. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        $data['title'] = 'Détails du Stock';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($stock->entreprise_id != $entrepriseId) {
            return redirect()->route('stocks.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce stock.']);
        }

        $data['stock'] = $stock->load(['product', 'movements.user']);

        return view('stock.stocks.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        $data['title'] = 'Modifier le Stock';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($stock->entreprise_id != $entrepriseId) {
            return redirect()->route('stocks.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce stock.']);
        }

        $data['stock'] = $stock;
        $data['products'] = Product::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.stocks.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($stock->entreprise_id != $entrepriseId) {
            return redirect()->route('stocks.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce stock.']);
        }

        $request->validate([
            'min_quantity' => 'integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $stock->update([
                'min_quantity' => $request->min_quantity ?? 0,
                'max_quantity' => $request->max_quantity,
                'unit_cost' => $request->unit_cost ?? $stock->unit_cost,
                'location' => $request->location
            ]);

            DB::commit();

            Log::info('Stock modifié avec succès', [
                'stock_id' => $stock->id,
                'updated_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('stocks.index')
                ->with('success', 'Le stock a été modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la modification du stock', [
                'stock_id' => $stock->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la modification du stock. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($stock->entreprise_id != $entrepriseId) {
            return redirect()->route('stocks.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce stock.']);
        }

        try {
            DB::beginTransaction();

            $stockId = $stock->id;
            $stock->delete();

            DB::commit();

            Log::info('Stock supprimé avec succès', [
                'stock_id' => $stockId,
                'deleted_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('stocks.index')
                ->with('success', 'Le stock a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression du stock', [
                'stock_id' => $stock->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la suppression du stock. Veuillez réessayer.']);
        }
    }

    /**
     * Ajuster un stock
     */
    public function adjust(Request $request, Stock $stock)
    {
        // À implémenter
    }

    /**
     * Entrée de stock
     */
    public function entry(Request $request, Stock $stock)
    {
        // À implémenter
    }

    /**
     * Sortie de stock
     */
    public function exit(Request $request, Stock $stock)
    {
        // À implémenter
    }
}
