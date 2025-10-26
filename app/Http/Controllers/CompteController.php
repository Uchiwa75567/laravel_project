<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    /**
     * Display a listing of the comptes.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validation des paramètres de requête
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'type' => 'string|in:courant,epargne,entreprise',
            'statut' => 'string|in:actif,bloque,ferme',
            'search' => 'string|nullable',
            'sort' => 'string|in:dateCreation,solde,titulaire',
            'order' => 'string|in:asc,desc',
        ]);

        $page = $validated['page'] ?? 1;
        $limit = $validated['limit'] ?? 10;
        $type = $validated['type'] ?? null;
        $statut = $validated['statut'] ?? null;
        $search = $validated['search'] ?? null;
        $sort = $validated['sort'] ?? 'dateCreation';
        $order = $validated['order'] ?? 'desc';

        // Construction de la requête
        $query = Compte::with('client:id,nom,prenom,email');

        // Filtrage selon le rôle de l'utilisateur
        if (!$user->isAdmin()) {
            // Pour les clients, récupérer seulement leurs comptes
            $client = Client::where('email', $user->email)->first();
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }
            $query->where('client_id', $client->id);
        }

        // Appliquer les filtres
        if ($type) {
            $query->where('type', $type);
        }

        if ($statut) {
            $query->where('is_active', $statut === 'actif');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('prenom', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Appliquer le tri
        switch ($sort) {
            case 'dateCreation':
                $query->orderBy('date_ouverture', $order);
                break;
            case 'solde':
                $query->orderBy('solde', $order);
                break;
            case 'titulaire':
                $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                      ->orderBy('clients.nom', $order)
                      ->orderBy('clients.prenom', $order)
                      ->select('comptes.*');
                break;
        }

        // Pagination
        $comptes = $query->paginate($limit, ['*'], 'page', $page);

        // Transformer les données
        $data = $comptes->getCollection()->map(function ($compte) {
            return [
                'id' => $compte->id,
                'numeroCompte' => $compte->numero,
                'titulaire' => $compte->client->nom . ' ' . $compte->client->prenom,
                'type' => $compte->type,
                'solde' => (float) $compte->solde,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_ouverture->toDateString(),
                'statut' => $compte->is_active ? 'actif' : 'bloque',
                'metadata' => [
                    'derniereModification' => $compte->updated_at->toISOString(),
                ],
                'version' => 1,
            ];
        });

        // Construire la réponse de pagination
        $pagination = [
            'currentPage' => $comptes->currentPage(),
            'totalPages' => $comptes->lastPage(),
            'totalItems' => $comptes->total(),
            'itemsPerPage' => $comptes->perPage(),
            'hasNext' => $comptes->hasMorePages(),
            'hasPrevious' => $comptes->currentPage() > 1,
        ];

        // Construire les liens
        $queryParams = $request->query();
        $baseUrl = '/api/v1/comptes';

        $links = [
            'self' => $baseUrl . '?' . http_build_query($queryParams),
            'first' => $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])),
            'last' => $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $comptes->lastPage()])),
        ];

        if ($comptes->hasMorePages()) {
            $links['next'] = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $comptes->currentPage() + 1]));
        }

        if ($comptes->currentPage() > 1) {
            $links['previous'] = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $comptes->currentPage() - 1]));
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => $pagination,
            'links' => $links,
        ]);
    }

    /**
     * Retrieve a specific compte by ID.
     *
     * @OA\Get(
     *     path="/api/v1/comptes/{compteId}",
     *     tags={"Comptes"},
     *     summary="Récupérer un compte par ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="UUID du compte",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string"),
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="type", type="string", example="courant"),
     *                 @OA\Property(property="solde", type="number", format="float"),
     *                 @OA\Property(property="devise", type="string"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="motifBlocage", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas."),
     *                 @OA\Property(property="details", type="object", @OA\Property(property="compteId", type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $compteId): JsonResponse
    {
        $user = Auth::user();

        $compte = Compte::with('client')->find($compteId);
        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => "Le compte avec l'ID spécifié n'existe pas.",
                    'details' => ['compteId' => $compteId],
                ],
            ], 404);
        }

        // If not admin, ensure the compte belongs to the authenticated user's client
        if (!$user->isAdmin()) {
            $client = Client::where('email', $user->email)->first();
            if (!$client || $compte->client_id !== $client->id) {
                // Return 404 to avoid leaking existence of others' comptes
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMPTE_NOT_FOUND',
                        'message' => "Le compte avec l'ID spécifié n'existe pas.",
                        'details' => ['compteId' => $compteId],
                    ],
                ], 404);
            }
        }

        $client = $compte->client;
        $titulaire = $client->name ?? (isset($client->nom) ? trim(($client->nom ?? '') . ' ' . ($client->prenom ?? '')) : ($client->email ?? ''));

        $data = [
            'id' => (string) $compte->id,
            'numeroCompte' => $compte->numero,
            'titulaire' => $titulaire,
            'type' => $compte->type,
            'solde' => (float) $compte->solde,
            'devise' => $compte->devise,
            'dateCreation' => $compte->date_ouverture?->toISOString() ?? ($compte->date_ouverture?->toDateTimeString() ?? null),
            'statut' => $compte->is_active ? 'actif' : 'bloque',
            'motifBlocage' => $compte->motif_blocage ?? null,
            'metadata' => [
                'derniereModification' => $compte->updated_at?->toISOString() ?? ($compte->updated_at?->toDateTimeString() ?? null),
                'version' => 1,
            ],
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Create a new compte. If the client doesn't exist, create the client and a user account.
     *
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     tags={"Comptes"},
     *     summary="Créer un compte",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="solde", type="number", format="float"),
     *             @OA\Property(property="devise", type="string"),
     *             @OA\Property(property="client", type="object")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compte créé"),
     *     @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'type' => 'required|string|in:courant,epargne,joint',
            'solde' => 'required|numeric|min:10000',
            'devise' => 'required|string|size:3',
            'client.name' => 'required|string|max:255',
            'client.email' => 'required|email|unique:clients,email',
            'client.phone' => ['required','unique:clients,phone','regex:/^\+221[0-9]{8,9}$/'],
            'client.address' => 'required|string|max:500',
        ], [
            'client.email.unique' => 'L\'email est déjà utilisé',
            'client.phone.unique' => 'Le téléphone est déjà utilisé',
            'client.phone.regex' => 'Le téléphone doit être au format international sénégalais, ex: +221771234567',
            'solde.min' => 'Le solde initial doit être supérieur ou égal à 10000',
        ]);

        $user = Auth::user();

        // Check or create client
        $clientData = $payload['client'];
        $client = \App\Models\Client::where('email', $clientData['email'])->orWhere('phone', $clientData['phone'])->first();
        $generatedPassword = null;
        $generatedCode = null;
        if (!$client) {
            // generate password and code
            $generatedPassword = \Illuminate\Support\Str::random(10);
            $generatedCode = rand(100000, 999999);

            // create an associated User so the client can authenticate
            $userModel = \App\Models\User::where('email', $clientData['email'])->first();
            if (!$userModel) {
                $userModel = \App\Models\User::create([
                    'name' => $clientData['name'],
                    'email' => $clientData['email'],
                    'password' => bcrypt($generatedPassword),
                    'is_active' => true,
                ]);
            }

            $client = \App\Models\Client::create([
                'name' => $clientData['name'],
                'email' => $clientData['email'],
                'phone' => $clientData['phone'],
                'address' => $clientData['address'] ?? null,
                'is_active' => true,
                'last_order_at' => null,
            ]);

            // Send authentication email with password (simple Mailable)
            try {
                \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ClientCredentialsMail($generatedPassword));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Unable to send client credentials email: ' . $e->getMessage());
            }

            // Send SMS with code via Twilio if configured, otherwise log it
            try {
                $twilioSid = config('services.twilio.sid');
                $twilioToken = config('services.twilio.token');
                $twilioFrom = config('services.twilio.from');

                if ($twilioSid && $twilioToken && $twilioFrom) {
                    $twilio = new \Twilio\Rest\Client($twilioSid, $twilioToken);
                    $twilio->messages->create($client->phone, [
                        'from' => $twilioFrom,
                        'body' => "Votre code de vérification: {$generatedCode}",
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::info("SMS to {$client->phone}: your verification code is {$generatedCode}");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Unable to send SMS: ' . $e->getMessage());
            }
        }

        // Create compte
        $numero = method_exists(\App\Models\Compte::class, 'generateNumero') ? \App\Models\Compte::generateNumero() : \Illuminate\Support\Str::upper('C' . \Illuminate\Support\Str::random(8));

        $compte = \App\Models\Compte::create([
            'numero' => $numero,
            'type' => $payload['type'],
            'solde' => $payload['solde'],
            'devise' => $payload['devise'],
            'is_active' => true,
            'client_id' => $client->id,
            'date_ouverture' => now(),
            'last_transaction_at' => null,
        ]);

        $responseData = [
            'success' => true,
            'message' => 'Compte créé avec succès',
            'data' => [
                'id' => (string) $compte->id,
                'numeroCompte' => $compte->numero,
                'titulaire' => $client->name,
                'type' => $compte->type,
                'solde' => (float) $compte->solde,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_ouverture->toISOString(),
                'statut' => $compte->is_active ? 'actif' : 'bloque',
                'metadata' => [
                    'derniereModification' => $compte->updated_at?->toISOString(),
                    'version' => 1,
                ],
            ],
        ];

        return response()->json($responseData, 201);
    }

    /**
     * Update client information related to a compte (partial update).
     * All fields optional but at least one must be provided.
     */
    public function update(Request $request, string $compteId): JsonResponse
    {
        $user = Auth::user();

        // Quick presence check: at least one of the accepted fields must be present
        $hasTitulaire = $request->filled('titulaire');
        $info = $request->input('informationsClient', []);
        $hasInfoFields = is_array($info) && (array_key_exists('telephone', $info) || array_key_exists('email', $info) || array_key_exists('password', $info));

        if (!$hasTitulaire && !$hasInfoFields) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_FIELDS_PROVIDED',
                    'message' => 'Au moins un champ doit être fourni pour la mise à jour.',
                ],
            ], 422);
        }

        // Validate formats
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient.telephone' => ['sometimes','regex:/^\+221[0-9]{8,9}$/'],
            'informationsClient.email' => 'sometimes|email',
            'informationsClient.password' => ['sometimes','string','regex:/^(?=.{10,}$)(?=(.*[a-z]){2,})(?=(.*[^A-Za-z0-9]){2,})[A-Z].*/'],
        ], [
            'informationsClient.telephone.regex' => 'Le téléphone doit être au format international sénégalais, ex: +221771234567',
            'informationsClient.password.regex' => 'Le mot de passe doit contenir au moins 10 caractères, commencer par une lettre majuscule, contenir au moins 2 lettres minuscules et 2 caractères spéciaux',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $compte = Compte::with('client')->find($compteId);
        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => "Le compte avec l'ID spécifié n'existe pas.",
                    'details' => ['compteId' => $compteId],
                ],
            ], 404);
        }

        // Authorization: non-admins can only update their own comptes
        if (!$user->isAdmin()) {
            $clientAuth = Client::where('email', $user->email)->first();
            if (!$clientAuth || $compte->client_id !== $clientAuth->id) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMPTE_NOT_FOUND',
                        'message' => "Le compte avec l'ID spécifié n'existe pas.",
                        'details' => ['compteId' => $compteId],
                    ],
                ], 404);
            }
        }

        $client = $compte->client;

        // Uniqueness checks
        if (isset($info['telephone']) && $info['telephone'] !== $client->phone) {
            if (Client::where('phone', $info['telephone'])->where('id', '<>', $client->id)->exists()) {
                return response()->json(['success' => false, 'error' => ['code' => 'PHONE_TAKEN', 'message' => 'Le téléphone est déjà utilisé']], 422);
            }
        }

        if (isset($info['email']) && $info['email'] !== $client->email) {
            if (Client::where('email', $info['email'])->where('id', '<>', $client->id)->exists()) {
                return response()->json(['success' => false, 'error' => ['code' => 'EMAIL_TAKEN', 'message' => 'L\'email est déjà utilisé']], 422);
            }
            // Also ensure no other user has that email
            $existingUser = \App\Models\User::where('email', $info['email'])->first();
            if ($existingUser && $existingUser->email !== $client->email) {
                return response()->json(['success' => false, 'error' => ['code' => 'EMAIL_TAKEN', 'message' => 'L\'email est déjà utilisé par un compte utilisateur']], 422);
            }
        }

        // Apply updates
        $oldEmail = $client->email;
        $userModel = \App\Models\User::where('email', $oldEmail)->first();

        if ($hasTitulaire) {
            $client->name = $request->input('titulaire');
        }

        if (isset($info['telephone'])) {
            $client->phone = $info['telephone'];
        }

        if (isset($info['email'])) {
            $client->email = $info['email'];
            // Update associated user email if present
            if ($userModel) {
                $userModel->email = $info['email'];
                $userModel->save();
            }
        }

        if (isset($info['password'])) {
            $newPassword = $info['password'];
            if ($userModel) {
                $userModel->password = bcrypt($newPassword);
                $userModel->save();
            } else {
                // create a user for this client if none exists
                \App\Models\User::create([
                    'name' => $client->name ?? ($client->email ?? 'client'),
                    'email' => $client->email,
                    'password' => bcrypt($newPassword),
                    'is_active' => true,
                ]);
            }
        }

        $client->save();

        // Prepare response data (similar to show)
        $titulaire = $client->name ?? (isset($client->nom) ? trim(($client->nom ?? '') . ' ' . ($client->prenom ?? '')) : ($client->email ?? ''));

        $data = [
            'id' => (string) $compte->id,
            'numeroCompte' => $compte->numero,
            'titulaire' => $titulaire,
            'type' => $compte->type,
            'solde' => (float) $compte->solde,
            'devise' => $compte->devise,
            'dateCreation' => $compte->date_ouverture?->toISOString() ?? ($compte->date_ouverture?->toDateTimeString() ?? null),
            'statut' => $compte->is_active ? 'actif' : 'bloque',
            'metadata' => [
                'derniereModification' => $compte->updated_at?->toISOString() ?? ($compte->updated_at?->toDateTimeString() ?? null),
                'version' => 1,
            ],
        ];

        return response()->json(['success' => true, 'message' => 'Compte mis à jour avec succès', 'data' => $data], 201);
    }
}
