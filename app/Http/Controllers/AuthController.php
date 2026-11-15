<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginAccountRequest;
use App\Http\Requests\RegisterAccountRequest;
use App\Http\Requests\RequestPasswordResetRequest;
use App\Http\Requests\ResetAccountPasswordRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View|RedirectResponse
    {
        return auth()->check() ? redirect($this->authenticatedDestination(auth()->user())) : view('auth.register');
    }

    public function register(RegisterAccountRequest $request, string $locale, WalletService $wallets): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data, $wallets): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'password' => $data['password'],
                'role' => 'customer',
                'preferred_locale' => app()->getLocale(),
            ]);
            $wallets->ensureWallet($user);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.dashboard')->with('success', __('ui.account_created'));
    }

    public function showLogin(): View|RedirectResponse
    {
        return auth()->check() ? redirect($this->authenticatedDestination(auth()->user())) : view('auth.login');
    }

    public function login(LoginAccountRequest $request, string $locale, RateLimiter $limiter): RedirectResponse
    {
        $email = Str::lower($request->validated('email'));
        $key = Str::transliterate($email.'|'.$request->ip());

        if ($limiter->tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => __('ui.login_throttled', ['seconds' => $limiter->availableIn($key)])])->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $email, 'password' => $request->validated('password')], $request->boolean('remember'))) {
            $limiter->hit($key, 60);

            return back()->withErrors(['email' => __('ui.invalid_credentials')])->onlyInput('email');
        }

        if (Auth::user()?->account_status !== 'active') {
            Auth::logout();
            $limiter->hit($key, 60);

            return back()->withErrors(['email' => __('ui.account_suspended')])->onlyInput('email');
        }

        $limiter->clear($key);
        $request->session()->regenerate();
        $user = $request->user();
        $user->forceFill(['preferred_locale' => app()->getLocale()])->saveQuietly();

        $destination = $user->isAdmin()
            ? route('admin.dashboard')
            : ($user->isStaff() ? route('staff.bookings.index') : route('account.dashboard'));

        return redirect()->intended($destination);
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(RequestPasswordResetRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __('ui.reset_link_sent'))
            : back()->withErrors(['email' => __('ui.reset_email_not_found')]);
    }

    public function showResetPassword(Request $request, string $locale, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(ResetAccountPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __('ui.password_reset_done'))
            : back()->withErrors(['email' => __('ui.reset_link_invalid')]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', __('ui.signed_out'));
    }

    private function authenticatedDestination(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        return $user->isStaff() ? route('staff.dashboard') : route('account.dashboard');
    }
}
