<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarchandController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\EnginController;
use App\Http\Controllers\ModeLivraisonController;
use App\Http\Controllers\PoidsColisController;
use App\Http\Controllers\TypeColisController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\HistoriqueFraisLivraisonController;
use App\Http\Controllers\FraisLivraisonController;
use App\Http\Controllers\TypeEnginController;
use App\Http\Controllers\ColisController;
use App\Http\Controllers\TarifLivraisonController;
use App\Http\Controllers\TempController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DelaisController;
use App\Http\Controllers\PoidController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\HistoriqueLivraisonController;
use App\Http\Controllers\RamassageController;
use App\Http\Controllers\LocationController;

// Routes d'authentification
Route::group(['middleware' => ['auth', 'tenant']], function (){

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/dashboard/colis-en-cours', [DashboardController::class, 'getColisEnCoursPaginated'])->name('api.dashboard.colis-en-cours');
    Route::get('/api/dashboard/ramassages', [DashboardController::class, 'getRamassagesPaginated'])->name('api.dashboard.ramassages');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Routes pour les marchands
    Route::resource('marchands', MarchandController::class)->middleware('permission:marchands.read');
    Route::get('/marchands-search', [MarchandController::class, 'search'])->name('marchands.search')->middleware('permission:marchands.read');
    Route::patch('/marchands/{marchand}/toggle-status', [MarchandController::class, 'toggleStatus'])->name('marchands.toggle-status')->middleware('permission:marchands.update');

    // Routes pour les boutiques
    Route::resource('boutiques', BoutiqueController::class)->middleware('permission:marchands.read');
    Route::get('/boutiques-search', [BoutiqueController::class, 'search'])->name('boutiques.search')->middleware('permission:marchands.read');
    Route::patch('/boutiques/{boutique}/toggle-status', [BoutiqueController::class, 'toggleStatus'])->name('boutiques.toggle-status')->middleware('permission:marchands.update');
    Route::get('/boutiques/{boutique}/colis', [BoutiqueController::class, 'colisHistory'])->name('boutiques.colis')->middleware('permission:colis.read');
    Route::get('/boutiques/{boutique}/livraisons', [BoutiqueController::class, 'livraisonsHistory'])->name('boutiques.livraisons')->middleware('permission:colis.read');

    // Routes pour les types d'engins
    Route::resource('type-engins', TypeEnginController::class)->middleware('permission:livreurs.read');
    Route::get('/type-engins-search', [TypeEnginController::class, 'search'])->name('type-engins.search')->middleware('permission:livreurs.read');

    // Routes pour les types de colis
    Route::resource('type-colis', TypeColisController::class)->middleware('permission:colis.read');
    Route::get('/type-colis-search', [TypeColisController::class, 'search'])->name('type-colis.search')->middleware('permission:colis.read');

    // Routes pour les délais
    Route::resource('delais', DelaisController::class)->middleware('permission:colis.read');
    Route::get('/delais-search', [DelaisController::class, 'search'])->name('delais.search')->middleware('permission:colis.read');

    // Routes pour les modes de livraison
    Route::resource('mode-livraisons', ModeLivraisonController::class)->middleware('permission:colis.read');
    Route::get('/mode-livraisons-search', [ModeLivraisonController::class, 'search'])->name('mode-livraisons.search')->middleware('permission:colis.read');

    // Routes pour les poids
    Route::resource('poids', PoidController::class)->middleware('permission:colis.read');
    Route::get('/poids-search', [PoidController::class, 'search'])->name('poids.search')->middleware('permission:colis.read');

    // Routes pour les livraisons
    Route::resource('livraisons', LivraisonController::class)->middleware('permission:colis.read');

    // Routes pour l'historique des livraisons
    Route::resource('historique-livraisons', HistoriqueLivraisonController::class)->middleware('permission:colis.read');
    Route::get('/historique-livraisons-search', [HistoriqueLivraisonController::class, 'search'])->name('historique-livraisons.search')->middleware('permission:colis.read');

    // Routes pour les ramassages
    Route::resource('ramassages', RamassageController::class)->middleware('permission:colis.read');
    Route::post('/ramassages/{ramassage}/planifier', [RamassageController::class, 'planifier'])->name('ramassages.planifier')->middleware('permission:colis.update');
    Route::post('/ramassages/{ramassage}/ajouter-colis', [RamassageController::class, 'ajouterColis'])->name('ramassages.ajouter-colis')->middleware('permission:colis.update');
    Route::patch('/ramassages/{ramassage}/colis/{colis}/statut', [RamassageController::class, 'updateStatutColis'])->name('ramassages.update-statut-colis')->middleware('permission:colis.update');
    Route::get('/api/ramassages/colis-disponibles', [RamassageController::class, 'getColisDisponibles'])->name('ramassages.colis-disponibles')->middleware('permission:colis.read');

    // Routes pour les planifications de ramassages
    Route::put('/planification-ramassages/{planification}', [RamassageController::class, 'updatePlanification'])->name('planification-ramassages.update')->middleware('permission:colis.update');
    Route::get('/api/boutiques/by-marchand/{marchandId}', [RamassageController::class, 'getBoutiquesByMarchand'])->name('api.boutiques.by-marchand')->middleware('permission:colis.read');

    // Routes API pour les données des formulaires de colis
    Route::get('/api/communes', [RamassageController::class, 'getCommunes'])->name('api.communes')->middleware('permission:colis.read');
    Route::get('/api/types-colis', [RamassageController::class, 'getTypesColis'])->name('api.types-colis')->middleware('permission:colis.read');
    Route::get('/api/poids', [RamassageController::class, 'getPoids'])->name('api.poids')->middleware('permission:colis.read');
    Route::get('/api/conditionnements', [RamassageController::class, 'getConditionnements'])->name('api.conditionnements')->middleware('permission:colis.read');
    Route::get('/api/delais', [RamassageController::class, 'getDelais'])->name('api.delais')->middleware('permission:colis.read');
    Route::get('/api/modes-livraison', [RamassageController::class, 'getModesLivraison'])->name('api.modes-livraison')->middleware('permission:colis.read');
    Route::get('/api/periodes', [RamassageController::class, 'getPeriodes'])->name('api.periodes')->middleware('permission:colis.read');
Route::get('/api/ramassages/{id}/colis-data', [RamassageController::class, 'getColisData'])->name('api.ramassages.colis-data')->middleware('permission:colis.read');


    // Routes pour les engins
    Route::resource('engins', EnginController::class)->middleware('permission:livreurs.read');
    Route::get('/engins-search', [EnginController::class, 'search'])->name('engins.search')->middleware('permission:livreurs.read');
    Route::get('/engins/{engin}/edit', [EnginController::class, 'edit'])->name('engins.edit')->middleware('permission:livreurs.update');
    Route::put('/engins/{engin}', [EnginController::class, 'update'])->name('engins.update')->middleware('permission:livreurs.update');
    Route::delete('/engins/{engin}', [EnginController::class, 'destroy'])->name('engins.destroy')->middleware('permission:livreurs.delete');
    Route::patch('/engins/{engin}/toggle-status', [EnginController::class, 'toggleStatus'])->name('engins.toggle-status')->middleware('permission:livreurs.update');
    Route::get('/engins/{engin}/colis', [EnginController::class, 'colisHistory'])->name('engins.colis')->middleware('permission:colis.read');
    Route::get('/engins/{engin}/livraisons', [EnginController::class, 'livraisonsHistory'])->name('engins.livraisons')->middleware('permission:colis.read');

    // Routes pour les livreurs
    Route::resource('livreurs', LivreurController::class)->middleware('permission:livreurs.read');
    Route::get('/livreurs-search', [LivreurController::class, 'search'])->name('livreurs.search')->middleware('permission:livreurs.read');
    Route::patch('/livreurs/{livreur}/toggle-status', [LivreurController::class, 'toggleStatus'])->name('livreurs.toggle-status')->middleware('permission:livreurs.update');
    Route::get('/livreurs/{livreur}/colis', [LivreurController::class, 'colisHistory'])->name('livreurs.colis')->middleware('permission:colis.read');
    Route::get('/livreurs/{livreur}/livraisons', [LivreurController::class, 'livraisonsHistory'])->name('livreurs.livraisons')->middleware('permission:colis.read');

    // Routes pour les colis
    Route::get('/colis', [ColisController::class, 'index'])->name('colis.index')->middleware('permission:colis.read');
    Route::get('/colis/create', [ColisController::class, 'create'])->name('colis.create')->middleware('permission:colis.create');
    Route::post('/colis', [ColisController::class, 'store'])->name('colis.store')->middleware('permission:colis.create');
    Route::post('/colis/create-multi-boutiques', [ColisController::class, 'storeMultiBoutiques'])->name('colis.store-multi-boutiques')->middleware('permission:colis.create');

    // Routes pour l'assignation en masse (doivent être avant les routes avec paramètres)
    Route::get('/colis/assign', [ColisController::class, 'showAssignPage'])->name('colis.assign')->middleware('permission:colis.update');
    Route::patch('/colis/bulk-assign-livreur', [ColisController::class, 'bulkAssignLivreur'])->name('colis.bulk-assign-livreur')->middleware('permission:colis.update');

    // Routes pour les packages de colis
    Route::get('/colis/packages', [ColisController::class, 'packages'])->name('colis.packages')->middleware('permission:colis.read');
    Route::get('/colis/packages/{id}', [ColisController::class, 'showPackage'])->name('colis.package.show')->middleware('permission:colis.read');

    // Routes AJAX pour les colis
    Route::get('/colis/boutiques-by-marchand/{marchand}', [ColisController::class, 'getBoutiquesByMarchand'])->name('colis.boutiques-by-marchand');
    Route::get('/colis/communes-by-zone/{zone}', [ColisController::class, 'getCommunesByZone'])->name('colis.communes-by-zone');
    Route::get('/colis/engins-by-livreur/{livreur}', [ColisController::class, 'getEnginsByLivreur'])->name('colis.engins-by-livreur');
    Route::get('/colis/{colis}', [ColisController::class, 'show'])->name('colis.show')->middleware('permission:colis.read');
    Route::get('/colis/{colis}/edit', [ColisController::class, 'edit'])->name('colis.edit')->middleware('permission:colis.update');
    Route::put('/colis/{colis}', [ColisController::class, 'update'])->name('colis.update')->middleware('permission:colis.update');
    Route::delete('/colis/{colis}', [ColisController::class, 'destroy'])->name('colis.destroy')->middleware('permission:colis.delete');

    Route::get('/colis-search', [ColisController::class, 'search'])->name('colis.search')->middleware('permission:colis.read');
    Route::patch('/colis/{colis}/toggle-status', [ColisController::class, 'toggleStatus'])->name('colis.toggle-status')->middleware('permission:colis.update');

    // Routes pour l'assignation multiple
    Route::post('/colis/assign-multiple', [ColisController::class, 'assignMultipleColis'])->name('colis.assign-multiple')->middleware('permission:colis.update');
    Route::get('/colis/communes-by-zone', [ColisController::class, 'getCommunesByZone'])->name('colis.communes-by-zone')->middleware('permission:colis.read');

    // Routes pour l'assignation individuelle
    Route::patch('/colis/{colis}/assign-livreur', [ColisController::class, 'assignLivreur'])->name('colis.assign-livreur')->middleware('permission:colis.update');
    Route::patch('/colis/{colis}/mark-delivered', [ColisController::class, 'markAsDelivered'])->name('colis.mark-delivered')->middleware('permission:colis.update');

    // Routes pour les actions en masse
    Route::post('/colis/bulk-update-status', [ColisController::class, 'bulkUpdateStatus'])->name('colis.bulk-update-status')->middleware('permission:colis.update');

    // Routes pour les statistiques et rapports
    Route::get('/colis/available', [ColisController::class, 'getAvailableColis'])->name('colis.available')->middleware('permission:colis.read');
    Route::get('/colis/livreur/{livreur}/assignments', [ColisController::class, 'getLivreurAssignments'])->name('colis.livreur-assignments')->middleware('permission:colis.read');
    Route::get('/colis/livreur/{livreur}/optimize-routes', [ColisController::class, 'optimizeDeliveryRoutes'])->name('colis.optimize-routes')->middleware('permission:colis.read');
    Route::get('/colis/by-livreur-zone', [ColisController::class, 'getColisByLivreurAndZone'])->name('colis.by-livreur-zone');

    // Routes pour les tarifs de livraison
    Route::resource('tarifs', TarifLivraisonController::class)->middleware('permission:settings.read');
    Route::post('/tarifs/calculate-cost', [TarifLivraisonController::class, 'calculateCost'])->name('tarifs.calculate-cost')->middleware('permission:colis.read');

    // Routes pour les périodes temporelles
    Route::resource('temps', TempController::class)->middleware('permission:settings.read');
    Route::get('/temps/current', [TempController::class, 'getCurrent'])->name('temps.current')->middleware('permission:settings.read');

    // Routes pour les rapports
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index')->middleware('permission:reports.read');
    Route::get('/rapports/{type}', [RapportController::class, 'show'])->name('rapports.show')->middleware('permission:reports.read');
    Route::get('/rapports-search', [RapportController::class, 'search'])->name('rapports.search')->middleware('permission:reports.read');

    // Routes pour les frais de livraison
    Route::resource('frais-livraisons', FraisLivraisonController::class)->middleware('permission:settings.read');
    Route::get('/frais-livraisons-search', [FraisLivraisonController::class, 'search'])->name('frais-livraisons.search')->middleware('permission:settings.read');

    // Routes pour l'historique des frais de livraison
    Route::get('/historique-frais-livraisons', [HistoriqueFraisLivraisonController::class, 'index'])->name('historique-frais-livraisons.index')->middleware('permission:reports.read');
    Route::get('/historique-frais-livraisons/{id}', [HistoriqueFraisLivraisonController::class, 'show'])->name('historique-frais-livraisons.show')->middleware('permission:reports.read');
    Route::get('/historique-frais-livraisons-search', [HistoriqueFraisLivraisonController::class, 'search'])->name('historique-frais-livraisons.search')->middleware('permission:reports.read');
    Route::get('/historique-frais-livraisons-export', [HistoriqueFraisLivraisonController::class, 'export'])->name('historique-frais-livraisons.export')->middleware('permission:reports.read');
    Route::get('/historique-frais-livraisons-statistics', [HistoriqueFraisLivraisonController::class, 'statistics'])->name('historique-frais-livraisons.statistics')->middleware('permission:reports.read');

    // Routes pour l'entreprise
    Route::get('/entreprise', [EntrepriseController::class, 'index'])->name('entreprise.index')->middleware('permission:settings.read');
    Route::get('/entreprise/create', [EntrepriseController::class, 'create'])->name('entreprise.create')->middleware('permission:settings.update');
    Route::post('/entreprise', [EntrepriseController::class, 'store'])->name('entreprise.store')->middleware('permission:settings.update');
    Route::get('/entreprise/edit', [EntrepriseController::class, 'edit'])->name('entreprise.edit')->middleware('permission:settings.update');
    Route::put('/entreprise', [EntrepriseController::class, 'update'])->name('entreprise.update')->middleware('permission:settings.update');
    Route::put('/entreprise/toggle-status', [EntrepriseController::class, 'toggleStatus'])->name('entreprise.toggle-status')->middleware('permission:settings.update');
    Route::post('/entreprise/regenerate-tarifs', [EntrepriseController::class, 'regenerateTarifs'])->name('entreprise.regenerate-tarifs')->middleware('permission:settings.update');

    // Routes pour la gestion du profil utilisateur
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('auth.profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('auth.change-password');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('auth.password.update');

    // Routes pour les pages supplémentaires
    Route::get('/subscription-history', [AuthController::class, 'showSubscriptionHistory'])->name('auth.subscription-history');
    Route::get('/pricing', [AuthController::class, 'showPricing'])->name('auth.pricing');
    Route::get('/faq', [AuthController::class, 'showFAQ'])->name('auth.faq');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Routes pour la gestion des utilisateurs (admin seulement)
    Route::resource('users', UserController::class)->middleware('permission:users.read');
    Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->middleware('permission:users.update');
    Route::post('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password')->middleware('permission:users.update');

    // Routes pour la gestion des permissions des rôles
    Route::get('/role-permissions', [App\Http\Controllers\RolePermissionController::class, 'index'])->name('role-permissions.index')->middleware('permission:settings.update');
    Route::post('/role-permissions/update', [App\Http\Controllers\RolePermissionController::class, 'update'])->name('role-permissions.update')->middleware('permission:settings.update');
    Route::get('/role-permissions/get', [App\Http\Controllers\RolePermissionController::class, 'getRolePermissions'])->name('role-permissions.get')->middleware('permission:settings.read');
});

// Routes d'authentification (sans middleware auth)
Route::get('/', [AuthController::class, 'showLogin'])->name('auth.login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'loginUser'])->name('auth.login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'registerUser'])->name('auth.register.post');
Route::get('/password-forget', [AuthController::class, 'showPasswordForget'])->name('auth.password-forget');
Route::post('/password-forget', [AuthController::class, 'sendPasswordResetEmail'])->name('auth.password-forget.post');
Route::get('/reset-password', [AuthController::class, 'showPasswordReset'])->name('auth.reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password.post');


// Routes pour la vérification OTP
Route::get('/verify-otp', [AuthController::class, 'showVerifyOTP'])->name('auth.verify-otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('auth.verify-otp.post');
Route::post('/resend-otp', [AuthController::class, 'resendOTP'])->name('auth.resend-otp');

// Routes pour les pages d'erreur
Route::get('/error/403', [App\Http\Controllers\ErrorController::class, 'show403'])->name('error.403');
Route::get('/error/404', [App\Http\Controllers\ErrorController::class, 'show404'])->name('error.404');
Route::get('/error/500', [App\Http\Controllers\ErrorController::class, 'show500'])->name('error.500');

// Route API pour les informations utilisateur (pour les pages d'erreur)
Route::get('/api/user-info', [App\Http\Controllers\ErrorController::class, 'getUserInfo'])->name('api.user-info');

// Route de test pour déclencher une erreur 403
Route::get('/test-403', function() {
    abort(403, 'Test d\'erreur 403');
})->name('test.403');

// Route de test pour les données du graphique
Route::get('/test-chart-data', function() {
    $entrepriseId = 1;

    $controller = new \App\Http\Controllers\DashboardController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getChartData');
    $method->setAccessible(true);

    $chartData = $method->invoke($controller, $entrepriseId);

    return response()->json([
        'success' => true,
        'data' => [
            'labels' => $chartData['shipment_labels'],
            'shipment_data' => $chartData['shipment_data'],
            'delivery_data' => $chartData['delivery_data'],
            'total_shipments' => array_sum($chartData['shipment_data']),
            'total_deliveries' => array_sum($chartData['delivery_data'])
        ]
    ]);
});

// Route AJAX pour actualiser les données du graphique
Route::get('/api/chart-data', function(Request $request) {
    $entrepriseId = auth()->user()->entreprise_id ?? 1;
    $month = $request->get('month', now()->month);

    $controller = new \App\Http\Controllers\DashboardController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getChartData');
    $method->setAccessible(true);

    $chartData = $method->invoke($controller, $entrepriseId, $month);

    return response()->json([
        'success' => true,
        'data' => [
            'labels' => $chartData['shipment_labels'],
            'shipment_data' => $chartData['shipment_data'],
            'delivery_data' => $chartData['delivery_data'],
            'total_shipments' => array_sum($chartData['shipment_data']),
            'total_deliveries' => array_sum($chartData['delivery_data'])
        ]
    ]);
})->middleware('auth');

// Route de test pour les données du graphique (sans auth pour debug)
Route::get('/api/chart-data-test', function(Request $request) {
    $entrepriseId = 1;
    $month = $request->get('month', now()->month);

    $controller = new \App\Http\Controllers\DashboardController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getChartData');
    $method->setAccessible(true);

    $chartData = $method->invoke($controller, $entrepriseId, $month);

    return response()->json([
        'success' => true,
        'data' => [
            'labels' => $chartData['shipment_labels'],
            'shipment_data' => $chartData['shipment_data'],
            'delivery_data' => $chartData['delivery_data'],
            'total_shipments' => array_sum($chartData['shipment_data']),
            'total_deliveries' => array_sum($chartData['delivery_data'])
        ]
    ]);
});

// Route pour la recherche de colis
Route::get('/api/search-colis', function(Request $request) {
    $query = $request->input('q', '');

    if (strlen($query) < 2) {
        return response()->json(['colis' => []]);
    }

    try {
        // Si l'utilisateur est connecté, récupérer son entreprise via created_by
        $entrepriseId = null;
        if (auth()->check()) {
            $user = auth()->user();

            // D'abord essayer l'entreprise_id direct
            if ($user->entreprise_id) {
                $entrepriseId = $user->entreprise_id;
            } else {
                // Sinon chercher l'entreprise créée par cet utilisateur
                $entreprise = \App\Models\Entreprise::where('created_by', $user->id)->first();
                if ($entreprise) {
                    $entrepriseId = $entreprise->id;
                }
            }
        }

        $queryBuilder = \App\Models\Colis::query();

        if ($entrepriseId) {
            $queryBuilder->where('entreprise_id', $entrepriseId);
        }

        $colis = $queryBuilder->where('code', 'LIKE', '%' . $query . '%')
                             ->with(['commune', 'livreur'])
                             ->limit(10)
                             ->get()
                             ->map(function($colis) {
                                 return [
                                     'id' => $colis->id,
                                     'code' => $colis->code,
                                     'client' => $colis->nom_client ?? 'N/A',
                                     'commune' => $colis->commune ? $colis->commune->nom : 'N/A',
                                     'livreur' => $colis->livreur ? $colis->livreur->first_name . ' ' . $colis->livreur->last_name : 'N/A',
                                     'status' => $colis->status,
                                     'prix' => $colis->prix_de_vente ?? 0,
                                     'url' => route('colis.show', $colis),
                                     'created_at' => $colis->created_at->format('d/m/Y')
                                 ];
                             });

        return response()->json(['colis' => $colis]);
    } catch (\Exception $e) {
        \Log::error('Erreur recherche colis: ' . $e->getMessage());
        return response()->json(['colis' => [], 'error' => $e->getMessage()], 500);
    }
})->middleware(['web', 'throttle:60,1']);

// Route API pour récupérer les ramassages par boutique
Route::get('/api/ramassages/by-boutique/{boutiqueId}', function($boutiqueId) {
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
        }

        // Récupérer l'entreprise de l'utilisateur
        $entrepriseId = $user->entreprise_id;
        if (!$entrepriseId) {
            // Fallback: chercher une entreprise créée par cet utilisateur
            $entreprise = \App\Models\Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut
        }

               $ramassages = \App\Models\Ramassage::with(['marchand', 'boutique'])
                   ->where('boutique_id', $boutiqueId)
                   ->where('entreprise_id', $entrepriseId)
                   ->where('statut', 'termine') // Filtrer uniquement les ramassages terminés
                   ->whereNotNull('colis_data')
                   ->where('colis_data', '!=', '')
                   ->where('colis_data', '!=', '[]')
                   ->where('colis_data', '!=', 'null')
                   ->whereNotExists(function ($query) {
                       $query->select(\DB::raw(1))
                             ->from('ramassage_colis')
                             ->whereRaw('ramassage_colis.ramassage_id = ramassages.id');
                   })
                   ->orderBy('created_at', 'desc')
                   ->get();

        return response()->json([
            'success' => true,
            'ramassages' => $ramassages,
            'debug' => [
                'boutique_id' => $boutiqueId,
                'entreprise_id' => $entrepriseId,
                'user_id' => $user->id,
                'count' => $ramassages->count()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware(['web', 'auth']);

// Route de test pour vérifier l'authentification
Route::get('/test-auth', function() {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? auth()->user()->email : null,
        'entreprise_id' => auth()->check() ? auth()->user()->entreprise_id : null,
        'entreprise_created_by' => auth()->check() ? \App\Models\Entreprise::where('created_by', auth()->user()->id)->first()?->id : null
    ]);
});

// Routes Swagger
require __DIR__.'/swagger.php';

// Route pour synchroniser les données de frais de livraison
Route::get('/api/sync-frais-data', function() {
    try {
        $user = auth()->user();
        $entrepriseId = $user->entreprise_id ?? 1;

        $controller = new \App\Http\Controllers\DashboardController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('syncFraisData');
        $method->setAccessible(true);

        $method->invoke($controller, $entrepriseId);

        return response()->json([
            'success' => true,
            'message' => 'Données de frais synchronisées avec succès'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// Route pour obtenir les statistiques de frais de livraison
Route::get('/api/frais-stats', function() {
    try {
        $user = auth()->user();
        $entrepriseId = $user->entreprise_id ?? 1;

        $controller = new \App\Http\Controllers\DashboardController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getFraisStats');
        $method->setAccessible(true);

        $fraisStats = $method->invoke($controller, $entrepriseId);

        return response()->json([
            'success' => true,
            'data' => $fraisStats
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// Route pour obtenir les statistiques détaillées de frais de livraison
Route::get('/api/frais-stats-detailed', function() {
    try {
        $user = auth()->user();
        $entrepriseId = $user->entreprise_id ?? 1;

        $controller = new \App\Http\Controllers\DashboardController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getFraisStatsDetailed');
        $method->setAccessible(true);

        $fraisStatsDetailed = $method->invoke($controller, $entrepriseId);

        return response()->json([
            'success' => true,
            'data' => $fraisStatsDetailed
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// Route de test simple pour les frais
Route::get('/api/test-frais', function() {
    try {
        $entrepriseId = 1;

        // Test direct
        $totalDirect = \App\Models\Historique_livraison::where('entreprise_id', $entrepriseId)
            ->sum('montant_de_la_livraison');

        // Test via contrôleur
        $controller = new \App\Http\Controllers\DashboardController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getFraisStats');
        $method->setAccessible(true);
        $fraisStats = $method->invoke($controller, $entrepriseId);

        return response()->json([
            'success' => true,
            'direct_total' => $totalDirect,
            'controller_stats' => $fraisStats,
            'debug_info' => [
                'entreprise_id' => $entrepriseId,
                'today' => now()->format('Y-m-d'),
                'records_count' => \App\Models\Historique_livraison::where('entreprise_id', $entrepriseId)->count()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Routes pour les reversements
Route::middleware(['auth'])->group(function () {
    // Routes de consultation (lecture)
    Route::get('/reversements', [App\Http\Controllers\ReversementController::class, 'index'])->name('reversements.index')->middleware('permission:reversements.read');
    Route::get('/balances', [App\Http\Controllers\ReversementController::class, 'balances'])->name('balances.index')->middleware('permission:reversements.read');
    Route::get('/historique-balances', [App\Http\Controllers\ReversementController::class, 'historique'])->name('historique.balances')->middleware('permission:reversements.read');

    // Routes de création (AVANT la route {id} pour éviter les conflits)
    Route::get('/reversements/create', [App\Http\Controllers\ReversementController::class, 'create'])->name('reversements.create')->middleware('permission:reversements.create');
    Route::post('/reversements', [App\Http\Controllers\ReversementController::class, 'store'])->name('reversements.store')->middleware('permission:reversements.create');

    // Route de consultation d'un reversement spécifique (APRÈS create)
    Route::get('/reversements/{id}', [App\Http\Controllers\ReversementController::class, 'show'])->name('reversements.show')->middleware('permission:reversements.read');

    // Routes de validation/annulation (mise à jour)
    Route::post('/reversements/{id}/validate', [App\Http\Controllers\ReversementController::class, 'validateReversement'])->name('reversements.validate')->middleware('permission:reversements.update');
    Route::post('/reversements/{id}/cancel', [App\Http\Controllers\ReversementController::class, 'cancelReversement'])->name('reversements.cancel')->middleware('permission:reversements.update');
});

// Routes des abonnements
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/change-plan', [App\Http\Controllers\SubscriptionController::class, 'changePlan'])->name('subscriptions.change-plan');
    Route::get('/subscriptions/payment/{plan_id}', [App\Http\Controllers\SubscriptionController::class, 'payment'])->name('subscriptions.payment');
    Route::post('/subscriptions/process-payment', [App\Http\Controllers\SubscriptionController::class, 'processPayment'])->name('subscriptions.process-payment');
    Route::get('/subscriptions/success', [App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::post('/subscriptions/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscriptions/{id}/activate', [App\Http\Controllers\SubscriptionController::class, 'activate'])->name('subscriptions.activate');
});

// Routes des notifications
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'settings'])->name('notifications.settings');
});

// API Routes pour les abonnements
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api/subscriptions/plans', [App\Http\Controllers\SubscriptionController::class, 'getPlans']);
    Route::get('/api/subscriptions/user', [App\Http\Controllers\SubscriptionController::class, 'getUserSubscription']);
});

// Routes de géolocalisation
Route::middleware(['auth', 'tenant', 'subscription:Premium'])->group(function () {
    Route::get('/location/admin-monitor', [App\Http\Controllers\LocationController::class, 'adminMonitor'])->name('location.admin-monitor');
});

// Routes pour les abonnements et mises à niveau
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/subscription/required', function () {
        return view('subscription.required', ['menu' => 'subscriptions']);
    })->name('subscription.required');

    Route::get('/subscription/upgrade', function () {
        return view('subscription.upgrade', ['menu' => 'subscriptions']);
    })->name('subscription.upgrade');

    // Documentation
    Route::get('/documentation', function () {
        return view('documentation.index', ['menu' => 'documentation']);
    })->name('documentation.index');
});
