<?php

namespace App\Repositories\Interfaces;

interface CompanyRepositoryInterface
{
    public function search(string $query, int $limit = 10): \Illuminate\Database\Eloquent\Collection;
    
    public function findById(int $id): ?\Illuminate\Database\Eloquent\Model;
    
    public function findDetails(int $id): \Illuminate\Database\Eloquent\Model;
    
    public function getReports(int $companyId): \Illuminate\Database\Eloquent\Collection;
    
    public function getAll(int $page = 1, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
