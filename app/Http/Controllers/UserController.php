<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Users", description="Gestion des utilisateurs")
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Lister les utilisateurs",
     *     @OA\Response(response=200, description="Liste des utilisateurs")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::paginate($request->query('limit', 15));
        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Récupérer un utilisateur",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails de l'utilisateur"),
     *     @OA\Response(response=404, description="Non trouvé")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Créer un utilisateur",
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=201, description="Utilisateur créé")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response()->json($user, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Mettre à jour un utilisateur",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Utilisateur mis à jour")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);
        if (isset($data['password'])) $data['password'] = bcrypt($data['password']);
        $user->update($data);
        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Supprimer un utilisateur",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=204, description="Supprimé")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        $user->delete();
        return response()->json(null, 204);
    }
}
