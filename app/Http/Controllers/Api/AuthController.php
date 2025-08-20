<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Registered', 'user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'scope'    => ['nullable', 'string'],
        ]);

        $base = rtrim(env('PASSPORT_BASE_URL', config('app.url')), '/');

        $response = Http::asForm()
            ->acceptJson()
            ->post("$base/oauth/token", [
                'grant_type'    => 'password',
                'client_id'     => config('services.passport.password_client_id'),
                'client_secret' => config('services.passport.password_client_secret'),
                'username'      => $data['email'],
                'password'      => $data['password'],
                'scope'         => $data['scope'] ?? '',
            ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'OAuth error',
                'status'  => $response->status(),
                'error'   => $response->json('error'),
                'error_description' => $response->json('error_description'),
            ], 401);
        }

        return response()->json($response->json());
    }


    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $clientId     = config('services.passport.password_client_id');
        $clientSecret = config('services.passport.password_client_secret');

        $tokenResponse = Http::asForm()->post(url('/oauth/token'), [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'scope'         => $request->input('scope', ''),
        ]);

        if ($tokenResponse->failed()) {
            return response()->json(['message' => 'Refresh failed', 'error' => $tokenResponse->json()], 401);
        }

        return response()->json($tokenResponse->json());
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user->token();

        $token->revoke();

        return response()->json(['message' => 'Logged out']);
    }
}
