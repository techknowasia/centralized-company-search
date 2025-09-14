<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(): View
    {
        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();

        return view('cart.index', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer',
            'report_id' => 'required|integer',
            'country' => 'required|string|in:sg,mx',
            'quantity' => 'integer|min:1|max:10'
        ]);

        try {
            // Check if the report is already in cart before adding
            $isAlreadyInCart = $this->cartService->isReportInCart(
                $request->company_id,
                $request->report_id,
                $request->country
            );

            if ($isAlreadyInCart) {
                $duplicateInfo = $this->cartService->getDuplicateReportInfo(
                    $request->company_id,
                    $request->report_id,
                    $request->country
                );

                $message = $duplicateInfo 
                    ? "This report ({$duplicateInfo['report_name']}) for {$duplicateInfo['company_name']} is already in your cart with quantity {$duplicateInfo['quantity']}. The quantity has been updated."
                    : "This report is already in your cart. The quantity has been updated.";

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'isDuplicate' => true,
                    'cartCount' => $this->cartService->getCartCount(),
                    'cartTotal' => $this->cartService->getTotal()
                ]);
            }

            $cartItem = $this->cartService->addToCart(
                $request->company_id,
                $request->report_id,
                $request->country,
                $request->quantity ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Report added to cart successfully',
                'cartItem' => $cartItem,
                'isDuplicate' => false,
                'cartCount' => $this->cartService->getCartCount(),
                'cartTotal' => $this->cartService->getTotal()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to cart: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getCompanyReports(Request $request, int $companyId): JsonResponse
    {
        try {
            $country = $request->query('country');
            
            if (!$country) {
                return response()->json([
                    'success' => false,
                    'message' => 'Country parameter is required'
                ], 400);
            }
            
            $reports = $this->cartService->getCompanyReports($companyId, $country);
            
            return response()->json([
                'success' => true,
                'reports' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function remove(string $cartItemId): JsonResponse
    {
        $this->cartService->removeFromCart($cartItemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cartCount' => $this->cartService->getCartCount(),
            'cartTotal' => $this->cartService->getTotal()
        ]);
    }

    public function update(Request $request, string $cartItemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $this->cartService->updateCartItem($cartItemId, $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cartCount' => $this->cartService->getCartCount(),
            'cartTotal' => $this->cartService->getTotal()
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cartCount' => 0,
            'cartTotal' => 0
        ]);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartService->getCartCount()
        ]);
    }
}