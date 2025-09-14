<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    private const CART_KEY = 'cart_items';

    public function addToCart(int $companyId, int $reportId, string $country, int $quantity = 1): array
    {
        $cartItems = $this->getCartItems();
        
        // Get report details
        $report = $this->getReportDetails($reportId, $country);
        $company = $this->getCompanyDetails($companyId, $country);
        
        if (!$report || !$company) {
            throw new \Exception('Report or company not found');
        }

        $cartItemId = $this->generateCartItemId($companyId, $reportId, $country);
        
        // Check if item already exists in cart
        $existingItem = $cartItems->firstWhere('id', $cartItemId);
        
        if ($existingItem) {
            // Throw exception to prevent duplicate addition
            throw new \Exception('This report is already in your cart');
        }

        // Add new item
        $cartItem = [
            'id' => $cartItemId,
            'company_id' => $companyId,
            'report_id' => $reportId,
            'country' => $country,
            'quantity' => $quantity,
            'company_name' => $company->name,
            'report_name' => $report->name,
            'price' => $this->getReportPrice($reportId, $country, $companyId),
            'added_at' => now()->toISOString()
        ];
        
        $cartItems->push($cartItem);
        $this->saveCartItems($cartItems);
        
        return $cartItem;
    }

    /**
     * Check if a specific report for a company already exists in the cart
     */
    public function isReportInCart(int $companyId, int $reportId, string $country): bool
    {
        $cartItems = $this->getCartItems();
        $cartItemId = $this->generateCartItemId($companyId, $reportId, $country);
        
        return $cartItems->contains('id', $cartItemId);
    }

    /**
     * Get duplicate report information for better error messages
     */
    public function getDuplicateReportInfo(int $companyId, int $reportId, string $country): ?array
    {
        $cartItems = $this->getCartItems();
        $cartItemId = $this->generateCartItemId($companyId, $reportId, $country);
        
        $existingItem = $cartItems->firstWhere('id', $cartItemId);
        
        if ($existingItem) {
            return [
                'company_name' => $existingItem['company_name'],
                'report_name' => $existingItem['report_name'],
                'country' => $existingItem['country'],
                'quantity' => $existingItem['quantity']
            ];
        }
        
        return null;
    }

    public function removeFromCart(string $cartItemId): void
    {
        $cartItems = $this->getCartItems();
        $cartItems = $cartItems->reject(function ($item) use ($cartItemId) {
            return $item['id'] === $cartItemId;
        });
        
        $this->saveCartItems($cartItems);
    }

    public function updateCartItem(string $cartItemId, int $quantity): void
    {
        $cartItems = $this->getCartItems();
        $cartItems = $cartItems->map(function ($item) use ($cartItemId, $quantity) {
            if ($item['id'] === $cartItemId) {
                $item['quantity'] = $quantity;
            }
            return $item;
        });
        
        $this->saveCartItems($cartItems);
    }

    public function clearCart(): void
    {
        Session::forget(self::CART_KEY);
    }

    public function getCartItems(): Collection
    {
        return collect(Session::get(self::CART_KEY, []));
    }

    public function getCartCount(): int
    {
        return $this->getCartItems()->sum('quantity');
    }

    public function getTotal(): float
    {
        return $this->getCartItems()->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    private function generateCartItemId(int $companyId, int $reportId, string $country): string
    {
        // For Mexico, include state_id in the cart item ID to prevent duplicates
        // across different states for the same company and report
        if ($country === 'mx') {
            $company = $this->getCompanyDetails($companyId, $country);
            $stateId = $company ? $company->state_id : 0;
            return md5("{$companyId}_{$reportId}_{$country}_{$stateId}");
        }
        
        // For Singapore, use the existing logic
        return md5("{$companyId}_{$reportId}_{$country}");
    }

    private function getReportDetails(int $reportId, string $country): ?object
    {
        try {
            $connection = $country === 'sg' ? 'companies_house_sg' : 'companies_house_mx';
            return DB::connection($connection)
                ->table('reports')
                ->where('id', $reportId)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCompanyDetails(int $companyId, string $country): ?object
    {
        try {
            $connection = $country === 'sg' ? 'companies_house_sg' : 'companies_house_mx';
            return DB::connection($connection)
                ->table('companies')
                ->where('id', $companyId)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getReportPrice(int $reportId, string $country, int $companyId): float
    {
        if ($country === 'sg') {
            // Singapore: Direct pricing from reports table
            $report = DB::connection('companies_house_sg')
                ->table('reports')
                ->where('id', $reportId)
                ->first();
            
            return $report ? (float) $report->amount : 0;
        } else {
            // Mexico: Pricing from report_state table
            $company = DB::connection('companies_house_mx')
                ->table('companies')
                ->where('id', $companyId)
                ->first();
            
            if (!$company) return 0;
            
            $reportState = DB::connection('companies_house_mx')
                ->table('report_state')
                ->where('report_id', $reportId)
                ->where('state_id', $company->state_id)
                ->first();
            
            return $reportState ? (float) $reportState->amount : 0;
        }
    }

    private function saveCartItems(Collection $cartItems): void
    {
        Session::put(self::CART_KEY, $cartItems->toArray());
    }
}