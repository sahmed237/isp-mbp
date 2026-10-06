<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDocumentController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerLocationController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotspotVoucherController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NetworkDeviceController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\RadiusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PublicInvoicePaymentController;
use App\Http\Controllers\PublicHotspotController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\ResellerPortalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public Accessible Invoice Payment & QR Code Resolution
Route::prefix('pay')->name('public.invoices.')->group(function () {
    Route::get('/invoice/{identifier}', [PublicInvoicePaymentController::class, 'show'])->name('pay');
    Route::post('/invoice/{identifier}/checkout', [PublicInvoicePaymentController::class, 'checkout'])->name('checkout');
    Route::get('/invoice/{identifier}/callback/{gateway}', [PublicInvoicePaymentController::class, 'callback'])->name('callback');
});

// Public Self-Service Hotspot Voucher Portal
Route::prefix('hotspot')->name('public.hotspot.')->group(function () {
    Route::get('/', [PublicHotspotController::class, 'index'])->name('index');
    Route::get('/buy', [PublicHotspotController::class, 'index'])->name('buy');
    Route::post('/checkout', [PublicHotspotController::class, 'checkout'])->name('checkout');
    Route::get('/callback/{gateway}', [PublicHotspotController::class, 'callback'])->name('callback');
    Route::get('/voucher/{code}', [PublicHotspotController::class, 'showVoucher'])->name('voucher');
    Route::match(['get', 'post'], '/lookup', [PublicHotspotController::class, 'lookup'])->name('lookup');
});

// Guest Authentication Routes (Admin Staff)
Route::middleware('guest:web')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Customer Self-Service Portal Routes (Subscribers)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerPortalController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerPortalController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerPortalController::class, 'logout'])->name('logout');
        Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}', [CustomerPortalController::class, 'showInvoice'])->name('invoices.show');
        Route::post('/invoices/{invoice}/pay', [CustomerPortalController::class, 'payInvoice'])->name('invoices.pay');
        Route::get('/payment/callback/{gateway}', [CustomerPortalController::class, 'paymentCallback'])->name('payment.callback');
        Route::get('/payments', [CustomerPortalController::class, 'payments'])->name('payments');
        Route::get('/hotspot', [CustomerPortalController::class, 'hotspot'])->name('hotspot');
        Route::post('/hotspot/buy', [CustomerPortalController::class, 'buyHotspot'])->name('hotspot.buy');

        // Reseller Hotspot Hub & Wallet
        Route::prefix('reseller')->name('reseller.')->group(function () {
            Route::get('/vouchers', [CustomerPortalController::class, 'resellerVouchers'])->name('vouchers');
            Route::post('/vouchers/buy', [CustomerPortalController::class, 'resellerBuyBatch'])->name('vouchers.buy');
            Route::post('/wallet/topup', [CustomerPortalController::class, 'resellerTopupWallet'])->name('wallet.topup');
            Route::get('/callback/{gateway}', [CustomerPortalController::class, 'resellerCallback'])->name('callback');
            Route::get('/vouchers/batch/{batchId}/print', [CustomerPortalController::class, 'resellerPrintBatch'])->name('vouchers.print');
        });

        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::put('/profile/password', [CustomerPortalController::class, 'updatePassword'])->name('profile.password');
    });
});

// Dedicated Voucher Reseller & Agent Portal Routes
Route::prefix('reseller')->name('reseller.')->group(function () {
    Route::middleware('guest:reseller')->group(function () {
        Route::get('/login', [ResellerPortalController::class, 'showLogin'])->name('login');
        Route::post('/login', [ResellerPortalController::class, 'login'])->middleware('throttle:5,1');
        Route::get('/apply', [ResellerPortalController::class, 'showApply'])->name('apply');
        Route::post('/apply', [ResellerPortalController::class, 'apply'])->name('apply.submit');
        Route::get('/applied', [ResellerPortalController::class, 'applied'])->name('applied');
    });

    Route::middleware('auth:reseller')->group(function () {
        Route::post('/logout', [ResellerPortalController::class, 'logout'])->name('logout');
        Route::get('/', fn() => redirect()->route('reseller.dashboard'));
        Route::get('/dashboard', [ResellerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/vouchers', [ResellerPortalController::class, 'vouchers'])->name('vouchers');
        Route::post('/vouchers/buy', [ResellerPortalController::class, 'buyBatch'])->name('vouchers.buy');
        Route::get('/vouchers/batch/{batchId}/print', [ResellerPortalController::class, 'printBatch'])->name('vouchers.print');
        Route::post('/wallet/topup', [ResellerPortalController::class, 'topupWallet'])->name('wallet.topup');
        Route::get('/callback/{gateway}', [ResellerPortalController::class, 'callback'])->name('callback');
        Route::get('/profile', [ResellerPortalController::class, 'profile'])->name('profile');
        Route::put('/profile', [ResellerPortalController::class, 'updateProfile'])->name('profile.update');
    });
});

// Authenticated Administrative Routes
Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile & Credentials
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customer Management, Groups & Locations
    Route::get('customers/groups', [CustomerGroupController::class, 'index'])->name('customers.groups.index');
    Route::get('customers/locations', [CustomerLocationController::class, 'index'])->name('customers.locations.index');
    Route::resource('customers', CustomerController::class);

    // Customer KYC Documents
    Route::get('customer-documents', [CustomerDocumentController::class, 'index'])->name('customers.documents.index');
    Route::post('customers/{customer}/documents', [CustomerDocumentController::class, 'store'])->name('customers.documents.store');
    Route::get('customer-documents/{document}/download', [CustomerDocumentController::class, 'download'])->name('customers.documents.download');
    Route::delete('customer-documents/{document}', [CustomerDocumentController::class, 'destroy'])->name('customers.documents.destroy');

    // Reseller & Voucher Agent Management
    Route::prefix('resellers')->name('resellers.')->group(function () {
        Route::get('/', [ResellerController::class, 'index'])->name('index');
        Route::get('/create', [ResellerController::class, 'create'])->name('create');
        Route::post('/', [ResellerController::class, 'store'])->name('store');
        Route::get('/{reseller}', [ResellerController::class, 'show'])->name('show');
        Route::post('/{reseller}/approve', [ResellerController::class, 'approve'])->name('approve');
        Route::post('/{reseller}/reject', [ResellerController::class, 'reject'])->name('reject');
        Route::post('/{reseller}/toggle-status', [ResellerController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{reseller}/wallet', [ResellerController::class, 'adjustWallet'])->name('wallet');
        Route::delete('/{reseller}', [ResellerController::class, 'destroy'])->name('destroy');
    });

    // Subscriptions Management
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('subscriptions/{subscription}/activate', [SubscriptionController::class, 'activate'])->name('subscriptions.activate');
    Route::post('subscriptions/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Invoices Management
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');

    // Payments Management
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');

    // Hotspot Vouchers Management
    Route::get('hotspot/vouchers', [HotspotVoucherController::class, 'index'])->name('vouchers.index');
    Route::post('hotspot/vouchers', [HotspotVoucherController::class, 'store'])->name('vouchers.store');
    Route::get('hotspot/vouchers/batch/{batchId}/print', [HotspotVoucherController::class, 'printBatch'])->name('vouchers.print_batch');
    Route::delete('hotspot/vouchers/{voucher}', [HotspotVoucherController::class, 'destroy'])->name('vouchers.destroy');

    // Internet Packages & Bandwidth Plans
    Route::resource('packages', PackageController::class);

    // Network Infrastructure & Devices
    Route::post('network-devices/{networkDevice}/test-connection', [NetworkDeviceController::class, 'testConnection'])
        ->name('network-devices.test-connection');
    Route::resource('network-devices', NetworkDeviceController::class)->parameters([
        'network-devices' => 'networkDevice',
    ]);

    // User & Staff Administration
    Route::resource('users', UserController::class);

    // Roles & Granular Permissions
    Route::resource('roles', RoleController::class);

    // Organizations & Multi-tenancy
    Route::resource('organizations', OrganizationController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('branches', BranchController::class)->except(['show']);

    // Security Audit Logs (Immutable)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // FreeRADIUS AAA Infrastructure Management
    Route::get('/radius', [RadiusController::class, 'index'])->name('radius.index');
    Route::post('/radius/nas', [RadiusController::class, 'storeNas'])->name('radius.nas.store');
    Route::put('/radius/nas/{nas}', [RadiusController::class, 'updateNas'])->name('radius.nas.update');
    Route::delete('/radius/nas/{nas}', [RadiusController::class, 'destroyNas'])->name('radius.nas.destroy');

    // Module Shortcuts & Seamless Redirection
    Route::get('/modules/radius', fn() => redirect()->route('radius.index'));
    Route::get('/modules/plans', fn() => redirect()->route('packages.index'));
    Route::get('/modules/subscriptions', fn() => redirect()->route('subscriptions.index'));
    Route::get('/modules/invoices', fn() => redirect()->route('invoices.index'));
    Route::get('/modules/payments', fn() => redirect()->route('payments.index'));
    Route::get('/modules/hotspot', fn() => redirect()->route('vouchers.index'));
    Route::get('/modules/areas', fn() => redirect()->route('branches.index'));
    Route::get('/modules/groups', fn() => redirect()->route('customers.groups.index'));
    Route::get('/modules/locations', fn() => redirect()->route('customers.locations.index'));
    Route::get('/modules/documents', fn() => redirect()->route('customers.documents.index'));

    // System Settings & Integrations (Restricted to Super Administrator)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
        Route::post('/test-mail', [SettingController::class, 'sendTestMail'])->name('test-mail');
    });

    // Placeholders for Future Roadmap Modules (Support, Reports)
    Route::get('modules/{module}', [PlaceholderController::class, 'show'])->name('modules.placeholder');
});
