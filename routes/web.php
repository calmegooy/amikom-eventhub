<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\OrganizerController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WebSettingController;
use App\Http\Controllers\Auth\AuthController as UserAuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckInController;
// Admin Controllers
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\Organizer\CouponController as OrganizerCouponController;
use App\Http\Controllers\Organizer\DashboardController as OrganizerDashboardController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\OrganizationController as OrganizerOrganizationController;
use App\Http\Controllers\OrganizerProfileController;
// Auth & Organizer Controllers
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - AMIKOM Event Hub
|--------------------------------------------------------------------------
*/

// =============================================
// 1. PUBLIC PAGES
// =============================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profil', [HomeController::class, 'profil'])->name('profil');
Route::get('/katalog', [HomeController::class, 'katalog'])->name('katalog');
Route::get('/bantuan', [HomeController::class, 'bantuan'])->name('bantuan');
Route::get('/kontak', [HomeController::class, 'kontak'])->name('kontak');

// =============================================
// 2. EVENT CATALOG
// =============================================
Route::get('/event/{id}', [EventController::class, 'show'])->name('events.show');

// =============================================
// 3. CHECKOUT & PAYMENT FLOW (USER ONLY / AUTH)
// =============================================
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/checkout/{event}', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/{event}/coupon', [CheckoutController::class, 'checkCoupon'])->name('checkout.coupon.check');
    Route::post('/checkout/{event}', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/payment/finish', [CheckoutController::class, 'finish'])->name('checkout.finish');
    Route::get('/payment/{order_id}', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::get('/success/{order_id}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/ticket/{order_id}', [TicketController::class, 'show'])->name('ticket');

    // Halaman Riwayat Tiket Saya milik User
    Route::get('/tiket-saya', [UserTicketController::class, 'index'])->name('user.tickets');
    Route::get('/tiket-saya/{order_id}', [UserTicketController::class, 'show'])->name('user.tickets.show');
    Route::delete('/tiket-saya/{order_id}', [UserTicketController::class, 'destroy'])->name('user.tickets.destroy');

    // ============================================================
    // ROUTE DEV SIMULASI LUNAS (Khusus Testing Lokal / Sandbox)
    // ============================================================
    if (app()->environment('local', 'testing')) {
        Route::post('/dev-pay-success/{order_id}', [CheckoutController::class, 'devSuccess'])->name('dev.pay.success');
    }
});

// Midtrans Webhook Callback (Public Endpoint)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'handle']);

// =============================================
// 4. RATING, REVIEWS & E-CERTIFICATE
// =============================================
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/review/{order_id}', [ReviewController::class, 'create'])->name('review.create');
    Route::post('/review/{order_id}', [ReviewController::class, 'store'])->name('review.store');

    Route::get('/certificate/{order_id}', [CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/certificate/{order_id}/preview', [CertificateController::class, 'preview'])->name('certificate.preview');
    Route::get('/certificate/{order_id}/download', [CertificateController::class, 'download'])->name('certificate.download');
});

// =============================================
// 5. USER & ORGANIZER AUTHENTICATION
// =============================================
Route::prefix('auth')->name('user.')->group(function () {
    Route::get('login', [UserAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [UserAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
    Route::get('register', [UserAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [UserAuthController::class, 'register'])->name('register.post');
    Route::get('register/organizer', [UserAuthController::class, 'showRegisterOrganizer'])->name('register.organizer');
    Route::post('register/organizer', [UserAuthController::class, 'registerOrganizer'])->name('register.organizer.post');

    // Google SSO
    Route::get('google/redirect', [UserAuthController::class, 'redirectToGoogle'])
        ->middleware(['guest', 'throttle:10,1'])
        ->name('google.redirect');
    Route::get('google/callback', [UserAuthController::class, 'handleGoogleCallback'])
        ->middleware(['guest', 'throttle:10,1'])
        ->name('google.callback');
});

// Alias Route Login untuk Fallback Middleware Auth
Route::get('/login', function () {
    return redirect()->route('user.login');
})->name('login');

// =============================================
// 6. ORGANIZER PANEL (MULTI-TENANT & ROLE GUARD)
// =============================================
Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'role:organizer'])->group(function () {

    // Dashboard Analytics
    Route::get('/', [OrganizerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard.alt');

    // Profil tenant aktif
    Route::get('/organization/edit', [OrganizerOrganizationController::class, 'edit'])->name('organization.edit');
    Route::put('/organization', [OrganizerOrganizationController::class, 'update'])->name('organization.update');

    // Waiting Room (untuk organizer yang belum disetujui admin)
    Route::get('/waiting', function () {
        return view('organizer.waiting');
    })->name('waiting');

    // Event Management (CRUD)
    Route::resource('events', OrganizerEventController::class);

    // Scanner Check-in wajib terikat ke satu event milik organizer.
    Route::get('/events/{event}/scanner', [CheckInController::class, 'index'])->name('scanner');
    Route::post('/events/{event}/checkin', [CheckInController::class, 'process'])->name('checkin');

    // Coupon Management
    Route::get('/coupons', [OrganizerCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [OrganizerCouponController::class, 'store'])->name('coupons.store');
    Route::delete('/coupons/{coupon}', [OrganizerCouponController::class, 'destroy'])->name('coupons.destroy');
    Route::patch('/coupons/{coupon}/toggle', [OrganizerCouponController::class, 'toggle'])->name('coupons.toggle');

    Route::get('/events/{event}/export-attendees', [OrganizerEventController::class, 'exportAttendees'])->name('events.export-attendees');

    // Ulasan Event
    Route::get('/reviews', [ReviewController::class, 'organizerIndex'])->name('reviews.index');
});

// =============================================
// 7. ADMIN AREA (SUPER CONTROL PANEL & ROLE GUARD)
// =============================================
Route::prefix('admin')->name('admin.')->group(function () {

    // Auth Admin
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Authenticated Routes
    Route::middleware(['auth', 'role:admin'])->group(function () {

        // Dashboard Admin
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alt');

        // CRUD Event Global
        Route::resource('events', AdminEventController::class);

        // Transactions Rekap
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

        // Master Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Master Partners
        Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
        Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
        Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
        Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');

        // Organizer Approval System
        Route::get('/organizers', [OrganizerController::class, 'index'])->name('organizers.index');
        Route::patch('/organizers/{organizer}/approve', [OrganizerController::class, 'approve'])->name('organizers.approve');
        Route::patch('/organizers/{organizer}/reject', [OrganizerController::class, 'reject'])->name('organizers.reject');

        // Pengaturan Tampilan Web View (CMS)
        Route::get('/settings', [WebSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [WebSettingController::class, 'update'])->name('settings.update');

        // Review Management
        Route::get('/reviews', [ReviewController::class, 'adminIndex'])->name('reviews.index');
        Route::delete('/reviews/{review}', [ReviewController::class, 'adminDestroy'])->name('reviews.destroy');
    });
});

// =============================================
// 8. PUBLIC ORGANIZER PROFILE (WILDCARD {slug})
// HARUS DI PALING BAWAH AGAR TIDAK MENABRAK ROUTE /organizer/... LAINNYA
// =============================================
Route::get('/organizer/{slug}', [OrganizerProfileController::class, 'show'])->name('organizer.profile');
