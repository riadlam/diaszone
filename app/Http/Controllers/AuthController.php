<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login page (Google sign-in only).
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard.myaccount');
        }

        return view('auth.login', [
            'signupHint' => $request->boolean('signup'),
        ]);
    }

    /**
     * Legacy email/password login removed — use Google from the login page.
     */
    public function login(Request $request)
    {
        return redirect()
            ->route('login')
            ->with('error', __('auth.use_google_instead'));
    }

    /**
     * Sign up is the same as sign in with Google.
     */
    public function showSignupForm()
    {
        return redirect()->route('login', ['signup' => 1]);
    }

    /**
     * Legacy signup removed — use Google from the login page.
     */
    public function signup(Request $request)
    {
        return redirect()
            ->route('login', ['signup' => 1])
            ->with('error', __('auth.use_google_instead'));
    }

    /**
     * Redirect to Google OAuth (optionally mark return to checkout after login).
     */
    public function redirectToGoogle(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.myaccount');
        }

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('error', __('auth.google_not_configured'));
        }

        if ($request->boolean('checkout')) {
            session(['url.intended' => route('select-payment', [], true)]);
        }

        $redirect = $request->query('redirect');
        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            session(['url.intended' => url($redirect)]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.myaccount');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', __('auth.google_failed'));
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();

        if (! $googleId || ! $email) {
            return redirect()
                ->route('login')
                ->with('error', __('auth.google_missing_email'));
        }

        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->google_id = $googleId;
                $user->google_avatar = $googleUser->getAvatar();
                $user->save();
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::password(32)),
                'google_id' => $googleId,
                'google_avatar' => $googleUser->getAvatar(),
                'status' => 'active',
            ]);
        } else {
            if ($user->google_avatar !== $googleUser->getAvatar()) {
                $user->google_avatar = $googleUser->getAvatar();
                $user->save();
            }
        }

        if (! $user->isActive()) {
            return redirect()
                ->route('login')
                ->with('error', __('auth.account_inactive'));
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard.myaccount'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}
