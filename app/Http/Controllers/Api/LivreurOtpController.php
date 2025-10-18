<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use App\Models\LivreurOtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Livreur OTP",
 *     description="APIs pour la gestion des codes OTP des livreurs"
 * )
 */
class LivreurOtpController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/livreur/check-phone",
     *     summary="Vérifier l'existence du numéro de téléphone du livreur",
     *     description="Vérifie si le numéro de téléphone existe et envoie un code OTP par WhatsApp",
     *     tags={"Livreur OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(property="mobile", type="string", example="2250712345678", description="Numéro de téléphone du livreur avec indicatif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mobile", type="string", example="2250712345678"),
     *                 @OA\Property(property="expires_at", type="string", format="datetime", example="2025-10-18T16:20:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livreur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun livreur trouvé avec ce numéro de téléphone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function checkPhone(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string|min:10|max:20'
            ], [
                'mobile.required' => 'Le numéro de téléphone est requis.',
                'mobile.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'mobile.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
                'mobile.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mobile = $request->mobile;

            // Vérifier si le livreur existe
            $livreur = Livreur::where('mobile', $mobile)->first();

            if (!$livreur) {
                Log::warning('Tentative de vérification OTP pour un livreur inexistant', [
                    'mobile' => $mobile,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun livreur trouvé avec ce numéro de téléphone'
                ], 404);
            }

            // Générer un code OTP de 4 chiffres
            $otpCode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            // Supprimer les anciens codes OTP non vérifiés pour ce numéro
            LivreurOtpVerification::where('mobile', $mobile)
                ->where('is_verified', false)
                ->delete();

            // Créer un nouveau code OTP
            $otpVerification = LivreurOtpVerification::create([
                'mobile' => $mobile,
                'otp_code' => $otpCode,
                'type' => 'password_reset',
                'expires_at' => now()->addMinutes(10), // Expire dans 10 minutes
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Envoyer le code OTP par WhatsApp
            $message = "Bonjour {$livreur->first_name} {$livreur->last_name},\n\n";
            $message .= "Votre code de vérification MOYOO :\n";
            $message .= "🔑 Code : {$otpCode}\n\n";
            $message .= "Ce code expire dans 10 minutes.\n";
            $message .= "Ne partagez jamais ce code avec personne.\n\n";
            $message .= "Cordialement,\nL'équipe MOYOO";

            $whatsappResult = $this->sendWhatsAppMessage($mobile, $message);

            Log::info('Code OTP généré et envoyé pour livreur', [
                'livreur_id' => $livreur->id,
                'mobile' => $mobile,
                'otp_id' => $otpVerification->id,
                'whatsapp_sent' => $whatsappResult['success'],
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code OTP envoyé avec succès',
                'data' => [
                    'mobile' => $mobile,
                    'expires_at' => $otpVerification->expires_at->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du téléphone livreur', [
                'mobile' => $request->mobile ?? 'N/A',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du code OTP'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/verify-otp",
     *     summary="Vérifier le code OTP",
     *     description="Vérifie le code OTP de 4 chiffres envoyé au livreur",
     *     tags={"Livreur OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "otp_code"},
     *             @OA\Property(property="mobile", type="string", example="2250712345678", description="Numéro de téléphone du livreur"),
     *             @OA\Property(property="otp_code", type="string", example="1234", description="Code OTP de 4 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code OTP vérifié avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mobile", type="string", example="2250712345678"),
     *                 @OA\Property(property="verified_at", type="string", format="datetime", example="2025-10-18T16:15:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code OTP invalide ou expiré")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string|min:10|max:20',
                'otp_code' => 'required|string|size:4'
            ], [
                'mobile.required' => 'Le numéro de téléphone est requis.',
                'mobile.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'mobile.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
                'mobile.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
                'otp_code.required' => 'Le code OTP est requis.',
                'otp_code.string' => 'Le code OTP doit être une chaîne de caractères.',
                'otp_code.size' => 'Le code OTP doit contenir exactement 4 chiffres.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mobile = $request->mobile;
            $otpCode = $request->otp_code;

            // Rechercher le code OTP
            $otpVerification = LivreurOtpVerification::where('mobile', $mobile)
                ->where('otp_code', $otpCode)
                ->where('is_verified', false)
                ->notExpired()
                ->first();

            if (!$otpVerification) {
                Log::warning('Tentative de vérification OTP invalide', [
                    'mobile' => $mobile,
                    'otp_code' => $otpCode,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Code OTP invalide ou expiré'
                ], 400);
            }

            // Marquer le code comme vérifié
            $otpVerification->markAsVerified();

            Log::info('Code OTP vérifié avec succès', [
                'mobile' => $mobile,
                'otp_id' => $otpVerification->id,
                'verified_at' => $otpVerification->verified_at,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code OTP vérifié avec succès',
                'data' => [
                    'mobile' => $mobile,
                    'verified_at' => $otpVerification->verified_at->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification OTP', [
                'mobile' => $request->mobile ?? 'N/A',
                'otp_code' => $request->otp_code ?? 'N/A',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification du code OTP'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/update-password",
     *     summary="Mettre à jour le mot de passe du livreur",
     *     description="Met à jour le mot de passe du livreur après vérification OTP",
     *     tags={"Livreur OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="mobile", type="string", example="2250712345678", description="Numéro de téléphone du livreur"),
     *             @OA\Property(property="new_password", type="string", example="nouveauMotDePasse123", description="Nouveau mot de passe"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="nouveauMotDePasse123", description="Confirmation du nouveau mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mobile", type="string", example="2250712345678"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-10-18T16:20:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP non vérifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Veuillez d'abord vérifier votre code OTP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livreur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Livreur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string|min:10|max:20',
                'new_password' => 'required|string|min:8|confirmed'
            ], [
                'mobile.required' => 'Le numéro de téléphone est requis.',
                'mobile.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'mobile.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
                'mobile.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
                'new_password.required' => 'Le nouveau mot de passe est requis.',
                'new_password.string' => 'Le nouveau mot de passe doit être une chaîne de caractères.',
                'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mobile = $request->mobile;
            $newPassword = $request->new_password;

            // Vérifier si le livreur existe
            $livreur = Livreur::where('mobile', $mobile)->first();

            if (!$livreur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé'
                ], 404);
            }

            // Vérifier si le code OTP a été vérifié récemment (dans les 30 dernières minutes)
            $otpVerification = LivreurOtpVerification::where('mobile', $mobile)
                ->where('is_verified', true)
                ->where('verified_at', '>=', now()->subMinutes(30))
                ->first();

            if (!$otpVerification) {
                Log::warning('Tentative de mise à jour mot de passe sans vérification OTP', [
                    'mobile' => $mobile,
                    'livreur_id' => $livreur->id,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord vérifier votre code OTP'
                ], 400);
            }

            // Mettre à jour le mot de passe
            $livreur->update([
                'password' => Hash::make($newPassword)
            ]);

            // Supprimer tous les codes OTP vérifiés pour ce numéro
            LivreurOtpVerification::where('mobile', $mobile)
                ->where('is_verified', true)
                ->delete();

            Log::info('Mot de passe livreur mis à jour avec succès', [
                'livreur_id' => $livreur->id,
                'mobile' => $mobile,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès',
                'data' => [
                    'mobile' => $mobile,
                    'updated_at' => $livreur->updated_at->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mot de passe livreur', [
                'mobile' => $request->mobile ?? 'N/A',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du mot de passe'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/resend-otp",
     *     summary="Renvoyer le code OTP",
     *     description="Renvoye un nouveau code OTP au livreur",
     *     tags={"Livreur OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(property="mobile", type="string", example="2250712345678", description="Numéro de téléphone du livreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP renvoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code OTP renvoyé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mobile", type="string", example="2250712345678"),
     *                 @OA\Property(property="expires_at", type="string", format="datetime", example="2025-10-18T16:25:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livreur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun livreur trouvé avec ce numéro de téléphone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies ne sont pas valides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resendOtp(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string|min:10|max:20'
            ], [
                'mobile.required' => 'Le numéro de téléphone est requis.',
                'mobile.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'mobile.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
                'mobile.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mobile = $request->mobile;

            // Vérifier si le livreur existe
            $livreur = Livreur::where('mobile', $mobile)->first();

            if (!$livreur) {
                Log::warning('Tentative de renvoi OTP pour un livreur inexistant', [
                    'mobile' => $mobile,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun livreur trouvé avec ce numéro de téléphone'
                ], 404);
            }

            // Vérifier s'il y a déjà un code OTP non vérifié récent (dans les 2 dernières minutes)
            $recentOtp = LivreurOtpVerification::where('mobile', $mobile)
                ->where('is_verified', false)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->first();

            if ($recentOtp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez attendre 2 minutes avant de demander un nouveau code'
                ], 429);
            }

            // Générer un nouveau code OTP de 4 chiffres
            $otpCode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            // Supprimer les anciens codes OTP non vérifiés pour ce numéro
            LivreurOtpVerification::where('mobile', $mobile)
                ->where('is_verified', false)
                ->delete();

            // Créer un nouveau code OTP
            $otpVerification = LivreurOtpVerification::create([
                'mobile' => $mobile,
                'otp_code' => $otpCode,
                'type' => 'password_reset',
                'expires_at' => now()->addMinutes(10), // Expire dans 10 minutes
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Envoyer le code OTP par WhatsApp
            $message = "Bonjour {$livreur->first_name} {$livreur->last_name},\n\n";
            $message .= "Votre nouveau code de vérification MOYOO :\n";
            $message .= "🔑 Code : {$otpCode}\n\n";
            $message .= "Ce code expire dans 10 minutes.\n";
            $message .= "Ne partagez jamais ce code avec personne.\n\n";
            $message .= "Cordialement,\nL'équipe MOYOO";

            $whatsappResult = $this->sendWhatsAppMessage($mobile, $message);

            Log::info('Code OTP renvoyé pour livreur', [
                'livreur_id' => $livreur->id,
                'mobile' => $mobile,
                'otp_id' => $otpVerification->id,
                'whatsapp_sent' => $whatsappResult['success'],
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code OTP renvoyé avec succès',
                'data' => [
                    'mobile' => $mobile,
                    'expires_at' => $otpVerification->expires_at->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du renvoi OTP livreur', [
                'mobile' => $request->mobile ?? 'N/A',
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du renvoi du code OTP'
            ], 500);
        }
    }

    /**
     * Envoyer un message WhatsApp via l'API Wassenger
     */
    private function sendWhatsAppMessage($phone, $message)
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
}
