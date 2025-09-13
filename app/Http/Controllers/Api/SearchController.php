<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\CompanySearchService;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    protected CompanySearchService $searchService;

    public function __construct(CompanySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function searchCompanies(SearchRequest $request): JsonResponse
    {
        $query = $request->validated()['q'];
        $country = $request->validated()['country'] ?? null;
        $limit = $request->validated()['per_page'] ?? 10;

        $companies = $this->searchService->searchAll($query, $country, $limit);

        return response()->json([
            'data' => CompanyResource::collection($companies),
            'meta' => [
                'query' => $query,
                'country' => $country,
                'total' => $companies->count(),
                'limit' => $limit
            ]
        ]);
    }

    public function getSuggestions(SearchRequest $request): JsonResponse
    {
        $query = $request->validated()['q'];
        $limit = $request->validated()['limit'] ?? 5;

        $suggestions = $this->searchService->getSuggestions($query, $limit);

        return response()->json([
            'data' => CompanyResource::collection($suggestions),
            'meta' => [
                'query' => $query,
                'total' => $suggestions->count()
            ]
        ]);
    }
}
