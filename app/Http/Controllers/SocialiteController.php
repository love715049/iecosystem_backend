<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function login(Request $request, $provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json([
                'message' => Str::ucfirst($provider) . ' auth fail.'
            ]);
        }

        $url = Socialite::with($provider)->stateless()->redirect()->getTargetUrl();
        return redirect($url);
    }

    public function call_back(Request $request, $provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json([
                'message' => Str::ucfirst($provider) . ' auth fail.'
            ]);
        }

        $userInfo = Socialite::driver($provider)->stateless()->user();
        if (!$userInfo) {
            return response()->json([
                'message' => Str::ucfirst($provider) . ' auth fail.'
            ]);
        }
        $user = $this->create_user($userInfo);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    private function create_user($data)
    {
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now()
            ]
        );

        return $user;
    }
}
