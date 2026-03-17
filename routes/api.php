<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Fix #3: Real health check with DB/Redis/Storage probes
Route::get('/health', function () {
    $checks = [
        'app'      => true,
        'database' => false,
        'redis'    => false,
        'storage'  => false,
    ];

    try { DB::select('SELECT 1'); $checks['database'] = true; } catch (\Exception $e) {}
    try { Cache::store('redis')->put('health_check', true, 10); $checks['redis'] = true; } catch (\Exception $e) {}
    try { Storage::disk('local')->put('health_check.txt', 'ok'); Storage::disk('local')->delete('health_check.txt'); $checks['storage'] = true; } catch (\Exception $e) {}

    $allHealthy = !in_array(false, $checks);

    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $allHealthy ? 200 : 503);
});
