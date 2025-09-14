<?php

namespace App\Services;

use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\SG\CompanyRepositorySG;
use App\Repositories\MX\CompanyRepositoryMX;
use App\Services\CountryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompanySearchService
{
    protected CompanyRepositorySG $sgRepository;
    protected CompanyRepositoryMX $mxRepository;
    protected CountryService $countryService;

    public function __construct(
        CompanyRepositorySG $sgRepository, 
        CompanyRepositoryMX $mxRepository,
        CountryService $countryService
    ) {
        $this->sgRepository = $sgRepository;
        $this->mxRepository = $mxRepository;
        $this->countryService = $countryService;
    }

    public function searchAll(string $query, ?string $country = null, int $limit = 50): Collection
    {
        $results = collect();

        try {
            $countriesToSearch = $country ? [$country] : $this->countryService->getAvailableCountries();
            
            foreach ($countriesToSearch as $countryCode) {
                $countryResults = $this->searchCountry($countryCode, $query, $limit);
                $results = $results->merge($countryResults);
            }

            // Sort by relevance
            return $this->sortByRelevance($results, $query);

        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return collect();
        }
    }

    public function getSuggestions(string $query, int $limit = 10): Collection
    {
        $cacheKey = "suggestions_{$query}_{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            $results = collect();

            try {
                foreach ($this->countryService->getAvailableCountries() as $countryCode) {
                    $suggestions = $this->getCountrySuggestions($countryCode, $query, $limit);
                    $results = $results->merge($suggestions);
                }

                return $this->sortByRelevance($results, $query)->take($limit * 2);

            } catch (\Exception $e) {
                \Log::error('Suggestions error: ' . $e->getMessage());
                return collect();
            }
        });
    }

    public function getCompanyReports(string $country, int $companyId): Collection
    {
        try {
            $countryConfig = $this->countryService->getCountryConfig($country);
            
            if (!$countryConfig) {
                throw new \Exception("Country {$country} not configured");
            }

            if ($this->countryService->hasDirectReports($country)) {
                return $this->getDirectReports($country, $companyId);
            } else {
                return $this->getStateBasedReports($country, $companyId);
            }

        } catch (\Exception $e) {
            \Log::error('Get reports error: ' . $e->getMessage());
            return collect();
        }
    }

    private function searchCountry(string $countryCode, string $query, int $limit): Collection
    {
        try {
            $connection = $this->countryService->getCountryConnection($countryCode);
            $countryName = $this->countryService->getCountryName($countryCode);
            
            $results = DB::connection($connection)
                ->table('companies')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('registration_number', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            return $results->map(function ($company) use ($countryCode, $countryName) {
                $company->country = $countryCode;
                $company->country_name = $countryName;
                return $company;
            });

        } catch (\Exception $e) {
            \Log::error("Search error for {$countryCode}: " . $e->getMessage());
            return collect();
        }
    }

    private function getCountrySuggestions(string $countryCode, string $query, int $limit): Collection
    {
        try {
            $connection = $this->countryService->getCountryConnection($countryCode);
            $countryName = $this->countryService->getCountryName($countryCode);
            
            $results = DB::connection($connection)
                ->table('companies')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get()
                ->values();

            return $results->map(function ($company) use ($countryCode, $countryName) {
                $company->country = $countryCode;
                $company->country_name = $countryName;
                return $company;
            });

        } catch (\Exception $e) {
            \Log::error("Suggestions error for {$countryCode}: " . $e->getMessage());
            return collect();
        }
    }

    private function getDirectReports(string $country, int $companyId): Collection
    {
        $connection = $this->countryService->getCountryConnection($country);
        
        return DB::connection($connection)
            ->table('reports')
            ->where('is_active', 1)
            ->orderBy('order')
            ->get();
    }

    private function getStateBasedReports(string $country, int $companyId): Collection
    {
        $connection = $this->countryService->getCountryConnection($country);
        
        // Get company's state_id
        $company = DB::connection($connection)
            ->table('companies')
            ->where('id', $companyId)
            ->first();

        if (!$company) {
            return collect();
        }

        // Get reports available for this state
        return DB::connection($connection)
            ->table('report_state')
            ->join('reports', 'report_state.report_id', '=', 'reports.id')
            ->select('reports.*', 'report_state.amount')
            ->where('report_state.state_id', $company->state_id)
            ->where('reports.status', 1)
            ->orderBy('reports.order')
            ->get();
    }

    private function sortByRelevance(Collection $results, string $query): Collection
    {
        return $results->sortByDesc(function ($company) use ($query) {
            $name = strtolower($company->name);
            $searchQuery = strtolower($query);
            
            if ($name === $searchQuery) return 100;
            if (str_starts_with($name, $searchQuery)) return 90;
            if (str_contains($name, $searchQuery)) return 80;
            return 70;
        });
    }
}