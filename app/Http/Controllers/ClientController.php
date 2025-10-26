<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Clients", description="Gestion des clients")
 */
class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/clients",
     *     tags={"Clients"},
     *     summary="Lister les clients",
     *     @OA\Response(response=200, description="Liste des clients")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $clients = Client::paginate($request->query('limit', 15));
        return response()->json($clients);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Récupérer un client",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails du client"),
     *     @OA\Response(response=404, description="Non trouvé")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }
        return response()->json($client);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients",
     *     tags={"Clients"},
     *     summary="Créer un client",
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=201, description="Client créé")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
        ]);
        $client = Client::create($data);
        return response()->json($client, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Mettre à jour un client",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Client mis à jour")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $id,
        ]);
        $client->update($data);
        return response()->json($client);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Supprimer un client",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=204, description="Supprimé")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }
        $client->delete();
        return response()->json(null, 204);
    }
}
