<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CompanySearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    protected CompanySearchService $searchService;

    public function __construct(CompanySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(): View
    {
        return view('search.index');
    }

    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $country = $request->get('country');
        $page = $request->get('page', 1);
        $perPage = 10;

        $companies = collect();
        $totalResults = 0;

        if (!empty($query)) {
            try {
                // Get search results
                $searchResults = $this->searchService->searchAll($query, $country, 1000); // Get more for pagination
                $totalResults = $searchResults->count();
                
                // Manual pagination
                $companies = $searchResults->forPage($page, $perPage);
            } catch (\Exception $e) {
                \Log::error('Search error: ' . $e->getMessage());
                $companies = collect();
                $totalResults = 0;
            }
        }

        return view('search.results', [
            'companies' => $companies,
            'query' => $query,
            'country' => $country,
            'currentPage' => (int) $page,
            'perPage' => $perPage,
            'totalResults' => $totalResults,
            'totalPages' => ceil($totalResults / $perPage)
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        try {
            $suggestions = $this->searchService->getSuggestions($query, 10);
            
            return response()->json([
                'success' => true,
                'data' => $suggestions->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Suggestions error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions'
            ], 500);
        }
    }
}
