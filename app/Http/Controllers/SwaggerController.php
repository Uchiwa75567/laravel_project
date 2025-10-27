<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Laravel API Documentation",
 *     version="1.0.0",
 *     description="API documentation for Laravel application with Passport authentication"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer {token}"
 * )
 *
 * @OA\PathItem(
 *     path="/api/user",
 *     @OA\Get(
 *         summary="Get authenticated user",
 *         description="Returns the authenticated user information",
 *         operationId="getUser",
 *         tags={"Authentication"},
 *         security={{"bearerAuth":{}}},
 *         @OA\Response(
 *             response=200,
 *             description="Successful operation",
 *             @OA\JsonContent(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com")
 *             )
 *         ),
 *         @OA\Response(
 *             response=401,
 *             description="Unauthenticated"
 *         )
 *     )
 * )
 *
 * @OA\PathItem(
 *     path="/api/welcome",
 *     @OA\Get(
 *         summary="Get welcome message",
 *         description="Returns a welcome message and logs the request",
 *         operationId="getWelcome",
 *         tags={"General"},
 *         @OA\Response(
 *             response=200,
 *             description="Successful operation",
 *             @OA\JsonContent(
 *                 @OA\Property(property="message", type="string", example="Welcome to the Laravel API Service!")
 *             )
 *         )
 *     )
 * )
 *
 * @OA\PathItem(
 *     path="/api/v1/comptes",
 *     @OA\Get(
 *         summary="Lister tous les comptes",
 *         description="Permet à l'admin de récupérer tous les comptes ou au client de récupérer ses propres comptes",
 *         operationId="getComptes",
 *         tags={"Comptes"},
 *         security={{"bearerAuth":{}}},
 *         @OA\Parameter(
 *             name="page",
 *             in="query",
 *             description="Numéro de page",
 *             required=false,
 *             @OA\Schema(type="integer", default=1, minimum=1)
 *         ),
 *         @OA\Parameter(
 *             name="limit",
 *             in="query",
 *             description="Nombre d'éléments par page",
 *             required=false,
 *             @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
 *         ),
 *         @OA\Parameter(
 *             name="type",
 *             in="query",
 *             description="Filtrer par type",
 *             required=false,
 *             @OA\Schema(type="string", enum={"courant", "epargne", "entreprise"})
 *         ),
 *         @OA\Parameter(
 *             name="statut",
 *             in="query",
 *             description="Filtrer par statut",
 *             required=false,
 *             @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
 *         ),
 *         @OA\Parameter(
 *             name="search",
 *             in="query",
 *             description="Recherche par titulaire ou numéro",
 *             required=false,
 *             @OA\Schema(type="string")
 *         ),
 *         @OA\Parameter(
 *             name="sort",
 *             in="query",
 *             description="Tri",
 *             required=false,
 *             @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
 *         ),
 *         @OA\Parameter(
 *             name="order",
 *             in="query",
 *             description="Ordre",
 *             required=false,
 *             @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
 *         ),
 *         @OA\Response(
 *             response=200,
 *             description="Liste des comptes récupérée avec succès",
 *             @OA\JsonContent(
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(
 *                     property="data",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *                         @OA\Property(property="numeroCompte", type="string", example="C00123456"),
 *                         @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
 *                         @OA\Property(property="type", type="string", example="epargne"),
 *                         @OA\Property(property="solde", type="number", format="float", example=1250000),
 *                         @OA\Property(property="devise", type="string", example="FCFA"),
 *                         @OA\Property(property="dateCreation", type="string", format="date", example="2023-03-15"),
 *                         @OA\Property(property="statut", type="string", example="bloque"),
 *                         @OA\Property(
 *                             property="metadata",
 *                             type="object",
 *                             @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z")
 *                         ),
 *                         @OA\Property(property="version", type="integer", example=1)
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="pagination",
 *                     type="object",
 *                     @OA\Property(property="currentPage", type="integer", example=1),
 *                     @OA\Property(property="totalPages", type="integer", example=3),
 *                     @OA\Property(property="totalItems", type="integer", example=25),
 *                     @OA\Property(property="itemsPerPage", type="integer", example=10),
 *                     @OA\Property(property="hasNext", type="boolean", example=true),
 *                     @OA\Property(property="hasPrevious", type="boolean", example=false)
 *                 ),
 *                 @OA\Property(
 *                     property="links",
 *                     type="object",
 *                     @OA\Property(property="self", type="string", example="/api/v1/comptes?page=1&limit=10"),
 *                     @OA\Property(property="next", type="string", example="/api/v1/comptes?page=2&limit=10"),
 *                     @OA\Property(property="first", type="string", example="/api/v1/comptes?page=1&limit=10"),
 *                     @OA\Property(property="last", type="string", example="/api/v1/comptes?page=3&limit=10")
 *                 )
 *             )
 *         ),
 *         @OA\Response(
 *             response=401,
 *             description="Non autorisé",
 *             @OA\JsonContent(
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Non autorisé")
 *             )
 *         ),
 *         @OA\Response(
 *             response=403,
 *             description="Accès interdit",
 *             @OA\JsonContent(
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Accès interdit")
 *             )
 *         )
 *     )
 * )
 */
class SwaggerController extends Controller
{
    //
}
