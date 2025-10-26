<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    /**
     * Returns a welcome message and logs the request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function welcome(Request $request)
    {
        // Log request metadata: method and path
        Log::info("Request received: {$request->method()} {$request->path()}");

        // Return JSON welcome message
        return response()->json(['message' => 'Welcome to the Laravel API Service!']);
    }
}
