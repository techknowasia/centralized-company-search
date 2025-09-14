<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected PaymentService $paymentService;

    public function __construct(CartService $cartService, PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->paymentService = $paymentService;
    }

    public function index(): View
    {
        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'card_number' => 'required|string|max:20',
            'expiry_date' => 'required|string|max:5',
            'cvc' => 'required|string|max:4',
            'terms_accepted' => 'required|accepted'
        ]);

        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Process payment
        $paymentResult = $this->paymentService->processPayment([
            'amount' => $total,
            'currency' => 'USD',
            'card_number' => $request->card_number,
            'expiry_date' => $request->expiry_date,
            'cvc' => $request->cvc,
            'customer_name' => $request->name,
            'customer_email' => $request->email
        ]);

        if ($paymentResult['success']) {
            // Clear cart after successful payment
            $this->cartService->clearCart();
            
            return redirect()->route('home')->with('success', 'Order placed successfully! You will receive your reports via email.');
        }

        return redirect()->back()->with('error', 'Payment failed. Please try again.');
    }
}