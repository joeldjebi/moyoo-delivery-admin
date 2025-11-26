<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Entreprise;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] = 'Gestion des Utilisateurs';
        $data['menu'] = 'users';

        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasPermission('users.read')) {
            abort(403, 'Vous n\'avez pas les permissions pour voir les utilisateurs.');
        }

        // Super admin voit tous les utilisateurs (sauf ceux supprimés)
        if ($user->isSuperAdmin()) {
            $data['users'] = User::with('entreprise')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Les autres utilisateurs ne voient que ceux de leur entreprise (sauf ceux supprimés)
            $data['users'] = User::where('entreprise_id', $user->entreprise_id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('users.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Créer un Utilisateur';
        $data['menu'] = 'users';

        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasPermission('users.create')) {
            abort(403, 'Vous n\'avez pas les permissions pour créer des utilisateurs.');
        }

        // Super admin peut créer pour n'importe quelle entreprise
        if ($user->isSuperAdmin()) {
            $data['entreprises'] = Entreprise::active()->get();
        } else {
            // Les autres ne peuvent créer que pour leur entreprise
            $data['entreprises'] = collect([$user->entreprise]);
        }

        return view('users.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'mobile' => 'required|string|max:20|unique:users,mobile',
                'role' => 'required|in:admin,user,manager',
                'user_type' => 'required|in:entreprise_admin,entreprise_user',
                'status' => 'required|in:active,inactive',
                'custom_permissions' => 'array',
                'custom_permissions.*' => 'string'
            ];

            // Super admin peut choisir l'entreprise
            if ($user->isSuperAdmin()) {
                $rules['entreprise_id'] = 'required|exists:entreprises,id';
            }

            $validated = $request->validate($rules, [
                'first_name.required' => 'Le prénom est obligatoire.',
                'last_name.required' => 'Le nom est obligatoire.',
                'mobile.required' => 'Le numéro de téléphone est obligatoire.',
                'mobile.unique' => 'Ce numéro de téléphone est déjà utilisé.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'role.required' => 'Le rôle est obligatoire.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
                'user_type.required' => 'Le type d\'utilisateur est obligatoire.',
                'user_type.in' => 'Le type d\'utilisateur sélectionné n\'est pas valide.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut sélectionné n\'est pas valide.',
                'entreprise_id.required' => 'L\'entreprise est obligatoire.',
                'entreprise_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.'
            ]);

            // Définir l'entreprise_id selon le type d'utilisateur
            if (!$user->isSuperAdmin()) {
                $validated['entreprise_id'] = $user->entreprise_id;
            }

            // Générer un mot de passe numérique à 8 chiffres
            $generatedPassword = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $validated['password'] = Hash::make($generatedPassword);
            $validated['created_by'] = $user->id;

            // Gérer les permissions personnalisées
            $customPermissions = $request->input('custom_permissions', []);
            
            // Si aucune permission personnalisée n'est fournie, attribuer toutes les permissions
            // (pour les nouveaux utilisateurs créés par un admin)
            if (empty($customPermissions)) {
                $allAvailablePermissions = User::getAllAvailablePermissions();
                $customPermissions = array_keys($allAvailablePermissions);
            }
            
            $validated['permissions'] = $customPermissions;

            // Créer l'utilisateur
            $newUser = User::create($validated);

            // Envoyer le mot de passe via email
            $this->sendPasswordViaEmail($newUser, $generatedPassword);

            // Assigner automatiquement le plan Free au nouvel utilisateur (par défaut)
            try {
                $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                if ($freePlan) {
                    $newUser->assignSubscriptionPlan($freePlan->id, true);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur assignation plan Free au nouvel utilisateur', [
                    'user_id' => $newUser->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Utilisateur créé par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'new_user_id' => $newUser->id,
                'new_user_email' => $newUser->email,
                'generated_password' => $generatedPassword,
                'ip' => $request->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été créé avec succès. Le mot de passe a été envoyé par email.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création de l\'utilisateur. Veuillez réessayer.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $data['title'] = 'Détails de l\'Utilisateur';
        $data['menu'] = 'users';

        // Charger la relation entreprise
        $data['user'] = $user->load('entreprise');

        return view('users.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $data['title'] = 'Modifier l\'Utilisateur';
        $data['menu'] = 'users';
        $data['user'] = $user;

        return view('users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            // Validation des données
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|in:admin,user,manager',
                'status' => 'required|in:active,inactive',
                'custom_permissions' => 'array',
                'custom_permissions.*' => 'string'
            ], [
                'first_name.required' => 'Le prénom est obligatoire.',
                'last_name.required' => 'Le nom est obligatoire.',
                'mobile.required' => 'Le numéro de téléphone est obligatoire.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'role.required' => 'Le rôle est obligatoire.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut sélectionné n\'est pas valide.'
            ]);

            // Gérer les permissions personnalisées
            $customPermissions = $request->input('custom_permissions', []);

            // Mettre à jour l'utilisateur
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $customPermissions
            ]);

            Log::info('Utilisateur modifié par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été modifié avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la modification de l\'utilisateur. Veuillez réessayer.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Empêcher l'auto-suppression
            if ($user->id === Auth::id()) {
                return redirect()->back()
                    ->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            }

            $userEmail = $user->email;
            $user->delete();

            Log::info('Utilisateur supprimé par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'deleted_user_email' => $userEmail,
                'ip' => request()->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été supprimé avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la suppression de l\'utilisateur. Veuillez réessayer.']);
        }
    }

    /**
     * Restaurer un utilisateur supprimé
     */
    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            Log::info('Utilisateur restauré par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'restored_user_id' => $user->id,
                'restored_user_email' => $user->email,
                'ip' => request()->ip()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été restauré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration d\'un utilisateur: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la restauration de l\'utilisateur. Veuillez réessayer.']);
        }
    }

    /**
     * Changer le mot de passe d'un utilisateur
     */
    public function changePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
            ], [
                'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
                'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('Mot de passe utilisateur modifié par un admin', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Le mot de passe a été modifié avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de mot de passe: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors du changement de mot de passe. Veuillez réessayer.']);
        }
    }

    /**
     * Envoyer le mot de passe généré via email
     */
    private function sendPasswordViaEmail($user, $password)
    {
        try {
            $toEmail = $user->email;
            $toName = $user->first_name . ' ' . $user->last_name;
            $subject = 'Vos identifiants de connexion - Plateforme MOYOO';

            // Contenu texte
            $textPart = "Bonjour {$toName},\n\n";
            $textPart .= "Votre compte a été créé avec succès sur la plateforme MOYOO.\n\n";
            $textPart .= "Vos identifiants de connexion :\n";
            $textPart .= "Email : {$user->email}\n";
            $textPart .= "Mot de passe : {$password}\n\n";
            $textPart .= "Vous pouvez vous connecter à l'adresse : " . url('/login') . "\n\n";
            $textPart .= "Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.\n\n";
            $textPart .= "Cordialement,\nL'équipe MOYOO";

            // Contenu HTML
            $htmlPart = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Vos identifiants de connexion</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background-color: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                    .credentials { background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff; }
                    .btn { display: inline-block; background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                    .btn:hover { background-color: #0056b3; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                    .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>Bienvenue sur MOYOO</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$toName}</strong>,</p>

                    <p>Votre compte a été créé avec succès sur la plateforme MOYOO.</p>

                    <div class='credentials'>
                        <h3>Vos identifiants de connexion :</h3>
                        <p><strong>Email :</strong> {$user->email}</p>
                        <p><strong>Mot de passe :</strong> <code style='background-color: #e9ecef; padding: 4px 8px; border-radius: 4px;'>{$password}</code></p>
                    </div>

                    <div style='text-align: center;'>
                        <a href='" . url('/login') . "' class='btn'>Se connecter maintenant</a>
                    </div>

                    <div class='warning'>
                        <strong>⚠️ Recommandation de sécurité :</strong><br>
                        Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.
                    </div>

                    <p>Vous pouvez maintenant accéder à votre espace personnel et commencer à utiliser nos services.</p>
                </div>
                <div class='footer'>
                    <p>Cordialement,<br><strong>L'équipe MOYOO</strong></p>
                    <p><small>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</small></p>
                </div>
            </body>
            </html>";

            // Utiliser la fonction Mailjet existante
            $success = $this->sendMailjetEmail($toEmail, $toName, $subject, $textPart, $htmlPart);

            if ($success) {
                Log::info('Mot de passe envoyé via email avec succès', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'password_generated' => true
                ]);
            } else {
                Log::error('Échec de l\'envoi du mot de passe via email', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mot de passe via email', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer un email via Mailjet
     */
    private function sendMailjetEmail($toEmail, $toName, $subject, $textPart, $htmlPart)
    {
        $apiKeyPublic = config('mailjet.api_key_public');
        $apiKeyPrivate = config('mailjet.api_key_private');
        $senderEmail = config('mailjet.default_from.email');
        $senderName = config('mailjet.default_from.name');
        $apiUrl = config('mailjet.api_url');

        if (!$apiKeyPublic || !$apiKeyPrivate || !$senderEmail) {
            Log::error('Configuration Mailjet manquante');
            return false;
        }

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
                    'TextPart' => $textPart,
                    'HTMLPart' => $htmlPart
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
            Log::error('Erreur cURL Mailjet: ' . $error);
            return false;
        }

        if ($httpCode !== 200) {
            Log::error('Erreur API Mailjet', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return false;
        }

        Log::info('Email envoyé avec succès via Mailjet', [
            'to' => $toEmail,
            'subject' => $subject
        ]);

        return true;
    }

}
