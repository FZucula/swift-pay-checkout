<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ZohoOAuthController;
use App\Http\Controllers\ZohoPlanController;
use App\Http\Controllers\ZohoInvoiceController;
use App\Http\Controllers\ZohoCustomerController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MillenniumIZIController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\EntitlementController;
use App\Http\Controllers\MetaTrackingController;

use App\Models\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/testes', function () {
    return $fullUrl = url()->full();
})->name('testes');

Route::get('/return-subs', [SubscriptionController::class, 'geAllZohoSubscriptions']);

Route::post('/store-user-data', [PaymentController::class, 'storeUserData'])->name('store.user.data');

Route::get('/terms-of-use', function () {
    return view('terms-of-use');
})->name('terms.of.use');

Route::get('/privacity-police', function () {
    return view('privacity-police');
})->name('privacity.police');

Route::get('/erro/400', fn() => abort(400));
Route::get('/erro/403', fn() => abort(403));
Route::get('/erro/404', fn() => abort(404));
Route::get('/erro/429', fn() => abort(429)); // Too Many Requests
Route::get('/erro/500', fn() => abort(500));
Route::get('/erro/503', fn() => abort(503));

// Rota específica para o erro do Zoho
Route::get('/erro/zoho-rate-limit', function() {
    return view('errors.zoho-rate-limit', [
        'message' => 'Você fez muitas requisições consecutivas ao Zoho. Por favor, tente novamente após algum tempo.'
    ]);
});

Route::get('/erro-de-metodo', function () {
    return view('pages.erros.metodo-nao-permitido');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/metatracking-data', [MetaTrackingController::class, 'dashboard'])->name('dashboard.metatracking-data');
Route::get('/dashboard/metatracking-list', [MetaTrackingController::class, 'index'])->name('dashboard.metatracking');
Route::get('/dashboard/metatracking/{id}', [MetaTrackingController::class, 'show'])->name('metatracking.show');

// create a destroy route for metatracking
Route::delete('/dashboard/metatracking/{id}', [MetaTrackingController::class, 'destroy'])->name('metatracking.destroy');
Route::get('/dashboard/subscriptions', [DashboardController::class, 'indexSubscription'])->name('dashboard.subscriptions');
Route::get('/dashboard/subscriptions/inactive', [SubscriptionController::class, 'getInactiveSubscriptions'])->name('dashboard.subscriptions.inactive');
Route::get('/dashboard/subscriptions/showSubscription/{id}', [SubscriptionController::class, 'showSubscription'])->name('subscription.show');
Route::get('/dashboard/payments', [DashboardController::class, 'indexPayment'])->name('dashboard.payments');
Route::get('/dashboard/payments/{user_id}', [DashboardController::class, 'userPayments'])->name('user.payments');
Route::get('/dashboard/coupons', [DashboardController::class, 'listCoupons'])->name('dashboard.coupons');
Route::get('/dashboard/view-coupons/{codigo}', [DashboardController::class, 'viewCoupon'])->name('coupons.show');
Route::get('/dashboard/logs', [DashboardController::class, 'indexLog'])->name('dashboard.logs');
Route::get('/dashboard/users', [DashboardController::class, 'indexUsers'])->name('dashboard.users');
Route::post('/dashboard/users/change-role', [DashboardController::class, 'changeUserRole'])->name('change.users.role');
Route::get('/dashboard/subscriptions/reconcile', [SubscriptionController::class, 'reconcileSubscriptions'])->name('local.zoho.reconcile');

Route::get('/dashboard/subscriptions/cancel-or-renew', [EntitlementController::class, 'cancelOrRenewSubscriptions'])->name('local.zoho.cancelOrRenew');

Route::get('/subscription/cancel-vimeo/{customer_id}/{product_id}/', [SubscriptionController::class, 'cancelVimeoSubscription'])->name('remove.vimeo.product');
Route::post('/subscription/add-vimeo-product', [SubscriptionController::class, 'addVimeoSubscription'])->name('add.vimeo.product');

Route::get('/dashboard/recibos', [ZohoInvoiceController::class, 'listarRecibos'])->name('dashboard.recibos');
Route::get('/dashboard/facturas', [ZohoInvoiceController::class, 'listarFacturas'])->name('dashboard.facturas');
// Rotas para gestão de assinaturas
Route::prefix('dashboard')->group(function () {
    // Criar assinatura (Zoho, Vimeo ou ambos)
    Route::post('/criar-inscrito', [DashboardController::class, 'criarInscrito'])
        ->name('dashboard.criarInscrito');

    // Atualizar assinatura existente
    Route::post('/atualizar-inscrito', [DashboardController::class, 'atualizarInscrito'])
        ->name('dashboard.atualizarInscrito');

    // Rota opcional para criar apenas no Zoho
    Route::post('/criar-zoho', [DashboardController::class, 'criarZohoSubscription'])
        ->name('dashboard.criarZoho');

    // Rota opcional para criar apenas no Vimeo
    Route::post('/criar-vimeo', [DashboardController::class, 'criarVimeoCustomer'])
        ->name('dashboard.criarVimeo');

    Route::post('/criar-vimeo', [SubscriptionController::class, 'criarVimeoCustomerFromView'])
        ->name('dashboard.criarVimeo.View');

    // Nova rota para criar ambos
    Route::post('/criar-zoho-vimeo', [DashboardController::class, 'criarZohoVimeo'])
        ->name('dashboard.criarZohoVimeo');

         // Novas rotas para gestão de status
    Route::post('/repor-assinatura', [DashboardController::class, 'reporAssinatura'])
        ->name('dashboard.reporAssinatura');

    Route::post('/alterar-status', [DashboardController::class, 'alterarStatus'])
        ->name('dashboard.alterarStatus');
});
Route::get('/pagamento/teste', [ZohoInvoiceController::class, 'pagamentoHardcoded']);

Route::get('/subscriptions/merge-list', [SubscriptionController::class, 'showMergeList'])->name('subscriptions.merge');


Route::middleware(['auth', 'throttle:60,1']);

Route::get('/zoho/connect', [ZohoOAuthController::class, 'redirectToZoho']);
Route::get('/zoho/callback', [ZohoOAuthController::class, 'handleCallback']);

Route::get('/zoho/listPlans', [BillingController::class, 'listPlans']);
Route::get('/zoho/checkout', [BillingController::class, 'checkout']);
Route::get('/mastercard', [BillingController::class, 'processUserSubscription']);
Route::post('/api/millennium/callback', [MillenniumIZIController::class, 'paymentCallback']);
Route::post('/process-free-purchase', [BillingController::class, 'processFreePurchase'])
    ->middleware('auth')
    ->name('process.free.purchase');

Route::post('/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
Route::post('/initiate-mastercard-payment', [BillingController::class, 'initiateMastercardPayment'])->middleware('auth');

// Rota pública principal
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/delete-user-roduct', [HomeController::class, 'deleteProduct'])->name('delete.product');

Route::get('/zoho/plans', [ZohoPlanController::class, 'index'])->name('zoho.plans');
Route::get('/zoho/customers', [ZohoCustomerController::class, 'index'])->name('zoho.customers');
    Route::get('/zoho/invoices', [ZohoInvoiceController::class, 'index'])->name('zoho.invoices');

// Rotas de autenticação do Zoho (para administração)
Route::prefix('admin/zoho')->group(function () {
    Route::get('/connect', [ZohoOAuthController::class, 'redirectToZoho'])->name('zoho.connect');
    Route::get('/callback', [ZohoOAuthController::class, 'handleCallback']);

    // Outras rotas administrativas podem ficar aqui
});

Route::get('/erro', function () {
    return view('error');
})->name('error');

Route::post('/process-emola-payment', [MillenniumIZIController::class, 'doPayment'])->middleware('auth')->name('process.emola');
Route::post('/process-mpesa-payment', [BillingController::class, 'processMpesaPayment'])->middleware('auth')->name('process.mpesa');
Route::post('/process-payment', [BillingController::class, 'contentAccess']);
Route::get('/payment-results', [BillingController::class, 'paymentResult'])->name('payment.result');

Route::get('/change-password', [NewPasswordController::class, 'create']);

Route::resource('cupons', CuponController::class);
Route::get('/cupons/check/{code}', [CuponController::class, 'checkValidity'])->name('cupons.check');
Route::get('/dashboard/check-zoho-coupon/{code}', [CuponController::class, 'checkZohoCouponValidity']);

// Millennium BIM Open Payments
Route::prefix('millennium')->group(function () {
    Route::post('/session', [MillenniumIZIController::class, 'getSession']);
    Route::post('/payment', [MillenniumIZIController::class, 'doPayment']);
    Route::post('/status', [MillenniumIZIController::class, 'getPaymentStatus']);
    Route::get('/callback2', [MillenniumIZIController::class, 'paymentCallback'])->name('millennium.callback');
    Route::post('/cancel', [MillenniumIZIController::class, 'cancelTransaction']);
});


// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/statistics', [ActivityLogController::class, 'statistics'])->name('activity-logs.statistics');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::delete('/activity-logs/{id}', [ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');
    Route::post('/activity-logs/clean', [ActivityLogController::class, 'clean'])->name('activity-logs.clean');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
});


Route::get('/users/with-mobile-prefixes', function () {
    // Busca os usuários com os prefixos especificados
    $users = User::where(function($query) {
        $query->where('mobile', 'like', '+25884%')
              ->orWhere('mobile', 'like', '+25885%')
              ->orWhere('mobile', 'like', '84%')
              ->orWhere('mobile', 'like', '85%')
              ->orWhere('mobile', 'like', '25884%')
              ->orWhere('mobile', 'like', '25885%');
    })->get();

    // Retorna os usuários como JSON
    return response()->json($users);
});


Route::get('/clear-all', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Todo cache foi limpo';
});

require __DIR__.'/auth.php';

