<?php

namespace App\Repositories\MX;

use App\Models\MX\CompanyMX;
use App\Models\MX\ReportMX;
use App\Models\MX\ReportStateMX;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyRepositoryMX implements CompanyRepositoryInterface
{
    public function search(string $query, int $limit = 10): Collection
    {
        return CompanyMX::where('name', 'like', "%{$query}%")
            ->orWhere('brand_name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->with('state')
            ->limit($limit)
            ->get();
    }

    public function findById(int $id): ?CompanyMX
    {
        return CompanyMX::find($id);
    }

    public function findDetails(int $id): CompanyMX
    {
        return CompanyMX::with('state')->findOrFail($id);
    }

    public function getReports(int $companyId): Collection
    {
        // Mexico: Reports depend on company's state via report_state table
        $company = CompanyMX::with('state')->findOrFail($companyId);
        
        if (!$company->state) {
            return collect();
        }

        return ReportMX::whereHas('reportStates', function ($query) use ($company) {
            $query->where('state_id', $company->state_id);
        })
        ->with(['reportStates' => function ($query) use ($company) {
            $query->where('state_id', $company->state_id);
        }])
        ->orderBy('order')
        ->get();
    }

    public function getAll(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return CompanyMX::with('state')->paginate($perPage, ['*'], 'page', $page);
    }
}
