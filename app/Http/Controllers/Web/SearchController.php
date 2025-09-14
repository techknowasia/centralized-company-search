<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CompanySearchService;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    protected CompanySearchService $searchService;
    protected CountryService $countryService;

    public function __construct(CompanySearchService $searchService, CountryService $countryService)
    {
        $this->searchService = $searchService;
        $this->countryService = $countryService;
    }

    public function index(): View
    {
        $countries = $this->countryService->getAllCountries();
        
        return view('search.index', [
            'countries' => $countries
        ]);
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
                $searchResults = $this->searchService->searchAll($query, $country, 1000);
                $totalResults = $searchResults->count();
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
            'totalPages' => ceil($totalResults / $perPage),
            'countries' => $this->countryService->getAllCountries()
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 8);
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ]);
        }

        try {
            $suggestions = $this->searchService->getSuggestions($query, $perPage * 3);
            
            $total = $suggestions->count();
            $lastPage = ceil($total / $perPage);
            $paginatedSuggestions = $suggestions->forPage($page, $perPage)->values();
            
            return response()->json([
                'success' => true,
                'data' => $paginatedSuggestions->toArray(),
                'meta' => [
                    'current_page' => (int) $page,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $total
                ]
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
