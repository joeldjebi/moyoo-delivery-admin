<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ModuleAccessService;

class CategoryController extends Controller
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
        $data['title'] = 'Gestion des Catégories';
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

        $query = Category::byEntreprise($entrepriseId)
            ->withCount('products');

        // Filtres
        if (request()->has('search') && request()->search) {
            $search = request()->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (request()->has('status') && request()->status !== '') {
            $query->where('is_active', request()->status == 'active');
        }

        $data['categories'] = $query->ordered()->paginate(15)->withQueryString();

        return view('stock.categories.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Créer une Catégorie';
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

        return view('stock.categories.create', $data);
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
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ], [
            'name.required' => 'Le nom de la catégorie est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        ]);

        try {
            DB::beginTransaction();

            $category = Category::create([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
                'icon' => $request->icon ? trim($request->icon) : null,
                'entreprise_id' => $entrepriseId,
                'is_active' => $request->has('is_active') ? true : false,
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            Log::info('Catégorie créée avec succès', [
                'category_id' => $category->id,
                'name' => $category->name,
                'created_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'La catégorie a été créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création de la catégorie', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création de la catégorie. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $data['title'] = 'Détails de la Catégorie';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($category->entreprise_id != $entrepriseId) {
            return redirect()->route('categories.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette catégorie.']);
        }

        $data['category'] = $category;
        $data['products'] = $category->products()
            ->byEntreprise($entrepriseId)
            ->with('category')
            ->ordered()
            ->paginate(15);

        return view('stock.categories.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $data['title'] = 'Modifier la Catégorie';
        $data['menu'] = 'stock';
        $data['user'] = Auth::user();

        if (empty($data['user'])) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $data['user']->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($category->entreprise_id != $entrepriseId) {
            return redirect()->route('categories.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette catégorie.']);
        }

        $data['category'] = $category;

        return view('stock.categories.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($category->entreprise_id != $entrepriseId) {
            return redirect()->route('categories.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette catégorie.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ], [
            'name.required' => 'Le nom de la catégorie est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        ]);

        try {
            DB::beginTransaction();

            $category->update([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
                'icon' => $request->icon ? trim($request->icon) : null,
                'is_active' => $request->has('is_active') ? true : false,
                'sort_order' => $request->sort_order ?? 0
            ]);

            DB::commit();

            Log::info('Catégorie modifiée avec succès', [
                'category_id' => $category->id,
                'name' => $category->name,
                'updated_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'La catégorie a été modifiée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la modification de la catégorie', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la modification de la catégorie. Veuillez réessayer.'])
                ->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $entrepriseId = $user->entreprise_id ?? 1;

        // Vérifier l'accès
        if ($category->entreprise_id != $entrepriseId) {
            return redirect()->route('categories.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette catégorie.']);
        }

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des produits associés
            if ($category->products()->count() > 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de supprimer cette catégorie car elle contient des produits.']);
            }

            $categoryName = $category->name;
            $categoryId = $category->id;

            $category->delete();

            DB::commit();

            Log::info('Catégorie supprimée avec succès', [
                'category_id' => $categoryId,
                'name' => $categoryName,
                'deleted_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'La catégorie a été supprimée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression de la catégorie', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la suppression de la catégorie. Veuillez réessayer.']);
        }
    }
}
