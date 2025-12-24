<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as Google_Client; 
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class GoogleController extends Controller
{
    public function handleGoogleLogin(Request $request)
    {
        try {
            // 1. Get Client ID from your config file for security
            $clientId = config('services.google.client_id');
            $client = new Google_Client(['client_id' => $clientId]);
            
            // 2. Verify the JWT ID Token
            $payload = $client->verifyIdToken($request->token);

            if ($payload) {
                $email = $payload['email'];
                $name = $payload['name'];
                $googleId = $payload['sub'];

                // 3. Find or update/create the user
                $user = User::where('email', $email)->first();

                if ($user) {
                    if (!$user->google_id) {
                        $user->update(['google_id' => $googleId]);
                    }
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'google_id' => $googleId,
                        'password' => Hash::make(Str::random(16)), 
                        'email_verified_at' => now(), 
                    ]);
                }

                // 4. Authenticate and SECURE the session
                Auth::login($user, true);
                $request->session()->regenerate(); // Important security step

                return response()->json([
                    'status' => 'success',
                    'message' => 'Welcome to TradLanka, ' . $user->name,
                    'user' => $user
                ]);
            }

            return response()->json([
                'status' => 'error', 
                'message' => 'Google Authentication failed. Invalid token.'
            ], 401);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}