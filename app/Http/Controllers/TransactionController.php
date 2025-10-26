<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Transactions", description="Gestion des transactions")
 */
class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Lister les transactions",
     *     @OA\Response(response=200, description="Liste des transactions")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::with(['compte', 'compteDestination'])->paginate($request->query('limit', 15));
        return response()->json($transactions);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Récupérer une transaction",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails de la transaction"),
     *     @OA\Response(response=404, description="Non trouvé")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $tx = Transaction::find($id);
        if (!$tx) return response()->json(['message' => 'Transaction non trouvée'], 404);
        return response()->json($tx);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Créer une transaction",
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=201, description="Transaction créée")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|string',
            'montant' => 'required|numeric',
            'compte_id' => 'required|uuid|exists:comptes,id',
        ]);
        $tx = Transaction::create(array_merge($data, ['reference' => Transaction::generateReference(), 'date_transaction' => now()]));
        return response()->json($tx, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Mettre à jour une transaction",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Transaction mise à jour")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tx = Transaction::find($id);
        if (!$tx) return response()->json(['message' => 'Transaction non trouvée'], 404);
        $data = $request->validate([
            'statut' => 'sometimes|string',
            'description' => 'sometimes|string',
        ]);
        $tx->update($data);
        return response()->json($tx);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Supprimer une transaction",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=204, description="Supprimé")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $tx = Transaction::find($id);
        if (!$tx) return response()->json(['message' => 'Transaction non trouvée'], 404);
        $tx->delete();
        return response()->json(null, 204);
    }
}
