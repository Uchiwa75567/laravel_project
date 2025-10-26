<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http as HttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Login using password grant and return access + refresh tokens
     */
    public function login(Request $request): JsonResponse
    {
        $params = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find password grant client
        $client = DB::table('oauth_clients')->where('password_client', true)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'OAuth password client not configured'], 500);
        }

        $tokenResponse = HttpClient::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $params['email'],
            'password' => $params['password'],
            'scope' => '',
        ]);

        if ($tokenResponse->failed()) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials or oauth error', 'details' => $tokenResponse->body()], 401);
        }

        $body = $tokenResponse->json();

        return response()->json([
            'success' => true,
            'access_token' => $body['access_token'] ?? null,
            'refresh_token' => $body['refresh_token'] ?? null,
            'expires_in' => $body['expires_in'] ?? null,
            'token_type' => $body['token_type'] ?? 'Bearer',
        ]);
    }

    /**
     * Refresh access token using refresh_token
     */
    public function refresh(Request $request): JsonResponse
    {
        $params = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $client = DB::table('oauth_clients')->where('password_client', true)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'OAuth password client not configured'], 500);
        }

        $tokenResponse = HttpClient::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $params['refresh_token'],
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        if ($tokenResponse->failed()) {
            return response()->json(['success' => false, 'message' => 'Invalid refresh token or oauth error', 'details' => $tokenResponse->body()], 401);
        }

        $body = $tokenResponse->json();

        return response()->json([
            'success' => true,
            'access_token' => $body['access_token'] ?? null,
            'refresh_token' => $body['refresh_token'] ?? null,
            'expires_in' => $body['expires_in'] ?? null,
            'token_type' => $body['token_type'] ?? 'Bearer',
        ]);
    }

    /**
     * Logout: revoke current access token and associated refresh tokens
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $token = $user->token();
        if ($token) {
            // revoke refresh tokens
            DB::table('oauth_refresh_tokens')->where('access_token_id', $token->id)->update(['revoked' => true]);
            $token->revoke();
        }

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }
}
