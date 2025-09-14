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

    public function __construct(
        CompanyRepositorySG $sgRepository,
        CompanyRepositoryMX $mxRepository
    ) {
        $this->sgRepository = $sgRepository;
        $this->mxRepository = $mxRepository;
    }

    /**
     * Fast unified search across all databases
     * Optimized for partial matching on millions of records
     */
    public function searchAll(string $query, ?string $country = null, int $limit = 10): Collection
    {
        // Cache key for search results
        $cacheKey = "search_companies:" . md5($query . $country . $limit);
        
        return Cache::remember($cacheKey, 300, function () use ($query, $country, $limit) {
            $results = collect();

            // Search Singapore companies
            if (!$country || $country === 'sg') {
                $sgResults = $this->searchSingapore($query, $limit);
                $results = $results->merge($sgResults);
            }

            // Search Mexico companies
            if (!$country || $country === 'mx') {
                $mxResults = $this->searchMexico($query, $limit);
                $results = $results->merge($mxResults);
            }

            // Sort by relevance and limit results
            return $this->sortByRelevance($results, $query)->take($limit);
        });
    }

    /**
     * Optimized Singapore search with partial matching
     */
    private function searchSingapore(string $query, int $limit): Collection
    {
        try {
            // Use raw SQL for better performance on large datasets
            $sql = "
                SELECT 
                    id, name, slug, registration_number, address,
                    'sg' as country, 'Singapore' as country_name,
                    CASE 
                        WHEN name = ? THEN 100
                        WHEN name LIKE ? THEN 90
                        WHEN name LIKE ? THEN 80
                        WHEN former_names LIKE ? THEN 70
                        WHEN registration_number LIKE ? THEN 60
                        ELSE 50
                    END as relevance_score
                FROM companies 
                WHERE 
                    name LIKE ? 
                    OR former_names LIKE ? 
                    OR registration_number LIKE ?
                ORDER BY relevance_score DESC, name ASC
                LIMIT ?
            ";

            $exactMatch = $query;
            $startsWith = $query . '%';
            $contains = '%' . $query . '%';
            $regNumber = '%' . $query . '%';

            $results = DB::connection('companies_house_sg')
                ->select($sql, [
                    $exactMatch, $startsWith, $contains, $contains, $regNumber,
                    $contains, $contains, $regNumber, $limit
                ]);

            return collect($results)->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug ?? null,
                    'registration_number' => $item->registration_number ?? null,
                    'address' => $item->address ?? null,
                    'country' => $item->country,
                    'country_name' => $item->country_name,
                    'relevance_score' => $item->relevance_score
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Singapore search failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Optimized Mexico search with partial matching
     */
    private function searchMexico(string $query, int $limit): Collection
    {
        try {
            // Use raw SQL with JOIN for better performance
            $sql = "
                SELECT 
                    c.id, c.name, c.slug, c.brand_name, c.address,
                    s.name as state_name,
                    'mx' as country, 'Mexico' as country_name,
                    CASE 
                        WHEN c.name = ? THEN 100
                        WHEN c.name LIKE ? THEN 90
                        WHEN c.name LIKE ? THEN 80
                        WHEN c.brand_name LIKE ? THEN 70
                        ELSE 50
                    END as relevance_score
                FROM companies c
                LEFT JOIN states s ON c.state_id = s.id
                WHERE 
                    c.name LIKE ? 
                    OR c.brand_name LIKE ? 
                    OR c.slug LIKE ?
                ORDER BY relevance_score DESC, c.name ASC
                LIMIT ?
            ";

            $exactMatch = $query;
            $startsWith = $query . '%';
            $contains = '%' . $query . '%';

            $results = DB::connection('companies_house_mx')
                ->select($sql, [
                    $exactMatch, $startsWith, $contains, $contains,
                    $contains, $contains, $contains, $limit
                ]);

            return collect($results)->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug ?? null,
                    'brand_name' => $item->brand_name ?? null,
                    'address' => $item->address ?? null,
                    'state_name' => $item->state_name ?? null,
                    'country' => $item->country,
                    'country_name' => $item->country_name,
                    'relevance_score' => $item->relevance_score
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Mexico search failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Sort results by relevance score
     */
    private function sortByRelevance(Collection $results, string $query): Collection
    {
        return $results->sortByDesc('relevance_score');
    }

    /**
     * Get search suggestions (faster, limited results)
     */
    public function getSuggestions(string $query, int $limit = 5): Collection
    {
        // Use a shorter cache time for suggestions
        $cacheKey = "search_suggestions:" . md5($query . $limit);
        
        return Cache::remember($cacheKey, 60, function () use ($query, $limit) {
            $results = collect();

            // Get suggestions from both countries
            $sgSuggestions = $this->getSingaporeSuggestions($query, $limit);
            $mxSuggestions = $this->getMexicoSuggestions($query, $limit);

            $results = $results->merge($sgSuggestions)->merge($mxSuggestions);

            return $this->sortByRelevance($results, $query)->take($limit);
        });
    }

    /**
     * Fast suggestions for Singapore
     */
    private function getSingaporeSuggestions(string $query, int $limit): Collection
    {
        try {
            $sql = "
                SELECT 
                    id, name, slug, registration_number,
                    'sg' as country, 'Singapore' as country_name,
                    CASE 
                        WHEN name LIKE ? THEN 100
                        WHEN name LIKE ? THEN 80
                        ELSE 50
                    END as relevance_score
                FROM companies 
                WHERE name LIKE ? OR registration_number LIKE ?
                ORDER BY relevance_score DESC, name ASC
                LIMIT ?
            ";

            $startsWith = $query . '%';
            $contains = '%' . $query . '%';

            $results = DB::connection('companies_house_sg')
                ->select($sql, [$startsWith, $contains, $contains, $contains, $limit]);

            return collect($results)->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug ?? null,
                    'registration_number' => $item->registration_number ?? null,
                    'country' => $item->country,
                    'country_name' => $item->country_name,
                    'relevance_score' => $item->relevance_score
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Singapore suggestions failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Fast suggestions for Mexico
     */
    private function getMexicoSuggestions(string $query, int $limit): Collection
    {
        try {
            $sql = "
                SELECT 
                    c.id, c.name, c.slug, c.brand_name,
                    s.name as state_name,
                    'mx' as country, 'Mexico' as country_name,
                    CASE 
                        WHEN c.name LIKE ? THEN 100
                        WHEN c.name LIKE ? THEN 80
                        ELSE 50
                    END as relevance_score
                FROM companies c
                LEFT JOIN states s ON c.state_id = s.id
                WHERE c.name LIKE ? OR c.brand_name LIKE ?
                ORDER BY relevance_score DESC, c.name ASC
                LIMIT ?
            ";

            $startsWith = $query . '%';
            $contains = '%' . $query . '%';

            $results = DB::connection('companies_house_mx')
                ->select($sql, [$startsWith, $contains, $contains, $contains, $limit]);

            return collect($results)->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug ?? null,
                    'brand_name' => $item->brand_name ?? null,
                    'state_name' => $item->state_name ?? null,
                    'country' => $item->country,
                    'country_name' => $item->country_name,
                    'relevance_score' => $item->relevance_score
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Mexico suggestions failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get company details with reports
     */
    public function getCompanyDetails(string $country, int $id): array
    {
        $company = match ($country) {
            'sg' => $this->sgRepository->findDetails($id),
            'mx' => $this->mxRepository->findDetails($id),
            default => throw new \InvalidArgumentException("Invalid country: {$country}")
        };

        $reports = $this->getCompanyReports($country, $id);

        return [
            'company' => $company,
            'reports' => $reports,
            'country' => $country
        ];
    }

    /**
     * Get company reports
     */
    public function getCompanyReports(string $country, int $companyId): Collection
    {
        return match ($country) {
            'sg' => $this->sgRepository->getReports($companyId),
            'mx' => $this->mxRepository->getReports($companyId),
            default => throw new \InvalidArgumentException("Invalid country: {$country}")
        };
    }

    /**
     * Clear search cache
     */
    public function clearSearchCache(): void
    {
        Cache::flush();
    }

    /**
     * Get search statistics
     */
    public function getSearchStats(): array
    {
        try {
            $sgCount = DB::connection('companies_house_sg')->table('companies')->count();
            $mxCount = DB::connection('companies_house_mx')->table('companies')->count();
            
            return [
                'singapore_companies' => $sgCount,
                'mexico_companies' => $mxCount,
                'total_companies' => $sgCount + $mxCount,
                'cache_status' => 'enabled'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Unable to fetch statistics',
                'message' => $e->getMessage()
            ];
        }
    }
}
