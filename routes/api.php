<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/welcome', [WelcomeController::class, 'welcome']);

// Routes pour les comptes - nécessite authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/comptes', [CompteController::class, 'index']);
    Route::get('/v1/comptes/{compte}', [CompteController::class, 'show']);
    Route::post('/v1/comptes', [CompteController::class, 'store']);
    Route::patch('/v1/comptes/{compte}', [CompteController::class, 'update']);
    // RESTful resources for v1
    Route::apiResource('/v1/clients', ClientController::class);
    Route::apiResource('/v1/transactions', TransactionController::class);
    Route::apiResource('/v1/users', UserController::class);
});

// Authentication routes (token issuance using Passport OAuth2 password grant)
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login'])->middleware(['throttle:ip-minute']);
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register'])->middleware(['throttle:ip-minute']);
    Route::post('/auth/refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])->middleware(['throttle:ip-minute']);
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['auth:api','throttle:user-hour','throttle:ip-minute']);
    Route::get('/auth/authentification', [\App\Http\Controllers\AuthController::class, 'authentification'])->middleware(['auth:api','throttle:user-hour']);
});
