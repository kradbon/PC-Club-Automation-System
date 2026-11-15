<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClubEventController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\StaffBookingController;
use App\Http\Controllers\StaffCustomerOrderController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FoodOrderController;
use App\Http\Controllers\StaffFoodOrderController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\AdminFoodItemController;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\EnsureUserIsCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $locale = $request->user()?->preferred_locale ?? $request->session()->get('locale', config('app.locale', 'en'));

    return redirect('/'.(in_array($locale, ['en', 'ru', 'tj'], true) ? $locale : 'en'));
})->name('root');

Route::get('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'ru', 'tj'], true), 404);
    $request->session()->put('locale', $locale);
    $request->user()?->forceFill(['preferred_locale' => $locale])->saveQuietly();

    $referer = $request->headers->get('referer');
    $refererPath = is_string($referer) ? parse_url($referer, PHP_URL_PATH) : null;
    $segments = is_string($refererPath) ? explode('/', trim($refererPath, '/')) : [];
    $query = is_string($referer) ? parse_url($referer, PHP_URL_QUERY) : null;

    if (isset($segments[0]) && in_array($segments[0], ['en', 'ru', 'tj'], true)) {
        $segments[0] = $locale;
        $target = '/'.implode('/', $segments);
    } else {
        $target = '/'.$locale;
    }

    return redirect($target.($query ? '?'.$query : ''));
})->whereIn('locale', ['en', 'ru', 'tj'])->name('locale.switch');

Route::post('/webhooks/payments/{provider}', [PaymentWebhookController::class, 'receive'])
    ->middleware('throttle:120,1')->name('payments.webhook');

Route::prefix('{locale}')->whereIn('locale', ['en', 'ru', 'tj'])->middleware(SetLocale::class)->group(function (): void {
    Route::get('/', [ClubEventController::class, 'index'])->name('home');
    Route::get('/club', [ClubEventController::class, 'club'])->name('club');
    Route::get('/events', [ClubEventController::class, 'events'])->name('events.index');
    Route::get('/setup', [ClubEventController::class, 'setup'])->name('setup');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');
    Route::get('/prices', fn () => redirect()->route('rooms'))->name('prices');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/{tournament:slug}', [TournamentController::class, 'show'])->name('tournaments.show');

    Route::middleware('guest')->group(function (): void {
        Route::get('/join', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/join', [AuthController::class, 'register'])->name('register.store');
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', EnsureUserIsCustomer::class])->prefix('account')->name('account.')->group(function (): void {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/sessions', [AccountController::class, 'sessions'])->name('sessions');
        Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
        Route::get('/tariffs', [AccountController::class, 'tariffs'])->name('tariffs');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/food', [FoodOrderController::class, 'index'])->name('food');
        Route::post('/food/orders', [FoodOrderController::class, 'store'])->middleware('throttle:6,1')->name('food.store');
        Route::post('/tariffs/{tariff}/order', [AccountController::class, 'orderTariff'])->name('order');
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
        Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('/booking-availability', [BookingController::class, 'availability'])->middleware('throttle:60,1')->name('bookings.availability');
        Route::get('/wallet', [WalletController::class, 'show'])->name('wallet');
        Route::post('/wallet/top-up', [WalletController::class, 'topUp'])->middleware('throttle:5,1')->name('wallet.top-up');
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.store');
        Route::delete('/teams/{team}/members/{member}', [TeamController::class, 'removeMember'])->name('teams.members.delete');
        Route::post('/feedback', [ReviewController::class, 'store'])->middleware('throttle:3,10')->name('reviews.store');
        Route::post('/tournaments/{tournament}/register', [TournamentController::class, 'register'])->name('tournaments.register');
        Route::delete('/tournaments/{tournament}/registration', [TournamentController::class, 'cancelRegistration'])->name('tournaments.cancel');
    });

    Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function (): void {
        Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pcs', [StaffDashboardController::class, 'pcs'])->name('pcs');
        Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
        Route::get('/bookings', [StaffBookingController::class, 'index'])->name('bookings.index');
        Route::post('/customer-orders', [StaffCustomerOrderController::class, 'store'])->name('customer-orders.store');
        Route::get('/assisted', fn () => redirect()->route('staff.bookings.index'))->name('assisted.legacy-redirect');
        Route::get('/food-orders', [StaffFoodOrderController::class, 'index'])->name('food-orders.index');
        Route::put('/food-orders/{foodOrder}/status', [StaffFoodOrderController::class, 'update'])->name('food-orders.update');
        Route::post('/bookings/{booking}/decision', [StaffBookingController::class, 'decide'])->name('bookings.decide');
        Route::post('/bookings/{booking}/cancel', [StaffBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/complete', [StaffBookingController::class, 'complete'])->name('bookings.complete');
        Route::post('/bookings/{booking}/check-in', [StaffBookingController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('/bookings/{booking}/start-session', [StaffBookingController::class, 'startSession'])->name('bookings.start-session');
        Route::post('/bookings/{booking}/end-session', [StaffBookingController::class, 'endSession'])->name('bookings.end-session');
        Route::post('/bookings/{booking}/no-show', [StaffBookingController::class, 'noShow'])->name('bookings.no-show');
        Route::post('/bookings/{booking}/package-order', [StaffBookingController::class, 'packageOrder'])->name('bookings.package-order');
        Route::post('/bookings/{booking}/food-order', [StaffBookingController::class, 'foodOrder'])->name('bookings.food-order');
    });

    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
        Route::get('/food-menu', [AdminFoodItemController::class, 'index'])->name('food-menu');
        Route::post('/food-menu', [AdminFoodItemController::class, 'store'])->name('food-menu.store');
        Route::put('/food-menu/{foodItem}', [AdminFoodItemController::class, 'update'])->name('food-menu.update');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/financials', [AdminController::class, 'financials'])->name('financials');
        Route::get('/tournaments', [AdminController::class, 'tournaments'])->name('tournaments');
        Route::post('/tournaments', [AdminController::class, 'storeTournament'])->name('tournaments.store');
        Route::put('/tournaments/{tournament}', [AdminController::class, 'updateTournament'])->name('tournaments.update');
        Route::get('/content', [AdminController::class, 'content'])->name('content');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews');
        Route::put('/reviews/{review}', [AdminController::class, 'moderateReview'])->name('reviews.moderate');
        Route::put('/content/{page}', [AdminController::class, 'updatePage'])->name('content.update');
        Route::post('/promotions', [AdminController::class, 'storePromotion'])->name('promotions.store');
        Route::put('/promotions/{promotion}', [AdminController::class, 'updatePromotion'])->name('promotions.update');
        Route::get('/discounts', fn () => redirect(route('admin.content', ['locale' => request()->route('locale')]).'#promotions'))->name('discounts');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::post('/users/{user}/wallet', [AdminController::class, 'adjustUserWallet'])->name('users.wallet');
        Route::put('/zones/{zone}', [AdminController::class, 'updateZone'])->name('zones.update');
        Route::put('/pcs/{pc}', [AdminController::class, 'updatePc'])->name('pcs.update');
        Route::get('/bookings', [StaffBookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/decision', [StaffBookingController::class, 'decide'])->name('bookings.decide');
        Route::post('/orders/{order}/decision', [AdminController::class, 'decideOrder'])->name('orders.decide');
        Route::get('/events', [AdminController::class, 'events'])->name('events');
        Route::post('/events', [AdminController::class, 'storeEvent'])->name('events.store');
        Route::put('/events/{event}', [AdminController::class, 'updateEvent'])->name('events.update');
        Route::delete('/events/{event}', [AdminController::class, 'archiveEvent'])->name('events.archive');
        Route::get('/tariffs', [AdminController::class, 'tariffs'])->name('tariffs');
        Route::post('/tariffs', [AdminController::class, 'storeTariff'])->name('tariffs.store');
        Route::put('/tariffs/{tariff}', [AdminController::class, 'updateTariff'])->name('tariffs.update');
        Route::delete('/tariffs/{tariff}', [AdminController::class, 'archiveTariff'])->name('tariffs.archive');
        Route::get('/rooms', [AdminController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [AdminController::class, 'storeRoom'])->name('rooms.store');
        Route::put('/rooms/{room}', [AdminController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}', [AdminController::class, 'archiveRoom'])->name('rooms.archive');
    });
});
