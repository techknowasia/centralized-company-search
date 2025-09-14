<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanySearchService;
use App\Http\Resources\CompanyDetailResource;
use App\Http\Resources\ReportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    protected CompanySearchService $searchService;

    public function __construct(CompanySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request): JsonResponse
    {
        // Get all companies (paginated)
        return response()->json(['message' => 'Get all companies endpoint']);
    }

    /**
     * Get company details by slug (searches across all databases)
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        try {
            // Search for company by slug across both databases
            $company = $this->findCompanyBySlug($slug);
            
            if (!$company) {
                return response()->json([
                    'error' => 'Company not found',
                    'message' => "No company found with slug: {$slug}"
                ], 404);
            }

            // Get reports for the company
            $reports = $this->searchService->getCompanyReports($company->country, $company->id);
            
            return response()->json([
                'data' => [
                    'company' => new CompanyDetailResource($company),
                    'reports' => ReportResource::collection($reports),
                    'country' => $company->country
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Company not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get company reports by slug
     */
    public function getReportsBySlug(Request $request, string $slug): JsonResponse
    {
        try {
            // Search for company by slug across both databases
            $company = $this->findCompanyBySlug($slug);
            
            if (!$company) {
                return response()->json([
                    'error' => 'Company not found',
                    'message' => "No company found with slug: {$slug}"
                ], 404);
            }

            // Get reports for the company
            $reports = $this->searchService->getCompanyReports($company->country, $company->id);
            
            return response()->json([
                'data' => ReportResource::collection($reports),
                'meta' => [
                    'company_slug' => $slug,
                    'company_id' => $company->id,
                    'country' => $company->country,
                    'total' => $reports->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Reports not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Find company by slug across all databases
     */
    private function findCompanyBySlug(string $slug): ?object
    {
        // Search Singapore database
        try {
            $sgCompany = DB::connection('companies_house_sg')
                ->table('companies')
                ->where('slug', $slug)
                ->first();
            
            if ($sgCompany) {
                $sgCompany->country = 'sg';
                $sgCompany->country_name = 'Singapore';
                return $sgCompany;
            }
        } catch (\Exception $e) {
            \Log::error('Singapore company search failed: ' . $e->getMessage());
        }

        // Search Mexico database
        try {
            $mxCompany = DB::connection('companies_house_mx')
                ->table('companies')
                ->leftJoin('states', 'companies.state_id', '=', 'states.id')
                ->select('companies.*', 'states.name as state_name')
                ->where('companies.slug', $slug)
                ->first();
            
            if ($mxCompany) {
                $mxCompany->country = 'mx';
                $mxCompany->country_name = 'Mexico';
                return $mxCompany;
            }
        } catch (\Exception $e) {
            \Log::error('Mexico company search failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Legacy method - Get company by ID (for backward compatibility)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $country = $request->query('country', 'sg');
        
        try {
            $data = $this->searchService->getCompanyDetails($country, $id);
            
            return response()->json([
                'data' => [
                    'company' => new CompanyDetailResource($data['company']),
                    'reports' => ReportResource::collection($data['reports']),
                    'country' => $data['country']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Company not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Legacy method - Get company reports by ID (for backward compatibility)
     */
    public function getReports(Request $request, int $id): JsonResponse
    {
        $country = $request->query('country', 'sg');
        
        try {
            $reports = $this->searchService->getCompanyReports($country, $id);
            
            return response()->json([
                'data' => ReportResource::collection($reports),
                'meta' => [
                    'company_id' => $id,
                    'country' => $country,
                    'total' => $reports->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Reports not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getSingaporeCompanies(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Singapore companies endpoint']);
    }

    public function getMexicoCompanies(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Mexico companies endpoint']);
    }

    public function getMexicoStates(): JsonResponse
    {
        return response()->json(['message' => 'Mexico states endpoint']);
    }
}
