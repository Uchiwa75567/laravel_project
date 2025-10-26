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
 */
class SwaggerController extends Controller
{
    //
}
