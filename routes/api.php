<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HelpFaqController;
use App\Http\Controllers\Api\Me\ArtisanBusinessCardController;
use App\Http\Controllers\Api\Me\ArtisanDashboardController;
use App\Http\Controllers\Api\Me\BatimentDashboardAnalyticsController;
use App\Http\Controllers\Api\Me\BatimentDashboardController;
use App\Http\Controllers\Api\Me\BesoinController as MeBesoinController;
use App\Http\Controllers\Api\Me\CandidatureController;
use App\Http\Controllers\Api\Me\CompleteProfileController;
use App\Http\Controllers\Api\Me\DevisController;
use App\Http\Controllers\Api\Me\FournisseurDashboardController;
use App\Http\Controllers\Api\Me\MessageController;
use App\Http\Controllers\Api\Me\NotificationController;
use App\Http\Controllers\Api\Me\ParticulierDashboardController;
use App\Http\Controllers\Api\Me\ProductController;
use App\Http\Controllers\Api\Me\ProfileController;
use App\Http\Controllers\Api\Me\ServiceController as MeServiceController;
use App\Http\Controllers\Api\Me\SupportTicketController;
use App\Http\Controllers\Api\Me\UserDocumentController;
use App\Http\Controllers\Api\PublicArtisanBusinessCardController;
use App\Http\Controllers\Api\PublicBesoinController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicMarketplaceProviderController;
use App\Http\Controllers\Api\PublicProductController;
use App\Http\Controllers\Api\PublicServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Batitravoo API',
        'auth' => 'Bearer (Laravel Sanctum)',
        'docs' => 'routes/api.php',
    ]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/categories', PublicCategoryController::class);
Route::get('/marketplace/providers', [PublicMarketplaceProviderController::class, 'index']);
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/{product}', [PublicProductController::class, 'show'])->whereNumber('product');
Route::get('/services', [PublicServiceController::class, 'index']);
Route::get('/services/{service}', [PublicServiceController::class, 'show'])->whereNumber('service');
Route::get('/artisans/{user}/carte-visite', [PublicArtisanBusinessCardController::class, 'show'])->whereNumber('user');
Route::get('/besoins', [PublicBesoinController::class, 'index']);
Route::get('/besoins/{besoin}', [PublicBesoinController::class, 'show'])->whereNumber('besoin');
Route::get('/help/faqs', [HelpFaqController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('api.active')->group(function () {
        Route::get('/me', [ProfileController::class, 'show']);
        Route::put('/me', [ProfileController::class, 'update']);
        Route::put('/me/password', [ProfileController::class, 'updatePassword']);
        Route::post('/me/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::post('/me/complete-profile', [CompleteProfileController::class, 'store']);

        Route::get('/me/carte-visite', [ArtisanBusinessCardController::class, 'show']);
        Route::put('/me/carte-visite', [ArtisanBusinessCardController::class, 'update']);
        Route::delete('/me/carte-visite', [ArtisanBusinessCardController::class, 'destroy']);

        Route::get('/me/documents', [UserDocumentController::class, 'index']);
        Route::post('/me/documents', [UserDocumentController::class, 'store']);
        Route::get('/me/documents/{document}/file', [UserDocumentController::class, 'download'])->whereNumber('document');
        Route::delete('/me/documents/{document}', [UserDocumentController::class, 'destroy'])->whereNumber('document');

        Route::get('/me/tickets', [SupportTicketController::class, 'index']);
        Route::get('/me/tickets/form-options', [SupportTicketController::class, 'formOptions']);
        Route::post('/me/tickets', [SupportTicketController::class, 'store']);
        Route::get('/me/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereNumber('ticket');
        Route::post('/me/tickets/{ticket}/messages', [SupportTicketController::class, 'storeMessage'])->whereNumber('ticket');

        Route::get('/me/notifications', [NotificationController::class, 'index']);
        Route::post('/me/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/me/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
        Route::get('/me/dashboard/artisan', ArtisanDashboardController::class);
        Route::get('/me/dashboard/batiment', BatimentDashboardController::class);
        Route::get('/me/dashboard/batiment/analytics', BatimentDashboardAnalyticsController::class);
        Route::get('/me/dashboard/fournisseur', FournisseurDashboardController::class);
        Route::get('/me/dashboard/particulier', ParticulierDashboardController::class);

        Route::get('/me/products', [ProductController::class, 'index']);
        Route::post('/me/products', [ProductController::class, 'store']);
        Route::put('/me/products/{product}', [ProductController::class, 'update'])->whereNumber('product');
        Route::delete('/me/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product');

        Route::get('/me/services', [MeServiceController::class, 'index']);
        Route::post('/me/services', [MeServiceController::class, 'store']);
        Route::put('/me/services/{service}', [MeServiceController::class, 'update'])->whereNumber('service');
        Route::delete('/me/services/{service}', [MeServiceController::class, 'destroy'])->whereNumber('service');

        Route::get('/me/besoins', [MeBesoinController::class, 'index']);
        Route::post('/me/besoins', [MeBesoinController::class, 'store']);
        Route::put('/me/besoins/{besoin}', [MeBesoinController::class, 'update'])->whereNumber('besoin');
        Route::delete('/me/besoins/{besoin}', [MeBesoinController::class, 'destroy'])->whereNumber('besoin');

        Route::get('/me/devis', [DevisController::class, 'index']);
        Route::post('/me/devis', [DevisController::class, 'store']);
        Route::get('/me/devis/{devis}', [DevisController::class, 'show'])->whereNumber('devis');
        Route::put('/me/devis/{devis}', [DevisController::class, 'update'])->whereNumber('devis');

        Route::get('/me/candidatures/received', [CandidatureController::class, 'indexReceived']);
        Route::get('/me/candidatures', [CandidatureController::class, 'indexAsApplicant']);
        Route::get('/me/besoins/{besoin}/candidatures', [CandidatureController::class, 'indexForBesoin'])->whereNumber('besoin');
        Route::post('/me/candidatures', [CandidatureController::class, 'store']);
        Route::put('/me/candidatures/{candidature}', [CandidatureController::class, 'update'])->whereNumber('candidature');

        Route::get('/me/messages/conversations', [MessageController::class, 'conversationPartners']);
        Route::get('/me/messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/me/messages', [MessageController::class, 'index']);
        Route::post('/me/messages', [MessageController::class, 'store']);
        Route::post('/me/messages/mark-read', [MessageController::class, 'markRead']);
    });
});
