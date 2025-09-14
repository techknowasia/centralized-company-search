<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\CompanySearchService;
use App\Http\Resources\SearchResultResource;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    protected CompanySearchService $searchService;

    public function __construct(CompanySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search companies across all databases
     * Optimized for millions of records
     */
    public function searchCompanies(SearchRequest $request): JsonResponse
    {
        $query = $request->validated()['q'];
        $country = $request->validated()['country'] ?? null;
        $limit = min($request->validated()['per_page'] ?? 10, 50); // Max 50 results

        $startTime = microtime(true);
        
        try {
            $companies = $this->searchService->searchAll($query, $country, $limit);
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'data' => SearchResultResource::collection($companies),
                'meta' => [
                    'query' => $query,
                    'country' => $country,
                    'total' => $companies->count(),
                    'limit' => $limit,
                    'search_time_ms' => $searchTime,
                    'performance' => $searchTime < 100 ? 'excellent' : ($searchTime < 500 ? 'good' : 'slow')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions
     * Fast suggestions for autocomplete
     */
    public function getSuggestions(SearchRequest $request): JsonResponse
    {
        $query = $request->validated()['q'];
        $limit = min($request->validated()['limit'] ?? 5, 20); // Max 20 suggestions

        $startTime = microtime(true);
        
        try {
            $suggestions = $this->searchService->getSuggestions($query, $limit);
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'data' => SearchResultResource::collection($suggestions),
                'meta' => [
                    'query' => $query,
                    'total' => $suggestions->count(),
                    'limit' => $limit,
                    'search_time_ms' => $searchTime
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Suggestions failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->searchService->getSearchStats();
            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Stats failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear search cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->searchService->clearSearchCache();
            return response()->json(['message' => 'Search cache cleared successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Cache clear failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
