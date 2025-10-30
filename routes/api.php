<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WelcomeController;

// Debug route (enabled only in debug mode) to create a test client and a compte
if (config('app.debug')) {
    Route::get('/debug/create-test-client', function () {
        $client = \App\Models\Client::firstOrCreate([
            'email' => 'test+bot@bankapi.com'
        ], [
            'name' => 'Test Bot',
            'phone' => '+221771234567',
            'address' => '123 Test Street',
            'is_active' => true
        ]);

        \App\Models\Compte::firstOrCreate([
            'client_id' => $client->id,
        ], [
            'numero' => 'C'.strtoupper(\Illuminate\Support\Str::random(8)),
            'type' => 'courant',
            'devise' => 'XOF',
            'is_active' => true,
            'date_ouverture' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Client de test créé/présent']);
    });
}

// Public routes
Route::get('/welcome', [WelcomeController::class, 'welcome']);

// Public auth routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Protected routes (Passport)
Route::middleware('auth:api')->prefix('v1')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Comptes
    Route::get('/comptes', [CompteController::class, 'index']);
    Route::get('/comptes/search', [CompteController::class, 'search']);
    Route::post('/comptes', [CompteController::class, 'store']);
    Route::get('/comptes/{compteId}', [CompteController::class, 'show']);
    Route::patch('/comptes/{compteId}', [CompteController::class, 'update']);
    Route::delete('/comptes/{compteId}', [CompteController::class, 'destroy']);
    Route::post('/comptes/{compteId}/block', [CompteController::class, 'block']);
});
