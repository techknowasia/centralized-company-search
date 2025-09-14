<?php

namespace App\Services;

class PaymentService
{
    public function processPayment(array $paymentData): array
    {
        // For assignment purposes, we'll simulate payment processing
        // In a real application, this would integrate with Stripe, PayPal, etc.
        
        try {
            // Simulate payment processing delay
            usleep(500000); // 0.5 seconds
            
            // For testing, always return success
            // In production, this would call actual payment gateway
            $success = $this->validatePaymentData($paymentData);
            
            if ($success) {
                return [
                    'success' => true,
                    'transaction_id' => 'TXN_' . uniqid(),
                    'message' => 'Payment processed successfully',
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Payment validation failed',
                'error_code' => 'VALIDATION_ERROR'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'error_code' => 'PROCESSING_ERROR'
            ];
        }
    }

    private function validatePaymentData(array $data): bool
    {
        // Basic validation for assignment purposes
        $required = ['amount', 'currency', 'card_number', 'expiry_date', 'cvc'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Validate card number format (basic)
        if (!preg_match('/^\d{13,19}$/', str_replace(' ', '', $data['card_number']))) {
            return false;
        }
        
        // Validate expiry date format
        if (!preg_match('/^\d{2}\/\d{2}$/', $data['expiry_date'])) {
            return false;
        }
        
        // Validate CVC
        if (!preg_match('/^\d{3,4}$/', $data['cvc'])) {
            return false;
        }
        
        return true;
    }
}