<?php

use App\Http\Controllers\Admin\ActivitiesController;
use App\Http\Controllers\Admin\AdministratorsController;
use App\Http\Controllers\Admin\BesoinController;
use App\Http\Controllers\Admin\CandidatureController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DevisController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\PendingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileValidationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsHubController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Web\App\ArtisanBusinessCardWebController;
use App\Http\Controllers\Web\App\ArtisanServiceWebController;
use App\Http\Controllers\Web\App\BatimentServiceWebController;
use App\Http\Controllers\Web\App\BesoinArtisanDevisStoreController;
use App\Http\Controllers\Web\App\BesoinCandidatureStoreController;
use App\Http\Controllers\Web\App\BesoinManageController;
use App\Http\Controllers\Web\App\BesoinWebController;
use App\Http\Controllers\Web\App\CandidatureManageController;
use App\Http\Controllers\Web\App\DevisStoreWebController;
use App\Http\Controllers\Web\App\DevisUpdateWebController;
use App\Http\Controllers\Web\App\FournisseurProductWebController;
use App\Http\Controllers\Web\App\MarketplaceShowController;
use App\Http\Controllers\Web\App\MessageSendController;
use App\Http\Controllers\Web\App\MessageThreadWebController;
use App\Http\Controllers\Web\App\NotificationReadController;
use App\Http\Controllers\Web\App\ProfileWebController;
use App\Http\Controllers\Web\App\ShellController;
use App\Http\Controllers\Web\App\SupplierCartWebController;
use App\Http\Controllers\Web\App\SupportTicketActionController;
use App\Http\Controllers\Web\App\UserDocumentWebController;
use App\Http\Controllers\Web\AnnuaireController;
use App\Http\Controllers\Web\ProfileLandingController;
use App\Http\Controllers\Web\AppHomeController;
use App\Http\Controllers\Web\AppLogoutController;
use App\Http\Controllers\Web\Auth\LoginController as WebLoginController;
use App\Http\Controllers\Web\Auth\RegisterController as WebRegisterController;
use App\Http\Controllers\Web\CompleteProfileWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/annuaire', AnnuaireController::class)->name('vitrine.annuaire');
Route::get('/particulier', [ProfileLandingController::class, 'particulier'])->name('vitrine.particulier');
Route::get('/artisan', [ProfileLandingController::class, 'artisan'])->name('vitrine.artisan');
Route::get('/fournisseur', [ProfileLandingController::class, 'fournisseur'])->name('vitrine.fournisseur');
Route::get('/entreprise-btp', [ProfileLandingController::class, 'entrepriseBtp'])->name('vitrine.entreprise_btp');
Route::view('/contact', 'vitrine.contact')->name('vitrine.contact');
Route::view("/centre-d-aide", 'vitrine.help_center')->name('vitrine.help_center');
Route::view('/faq', 'vitrine.faq')->name('vitrine.faq');
Route::view('/conditions-d-utilisation', 'vitrine.terms')->name('vitrine.terms');
Route::view('/politique-de-confidentialite', 'vitrine.privacy')->name('vitrine.privacy');

Route::get('/connexion', [WebLoginController::class, 'create'])->name('login');
Route::post('/connexion', [WebLoginController::class, 'store'])->name('login.store');
Route::get('/inscription', [WebRegisterController::class, 'create'])->name('register');
Route::post('/inscription', [WebRegisterController::class, 'store'])->name('register.store');

Route::prefix('app')->middleware(['auth', 'not_admin', 'api.active'])->group(function () {
    Route::get('/', AppHomeController::class)->name('app.home');
    Route::post('/logout', AppLogoutController::class)->name('app.logout');

    Route::get('/complete-profile', [CompleteProfileWebController::class, 'edit'])->name('app.complete-profile');
    Route::post('/complete-profile', [CompleteProfileWebController::class, 'update'])->name('app.complete-profile.store');

    $registerShell = function (string $slug): void {
        Route::prefix($slug)->middleware('app.profile:'.$slug)->name('app.'.$slug.'.')->group(function () use ($slug) {
            Route::get('/', [ShellController::class, 'home'])->name('home');
            Route::get('/dashboard', [ShellController::class, 'dashboard'])->name('dashboard');
            Route::get('/messages', [ShellController::class, 'messages'])->name('messages');
            Route::get('/messages/fil', MessageThreadWebController::class)->name('messages.thread');
            Route::post('/messages/envoyer', MessageSendController::class)->name('messages.send');
            Route::get('/parametres', [ShellController::class, 'settings'])->name('settings');
            Route::get('/profil', [ShellController::class, 'profile'])->name('profile');
            Route::get('/profil/mot-de-passe', [ShellController::class, 'profilePassword'])->name('profile.password.page');
            Route::get('/profil/localisation', [ShellController::class, 'profileLocation'])->name('profile.location.page');
            Route::post('/profil', [ProfileWebController::class, 'update'])->name('profile.update');
            Route::post('/profil/mot-de-passe', [ProfileWebController::class, 'updatePassword'])->name('profile.password');
            Route::post('/profil/avatar', [ProfileWebController::class, 'uploadAvatar'])->name('profile.avatar');
            Route::get('/mes-besoins', [ShellController::class, 'besoinsManage'])->name('besoins');
            Route::get('/mes-besoins/nouveau', [ShellController::class, 'besoinCreate'])->name('besoins.create');
            Route::post('/mes-besoins', [BesoinManageController::class, 'store'])->name('besoins.store');
            if (in_array($slug, ['particulier', 'batiment'], true)) {
                Route::get('/mes-besoins/{besoin}/modifier', [BesoinWebController::class, 'edit'])->name('besoins.edit')->whereNumber('besoin');
                Route::put('/mes-besoins/{besoin}', [BesoinWebController::class, 'update'])->name('besoins.update')->whereNumber('besoin');
                Route::delete('/mes-besoins/{besoin}', [BesoinWebController::class, 'destroy'])->name('besoins.destroy')->whereNumber('besoin');
            }
            if ($slug === 'batiment') {
                Route::get('/mes-services/nouveau', [BatimentServiceWebController::class, 'create'])->name('services.create');
                Route::post('/mes-services', [BatimentServiceWebController::class, 'store'])->name('services.store');
                Route::get('/mes-services/{service}/modifier', [BatimentServiceWebController::class, 'edit'])->name('services.edit')->whereNumber('service');
                Route::put('/mes-services/{service}', [BatimentServiceWebController::class, 'update'])->name('services.update')->whereNumber('service');
                Route::delete('/mes-services/{service}', [BatimentServiceWebController::class, 'destroy'])->name('services.destroy')->whereNumber('service');
            }
            if ($slug === 'artisan') {
                Route::get('/mes-services/nouveau', [ArtisanServiceWebController::class, 'create'])->name('services.create');
                Route::post('/mes-services', [ArtisanServiceWebController::class, 'store'])->name('services.store');
                Route::get('/mes-services/{service}/modifier', [ArtisanServiceWebController::class, 'edit'])->name('services.edit')->whereNumber('service');
                Route::put('/mes-services/{service}', [ArtisanServiceWebController::class, 'update'])->name('services.update')->whereNumber('service');
                Route::delete('/mes-services/{service}', [ArtisanServiceWebController::class, 'destroy'])->name('services.destroy')->whereNumber('service');
            }
            Route::get('/mes-services', [ShellController::class, 'servicesManage'])->name('services');
            Route::get('/mes-produits', [ShellController::class, 'productsManage'])->name('products');
            Route::get('/candidatures', [ShellController::class, 'candidatures'])->name('candidatures');
            Route::post('/candidatures/{candidature}/statut', [CandidatureManageController::class, 'update'])->name('candidatures.status')->whereNumber('candidature');
            Route::get('/marketplace', [ShellController::class, 'marketplace'])->name('marketplace');
            Route::get('/marketplace/produit/{product}', [MarketplaceShowController::class, 'product'])->name('marketplace.product')->whereNumber('product');
            Route::get('/marketplace/service/{service}', [MarketplaceShowController::class, 'service'])->name('marketplace.service')->whereNumber('service');
            Route::get('/marketplace/besoin/{besoin}', [MarketplaceShowController::class, 'besoin'])->name('marketplace.besoin')->whereNumber('besoin');
            Route::post('/marketplace/besoin/{besoin}/candidature', BesoinCandidatureStoreController::class)->name('marketplace.besoin.candidature')->whereNumber('besoin');
            Route::post('/marketplace/besoin/{besoin}/devis-proposition', BesoinArtisanDevisStoreController::class)->name('marketplace.besoin.devis')->whereNumber('besoin');
            if (in_array($slug, ['particulier', 'batiment', 'fournisseur'], true)) {
                Route::get('/panier', [ShellController::class, 'supplierCart'])->name('cart');
                Route::post('/panier/ajouter-produit', [SupplierCartWebController::class, 'addProduct'])->name('cart.add');
                Route::post('/panier/ligne/{index}', [SupplierCartWebController::class, 'updateLine'])->name('cart.line')->whereNumber('index');
                Route::post('/panier/ligne/{index}/supprimer', [SupplierCartWebController::class, 'removeLine'])->name('cart.line.remove')->whereNumber('index');
                Route::post('/panier/vider', [SupplierCartWebController::class, 'clear'])->name('cart.clear');
                Route::post('/panier/commander', [SupplierCartWebController::class, 'checkout'])->name('cart.checkout');
            }
            if ($slug === 'fournisseur') {
                Route::get('/commandes', [ShellController::class, 'fournisseurOrders'])->name('commandes');
            }
            Route::get('/devis', [ShellController::class, 'devis'])->name('devis');
            Route::get('/devis/nouveau', [ShellController::class, 'devisCreate'])->name('devis.create');
            Route::post('/devis', DevisStoreWebController::class)->name('devis.store');
            Route::put('/devis/{devis}', DevisUpdateWebController::class)->name('devis.update')->whereNumber('devis');
            Route::get('/devis/{devis}', [ShellController::class, 'devisShow'])->name('devis.show');
            Route::get('/support', [ShellController::class, 'support'])->name('support');
            Route::get('/support/nouveau', [ShellController::class, 'supportCreate'])->name('support.create');
            Route::post('/support', [SupportTicketActionController::class, 'store'])->name('support.store');
            Route::get('/support/tickets/{ticket}', [ShellController::class, 'supportShow'])->name('support.show')->whereNumber('ticket');
            Route::post('/support/tickets/{ticket}/messages', [SupportTicketActionController::class, 'reply'])->name('support.reply')->whereNumber('ticket');
            Route::get('/notifications', [ShellController::class, 'notificationsPage'])->name('notifications');
            Route::post('/notifications/lues', NotificationReadController::class)->name('notifications.read_all');

            if (in_array($slug, ['artisan', 'batiment', 'fournisseur'], true)) {
                Route::post('/documents', [UserDocumentWebController::class, 'store'])->name('documents.store');
                Route::delete('/documents/{document}', [UserDocumentWebController::class, 'destroy'])->name('documents.destroy')->whereNumber('document');
            }
            if ($slug === 'fournisseur') {
                Route::get('/mes-produits/nouveau', [FournisseurProductWebController::class, 'create'])->name('products.create');
                Route::post('/mes-produits', [FournisseurProductWebController::class, 'store'])->name('products.store');
                Route::get('/mes-produits/{product}/modifier', [FournisseurProductWebController::class, 'edit'])->name('products.edit')->whereNumber('product');
                Route::put('/mes-produits/{product}', [FournisseurProductWebController::class, 'update'])->name('products.update')->whereNumber('product');
                Route::delete('/mes-produits/{product}', [FournisseurProductWebController::class, 'destroy'])->name('products.destroy')->whereNumber('product');
                Route::get('/documents', [ShellController::class, 'documents'])->name('documents');
                Route::get('/aide', [ShellController::class, 'helpFournisseur'])->name('help');
                Route::get('/vue-publique', [ShellController::class, 'supplierPublicPreview'])->name('public_preview');
            }
            if ($slug === 'batiment') {
                Route::get('/documents', [ShellController::class, 'documents'])->name('documents');
                Route::get('/aide', [ShellController::class, 'helpBatiment'])->name('help');
                Route::get('/vue-publique', [ShellController::class, 'batimentPublicPreview'])->name('public_preview');
            }
            if ($slug === 'particulier') {
                Route::get('/service-client', [ShellController::class, 'serviceClient'])->name('service_client');
                Route::get('/documents', [ShellController::class, 'documents'])->name('documents');
                Route::get('/aide', [ShellController::class, 'helpParticulier'])->name('help');
            }
            if ($slug === 'artisan') {
                Route::get('/documents', [ShellController::class, 'documents'])->name('documents');
                Route::get('/aide', [ShellController::class, 'helpArtisan'])->name('help');
                Route::get('/carte-visite', [ArtisanBusinessCardWebController::class, 'edit'])->name('business_card');
                Route::post('/carte-visite', [ArtisanBusinessCardWebController::class, 'update'])->name('business_card.update');
                Route::delete('/carte-visite', [ArtisanBusinessCardWebController::class, 'destroy'])->name('business_card.destroy');
            }
        });
    };

    foreach (['particulier', 'artisan', 'batiment', 'fournisseur'] as $profileSlug) {
        $registerShell($profileSlug);
    }
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('search', GlobalSearchController::class)->name('search');
    Route::get('pending', PendingController::class)->name('pending');

    Route::get('reports', ReportController::class)->name('reports');

    Route::get('profile-validation', [ProfileValidationController::class, 'index'])->name('profile-validation.index');
    Route::get('profile-validation/{user}', [ProfileValidationController::class, 'show'])->name('profile-validation.show');
    Route::put('profile-validation/{user}', [ProfileValidationController::class, 'update'])->name('profile-validation.update');

    Route::get('activities', ActivitiesController::class)->name('activities');
    Route::get('moderation', ModerationController::class)->name('moderation');
    Route::get('settings-hub', SettingsHubController::class)->name('settings-hub');
    Route::get('support/tickets', [SupportTicketController::class, 'index'])->name('support.tickets.index');
    Route::get('support/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support.tickets.show');
    Route::put('support/tickets/{ticket}', [SupportTicketController::class, 'update'])->name('support.tickets.update');
    Route::post('support/tickets/{ticket}/messages', [SupportTicketController::class, 'storeMessage'])->name('support.tickets.messages.store');

    Route::get('support', fn () => redirect()->route('admin.support.tickets.index'))->name('support');

    Route::get('administrators', [AdministratorsController::class, 'index'])->name('administrators.index');
    Route::post('administrators', [AdministratorsController::class, 'store'])->name('administrators.store');
    Route::delete('administrators/{user}', [AdministratorsController::class, 'destroy'])->name('administrators.destroy');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');

    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');

    Route::get('devis', [DevisController::class, 'index'])->name('devis.index');
    Route::get('devis/{devis}', [DevisController::class, 'show'])->name('devis.show');
    Route::put('devis/{devis}', [DevisController::class, 'update'])->name('devis.update');

    Route::get('besoins', [BesoinController::class, 'index'])->name('besoins.index');
    Route::get('besoins/{besoin}', [BesoinController::class, 'show'])->name('besoins.show');
    Route::put('besoins/{besoin}', [BesoinController::class, 'update'])->name('besoins.update');

    Route::get('candidatures', [CandidatureController::class, 'index'])->name('candidatures.index');
    Route::put('candidatures/{candidature}', [CandidatureController::class, 'update'])->name('candidatures.update');
});
