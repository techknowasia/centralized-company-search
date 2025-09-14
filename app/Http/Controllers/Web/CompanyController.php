<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CompanySearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    protected CompanySearchService $searchService;

    public function __construct(CompanySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function show(string $slug): View
    {
        $company = $this->findCompanyBySlug($slug);
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        // Get reports for the company
        $reports = $this->searchService->getCompanyReports($company->country, $company->id);

        return view('company.show', [
            'company' => $company,
            'reports' => $reports
        ]);
    }

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
}