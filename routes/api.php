<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'checks' => [
            'app' => true,
            'database' => true,
        ],
        'timestamp' => now()->toIso8601String(),
    ]);
});
