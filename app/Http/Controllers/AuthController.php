<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }

        // First check if user exists by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User exists, update their google_id and avatar
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                // User does not exist in database - block login
                // Exception: allow superadmin email to create their own account
                $superadminEmail = env('SUPERADMIN_EMAIL', 'nicholas.joe.sumantri@gmail.com');
                
                if ($googleUser->getEmail() === $superadminEmail) {
                    // Create superadmin account
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'role' => 'superadmin',
                        'email_verified_at' => now(),
                    ]);
                } else {
                    // Redirect with error - user not registered
                    return redirect()->route('login')->with('error', 'Access denied. Your email is not registered in the system. Please contact the Superadmin to request access.');
                }
            }
        }

        Auth::login($user, true);

        return $this->redirectBasedOnRole($user);
    }

    protected function determineRole(string $email): string
    {
        $superadminEmail = env('SUPERADMIN_EMAIL', 'nicholas.joe.sumantri@gmail.com');
        
        if ($email === $superadminEmail) {
            return 'superadmin';
        }

        return 'supplier';
    }

    protected function redirectBasedOnRole(User $user)
    {
        // All roles redirect to dashboard after login
        return redirect()->route('dashboard');
    }

    public function logoutConfirm()
    {
        return view('auth.logout-confirm');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
