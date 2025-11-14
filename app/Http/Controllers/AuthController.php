<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\EmailVerification;
use App\Models\SubscriptionPlan;
use App\Models\Entreprise;
use App\Models\SubscriptionHistory;
use App\Models\PricingPlan;
use App\Models\Faq;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showLogin()
    {
        $data['title'] = 'Connexion';
        $data['menu'] = 'login';

        return view('auth.login', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function showRegister()
    {
        $data['title'] = 'Inscription';
        $data['menu'] = 'register';

        return view('auth.register', $data);
    }
    /**
     * Show the form for password forget.
     */
    public function showPasswordForget()
    {
        $data['title'] = 'Mot de passe oublié';
        $data['menu'] = 'password-forget';

        return view('auth.password-forget', $data);
    }
    /**
     * Show the form for reset password.
     */
    public function showPasswordReset()
    {
        $data['title'] = 'Réinitialisation du mot de passe';
        $data['menu'] = 'reset-password';

        return view('auth.reset-password-forget', $data);
    }

    /**
     * Show the OTP verification form
     */
    public function showVerifyOTP()
    {
        $data['title'] = 'Vérification du code OTP';
        $data['menu'] = 'verify-otp';
        // Fallbacks: session -> query string -> old input
        $emailFromSession = session('email');
        $emailFromQuery = request()->query('email');
        $emailFromOld = old('email');

        $data['email'] = $emailFromSession ?: $emailFromQuery ?: $emailFromOld;

        if (!$data['email']) {
            return redirect()->route('auth.register')
                ->withErrors(['error' => 'Aucune demande de vérification trouvée.']);
        }

        return view('auth.verify-otp', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function loginUser(Request $request)
    {
        // Log du début de la tentative de connexion
        Log::info('Tentative de connexion', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $request->boolean('remember')
        ]);

        // Limitation du taux de tentatives
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('Tentative de connexion bloquée - Trop de tentatives', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Log de la validation réussie
        Log::info('Validation des données de connexion réussie', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Vérifier le statut de l'entreprise
            // Essayer d'abord avec entreprise_id, puis avec created_by
            $entreprise = null;
            if ($user->entreprise_id) {
                $entreprise = Entreprise::find($user->entreprise_id);
            }

            // Si pas d'entreprise via entreprise_id, essayer avec created_by
            if (!$entreprise) {
                $entreprise = Entreprise::getEntrepriseByUser($user->id);
            }

            // Si l'utilisateur a une entreprise, vérifier son statut
            if ($entreprise) {
                // Vérifier si l'entreprise est active (statut = 1)
                if ((int)$entreprise->statut !== 1) {
                    // Déconnecter l'utilisateur
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Incrémenter le compteur de tentatives
                    RateLimiter::hit($key, 300);

                    Log::warning('Tentative de connexion bloquée - Entreprise inactive', [
                        'email' => $request->email,
                        'entreprise_id' => $entreprise->id,
                        'statut' => $entreprise->statut
                    ]);

                    throw ValidationException::withMessages([
                        'email' => ['Votre compte entreprise est inactif. Veuillez contacter l\'administrateur pour plus d\'informations.'],
                    ]);
                }
            }

            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Redirection si les informations d'entreprise doivent être mises à jour
            if ($entreprise && (int)($entreprise->not_update) === 0) {
                return redirect()->intended(route('entreprise.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        // Incrémenter le compteur de tentatives
        RateLimiter::hit($key, 300); // 5 minutes

        // Message d'erreur générique pour éviter la fuite d'informations
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function registerUser(Request $request)
    {
        // Log du début de l'inscription
        Log::info('Début du processus d\'inscription', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['password', 'password_confirmation'])
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'mobile' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:15',
            'password' => 'required|min:8|confirmed',
        ], [
            'mobile.regex' => 'Le format du numéro de téléphone est invalide.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'mobile.required' => 'Le numéro de téléphone est obligatoire.',
            'mobile.regex' => 'Le format du numéro de téléphone est invalide.',
            'mobile.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
            'mobile.max' => 'Le numéro de téléphone doit contenir au plus 15 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Log de la validation réussie
        Log::info('Validation des données d\'inscription réussie', [
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'ip' => $request->ip()
        ]);

        try {
            // Préparer les données utilisateur
            $userData = [
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'email' => strtolower(trim($request->email)),
                'mobile' => trim($request->mobile),
                'password' => Hash::make($request->password),
            ];

            // Créer la vérification OTP
            Log::info('Création de la vérification OTP', [
                'email' => $userData['email'],
                'ip' => $request->ip()
            ]);

            $verification = EmailVerification::createVerification($userData['email'], $userData);

            // Stocker l'email en session pour la page OTP
            session()->put('email', $userData['email']);

            // Envoyer l'OTP par email
            Log::info('Envoi de l\'OTP par email', [
                'email' => $userData['email'],
                'first_name' => $userData['first_name'],
                'ip' => $request->ip()
            ]);

            $this->sendOTPEmail($userData['email'], $userData['first_name'], $verification->otp);

            // Log de la demande d'inscription
            Log::info('OTP envoyé avec succès - Inscription en attente de vérification', [
                'email' => $userData['email'],
                'verification_id' => $verification->id,
                'ip' => $request->ip()
            ]);

            // Rediriger en passant aussi l'email dans la query pour fallback
            return redirect()->route('auth.verify-otp', ['email' => $userData['email']])
                ->with('success', 'Un code de vérification a été envoyé à votre adresse email.');

        } catch (\Exception $e) {
            Log::error('Registration OTP failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'envoi du code de vérification. Veuillez réessayer.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Logout user
     */
    // public function logout(Request $request)
    // {
    //     Auth::logout();
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return redirect()->route('auth.login')
    //         ->with('success', 'Vous avez été déconnecté avec succès.');
    // }

    /**
     * Envoyer un email avec Mailjet
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

    /**
     * Envoyer un email de confirmation d'inscription
     */
    private function sendWelcomeEmail($user)
    {
        $template = config('mailjet.templates.welcome');
        $subject = $template['subject'];
        $textPart = "Bonjour {$user->first_name},\n\nBienvenue sur MOYOO fleet ! Votre compte a été créé avec succès.\n\nVous pouvez maintenant vous connecter à votre espace administrateur.\n\nCordialement,\nL'équipe MOYOO";

        $htmlPart = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Bienvenue sur MOYOO fleet</h2>
            <p>Bonjour <strong>{$user->first_name}</strong>,</p>
            <p>Bienvenue sur MOYOO fleet ! Votre compte a été créé avec succès.</p>
            <p>Vous pouvez maintenant vous connecter à votre espace administrateur.</p>
            <div style='margin: 30px 0;'>
                <a href='" . route('auth.login') . "' style='background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Se connecter</a>
            </div>
            <p>Cordialement,<br>L'équipe MOYOO</p>
        </div>
        ";

        return $this->sendMailjetEmail(
            $user->email,
            $user->first_name . ' ' . $user->last_name,
            $subject,
            $textPart,
            $htmlPart
        );
    }

    /**
     * Envoyer un email avec code OTP
     */
    private function sendOTPEmail($email, $firstName, $otp)
    {
        $subject = 'Code de vérification - MOYOO fleet';
        $textPart = "Bonjour {$firstName},\n\nVotre code de vérification pour finaliser votre inscription est : {$otp}\n\nCe code est valide pendant 10 minutes.\n\nSi vous n'avez pas demandé cette inscription, ignorez cet email.\n\nCordialement,\nL'équipe MOYOO";

        $htmlPart = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Code de vérification</h2>
            <p>Bonjour <strong>{$firstName}</strong>,</p>
            <p>Votre code de vérification pour finaliser votre inscription est :</p>
            <div style='margin: 30px 0; text-align: center;'>
                <div style='background-color: #f8f9fa; border: 2px dashed #dee2e6; padding: 20px; border-radius: 10px; display: inline-block;'>
                    <span style='font-size: 32px; font-weight: bold; color: #2c3e50; letter-spacing: 5px;'>{$otp}</span>
                </div>
            </div>
            <p><small>Ce code est valide pendant 10 minutes.</small></p>
            <p>Si vous n'avez pas demandé cette inscription, ignorez cet email.</p>
            <p>Cordialement,<br>L'équipe MOYOO</p>
        </div>
        ";

        return $this->sendMailjetEmail(
            $email,
            $firstName,
            $subject,
            $textPart,
            $htmlPart
        );
    }

    /**
     * Envoyer un email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'Aucun compte trouvé avec cette adresse email.']);
        }

        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));

        // Stocker le token en session (ou en base de données pour plus de sécurité)
        session(['password_reset_token' => $token, 'password_reset_email' => $user->email]);

        $resetUrl = route('auth.reset-password') . '?token=' . $token;

        $template = config('mailjet.templates.password_reset');
        $subject = $template['subject'];
        $textPart = "Bonjour {$user->first_name},\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nCliquez sur le lien suivant pour réinitialiser votre mot de passe :\n{$resetUrl}\n\nCe lien est valide pendant 1 heure.\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\nCordialement,\nL'équipe MOYOO";

        $htmlPart = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Réinitialisation de votre mot de passe</h2>
            <p>Bonjour <strong>{$user->first_name}</strong>,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p>Cliquez sur le bouton suivant pour réinitialiser votre mot de passe :</p>
            <div style='margin: 30px 0;'>
                <a href='{$resetUrl}' style='background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Réinitialiser mon mot de passe</a>
            </div>
            <p><small>Ce lien est valide pendant 1 heure.</small></p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <p>Cordialement,<br>L'équipe MOYOO</p>
        </div>
        ";

        $emailSent = $this->sendMailjetEmail(
            $user->email,
            $user->first_name . ' ' . $user->last_name,
            $subject,
            $textPart,
            $htmlPart
        );

        if ($emailSent) {
            return redirect()->back()->with('success', 'Un email de réinitialisation a été envoyé à votre adresse email.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.']);
        }
    }

    /**
     * Traiter la réinitialisation du mot de passe
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
        ]);

        // Vérifier le token
        $storedToken = session('password_reset_token');
        $storedEmail = session('password_reset_email');

        if (!$storedToken || !$storedEmail || $storedToken !== $request->token || $storedEmail !== $request->email) {
            return redirect()->back()->withErrors(['error' => 'Token invalide ou expiré.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'Utilisateur non trouvé.']);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Supprimer le token de la session
        session()->forget(['password_reset_token', 'password_reset_email']);

        // Log de la réinitialisation
        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('auth.login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Vérifier l'OTP et finaliser l'inscription
     */
    public function verifyOTP(Request $request)
    {
        // Log du début de la vérification OTP
        Log::info('Début de la vérification OTP', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $request->validate([
            'otp' => 'required|string|size:6',
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $otp = $request->otp;

        // Trouver la vérification
        $verification = EmailVerification::where('email', $email)
            ->notExpired()
            ->notVerified()
            ->first();

        if (!$verification) {
            Log::warning('Vérification OTP échouée - Code invalide ou expiré', [
                'email' => $email,
                'ip' => $request->ip()
            ]);
            return redirect()->back()
                ->withErrors(['otp' => 'Code de vérification invalide ou expiré.'])
                ->withInput(['email' => $email]);
        }

        if (!$verification->isValid($otp)) {
            Log::warning('Vérification OTP échouée - Code incorrect', [
                'email' => $email,
                'otp_provided' => $otp,
                'ip' => $request->ip()
            ]);
            return redirect()->back()
                ->withErrors(['otp' => 'Code de vérification incorrect.'])
                ->withInput(['email' => $email]);
        }

        try {
            // Log de la validation OTP réussie
            Log::info('Validation OTP réussie - Création du compte en cours', [
                'email' => $email,
                'verification_id' => $verification->id,
                'ip' => $request->ip()
            ]);

            // Créer l'utilisateur avec les données stockées
            $userData = $verification->user_data;
            Log::info('Données utilisateur préparées pour la création', [
                'email' => $userData['email'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'mobile' => $userData['mobile'],
                'ip' => $request->ip()
            ]);

            $user = User::create($userData);

            // Créer une entreprise par défaut pour l'utilisateur
            Log::info('Création d\'une entreprise par défaut pour l\'utilisateur', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            // Vérifier s'il y a des communes disponibles
            $commune = \DB::table('communes')->first();
            if (!$commune) {
                // Créer la ville Abidjan si nécessaire
                $ville = \DB::table('villes')->where('libelle', 'Abidjan')->first();
                if (!$ville) {
                    $villeId = \DB::table('villes')->insertGetId([
                        'libelle' => 'Abidjan',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $villeId = $ville->id;
                }

                // Créer une commune par défaut (ex: Abobo) liée à Abidjan
                $communeId = \DB::table('communes')->insertGetId([
                    'libelle' => 'Abobo',
                    'ville_id' => $villeId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $communeId = $commune->id;
            }

            // Assurer l'unicité du mobile pour entreprises.mobile
            $entrepriseMobile = $user->mobile;
            if (\DB::table('entreprises')->where('mobile', $entrepriseMobile)->exists()) {
                $entrepriseMobile = $entrepriseMobile . '-' . substr((string) $user->id, -4);
            }

            $entreprise = Entreprise::create([
                'name' => $user->first_name . ' ' . $user->last_name . ' - Entreprise',
                'mobile' => $entrepriseMobile,
                'email' => $user->email,
                'adresse' => 'Adresse à définir',
                'commune_id' => $communeId,
                'statut' => 1, // 1 = actif
                'created_by' => $user->id
            ]);

            // Associer l'entreprise à l'utilisateur
            $user->update(['entreprise_id' => $entreprise->id]);

            Log::info('Entreprise créée et associée à l\'utilisateur', [
                'user_id' => $user->id,
                'entreprise_id' => $entreprise->id,
                'entreprise_name' => $entreprise->name,
                'ip' => $request->ip()
            ]);

            // Appeler le bootstrap tenant
            try {
                Log::info('Tenant bootstrap start', [
                    'entreprise_id' => $entreprise->id,
                    'user_id' => $user->id
                ]);

                $bootstrapService = app(\App\Services\TenantBootstrapService::class);
                $bootstrapService->bootstrapEntreprise($entreprise->id, $user->id);

                // Vérification finale après bootstrap
                $bootstrapVerification = $bootstrapService->verifyBootstrap($entreprise->id, $user->id);

                if (!$bootstrapVerification['success']) {
                    Log::warning('Bootstrap incomplet après création compte', [
                        'entreprise_id' => $entreprise->id,
                        'missing' => $bootstrapVerification['missing'],
                        'passed' => $bootstrapVerification['passed_checks'],
                        'total' => $bootstrapVerification['total_checks']
                    ]);

                    // Tenter une dernière réparation
                    $bootstrapService->repairMissingData($entreprise->id, $user->id, $bootstrapVerification['missing']);

                    // Vérifier à nouveau
                    $finalVerification = $bootstrapService->verifyBootstrap($entreprise->id, $user->id);
                    if ($finalVerification['success']) {
                        Log::info('Bootstrap complété après réparation finale', [
                            'entreprise_id' => $entreprise->id
                        ]);
                    } else {
                        Log::error('Bootstrap toujours incomplet après réparation', [
                            'entreprise_id' => $entreprise->id,
                            'still_missing' => $finalVerification['missing']
                        ]);
                    }
                } else {
                    Log::info('Bootstrap complété avec succès', [
                        'entreprise_id' => $entreprise->id,
                        'checks_passed' => $bootstrapVerification['passed_checks'],
                        'total_checks' => $bootstrapVerification['total_checks']
                    ]);
                }

                Log::info('Tenant bootstrap end', [
                    'entreprise_id' => $entreprise->id
                ]);
            } catch (\Throwable $tb) {
                Log::error('Tenant bootstrap failed', [
                    'error' => $tb->getMessage(),
                    'trace' => $tb->getTraceAsString(),
                    'entreprise_id' => $entreprise->id
                ]);

                // Tenter une réparation d'urgence
                try {
                    $bootstrapService = app(\App\Services\TenantBootstrapService::class);
                    $bootstrapVerification = $bootstrapService->verifyBootstrap($entreprise->id, $user->id);
                    if (!$bootstrapVerification['success']) {
                        $bootstrapService->repairMissingData($entreprise->id, $user->id, $bootstrapVerification['missing']);
                        Log::info('Réparation d\'urgence effectuée après échec bootstrap', [
                            'entreprise_id' => $entreprise->id
                        ]);
                    }
                } catch (\Throwable $repairError) {
                    Log::error('Réparation d\'urgence échouée', [
                        'entreprise_id' => $entreprise->id,
                        'error' => $repairError->getMessage()
                    ]);
                }
            }

            // Fallback: si les permissions de rôle ne sont pas seedées, les insérer maintenant
            try {
                if (\App\Models\RolePermission::where('entreprise_id', $entreprise->id)->count() === 0) {
                    Artisan::call('seed:role-permissions', ['--entreprise_id' => $entreprise->id]);
                    Log::info('Role permissions seeded via fallback for entreprise', [
                        'entreprise_id' => $entreprise->id
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Fallback seeding role_permissions failed', [
                    'entreprise_id' => $entreprise->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Accorder TOUS les droits au nouvel utilisateur (première création)
            try {
                // Récupérer toutes les permissions disponibles dans le système
                $allAvailablePermissions = \App\Models\User::getAllAvailablePermissions();
                $allPermissions = array_keys($allAvailablePermissions);

                // Attribuer toutes les permissions au nouvel utilisateur
                $user->update([
                    'role' => 'admin',
                    'user_type' => 'entreprise_admin',
                    'permissions' => $allPermissions, // Toutes les permissions disponibles
                ]);

                Log::info('Toutes les permissions attribuées au nouvel utilisateur (première création)', [
                    'user_id' => $user->id,
                    'entreprise_id' => $entreprise->id,
                    'permissions_count' => count($allPermissions),
                    'permissions' => $allPermissions
                ]);
            } catch (\Throwable $e) {
                Log::warning('Attribution des permissions au nouvel utilisateur échouée', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Marquer la vérification comme validée
            $verification->markAsVerified();

            // Envoyer l'email de bienvenue
            Log::info('Envoi de l\'email de bienvenue', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            $this->sendWelcomeEmail($user);

            // Log de l'inscription réussie
            Log::info('Inscription complétée avec succès - Compte créé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'mobile' => $user->mobile,
                'entreprise_id' => $user->entreprise_id,
                'created_at' => $user->created_at,
                'ip' => $request->ip()
            ]);

            // Nettoyer la session
            session()->forget('email');

            return redirect()->route('auth.login')
                ->with('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            Log::error('User creation failed after OTP verification', [
                'email' => $email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.'])
                ->withInput(['email' => $email]);
        }
    }

    /**
     * Renvoyer un nouvel OTP
     */
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;

        // Vérifier s'il y a une vérification en cours
        $verification = EmailVerification::where('email', $email)
            ->notExpired()
            ->notVerified()
            ->first();

        if (!$verification) {
            return redirect()->route('auth.register')
                ->withErrors(['error' => 'Aucune demande de vérification trouvée.']);
        }

        try {
            // Générer un nouvel OTP
            $verification->update([
                'otp' => EmailVerification::generateOTP(),
                'expires_at' => now()->addMinutes(10)
            ]);

            // Envoyer le nouvel OTP
            $this->sendOTPEmail($email, $verification->user_data['first_name'], $verification->otp);

            Log::info('OTP resent', [
                'email' => $email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Un nouveau code de vérification a été envoyé.');

        } catch (\Exception $e) {
            Log::error('OTP resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.']);
        }
    }

    /**
     * Afficher le formulaire de modification du profil utilisateur
     */
    public function showProfile()
    {
        $data['title'] = 'Mon Profil';
        $data['menu'] = 'profile';
        $data['user'] = Auth::user();

        return view('auth.profile', $data);
    }

    /**
     * Mettre à jour les informations du profil utilisateur
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation des données
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            ], [
                'first_name.required' => 'Le prénom est obligatoire.',
                'last_name.required' => 'Le nom est obligatoire.',
                'mobile.required' => 'Le numéro de téléphone est obligatoire.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
            ]);

            // Mettre à jour les informations utilisateur
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
            ]);

            Log::info('Profil utilisateur mis à jour', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Vos informations ont été mises à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la mise à jour. Veuillez réessayer.']);
        }
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangePassword()
    {
        $data['title'] = 'Changer le mot de passe';
        $data['menu'] = 'change-password';
        $data['user'] = Auth::user();

        return view('auth.change-password', $data);
    }

    /**
     * Mettre à jour le mot de passe utilisateur
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation des données
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ], [
                'current_password.required' => 'Le mot de passe actuel est obligatoire.',
                'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
                'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);

            // Vérifier le mot de passe actuel
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }

            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            Log::info('Mot de passe utilisateur mis à jour', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Votre mot de passe a été mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mot de passe: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la mise à jour du mot de passe. Veuillez réessayer.']);
        }
    }

    /**
     * Afficher l'historique d'abonnement
     */
    public function showSubscriptionHistory()
    {
        $data['title'] = 'Historique d\'abonnement';
        $data['menu'] = 'subscription-history';

        // Récupérer l'entreprise de l'utilisateur connecté
        $entreprise = Entreprise::getEntrepriseByUser(Auth::id());

        if (!$entreprise) {
            return redirect()->route('entreprise.create')
                ->with('error', 'Veuillez d\'abord configurer votre entreprise.');
        }

        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        // Récupérer l'historique des abonnements de l'entreprise (depuis subscription_plans)
        $data['subscriptions'] = \App\Models\SubscriptionPlan::where('entreprise_id', $user->entreprise_id)
            ->with('pricingPlan')
            ->ordered()
            ->get();

        // Récupérer l'historique des paiements (depuis subscription_histories)
        $data['payment_history'] = SubscriptionHistory::where('entreprise_id', $user->entreprise_id)
            ->with('pricingPlan')
            ->ordered()
            ->get();

        // Informations sur l'abonnement actuel (depuis la table users)
        $data['current_subscription'] = [
            'subscription' => [
                'plan_id' => $user->subscription_plan_id,
                'status' => $user->subscription_status,
                'expires_at' => $user->subscription_expires_at,
                'is_trial' => $user->is_trial,
                'trial_expires_at' => $user->trial_expires_at,
                'has_active_subscription' => $user->hasActiveSubscription(),
                'can_access_premium' => $user->hasActiveSubscription('Premium')
            ]
        ];

        // Plan d'abonnement actuel (depuis subscription_plans via l'abonnement actif)
        $activeSubscription = $user->getActiveSubscription();
        if ($activeSubscription && $activeSubscription->pricingPlan) {
            $plan = $activeSubscription->pricingPlan;
            $data['current_subscription']['plan'] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => number_format($plan->price, 0, ',', ' '),
                'currency' => $plan->currency,
                'duration_days' => $plan->period === 'year' ? 365 : 30,
                'features' => $plan->features,
                'is_premium' => in_array($plan->name, ['Premium', 'Premium Annuel']),
                'is_free' => $plan->price == 0
            ];

            // Mettre à jour les données de l'abonnement existant avec les durées réelles
            $data['current_subscription']['subscription']['real_duration_days'] = $activeSubscription->getRealDurationDays();
            $data['current_subscription']['subscription']['remaining_days'] = $activeSubscription->getRemainingDays();
            $data['current_subscription']['subscription']['expires_at'] = $activeSubscription->expires_at;
        }

        return view('auth.subscription-history', $data);
    }

    /**
     * Afficher les forfaits
     */
    public function showPricing()
    {
        $data['title'] = 'Forfaits';
        $data['menu'] = 'pricing';

        // Récupérer tous les forfaits proposés (depuis pricing_plans)
        $query = \App\Models\PricingPlan::active()->ordered();

        $data['plans'] = $query->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => number_format($plan->price, 0, ',', ' '),
                'currency' => $plan->currency,
                'period' => $plan->period === 'year' ? 'an' : 'mois',
                'description' => $plan->description,
                'features' => $plan->features ?? [],
                'popular' => $plan->is_popular,
                'button_text' => $plan->price == 0 ? 'Passer au ' . $plan->name : 'Choisir ' . $plan->name,
                'button_class' => $plan->is_popular ? 'btn-primary' : 'btn-outline-primary'
            ];
        });

        return view('auth.pricing', $data);
    }

    /**
     * Afficher la FAQ
     */
    public function showFAQ()
    {
        $data['title'] = 'FAQ';
        $data['menu'] = 'faq';

        // Récupérer les FAQ depuis la base de données
        $faqs = Faq::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        $data['faqs'] = $faqs->map(function ($questions, $category) {
            return [
                'category' => $category,
                'questions' => $questions->map(function ($faq) {
                    return [
                        'question' => $faq->question,
                        'answer' => $faq->answer
                    ];
                })->toArray()
            ];
        })->values()->toArray();

        return view('auth.faq', $data);
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('Déconnexion utilisateur', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('success', 'Vous avez été déconnecté avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la déconnexion. Veuillez réessayer.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Auth $auth)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Auth $auth)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Auth $auth)
    {
        //
    }
}
