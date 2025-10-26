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
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentification"},
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur et retourne un token d'accès",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"login","password"},
     *                 @OA\Property(property="login", type="string", description="Login de l'utilisateur"),
     *                 @OA\Property(property="password", type="string", format="password", description="Mot de passe")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Connexion réussie"),
     *                 @OA\Property(property="data", type="object",
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="login", type="string"),
     *                         @OA\Property(property="type", type="string", enum={"admin","client"})
     *                     ),
     *                     @OA\Property(property="token", type="string", description="Token d'accès Bearer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=false),
     *                 @OA\Property(property="message", type="string", example="Identifiants invalides")
     *             )
     *         )
     *     )
     * )
     */
    /**
     * Login using password grant and return access + refresh tokens
     */
    public function login(Request $request): JsonResponse
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentification"},
     *     summary="Inscription d'un nouveau client",
     *     description="Crée un nouveau compte client",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"login","password","nom","nci","email","telephone","adresse"},
     *                 @OA\Property(property="login", type="string"),
     *                 @OA\Property(property="password", type="string", format="password"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="nci", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="telephone", type="string"),
     *                 @OA\Property(property="adresse", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Inscription réussie"),
     *                 @OA\Property(property="data", type="object",
     *                     @OA\Property(property="user", type="object"),
     *                     @OA\Property(property="token", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    // Note: the real registration logic may live elsewhere (CompteController). This annotation exposes /register in the docs.
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
    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion",
     *     description="Révoque le token d'accès actuel",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *             )
     *         )
     *     )
     * )
     */
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
