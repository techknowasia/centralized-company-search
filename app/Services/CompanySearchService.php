<?php

namespace App\Services;

use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\SG\CompanyRepositorySG;
use App\Repositories\MX\CompanyRepositoryMX;
use Illuminate\Support\Collection;

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

    public function searchAll(string $query, ?string $country = null, int $limit = 10): Collection
    {
        $results = collect();

        if (!$country || $country === 'sg') {
            $sgResults = $this->sgRepository->search($query, $limit);
            $sgResults->each(function ($item) {
                $item->country = 'sg';
                $item->country_name = 'Singapore';
            });
            $results = $results->merge($sgResults);
        }

        if (!$country || $country === 'mx') {
            $mxResults = $this->mxRepository->search($query, $limit);
            $mxResults->each(function ($item) {
                $item->country = 'mx';
                $item->country_name = 'Mexico';
            });
            $results = $results->merge($mxResults);
        }

        return $results->take($limit);
    }

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

    public function getCompanyReports(string $country, int $companyId): Collection
    {
        return match ($country) {
            'sg' => $this->sgRepository->getReports($companyId),
            'mx' => $this->mxRepository->getReports($companyId),
            default => throw new \InvalidArgumentException("Invalid country: {$country}")
        };
    }

    public function getSuggestions(string $query, int $limit = 5): Collection
    {
        return $this->searchAll($query, null, $limit);
    }
}
