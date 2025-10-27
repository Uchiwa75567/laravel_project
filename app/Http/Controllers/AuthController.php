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
     *     path="/v1/auth/login",
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
     *                 @OA\Property(property="login", type="string", description="Login (email) de l'utilisateur"),
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
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string"),
     *                 @OA\Property(property="expires_in", type="integer")
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
            return response()->json(['success' => false, 'message' => 'OAuth password client not configured'], 500);
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
     * @OA\Post(
     *     path="/v1/auth/register",
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
     *     @OA\Response(response=201, description="Inscription réussie"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $params = $request->validate([
            'login' => 'required|email',
            'password' => 'required|string|min:8',
            'nom' => 'required|string',
            'nci' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'required|string|unique:users,phone',
            'adresse' => 'nullable|string',
        ]);

        $userClass = \App\Models\User::class;
        $user = $userClass::create([
            'name' => $params['nom'],
            'email' => $params['email'],
            'phone' => $params['telephone'],
            'role' => 'client',
            'password' => bcrypt($params['password']),
            'preferences' => [
                'nci' => $params['nci'] ?? null,
                'adresse' => $params['adresse'] ?? null,
                'login' => $params['login'] ?? null,
            ],
        ]);

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
            return response()->json(['success' => false, 'message' => 'Unable to issue token', 'details' => $tokenResponse->body()], 500);
        }

        $body = $tokenResponse->json();

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => [
                'user' => $user,
                'token' => $body['access_token'] ?? null,
                'refresh_token' => $body['refresh_token'] ?? null,
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/auth/refresh",
     *     tags={"Authentification"},
     *     summary="Refresh token",
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
     *     @OA\Response(response=200, description="Token refreshed")
     * )
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
     * @OA\Post(
     *     path="/v1/auth/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion",
     *     description="Révoque le token d'accès actuel",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Déconnexion réussie")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $token = $user->token();
        if ($token) {
            DB::table('oauth_refresh_tokens')->where('access_token_id', $token->id)->update(['revoked' => true]);
            $token->revoke();
        }

        return response()->json(['success' => true, 'message' => 'Déconnexion réussie']);
    }

    /**
     * @OA\Get(
     *     path="/v1/auth/authentification",
     *     tags={"Authentification"},
     *     summary="Informations d'authentification",
     *     description="Retourne les informations de l'utilisateur authentifié",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function authentification(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        return response()->json(['success' => true, 'data' => ['user' => $user]]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/auth/delete/{id}",
     *     tags={"Authentification"},
     *     summary="Supprimer un compte utilisateur par son id",
     *     description="Supprime le compte de l'utilisateur spécifié. Seul l'utilisateur lui-même ou un administrateur peut supprimer le compte.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur à supprimer",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Compte supprimé avec succès"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function deleteAccount(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Only allow self-deletion or admin
        if ($user->id != $id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Action non autorisée'], 403);
        }

        $userToDelete = \App\Models\User::find($id);
        if (!$userToDelete) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }

        $userToDelete->delete();
        return response()->json(['success' => true, 'message' => 'Compte supprimé avec succès']);
    }
}
