<?php

namespace App\Http\Controllers;

use App\Models\Livreur;
use App\Models\Engin;
use App\Models\Zone;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LivreurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Gestion des Livreurs';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreurs'] = Livreur::where('entreprise_id', $data['user']->entreprise_id)
            ->with(['engin.typeEngin', 'zoneActivite', 'communes'])
                ->orderBy('first_name')
                ->paginate(15);

            return view('livreurs.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la liste des livreurs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la liste des livreurs.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Ajouter un Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['engins'] = Engin::where('status', 'actif')
                ->with('typeEngin')
                ->orderBy('libelle')
                ->get();

            $data['communes'] = Commune::orderBy('libelle')
                ->get();

            return view('livreurs.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
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

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20|unique:livreurs,mobile',
                'email' => 'nullable|email|max:255|unique:livreurs,email',
                'engin_id' => 'required|exists:engins,id',
                'zone_activite_id' => 'nullable|exists:communes,id',
                'communes' => 'nullable|array',
                'communes.*' => 'exists:communes,id',
                'permis' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:500',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            // Générer un mot de passe aléatoire de 8 chiffres
            $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

            Log::info('Génération du mot de passe pour le livreur', [
                'user_id' => $user->id,
                'livreur_name' => $request->first_name . ' ' . $request->last_name,
                'mobile' => $request->mobile,
                'ip' => $request->ip()
            ]);

            // Gérer l'upload de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('livreurs', 'public');
            }

            // Nettoyer le numéro de téléphone (supprimer les espaces)
            $cleanMobile = str_replace(' ', '', $request->mobile);
            $fullMobile = '225' . $cleanMobile;

            // Récupérer les livreurs pour la planification
            $entrepriseId = auth()->user()->entreprise_id;
            if (!$entrepriseId) {
                $entreprise = Entreprise::where('created_by', auth()->id())->first();
                $entrepriseId = $entreprise ? $entreprise->id : null;
            }

            $livreur = Livreur::create([
                'entreprise_id' => $entrepriseId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $fullMobile,
                'email' => $request->email,
                'engin_id' => $request->engin_id,
                'zone_activite_id' => $request->zone_activite_id,
                'permis' => $request->permis,
                'adresse' => $request->adresse,
                'password' => Hash::make($password),
                'photo' => $photoPath,
                'status' => 'actif',
                'created_by' => $user->id
            ]);

            // Attacher les communes sélectionnées
            if ($request->has('communes') && is_array($request->communes)) {
                $livreur->communes()->attach($request->communes);
            }

            // Générer le message WhatsApp
            $message = $this->generateLivreurAccessMessage($livreur, $password, $fullMobile);

            Log::info('Envoi des accès par WhatsApp', [
                'livreur_id' => $livreur->id,
                'mobile' => $fullMobile,
                'app_url' => env('MOYOO_LIVREUR_APP_URL', 'https://bit.ly/moyoo-livreur-app'),
                'entreprise_name' => $user->entreprise ? $user->entreprise->name : 'MOYOO',
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'password' => $password
            ]);

            $whatsappResult = $this->sendWhatsAppMessageInternal($fullMobile, $message);

            if ($whatsappResult['success']) {
                Log::info('Message WhatsApp envoyé avec succès', [
                    'livreur_id' => $livreur->id,
                    'mobile' => $fullMobile,
                    'response' => $whatsappResult['response']
                ]);
            } else {
                Log::warning('Échec de l\'envoi du message WhatsApp', [
                    'livreur_id' => $livreur->id,
                    'mobile' => $fullMobile,
                    'error' => $whatsappResult['error'] ?? 'Erreur inconnue',
                    'response' => $whatsappResult['response'] ?? null
                ]);
            }

            // Envoyer un email si l'email est renseigné
            $emailResult = ['success' => false];
            if ($request->email) {
                $emailMessage = $this->generateLivreurAccessEmailMessage($livreur, $password, $fullMobile);
                $emailResult = $this->sendLivreurAccessEmail($livreur, $emailMessage);

                if ($emailResult['success']) {
                    Log::info('Email envoyé avec succès', [
                        'livreur_id' => $livreur->id,
                        'email' => $request->email
                    ]);
                } else {
                    Log::warning('Échec de l\'envoi de l\'email', [
                        'livreur_id' => $livreur->id,
                        'email' => $request->email,
                        'error' => $emailResult['error'] ?? 'Erreur inconnue'
                    ]);
                }
            }

            DB::commit();

            Log::info('Livreur créé avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'mobile' => $fullMobile,
                'email' => $livreur->email,
                'password_generated' => true,
                'whatsapp_sent' => $whatsappResult['success'],
                'email_sent' => $request->email ? $emailResult['success'] : false,
                'user_id' => $user->id
            ]);

            $successMessage = 'Livreur créé avec succès !';
            $messagesSent = [];

            if ($whatsappResult['success']) {
                $messagesSent[] = 'WhatsApp';
            }

            if ($request->email && $emailResult['success']) {
                $messagesSent[] = 'email';
            }

            if (!empty($messagesSent)) {
                $successMessage .= ' Les accès ont été envoyés par ' . implode(' et ', $messagesSent) . '.';
            } else {
                if (!$whatsappResult['success'] && (!$request->email || !$emailResult['success'])) {
                    $successMessage .= ' Attention : L\'envoi des accès a échoué.';
                } elseif (!$whatsappResult['success']) {
                    $successMessage .= ' Attention : L\'envoi WhatsApp a échoué.';
                } elseif ($request->email && !$emailResult['success']) {
                    $successMessage .= ' Attention : L\'envoi email a échoué.';
                }
            }

            return redirect()->route('livreurs.index')
                ->with('success', $successMessage);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du livreur.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Détails du Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur->load(['engin.typeEngin', 'zoneActivite', 'communes']);

            return view('livreurs.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des détails du livreur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des détails du livreur.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Modifier le Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur->load('communes');
            $data['engins'] = Engin::where('status', 'actif')
                ->with('typeEngin')
                ->orderBy('libelle')
                ->get();

            $data['communes'] = Commune::orderBy('libelle')
                ->get();

            return view('livreurs.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20|unique:livreurs,mobile,' . $livreur->id,
                'email' => 'nullable|email|max:255|unique:livreurs,email,' . $livreur->id,
                'engin_id' => 'required|exists:engins,id',
                'communes' => 'nullable|array',
                'communes.*' => 'exists:communes,id',
                'permis' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:500',
                'status' => 'required|in:actif,inactif',
                'password' => 'nullable|string|min:8|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'engin_id' => $request->engin_id,
                'permis' => $request->permis,
                'adresse' => $request->adresse,
                'status' => $request->status,
                'updated_by' => $user->id
            ];

            // Mettre à jour le mot de passe si fourni
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Gérer l'upload de la nouvelle photo
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $updateData['photo'] = $photo->store('livreurs', 'public');
            }

            $livreur->update($updateData);

            // Mettre à jour les communes
            if ($request->has('communes')) {
                $livreur->communes()->sync($request->communes ?? []);
            }

            DB::commit();

            Log::info('Livreur mis à jour avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'user_id' => $user->id
            ]);

            return redirect()->route('livreurs.index')
                ->with('success', 'Livreur mis à jour avec succès !');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du livreur.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier si le livreur a des colis assignés
            $colisCount = $livreur->colis()->count();
            if ($colisCount > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce livreur car il a ' . $colisCount . ' colis assigné(s).');
            }

            DB::beginTransaction();

            $livreur->update([
                'deleted_by' => $user->id
            ]);
            $livreur->delete();

            DB::commit();

            Log::info('Livreur supprimé avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'user_id' => $user->id
            ]);

            return redirect()->route('livreurs.index')
                ->with('success', 'Livreur supprimé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du livreur.');
        }
    }

    /**
     * Afficher l'historique des colis d'un livreur
     */
    public function colisHistory(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Colis du Livreur - ' . $livreur->first_name . ' ' . $livreur->last_name;

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur;
            $data['colis'] = $livreur->colis()
                ->with(['zone', 'commune', 'engin', 'packageColis'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('livreurs.colis', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de l\'historique des colis: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de l\'historique des colis.');
        }
    }

    /**
     * Afficher l'historique des livraisons d'un livreur
     */
    public function livraisonsHistory(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Livraisons du Livreur - ' . $livreur->first_name . ' ' . $livreur->last_name;

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur;
            $data['livraisons'] = $livreur->livraisons()
                ->with(['colis.zone', 'colis.commune', 'marchand', 'boutique'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('livreurs.livraisons', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de l\'historique des livraisons: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de l\'historique des livraisons.');
        }
    }

    /**
     * Rechercher des livreurs
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $status = $request->get('status');

            $livreurs = Livreur::with(['engin.typeEngin', 'communes'])
                ->when($query, function($q) use ($query) {
                    $q->where(function($subQ) use ($query) {
                        $subQ->where('first_name', 'like', "%{$query}%")
                             ->orWhere('last_name', 'like', "%{$query}%")
                             ->orWhere('mobile', 'like', "%{$query}%")
                             ->orWhere('email', 'like', "%{$query}%");
                    });
                })
                ->when($status, function($q) use ($status) {
                    $q->where('status', $status);
                })
                ->orderBy('first_name')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $livreurs
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de livreurs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Basculer le statut d'un livreur
     */
    public function toggleStatus(Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $newStatus = $livreur->status === 'actif' ? 'inactif' : 'actif';
            $livreur->update([
                'status' => $newStatus,
                'updated_by' => $user->id
            ]);

            Log::info('Statut du livreur modifié', [
                'livreur_id' => $livreur->id,
                'ancien_statut' => $livreur->status,
                'nouveau_statut' => $newStatus,
                'user_id' => $user->id
            ]);

            return redirect()->back()
                ->with('success', 'Statut du livreur mis à jour avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut.');
        }
    }

    /**
     * Générer le message WhatsApp pour les accès livreur
     */
    private function generateLivreurAccessMessage($livreur, $password, $fullMobile)
    {
        $appUrl = env('MOYOO_LIVREUR_APP_URL', 'https://bit.ly/moyoo-livreur-app');

        // Récupérer le nom de l'entreprise de l'utilisateur qui crée le livreur
        $user = Auth::user();
        $entrepriseName = $user->entreprise ? $user->entreprise->name : 'MOYOO';

        $message = "Bonjour {$livreur->first_name} {$livreur->last_name},\n\n";
        $message .= "Votre compte livreur MOYOO a été créé avec succès !\n\n";
        $message .= "Vos identifiants de connexion :\n";
        $message .= "📱 Téléphone : {$fullMobile}\n";
        $message .= "🔑 Mot de passe : {$password}\n\n";
        $message .= "📲 Téléchargez l'application MOYOO :\n";
        $message .= "🔗 {$appUrl}\n\n";
        $message .= "Vous pouvez maintenant vous connecter à l'application MOYOO.\n\n";
        $message .= "Cordialement,\nL'équipe {$entrepriseName}";

        return $message;
    }

    /**
     * Envoyer un message WhatsApp via l'API Wassenger
     */
    private function sendWhatsAppMessageInternal($phone, $message)
    {
        // Configuration de l'API Wassenger
        $apiUrl = env('WASSENGER_API_URL');
        $token = env('WASSENGER_TOKEN');

        // Données à envoyer
        $data = [
            'phone' => $phone,
            'message' => $message
        ];

        // Initialisation de cURL
        $curl = curl_init();

        // Configuration des options cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Token: ' . $token
            ],
        ]);

        // Exécution de la requête
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        // Fermeture de cURL
        curl_close($curl);

        // Retour de la réponse
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
            ];
        }

        $responseData = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $responseData,
        ];
    }

    /**
     * Générer le message d'accès pour l'email
     */
    private function generateLivreurAccessEmailMessage($livreur, $password, $fullMobile)
    {
        $appUrl = env('MOYOO_LIVREUR_APP_URL', 'https://bit.ly/moyoo-livreur-app');
        $user = Auth::user();
        $entrepriseName = $user->entreprise ? $user->entreprise->name : 'MOYOO';

        // Version texte
        $textPart = "Bonjour {$livreur->first_name} {$livreur->last_name},\n\n";
        $textPart .= "Votre compte livreur MOYOO a été créé avec succès !\n\n";
        $textPart .= "Vos identifiants de connexion :\n";
        $textPart .= "Téléphone : {$fullMobile}\n";
        $textPart .= "Mot de passe : {$password}\n\n";
        $textPart .= "Téléchargez l'application MOYOO :\n";
        $textPart .= "{$appUrl}\n\n";
        $textPart .= "Vous pouvez maintenant vous connecter à l'application MOYOO.\n\n";
        $textPart .= "Cordialement,\nL'équipe {$entrepriseName}";

        // Version HTML
        $htmlPart = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                .credentials { background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4CAF50; }
                .credential-item { margin: 10px 0; }
                .credential-label { font-weight: bold; color: #555; }
                .credential-value { color: #333; font-size: 16px; }
                .app-link { background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenue sur MOYOO !</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$livreur->first_name} {$livreur->last_name}</strong>,</p>

                    <p>Votre compte livreur MOYOO a été créé avec succès !</p>

                    <div class='credentials'>
                        <h3 style='margin-top: 0; color: #4CAF50;'>Vos identifiants de connexion :</h3>
                        <div class='credential-item'>
                            <span class='credential-label'>📱 Téléphone :</span>
                            <span class='credential-value'>{$fullMobile}</span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label'>🔑 Mot de passe :</span>
                            <span class='credential-value'><strong>{$password}</strong></span>
                        </div>
                    </div>

                    <p style='text-align: center;'>
                        <a href='{$appUrl}' class='app-link' target='_blank'>📲 Télécharger l'application MOYOO</a>
                    </p>

                    <p>Vous pouvez maintenant vous connecter à l'application MOYOO avec vos identifiants.</p>

                    <p>Cordialement,<br>L'équipe <strong>{$entrepriseName}</strong></p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";

        return [
            'text' => $textPart,
            'html' => $htmlPart
        ];
    }

    /**
     * Envoyer un email avec les accès du livreur via Mailjet
     */
    private function sendLivreurAccessEmail($livreur, $emailMessage)
    {
        $apiKeyPublic = config('mailjet.api_key_public');
        $apiKeyPrivate = config('mailjet.api_key_private');
        $senderEmail = config('mailjet.default_from.email');
        $senderName = config('mailjet.default_from.name');
        $apiUrl = config('mailjet.api_url');

        if (!$apiKeyPublic || !$apiKeyPrivate || !$senderEmail) {
            Log::error('Configuration Mailjet manquante pour l\'envoi d\'email au livreur');
            return [
                'success' => false,
                'error' => 'Configuration Mailjet manquante'
            ];
        }

        $subject = 'Bienvenue sur MOYOO - Vos identifiants de connexion';
        $toName = $livreur->first_name . ' ' . $livreur->last_name;
        $toEmail = $livreur->email;

        $data = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $senderEmail,
                        'Name' => $senderName
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => $emailMessage['text'],
                    'HTMLPart' => $emailMessage['html']
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($apiKeyPublic . ':' . $apiKeyPrivate)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            Log::error('Erreur cURL Mailjet pour livreur: ' . $error);
            return [
                'success' => false,
                'error' => $error
            ];
        }

        if ($httpCode !== 200) {
            Log::error('Erreur Mailjet pour livreur - Code HTTP: ' . $httpCode . ' - Réponse: ' . $response);
            return [
                'success' => false,
                'error' => 'Erreur HTTP ' . $httpCode,
                'response' => $response
            ];
        }

        $responseData = json_decode($response, true);

        return [
            'success' => true,
            'response' => $responseData
        ];
    }
}
