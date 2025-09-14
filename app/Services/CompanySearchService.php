<?php

namespace App\Services;

use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\SG\CompanyRepositorySG;
use App\Repositories\MX\CompanyRepositoryMX;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompanySearchService
{
    protected CompanyRepositorySG $sgRepository;
    protected CompanyRepositoryMX $mxRepository;

    public function __construct(CompanyRepositorySG $sgRepository, CompanyRepositoryMX $mxRepository)
    {
        $this->sgRepository = $sgRepository;
        $this->mxRepository = $mxRepository;
    }

    public function searchAll(string $query, ?string $country = null, int $limit = 50): Collection
    {
        $results = collect();

        try {
            // Search Singapore database
            if (!$country || $country === 'sg') {
                $sgResults = $this->searchSingapore($query, $limit);
                $results = $results->merge($sgResults);
            }

            // Search Mexico database
            if (!$country || $country === 'mx') {
                $mxResults = $this->searchMexico($query, $limit);
                $results = $results->merge($mxResults);
            }

            // Sort by relevance (exact matches first, then partial matches)
            return $results->sortByDesc(function ($company) use ($query) {
                $name = strtolower($company->name);
                $searchQuery = strtolower($query);
                
                if ($name === $searchQuery) return 100;
                if (str_starts_with($name, $searchQuery)) return 90;
                if (str_contains($name, $searchQuery)) return 80;
                return 70;
            });

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
                // Get suggestions from Singapore
                $sgSuggestions = $this->getSingaporeSuggestions($query, $limit);
                $results = $results->merge($sgSuggestions);

                // Get suggestions from Mexico
                $mxSuggestions = $this->getMexicoSuggestions($query, $limit);
                $results = $results->merge($mxSuggestions);

                // Sort by relevance and limit results
                return $results->sortByDesc(function ($company) use ($query) {
                    $name = strtolower($company->name);
                    $searchQuery = strtolower($query);
                    
                    if ($name === $searchQuery) return 100;
                    if (str_starts_with($name, $searchQuery)) return 90;
                    if (str_contains($name, $searchQuery)) return 80;
                    return 70;
                })->take($limit * 2); // Get more for pagination

            } catch (\Exception $e) {
                \Log::error('Suggestions error: ' . $e->getMessage());
                return collect();
            }
        });
    }

    public function getCompanyReports(string $country, int $companyId): Collection
    {
        try {
            if ($country === 'sg') {
                return $this->getSingaporeReports($companyId);
            } elseif ($country === 'mx') {
                return $this->getMexicoReports($companyId);
            }
        } catch (\Exception $e) {
            \Log::error('Get reports error: ' . $e->getMessage());
        }

        return collect();
    }

    private function searchSingapore(string $query, int $limit): Collection
    {
        try {
            $results = DB::connection('companies_house_sg')
                ->table('companies')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('registration_number', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            return $results->map(function ($company) {
                $company->country = 'sg';
                $company->country_name = 'Singapore';
                return $company;
            });
        } catch (\Exception $e) {
            \Log::error('Singapore search error: ' . $e->getMessage());
            return collect();
        }
    }

    private function searchMexico(string $query, int $limit): Collection
    {
        try {
            $results = DB::connection('companies_house_mx')
                ->table('companies')
                ->leftJoin('states', 'companies.state_id', '=', 'states.id')
                ->select('companies.*', 'states.name as state_name')
                ->where('companies.name', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            return $results->map(function ($company) {
                $company->country = 'mx';
                $company->country_name = 'Mexico';
                return $company;
            });
        } catch (\Exception $e) {
            \Log::error('Mexico search error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getSingaporeSuggestions(string $query, int $limit): Collection
    {
        try {
            $results = DB::connection('companies_house_sg')
                ->table('companies')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            return $results->map(function ($company) {
                $company->country = 'sg';
                $company->country_name = 'Singapore';
                return $company;
            });
        } catch (\Exception $e) {
            \Log::error('Singapore suggestions error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getMexicoSuggestions(string $query, int $limit): Collection
    {
        try {
            $results = DB::connection('companies_house_mx')
                ->table('companies')
                ->leftJoin('states', 'companies.state_id', '=', 'states.id')
                ->select('companies.*', 'states.name as state_name')
                ->where('companies.name', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            return $results->map(function ($company) {
                $company->country = 'mx';
                $company->country_name = 'Mexico';
                return $company;
            });
        } catch (\Exception $e) {
            \Log::error('Mexico suggestions error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getSingaporeReports(int $companyId): Collection
    {
        try {
            return DB::connection('companies_house_sg')
                ->table('reports')
                ->where('is_active', 1)
                ->orderBy('order')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Singapore reports error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getMexicoReports(int $companyId): Collection
    {
        try {
            // Get company's state_id
            $company = DB::connection('companies_house_mx')
                ->table('companies')
                ->where('id', $companyId)
                ->first();

            if (!$company) {
                return collect();
            }

            // Get reports available for this state
            return DB::connection('companies_house_mx')
                ->table('report_state')
                ->join('reports', 'report_state.report_id', '=', 'reports.id')
                ->select('reports.*', 'report_state.amount')
                ->where('report_state.state_id', $company->state_id)
                ->where('reports.status', 1)
                ->orderBy('reports.order')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Mexico reports error: ' . $e->getMessage());
            return collect();
        }
    }
}
