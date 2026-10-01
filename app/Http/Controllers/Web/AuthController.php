<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Session-based auth for the Blade frontend. Same users table, same
 * blocked/pending rules as the API — only the transport differs
 * (cookie session instead of a Sanctum token).
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect('/') : view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }
        if ($user->is_blocked) {
            throw ValidationException::withMessages(['email' => 'This account has been suspended.']);
        }
        if ($user->status === 'pending') {
            throw ValidationException::withMessages(['email' => 'Please verify your email first — we sent you a link when you registered.']);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return Auth::check() ? redirect('/') : view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'customer',
            'status'   => 'active',
        ]);

        try {
            app(NotificationService::class)->accountCreated($user);
        } catch (\Throwable) {
            // Mail must never block registration.
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Google sign-in bridge. The API callback redirects the browser to
     * /auth/google#token=… (Sanctum token in the URL fragment). This page
     * reads the fragment client-side and exchanges it for a session.
     */
    public function googleBridge()
    {
        return view('auth.google-bridge');
    }

    public function googleExchange(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);

        $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($data['token']);
        if (! $pat || ! $pat->tokenable) {
            return response()->json(['message' => 'Invalid sign-in token.'], 422);
        }

        $user = $pat->tokenable;
        $pat->delete(); // one-time use: the token becomes a session and is retired

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json(['redirect' => url('/')]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
