<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class CountryService
{
    public function getAvailableCountries(): array
    {
        return array_keys(Config::get('countries', []));
    }

    public function getCountryConfig(string $countryCode): ?array
    {
        return Config::get("countries.{$countryCode}");
    }

    public function getAllCountries(): Collection
    {
        return collect(Config::get('countries', []));
    }

    public function getCountryFlag(string $countryCode): string
    {
        return $this->getCountryConfig($countryCode)['flag'] ?? '🏳️';
    }

    public function getCountryName(string $countryCode): string
    {
        return $this->getCountryConfig($countryCode)['name'] ?? ucfirst($countryCode);
    }

    public function getCountryConnection(string $countryCode): string
    {
        return $this->getCountryConfig($countryCode)['connection'] ?? 'mysql';
    }

    public function getCountryRepository(string $countryCode): string
    {
        return $this->getCountryConfig($countryCode)['repository'];
    }

    public function hasStates(string $countryCode): bool
    {
        return $this->getCountryConfig($countryCode)['schema']['has_states'] ?? false;
    }

    public function hasDirectReports(string $countryCode): bool
    {
        return $this->getCountryConfig($countryCode)['schema']['reports_direct'] ?? true;
    }

    public function getPricingTable(string $countryCode): string
    {
        return $this->getCountryConfig($countryCode)['schema']['pricing_table'] ?? 'reports';
    }
}
