<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// La documentation Swagger n'a pas besoin de redirection car elle est déjà accessible via /api/documentation

// Route de test d'envoi d'email (accessible uniquement en local)
if (app()->environment('local')) {
    Route::get('/debug/mail-test', function (\Illuminate\Http\Request $request) {
        $to = $request->query('to');
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Paramètre ?to= email invalide'], 422);
        }

        // Créer un compte factice minimal pour le Mailable
        $compte = new \App\Models\Compte([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'numero' => 'TEST' . strtoupper(\Illuminate\Support\Str::random(6)),
            'type' => 'epargne',
            'solde' => 0,
            'devise' => 'CFA',
            'is_active' => true,
            'date_ouverture' => now(),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\AccountCreatedMail($compte, null));
            return response()->json(['success' => true, 'message' => 'Email de test envoyé à ' . $to]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail test failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Échec envoi: ' . $e->getMessage()], 500);
        }
    });
}
