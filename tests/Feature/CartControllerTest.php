<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart_prevents_duplicates(): void
    {
        // Clear session
        Session::flush();

        // Mock the database responses
        $this->mockDatabaseResponses();

        // First request - should succeed
        $response1 = $this->postJson('/cart/add', [
            'company_id' => 1,
            'report_id' => 1,
            'country' => 'sg',
            'quantity' => 1
        ]);

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Second request - should return error for duplicate
        $response2 = $this->postJson('/cart/add', [
            'company_id' => 1,
            'report_id' => 1,
            'country' => 'sg',
            'quantity' => 1
        ]);

        $response2->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'This report is already in your cart'
            ]);
    }

    public function test_add_to_cart_validation(): void
    {
        $response = $this->postJson('/cart/add', [
            'company_id' => 'invalid',
            'report_id' => 1,
            'country' => 'invalid_country'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id', 'country']);
    }

    private function mockDatabaseResponses(): void
    {
        // This would need to be implemented based on your actual database setup
        // For now, we'll assume the database is properly seeded for testing
    }
}
