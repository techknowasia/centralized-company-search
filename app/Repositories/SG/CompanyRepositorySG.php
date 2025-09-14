<?php

namespace App\Repositories\SG;

use App\Models\SG\CompanySG;
use App\Models\SG\ReportSG;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyRepositorySG implements CompanyRepositoryInterface
{
    public function search(string $query, int $limit = 10): Collection
    {
        return CompanySG::where('name', 'like', "%{$query}%")
            ->orWhere('former_names', 'like', "%{$query}%")
            ->orWhere('registration_number', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    public function findById(int $id): ?CompanySG
    {
        return CompanySG::find($id);
    }

    public function findDetails(int $id): CompanySG
    {
        return CompanySG::findOrFail($id);
    }

    public function getReports(int $companyId): Collection
    {
        // Singapore: All companies have access to all reports
        return ReportSG::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getAll(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return CompanySG::paginate($perPage, ['*'], 'page', $page);
    }
}
