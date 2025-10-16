<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\EmailVerification;
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
        $data['email'] = session('email');

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
        // Limitation du taux de tentatives
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Log de la connexion réussie
            Log::info('User logged in successfully', [
                'user_id' => Auth::id(),
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Incrémenter le compteur de tentatives
        RateLimiter::hit($key, 300); // 5 minutes

        // Log de la tentative échouée
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

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
        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'required|email|unique:users,email|max:255',
            'mobile' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:15',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ], [
            'first_name.regex' => 'Le prénom ne peut contenir que des lettres et espaces.',
            'last_name.regex' => 'Le nom ne peut contenir que des lettres et espaces.',
            'mobile.regex' => 'Le format du numéro de téléphone est invalide.',
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
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
            $verification = EmailVerification::createVerification($userData['email'], $userData);

            // Envoyer l'OTP par email
            $this->sendOTPEmail($userData['email'], $userData['first_name'], $verification->otp);

            // Log de la demande d'inscription
            Log::info('Registration OTP sent', [
                'email' => $userData['email'],
                'ip' => $request->ip()
            ]);

            return redirect()->route('auth.verify-otp')
                ->with('success', 'Un code de vérification a été envoyé à votre adresse email.')
                ->with('email', $userData['email']);

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
        $timeout = config('mailjet.timeout');
        $verifySsl = config('mailjet.verify_ssl');

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
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_TIMEOUT => $timeout
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
            return redirect()->back()
                ->withErrors(['otp' => 'Code de vérification invalide ou expiré.']);
        }

        if (!$verification->isValid($otp)) {
            return redirect()->back()
                ->withErrors(['otp' => 'Code de vérification incorrect.']);
        }

        try {
            // Créer l'utilisateur avec les données stockées
            $userData = $verification->user_data;
            $user = User::create($userData);

            // Marquer la vérification comme validée
            $verification->markAsVerified();

            // Envoyer l'email de bienvenue
            $this->sendWelcomeEmail($user);

            // Log de l'inscription réussie
            Log::info('User registration completed', [
                'user_id' => $user->id,
                'email' => $user->email,
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
                ->withErrors(['error' => 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.']);
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

        // Récupérer l'historique d'abonnement de l'entreprise
        $data['subscriptions'] = SubscriptionHistory::forEntreprise($entreprise->id)
            ->with('pricingPlan')
            ->ordered()
            ->get();

        return view('auth.subscription-history', $data);
    }

    /**
     * Afficher les forfaits
     */
    public function showPricing()
    {
        $data['title'] = 'Forfaits';
        $data['menu'] = 'pricing';

        // Récupérer les plans de tarification depuis la base de données
        $data['plans'] = PricingPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'period' => $plan->formatted_period,
                    'description' => $plan->description,
                    'features' => $plan->features ?? [],
                    'popular' => $plan->is_popular,
                    'button_text' => $plan->is_popular ? 'Choisir ' . $plan->name : 'Commencer',
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
