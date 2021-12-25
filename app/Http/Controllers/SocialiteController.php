<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function login(Request $request, $provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json([
                'message' => __('auth.socialite', ['provider' => Str::ucfirst($provider)])
            ]);
        }

        $url = Socialite::with($provider)->stateless()->redirect()->getTargetUrl();
        return redirect($url);
    }

    public function call_back(Request $request, $provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json([
                'message' => __('auth.socialite', ['provider' => Str::ucfirst($provider)])
            ]);
        }

        $userInfo = Socialite::driver($provider)->stateless()->user();
        if (!$userInfo) {
            return response()->json([
                'message' => __('auth.socialite', ['provider' => Str::ucfirst($provider)])
            ]);
        }
        $user = $this->create_user($userInfo);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    public function line_login()
    {
        $url = 'https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=' .
            config('services.line.client_id') .
            '&redirect_uri=' . config('services.line.redirect') .
            '&state=e3fdewfcewcff' .
            '&scope=profile%20openid%20email';
        return redirect($url);
    }

    public function line_call_back(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.line.redirect'),
            'client_id' => config('services.line.client_id'),
            'client_secret' => config('services.line.client_secret'),
        ]);

        if (!$response->ok()) {
            return response()->json([
                'message' => __('auth.socialite', ['provider' => 'Line'])
            ]);
        }

        $id_token = $response->json('id_token');
        $userInfoResponse = Http::asForm()->post('https://api.line.me/oauth2/v2.1/verify', [
            'client_id' => config('services.line.client_id'),
            'id_token' => $id_token,
        ]);

        if (!$userInfoResponse->ok()) {
            return response()->json([
                'message' => __('auth.socialite', ['provider' => 'Line'])
            ]);
        }

        $user = $this->create_user($userInfoResponse);

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
