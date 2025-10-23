<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LivreurAuthController;
use App\Http\Controllers\Api\LivreurDeliveryController;
use App\Http\Controllers\Api\LivreurRamassageController;
use App\Http\Controllers\Api\LivreurOtpController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\FirebaseNotificationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\SwaggerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes pour l'enregistrement des tokens FCM
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('fcm-token', [FcmTokenController::class, 'store']);
    Route::delete('fcm-token', [FcmTokenController::class, 'destroy']);
});

// Routes pour l'authentification web (fallback)
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('fcm-token-web', [FcmTokenController::class, 'store']);
    Route::delete('fcm-token-web', [FcmTokenController::class, 'destroy']);

    // Routes pour les notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
});

// Routes publiques pour l'authentification des livreurs
Route::prefix('livreur')->group(function () {
    // Authentification
    Route::post('login', [LivreurAuthController::class, 'login']);
    Route::post('refresh', [LivreurAuthController::class, 'refresh']);

    // Gestion des codes OTP (routes publiques)
    Route::post('check-phone', [LivreurOtpController::class, 'checkPhone']);
    Route::post('verify-otp', [LivreurOtpController::class, 'verifyOtp']);
    Route::post('update-password', [LivreurOtpController::class, 'updatePassword']);
    Route::post('resend-otp', [LivreurOtpController::class, 'resendOtp']);
});

// Routes protégées pour les livreurs
Route::prefix('livreur')->middleware('auth:livreur')->group(function () {
    // Authentification
    Route::post('logout', [LivreurAuthController::class, 'logout']);
    Route::get('profile', [LivreurAuthController::class, 'profile']);
    Route::post('profile', [LivreurAuthController::class, 'updateProfile']);
    Route::post('change-password', [LivreurAuthController::class, 'changePassword']);

            // Gestion des livraisons et colis
            Route::get('colis-assignes', [LivreurDeliveryController::class, 'getColisAssignes']);
            Route::get('colis/{id}/details', [LivreurDeliveryController::class, 'getColisDetails']);
            Route::post('colis/{id}/start-delivery', [LivreurDeliveryController::class, 'startDelivery']);
            Route::post('colis/{id}/complete-delivery', [LivreurDeliveryController::class, 'completeDelivery']);
            Route::post('colis/{id}/cancel-delivery', [LivreurDeliveryController::class, 'cancelDelivery']);
            Route::get('colis/stats', [LivreurDeliveryController::class, 'getDailyStats']);

            // Gestion des ramassages
            Route::get('ramassages', [LivreurRamassageController::class, 'getRamassagesAssignes']);
            Route::get('ramassages/{id}/details', [LivreurRamassageController::class, 'getRamassageDetails']);
            Route::post('ramassages/{id}/start', [LivreurRamassageController::class, 'startRamassage']);
            Route::post('ramassages/{id}/complete', [LivreurRamassageController::class, 'completeRamassage']);
            Route::post('ramassages/{id}/cancel', [LivreurRamassageController::class, 'cancelRamassage']);
            Route::get('ramassages/stats/daily', [LivreurRamassageController::class, 'getDailyStats']);

            // Gestion des tokens FCM
            Route::post('fcm-token', [FcmTokenController::class, 'updateLivreurToken']);
            Route::delete('fcm-token', [FcmTokenController::class, 'deleteLivreurToken']);
});

// Routes pour les notifications Firebase (admin)
Route::prefix('admin')->middleware('auth:livreur')->group(function () {
    // Gestion des notifications Firebase
    Route::post('firebase/test-notification', [FirebaseNotificationController::class, 'testNotification']);
    Route::get('firebase/status', [FirebaseNotificationController::class, 'getStatus']);
    Route::post('firebase/send-to-livreur', [FirebaseNotificationController::class, 'sendToLivreur']);
    Route::post('firebase/send-to-marchand', [FirebaseNotificationController::class, 'sendToMarchand']);
});

// Route de test pour vérifier que l'API fonctionne
Route::get('/test', [SwaggerController::class, 'test']);
