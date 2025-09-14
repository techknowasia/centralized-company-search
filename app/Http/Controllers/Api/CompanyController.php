<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanySearchService;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ReportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function show(Request $request, int $id): JsonResponse
    {
        $country = $request->query('country', 'sg');
        
        try {
            $data = $this->searchService->getCompanyDetails($country, $id);
            
            return response()->json([
                'data' => [
                    'company' => new CompanyResource($data['company']),
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
