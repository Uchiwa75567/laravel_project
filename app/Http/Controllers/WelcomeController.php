<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    /**
     * Return a welcome message and log the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function welcome(Request $request)
    {
        // Log the request metadata
        Log::info('Request received', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        // Return JSON response
        return response()->json([
            'message' => 'Welcome to the Laravel API Service!'
        ]);
    }
}
