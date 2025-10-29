<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BlockCompteRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="API Endpoints pour la gestion des comptes bancaires"
 * )
 */
class CompteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     tags={"Comptes"},
     *     summary="Lister tous les comptes",
     *     description="Liste de tous les comptes avec pagination et filtres",
     *     operationId="listComptes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de la page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche sur numéro de compte ou nom du titulaire",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                      *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string"),
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="solde", type="number"),
     *                 @OA\Property(property="devise", type="string"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer")
     *                 )
     *             )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=3),
     *                 @OA\Property(property="totalItems", type="integer", example=25),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=true),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="self", type="string", example="/api/v1/comptes?page=1&limit=10"),
     *                 @OA\Property(property="next", type="string", example="/api/v1/comptes?page=2&limit=10"),
     *                 @OA\Property(property="first", type="string", example="/api/v1/comptes?page=1&limit=10"),
     *                 @OA\Property(property="last", type="string", example="/api/v1/comptes?page=3&limit=10")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validation des paramètres de requête
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'type' => 'string|in:epargne,cheque',
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
        $query = Compte::with('client:id,name,email');

        // Filtrage selon le rôle de l'utilisateur
        if (!$user->isAdmin()) {
            // Pour les clients, récupérer seulement leurs comptes
            $client = Client::where('email', $user->email)->first();
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nous n\'avons pas pu trouver votre profil client. Veuillez contacter notre service client.'
                ], 404);
            }
            $query->where('client_id', $client->id);
        }

        // Appliquer les filtres
        if ($type) {
            $query->where('type', $type);
        }

        if ($statut) {
            switch ($statut) {
                case 'actif':
                    $query->where('is_active', true)
                          ->where('is_archived', false)
                          ->where(function ($q) {
                              $q->whereNull('date_debut_blocage')
                                ->orWhere('date_debut_blocage', '>', now())
                                ->orWhere(function ($subQ) {
                                    $subQ->whereNotNull('date_fin_blocage')
                                          ->where('date_fin_blocage', '<', now());
                                });
                          });
                    break;
                case 'bloque':
                    $query->whereNotNull('date_debut_blocage')
                          ->where('date_debut_blocage', '<=', now())
                          ->where(function ($q) {
                              $q->whereNull('date_fin_blocage')
                                ->orWhere('date_fin_blocage', '>', now());
                          });
                    break;
                case 'ferme':
                    $query->where('is_archived', true);
                    break;
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%")
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
                      ->orderBy('clients.name', $order)
                      ->select('comptes.*');
                break;
        }

        // Pagination
        $comptes = $query->paginate($limit, ['*'], 'page', $page);

        // Transformer les données
        $data = $comptes->getCollection()->map(function ($compte) {
            $statut = 'actif';
            if ($compte->is_archived) {
                $statut = 'ferme';
            } elseif ($compte->isBlocked()) {
                $statut = 'bloque';
            }

            return [
                'id' => $compte->id,
                'numeroCompte' => $compte->numero,
                'titulaire' => $compte->client->name,
                'type' => $compte->type,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_ouverture->toDateString(),
                'statut' => $statut,
                'motifBlocage' => $compte->motif_blocage ?? null,
                'dateArchivage' => $compte->archived_at?->toISOString(),
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
                    'message' => "Nous n'avons pas pu trouver le compte demandé. Veuillez vérifier l'identifiant et réessayer.",
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
                        'message' => "Nous n'avons pas pu trouver le compte demandé. Veuillez vérifier l'identifiant et réessayer.",
                        'details' => ['compteId' => $compteId],
                    ],
                ], 404);
            }
        }

        $client = $compte->client;
        $titulaire = $client->name ?? $client->email;

        $statut = 'actif';
        if ($compte->is_archived) {
            $statut = 'ferme';
        } elseif ($compte->isBlocked()) {
            $statut = 'bloque';
        }

        $data = [
            'id' => (string) $compte->id,
            'numeroCompte' => $compte->numero,
            'titulaire' => $titulaire,
            'type' => $compte->type,
            'devise' => $compte->devise,
            'dateCreation' => $compte->date_ouverture?->toISOString() ?? ($compte->date_ouverture?->toDateTimeString() ?? null),
            'statut' => $statut,
            'motifBlocage' => $compte->motif_blocage ?? null,
            'dateArchivage' => $compte->archived_at?->toISOString(),
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
     *     summary="Créer un compte bancaire",
     *     description="Crée un nouveau compte bancaire pour un client existant ou nouveau. Un email de notification est automatiquement envoyé au client.",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             type="object",
      *             required={"type", "devise", "client"},
      *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="epargne", description="Type de compte"),
      *             @OA\Property(property="devise", type="string", example="CFA", description="Devise du compte"),
      *             @OA\Property(
      *                 property="client",
      *                 type="object",
      *                 nullable=true,
      *                 required={"titulaire", "nci", "email", "telephone", "adresse"},
      *                 @OA\Property(property="titulaire", type="string", example="bachir ndiaye", description="Nom du titulaire"),
      *                 @OA\Property(property="nci", type="string", example="1937200100168", description="Numéro de carte d'identité"),
      *                 @OA\Property(property="email", type="string", format="email", example="bachir@diame.com", description="Adresse email"),
      *                 @OA\Property(property="telephone", type="string", example="+221775626363", description="Numéro de téléphone (+221...)"),
      *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal", description="Adresse complète")
      *             )
      *         )
      *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre compte bancaire a été créé avec succès. Vous recevrez vos identifiants de connexion par email."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string"),
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="solde", type="number"),
     *                 @OA\Property(property="devise", type="string"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="actif")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données invalides"),
     *     @OA\Response(response=404, description="Client non trouvé"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'type' => 'required|string|in:epargne,cheque',
            'devise' => 'required|string|size:3',
            'client_id' => 'nullable|uuid|exists:clients,id',
            'client.id' => 'nullable|string',
            'client.titulaire' => 'required_without:client_id|string|max:255',
            'client.nci' => 'required_without:client_id|string|unique:clients,nci',
            'client.email' => 'required_without:client_id|email|unique:clients,email',
            'client.telephone' => ['required_without:client_id','unique:clients,phone','regex:/^\+221[0-9]{8,9}$/'],
            'client.adresse' => 'required_without:client_id|string|max:500',
        ], [
            'type.in' => 'Le type de compte doit être : epargne ou cheque.',
            'client.nci.unique' => 'Ce numéro de carte d\'identité est déjà enregistré.',
            'client.email.unique' => 'Cette adresse email est déjà utilisée par un autre client.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà enregistré.',
            'client.telephone.regex' => 'Le numéro de téléphone doit être au format international sénégalais, par exemple : +221771234567',
        ]);

        $user = Auth::user();

        // Check or create client
        $client = null;

        // If client_id is provided, use it directly
        if (isset($payload['client_id']) && !empty($payload['client_id'])) {
            $client = \App\Models\Client::find($payload['client_id']);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client avec l\'ID spécifié non trouvé.'
                ], 404);
            }
        } elseif (isset($payload['client']['id']) && !empty($payload['client']['id'])) {
            $client = \App\Models\Client::find($payload['client']['id']);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client avec l\'ID spécifié non trouvé.'
                ], 404);
            }
        } elseif (isset($payload['client'])) {
            $clientData = $payload['client'];
            // Check if client exists by email, phone, or NCI
            $client = \App\Models\Client::where('email', $clientData['email'])
                ->orWhere('phone', $clientData['telephone'])
                ->orWhere('nci', $clientData['nci'])
                ->first();

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
                        'name' => $clientData['titulaire'],
                        'email' => $clientData['email'],
                        'password' => bcrypt($generatedPassword),
                        'is_active' => true,
                    ]);
                }

                $client = \App\Models\Client::create([
                    'name' => $clientData['titulaire'],
                    'nci' => $clientData['nci'],
                    'email' => $clientData['email'],
                    'phone' => $clientData['telephone'],
                    'address' => $clientData['adresse'],
                    'is_active' => true,
                    'last_order_at' => null,
                ]);

                // Send authentication email with password (simple Mailable)
                try {
                    \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ClientCredentialsMail($generatedPassword));
                    \Illuminate\Support\Facades\Log::info('Client credentials email sent successfully to: ' . $client->email);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Unable to send client credentials email: ' . $e->getMessage());
                    // Log the password for debugging purposes
                    \Illuminate\Support\Facades\Log::info('Generated password for ' . $client->email . ': ' . $generatedPassword);
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
        }

        // Create compte
        $numero = method_exists(\App\Models\Compte::class, 'generateNumero') ? \App\Models\Compte::generateNumero() : \Illuminate\Support\Str::upper('C' . \Illuminate\Support\Str::random(8));

        $solde = 0;

        $compte = \App\Models\Compte::create([
            'numero' => $numero,
            'type' => $payload['type'],
            'solde' => $solde,
            'devise' => $payload['devise'],
            'is_active' => true,
            'client_id' => $client->id,
            'date_ouverture' => now(),
            'last_transaction_at' => null,
        ]);

        // Send email notification to client about account creation
        try {
            \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\AccountCreatedMail($compte, $generatedPassword ?? null));
            \Illuminate\Support\Facades\Log::info('Account creation email sent successfully to: ' . $client->email);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Unable to send account creation email to ' . $client->email . ': ' . $e->getMessage());
            // Log the password for debugging purposes
            if ($generatedPassword) {
                \Illuminate\Support\Facades\Log::info('Generated password for account creation ' . $client->email . ': ' . $generatedPassword);
            }
        }

        $responseData = [
            'success' => true,
            'message' => 'Votre compte bancaire a été créé avec succès. Vous recevrez vos identifiants de connexion par email.',
            'data' => [
                'id' => (string) $compte->id,
                'numeroCompte' => $compte->numero,
                'titulaire' => $client->name,
                'type' => $compte->type,
                'devise' => $compte->devise,
                'dateCreation' => $compte->date_ouverture->toISOString(),
                'statut' => $compte->is_active ? 'actif' : 'bloque',
                'metadata' => [
                    'derniereModification' => $compte->updated_at?->toISOString(),
                    'version' => 1,
                ],
            ],
        ];

        // Log the account creation
        \Illuminate\Support\Facades\Log::info('Compte created', [
            'compte_id' => $compte->id,
            'numero' => $compte->numero,
            'type' => $compte->type,
            'client_id' => $client->id,
            'created_by' => $user->id,
        ]);

        return response()->json($responseData, 201);
    }

    /**
     * Update client information related to a compte (partial update).
     * All fields optional but at least one must be provided.
     */
        // Duplicate update() method removed

// Orphaned/duplicate update logic removed

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{compteId}",
     *     tags={"Comptes"},
     *     summary="Fermer un compte par son id",
     *     description="Ferme le compte spécifié en le marquant comme archivé. Seul le propriétaire ou un administrateur peut fermer le compte.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à fermer",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Compte fermé avec succès"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Compte non trouvé")
     * )
     */
    public function destroy(Request $request, string $compteId): JsonResponse
    {
        $user = Auth::user();
        $compte = \App\Models\Compte::find($compteId);
        if (!$compte) {
            return response()->json(['success' => false, 'message' => 'Le compte que vous souhaitez fermer n\'existe pas ou a déjà été fermé.'], 404);
        }

        // Si l'utilisateur n'est pas admin, vérifier qu'il est le propriétaire du compte
        if (method_exists($user, 'isAdmin') && !$user->isAdmin()) {
            $client = Client::where('email', $user->email)->first();
            if (!$client || $compte->client_id !== $client->id) {
                return response()->json(['success' => false, 'message' => 'Vous n\'avez pas l\'autorisation de fermer ce compte. Contactez un administrateur si nécessaire.'], 403);
            }
        }

        // Archiver le compte au lieu de le supprimer
        $compte->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        // Log the action
        \Illuminate\Support\Facades\Log::info('Compte archived', [
            'compte_id' => $compte->id,
            'numero' => $compte->numero,
            'archived_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Le compte a été fermé avec succès.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/comptes/{compteId}",
     *     tags={"Comptes"},
     *     summary="Modifier les informations d'un compte",
     *     description="Tous les champs sont optionnels, mais au moins un doit être modifié. Téléphone unique et valide, email unique, mot de passe sécurisé.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à modifier",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="informationsClient", type="object",
     *                     @OA\Property(property="telephone", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="password", type="string", format="password")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compte mis à jour avec succès")
     * )
     */
    public function update(Request $request, string $compteId): JsonResponse
    {
        $compte = \App\Models\Compte::find($compteId);
        if (!$compte) {
            return response()->json(['success' => false, 'message' => 'Le compte que vous souhaitez modifier n\'existe pas ou n\'est pas accessible.'], 404);
        }
        $data = $request->only(['titulaire', 'informationsClient']);
        $fields = array_filter($data, function($v) { return !is_null($v) && $v !== ''; });
        // Vérifie qu'au moins un champ est modifié
        if (empty($fields) || (isset($fields['informationsClient']) && empty(array_filter($fields['informationsClient'], function($v){return !is_null($v) && $v !== '';})))) {
            return response()->json(['success' => false, 'message' => 'Veuillez fournir au moins une information à modifier pour ce compte.'], 422);
        }
        // Validation personnalisée
        $rules = [
            'titulaire' => 'nullable|string',
            'informationsClient.telephone' => [
                'nullable',
                'string',
                'regex:/^(\+221|00221)?7[05678][0-9]{7}$/',
                'unique:clients,phone',
            ],
            'informationsClient.email' => 'nullable|email|unique:clients,email',
            'informationsClient.password' => [
                'nullable',
                'string',
                'min:10',
                'regex:/^[A-Z][a-z]{2,}.*[!@#$%^&*()_+=\-{}\[\]:;"\'<>,.?\/]{2,}/',
            ],
        ];
        $validated = $request->validate($rules);
        // Mise à jour des champs
        if (isset($validated['titulaire'])) {
            $compte->titulaire = $validated['titulaire'];
        }
        if (isset($validated['informationsClient'])) {
            $client = $compte->client;
            if (isset($validated['informationsClient']['telephone'])) {
                $client->phone = $validated['informationsClient']['telephone'];
            }
            if (isset($validated['informationsClient']['email'])) {
                $client->email = $validated['informationsClient']['email'];
            }
            if (isset($validated['informationsClient']['password']) && $validated['informationsClient']['password'] !== '') {
                $client->password = bcrypt($validated['informationsClient']['password']);
            }
            $client->save();
        }
        $compte->metadata = [
            'derniereModification' => now()->toIso8601String(),
            'version' => ($compte->metadata['version'] ?? 0) + 1,
        ];
        $compte->save();
        return response()->json([
            'success' => true,
            'message' => 'Les informations du compte ont été mises à jour avec succès.',
            'data' => $compte,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{compteId}/block",
     *     tags={"Comptes"},
     *     summary="Bloquer un compte",
     *     description="Bloque un compte bancaire pour une période déterminée avec un motif",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à bloquer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"date_debut_blocage", "motif_blocage"},
     *             @OA\Property(property="date_debut_blocage", type="string", format="date-time", description="Date de début du blocage", example="2025-11-28T12:06:30.188Z"),
     *             @OA\Property(property="date_fin_blocage", type="string", format="date-time", nullable=true, description="Date de fin du blocage (optionnel)", example="2025-12-28T12:06:30.188Z"),
     *             @OA\Property(property="motif_blocage", type="string", description="Motif du blocage", example="Suspicion de fraude")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Le compte a été bloqué avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="dateDebutBlocage", type="string", format="date-time"),
     *                 @OA\Property(property="dateFinBlocage", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="motifBlocage", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Compte non trouvé"),
     *     @OA\Response(response=422, description="Données invalides"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function block(BlockCompteRequest $request, string $compteId): JsonResponse
    {
        $user = Auth::user();

        // Find the compte
        $compte = Compte::find($compteId);
        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte que vous souhaitez bloquer n\'existe pas.',
            ], 404);
        }

        // Check permissions (only admins can block comptes)
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas l\'autorisation de bloquer ce compte.',
            ], 403);
        }

        // Check if compte is already blocked
        if ($compte->isBlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte est déjà bloqué.',
            ], 422);
        }

        // Block the compte
        $compte->update([
            'date_debut_blocage' => $request->date_debut_blocage,
            'date_fin_blocage' => $request->date_fin_blocage,
            'motif_blocage' => $request->motif_blocage,
        ]);

        // Log the action
        \Illuminate\Support\Facades\Log::info('Compte blocked', [
            'compte_id' => $compte->id,
            'numero' => $compte->numero,
            'blocked_by' => $user->id,
            'motif' => $request->motif_blocage,
            'date_debut' => $request->date_debut_blocage,
            'date_fin' => $request->date_fin_blocage,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le compte a été bloqué avec succès.',
            'data' => [
                'id' => $compte->id,
                'numeroCompte' => $compte->numero,
                'statut' => 'bloque',
                'dateDebutBlocage' => $compte->date_debut_blocage?->toISOString(),
                'dateFinBlocage' => $compte->date_fin_blocage?->toISOString(),
                'motifBlocage' => $compte->motif_blocage,
            ],
        ]);
    }

}
