<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            // Redirect admin users to admin dashboard
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard.myaccount');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input - Laravel automatically protects against SQL injection
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Attempt authentication - Laravel uses prepared statements (SQL injection protected)
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Check if user exists and is active before attempting login
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->isActive()) {
            return back()
                ->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support for assistance.',
                ])
                ->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, $remember)) {
            // Double check status after authentication
            if (!Auth::user()->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()
                    ->withErrors([
                        'email' => 'Your account has been deactivated. Please contact support for assistance.',
                    ])
                    ->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            // Redirect admin users to admin dashboard
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard.myaccount'));
        }

        return back()
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])
            ->withInput($request->only('email'));
    }

    /**
     * Show the signup form
     */
    public function showSignupForm()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            return redirect()->route('dashboard.myaccount');
        }
        
        return view('auth.signup');
    }

    /**
     * Handle signup request
     */
    public function signup(Request $request)
    {
        // Validate input with strong password rules - Laravel automatically protects against SQL injection
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)
                ->letters()
                ->numbers()],
            'password_confirmation' => ['required'],
        ], [
            'name.required' => 'Name is required.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password_confirmation.required' => 'Please confirm your password.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Create user - Laravel's Eloquent uses prepared statements (SQL injection protected)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password is hashed, never stored in plain text
            'status' => 'active', // New users are active by default
        ]);

        // Automatically log in the user after signup
        Auth::login($user);

        return redirect()->route('dashboard.myaccount')
            ->with('success', 'Account created successfully! Welcome to DiasZone.');
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

