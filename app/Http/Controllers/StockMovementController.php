<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ModuleAccessService;

class StockMovementController extends Controller
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
        $data['title'] = 'Mouvements de Stock';
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

        $query = StockMovement::byEntreprise($entrepriseId)
            ->with(['product.category', 'user']);

        // Filtres
        if (request()->has('product_id') && request()->product_id) {
            $query->where('product_id', request()->product_id);
        }

        if (request()->has('type') && request()->type) {
            $query->where('type', request()->type);
        }

        if (request()->has('date_from') && request()->date_from) {
            $query->whereDate('created_at', '>=', request()->date_from);
        }

        if (request()->has('date_to') && request()->date_to) {
            $query->whereDate('created_at', '<=', request()->date_to);
        }

        if (request()->has('search') && request()->search) {
            $search = request()->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $data['stockMovements'] = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $data['products'] = Product::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.stock-movements.index', $data);
    }

    /**
     * Display movements for a specific product
     */
    public function byProduct(Product $product)
    {
        $data['title'] = 'Mouvements de Stock - ' . $product->name;
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($product->entreprise_id != $entrepriseId) {
            return redirect()->route('products.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce produit.']);
        }

        $data['product'] = $product->load('category');
        $data['stockMovements'] = StockMovement::byProduct($product->id)
            ->byEntreprise($entrepriseId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock.stock-movements.by-product', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // À implémenter si nécessaire
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // À implémenter si nécessaire
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        $data['title'] = 'Détails du Mouvement';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($stockMovement->entreprise_id != $entrepriseId) {
            return redirect()->route('stock-movements.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce mouvement.']);
        }

        $data['movement'] = $stockMovement->load(['product.category', 'user', 'stock']);

        return view('stock.stock-movements.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // À implémenter si nécessaire
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // À implémenter si nécessaire
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Les mouvements de stock ne doivent généralement pas être supprimés
    }
}
