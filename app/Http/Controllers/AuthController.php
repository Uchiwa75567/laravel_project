<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http as HttpClient;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentification"},
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur et retourne un access token + refresh token (OAuth2, JWT RS256)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"login","password"},
     *                 @OA\Property(property="login", type="string", description="Email de l'utilisateur"),
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
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string"),
     *                 @OA\Property(property="expires_in", type="integer"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $params = $request->validate([
            'login' => 'required|email',
            'password' => 'required|string',
        ]);

        $client = DB::table('oauth_clients')->where('password_client', true)->first();
        if (!$client) {
            return response()->json(['error' => 'OAuth password client non configuré'], 500);
        }

        $tokenResponse = HttpClient::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $params['login'],
            'password' => $params['password'],
            'scope' => '',
        ]);

        if ($tokenResponse->failed()) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        $body = $tokenResponse->json();

        return response()->json([
            'access_token' => $body['access_token'] ?? null,
            'refresh_token' => $body['refresh_token'] ?? null,
            'expires_in' => $body['expires_in'] ?? null,
            'token_type' => $body['token_type'] ?? 'Bearer',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     tags={"Authentification"},
     *     summary="Renouvelle l'access token",
     *     description="Renouvelle l'access token via le refresh token (OAuth2, JWT RS256)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"refresh_token"},
     *                 @OA\Property(property="refresh_token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token renouvelé")
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $params = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $client = DB::table('oauth_clients')->where('password_client', true)->first();
        if (!$client) {
            return response()->json(['error' => 'OAuth password client non configuré'], 500);
        }

        $tokenResponse = HttpClient::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $params['refresh_token'],
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        if ($tokenResponse->failed()) {
            return response()->json(['error' => 'Refresh token invalide'], 401);
        }

        $body = $tokenResponse->json();

        return response()->json([
            'access_token' => $body['access_token'] ?? null,
            'refresh_token' => $body['refresh_token'] ?? null,
            'expires_in' => $body['expires_in'] ?? null,
            'token_type' => $body['token_type'] ?? 'Bearer',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion",
     *     description="Invalide les tokens d'accès et de refresh (OAuth2, JWT RS256)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Déconnexion réussie")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $token = $user->token();
        if ($token) {
            DB::table('oauth_refresh_tokens')->where('access_token_id', $token->id)->update(['revoked' => true]);
            $token->revoke();
        }

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
