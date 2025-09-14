<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CartService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Mockery;

class CartServiceTest extends TestCase
{
    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
        Session::flush(); // Clear session for each test
    }

    public function test_prevents_duplicate_reports_for_singapore(): void
    {
        // Mock database responses
        $this->mockDatabaseResponses('sg');

        // Add first report
        $result1 = $this->cartService->addToCart(1, 1, 'sg', 1);
        
        // Try to add the same report again - should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This report is already in your cart');
        
        $this->cartService->addToCart(1, 1, 'sg', 1);

        // Should have only one item in cart with quantity 1
        $cartItems = $this->cartService->getCartItems();
        $this->assertCount(1, $cartItems);
        $this->assertEquals(1, $cartItems->first()['quantity']);
        
        // Check if duplicate detection works
        $this->assertTrue($this->cartService->isReportInCart(1, 1, 'sg'));
    }
    public function test_prevents_duplicate_reports_for_mexico_with_state(): void
    {
        // Mock database responses for Mexico
        $this->mockDatabaseResponses('mx');

        // Add first report for company in state 1
        $result1 = $this->cartService->addToCart(1, 1, 'mx', 1);
        
        // Try to add the same report again for the same company - should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This report is already in your cart');
        
        $this->cartService->addToCart(1, 1, 'mx', 1);

        // Should have only one item in cart with quantity 1
        $cartItems = $this->cartService->getCartItems();
        $this->assertCount(1, $cartItems);
        $this->assertEquals(1, $cartItems->first()['quantity']);
        
        // Check if duplicate detection works
        $this->assertTrue($this->cartService->isReportInCart(1, 1, 'mx'));
    }

    public function test_allows_different_reports_for_same_company(): void
    {
        // Mock database responses
        $this->mockDatabaseResponses('sg');

        // Add first report
        $this->cartService->addToCart(1, 1, 'sg', 1);
        
        // Add different report for same company
        $this->cartService->addToCart(1, 2, 'sg', 1);

        // Should have two items in cart
        $cartItems = $this->cartService->getCartItems();
        $this->assertCount(2, $cartItems);
    }

    public function test_allows_same_report_for_different_companies(): void
    {
        // Mock database responses
        $this->mockDatabaseResponses('sg');

        // Add report for first company
        $this->cartService->addToCart(1, 1, 'sg', 1);
        
        // Add same report for different company
        $this->cartService->addToCart(2, 1, 'sg', 1);

        // Should have two items in cart
        $cartItems = $this->cartService->getCartItems();
        $this->assertCount(2, $cartItems);
    }

    private function mockDatabaseResponses(string $country): void
    {
        $connection = $country === 'sg' ? 'companies_house_sg' : 'companies_house_mx';
        
        // Mock report data
        $report = (object) [
            'id' => 1,
            'name' => 'Test Report',
            'amount' => 100.00,
            'info' => 'Test report info'
        ];

        // Mock company data
        $company = (object) [
            'id' => 1,
            'name' => 'Test Company',
            'state_id' => 1
        ];

        // Mock DB facade
        DB::shouldReceive('connection')
            ->with($connection)
            ->andReturnSelf();

        DB::shouldReceive('table')
            ->with('reports')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('id', 1)
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->andReturn($report);

        DB::shouldReceive('table')
            ->with('companies')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('id', 1)
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->andReturn($company);

        // Mock report_state for Mexico
        if ($country === 'mx') {
            $reportState = (object) [
                'id' => 1,
                'report_id' => 1,
                'state_id' => 1,
                'amount' => 100.00
            ];

            DB::shouldReceive('table')
                ->with('report_state')
                ->andReturnSelf();

            DB::shouldReceive('where')
                ->with('report_id', 1)
                ->andReturnSelf();

            DB::shouldReceive('where')
                ->with('state_id', 1)
                ->andReturnSelf();

            DB::shouldReceive('first')
                ->andReturn($reportState);
        }
    }
}
