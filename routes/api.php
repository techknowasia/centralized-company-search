<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ReportController;

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

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Search routes
Route::prefix('search')->group(function () {
    Route::get('/companies', [SearchController::class, 'searchCompanies']);
    Route::get('/suggestions', [SearchController::class, 'getSuggestions']);
    Route::get('/stats', [SearchController::class, 'getStats']);
    Route::delete('/cache', [SearchController::class, 'clearCache']);
});

// Company routes - SPECIFIC ROUTES FIRST
Route::prefix('companies')->group(function () {
    Route::get('/', [CompanyController::class, 'index']);
    Route::get('/suggestions', [SearchController::class, 'getSuggestions']); // Specific route before {id}
    Route::get('/{id}', [CompanyController::class, 'show']);
    Route::get('/{id}/reports', [CompanyController::class, 'getReports']);
});

// Country-specific routes
Route::prefix('countries')->group(function () {
    Route::get('/sg/companies', [CompanyController::class, 'getSingaporeCompanies']);
    Route::get('/mx/companies', [CompanyController::class, 'getMexicoCompanies']);
    Route::get('/mx/states', [CompanyController::class, 'getMexicoStates']);
});

// Report routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::get('/{id}', [ReportController::class, 'show']);
});

// Test routes (remove in production)
Route::prefix('test')->group(function () {
    Route::get('/database-connections', function () {
        $connections = ['mysql', 'companies_house_sg', 'companies_house_mx'];
        $results = [];
        
        foreach ($connections as $connection) {
            try {
                \DB::connection($connection)->getPdo();
                $results[$connection] = 'connected';
            } catch (\Exception $e) {
                $results[$connection] = 'failed: ' . $e->getMessage();
            }
        }
        
        return response()->json([
            'database_connections' => $results,
            'timestamp' => now()->toISOString()
        ]);
    });
});

