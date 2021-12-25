<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthenticationController extends Controller
{

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email|confirmed',
            'account' => 'nullable|string|unique:users,account',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'nullable|string|max:255',
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['required', 'date'],
            'city' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(Arr::add($validator->getMessageBag()->toArray(), 'success', 'false'));
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => Arr::get($validated, 'name'),
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
            'account' => Arr::get($validated, 'account'),
            'gender' => $validated['gender'],
            'birthday' => $validated['birthday'],
            'city' => $validated['city'],
        ]);

        event(new Registered($user));

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
            'success' => true,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->getMessageBag());
        }

        $validated = $validator->validated();

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => __('auth.failed')
            ], 401);
        }

        return response()->json([
            'user' => auth()->user(),
            'token' => auth()->user()->createToken('API Token')->plainTextToken
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => __('auth.tokens_revoked')
        ]);
    }

    public function show(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->getMessageBag());
        }

        $validated = $validator->validated();

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->json([
            'message' => __('passwords.reset')
        ]);
    }

    public function email()
    {
        Mail::to(env('TEST_EMAIL'))->send(new TestMail());
    }

    public function profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6|confirmed',
            'name' => 'nullable|string|max:255',
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['nullable', 'date'],
            'city' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(Arr::add($validator->getMessageBag()->toArray(), 'success', 'false'));
        }

        $validated = $validator->validated();

        $profile = [];
        foreach ($validated as $key => $store)
        {
            if (!$store) {
                continue;
            }
            if ($key == 'password') {
                $store = Hash::make($validated['password']);
            }
            $profile[$key] = $store;
        }

        auth()->user()->update($profile);

        return response()->json([
            'user' => auth()->user()->refresh(),
            'success' => true,
        ]);
    }
}
