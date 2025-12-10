<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SellerAuthController extends Controller
{
    /**
     * Show seller login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('seller')->check()) {
            return redirect()->route('seller.dashboard');
        }
        return view('seller.auth.login');
    }

    /**
     * Handle seller login
     */
    public function login(Request $request)
    {
        $request->merge(array_map('trim', $request->all()));

        // throttle key per email + ip to limit brute-force attempts
        $email = (string) ($request->input('email') ?? '');
        $key = 'seller-login:' . Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."])->setStatusCode(429);
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('seller')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $seller = Auth::guard('seller')->user();
            
            if (!$seller->isActive()) {
                Auth::guard('seller')->logout();
                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact support.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('seller.dashboard'));
        }

        // mark a failed attempt
        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show seller registration form
     */
    public function showRegisterForm()
    {
        if (Auth::guard('seller')->check()) {
            return redirect()->route('seller.dashboard');
        }
        return view('seller.auth.register');
    }

    /**
     * Handle seller registration
     */
    public function register(Request $request)
    {
        $request->merge(array_map('trim', $request->all()));

        // Throttle registrations per IP for abuse prevention
        $regKey = 'seller-register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($regKey, 3)) {
            $seconds = RateLimiter::availableIn($regKey);
            return back()->withErrors(['email' => "Too many registration attempts. Please try again in {$seconds} seconds."])->setStatusCode(429);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:sellers|alpha_dash',
            'email' => 'required|string|email|max:255|unique:sellers',
            'phone' => 'nullable|string|max:20',
            'store_name' => 'nullable|string|max:255',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Sanitise textual fields and trim to safe length
        $safeName = strip_tags(substr($validated['name'], 0, 255));
        $safeStore = isset($validated['store_name']) ? strip_tags(substr($validated['store_name'], 0, 255)) : null;

        $seller = Seller::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'store_name' => $safeStore ?? ($safeName . "'s Store"),
            'password' => Hash::make($validated['password']),
            'status' => 'pending', // Needs admin approval
            'wallet_balance' => 0,
        ]);

        RateLimiter::hit($regKey, 60);

        return redirect()->route('seller.login')
            ->with('success', 'Registration successful! Please wait for admin approval before logging in.');
    }

    /**
     * Handle seller logout
     */
    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login');
    }
}
