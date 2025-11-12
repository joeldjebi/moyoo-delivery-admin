<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ModuleAccessService;

class ProductController extends Controller
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
        $data['title'] = 'Gestion des Produits';
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

        $query = Product::byEntreprise($entrepriseId)
            ->with(['category', 'stocks'])
            ->withCount('stocks');

        // Filtres
        if (request()->has('category_id') && request()->category_id) {
            $query->where('category_id', request()->category_id);
        }

        if (request()->has('search') && request()->search) {
            $search = request()->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $data['products'] = $query->ordered()->paginate(10)->withQueryString();
        $data['categories'] = Category::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.products.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Créer un Produit';
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

        $data['categories'] = Category::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.products.create', $data);
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ], [
            'name.required' => 'Le nom du produit est obligatoire.',
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'sku.unique' => 'Ce code SKU est déjà utilisé.',
            'barcode.unique' => 'Ce code-barres est déjà utilisé.',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products'), $imageName);
                $imagePath = 'uploads/products/' . $imageName;
            }

            $product = Product::create([
                'name' => trim($request->name),
                'category_id' => $request->category_id,
                'sku' => $request->sku ? trim($request->sku) : null,
                'barcode' => $request->barcode ? trim($request->barcode) : null,
                'description' => $request->description ? trim($request->description) : null,
                'price' => $request->price,
                'currency' => $request->currency,
                'unit' => $request->unit,
                'image' => $imagePath,
                'entreprise_id' => $entrepriseId,
                'is_active' => $request->has('is_active') ? true : false,
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            Log::info('Produit créé avec succès', [
                'product_id' => $product->id,
                'name' => $product->name,
                'created_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Le produit a été créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création du produit', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création du produit. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $data['title'] = 'Détails du Produit';
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
        $data['stocks'] = $product->stocks()->byEntreprise($entrepriseId)->get();
        $data['movements'] = $product->stockMovements()
            ->byEntreprise($entrepriseId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('stock.products.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $data['title'] = 'Modifier le Produit';
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

        $data['product'] = $product;
        $data['categories'] = Category::byEntreprise($entrepriseId)->active()->ordered()->get();

        return view('stock.products.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($product->entreprise_id != $entrepriseId) {
            return redirect()->route('products.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce produit.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ], [
            'name.required' => 'Le nom du produit est obligatoire.',
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'sku.unique' => 'Ce code SKU est déjà utilisé.',
            'barcode.unique' => 'Ce code-barres est déjà utilisé.',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($product->image && file_exists(public_path($product->image))) {
                    unlink(public_path($product->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products'), $imageName);
                $imagePath = 'uploads/products/' . $imageName;
            }

            $product->update([
                'name' => trim($request->name),
                'category_id' => $request->category_id,
                'sku' => $request->sku ? trim($request->sku) : null,
                'barcode' => $request->barcode ? trim($request->barcode) : null,
                'description' => $request->description ? trim($request->description) : null,
                'price' => $request->price,
                'currency' => $request->currency,
                'unit' => $request->unit,
                'image' => $imagePath,
                'is_active' => $request->has('is_active') ? true : false,
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            Log::info('Produit modifié avec succès', [
                'product_id' => $product->id,
                'name' => $product->name,
                'updated_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Le produit a été modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la modification du produit', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la modification du produit. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($product->entreprise_id != $entrepriseId) {
            return redirect()->route('products.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce produit.']);
        }

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des stocks associés
            if ($product->stocks()->count() > 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de supprimer ce produit car il a des stocks associés.']);
            }

            // Supprimer l'image si elle existe
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            $productName = $product->name;
            $productId = $product->id;

            $product->delete();

            DB::commit();

            Log::info('Produit supprimé avec succès', [
                'product_id' => $productId,
                'name' => $productName,
                'deleted_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Le produit a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression du produit', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la suppression du produit. Veuillez réessayer.']);
        }
    }
}