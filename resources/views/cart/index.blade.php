@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                </a>
            </div>
        </div>

        @if($cartItems->isEmpty())
            <!-- Empty Cart -->
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-4">Add some reports to get started</p>
                <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Start Shopping
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Cart Items ({{ $cartItems->count() }})</h2>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            @foreach($cartItems as $item)
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $item['company_name'] }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $item['report_name'] }}</p>
                                            <div class="flex items-center mt-2">
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                    {{ strtoupper($item['country']) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <!-- Quantity -->
                                            <div class="flex items-center space-x-2">
                                                {{-- <button onclick="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})" 
                                                        class="w-8 h-8 bg-gray-200 text-gray-600 rounded-full hover:bg-gray-300 transition-colors">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button> --}}
                                                <span class="w-8 text-center font-medium">{{ $item['quantity'] }}</span>
                                                {{-- <button onclick="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})" 
                                                        class="w-8 h-8 bg-gray-200 text-gray-600 rounded-full hover:bg-gray-300 transition-colors">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button> --}}
                                            </div>
                                            
                                            <!-- Price -->
                                            <div class="text-right">
                                                <div class="text-lg font-semibold text-gray-900">
                                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ${{ number_format($item['price'], 2) }} each
                                                </div>
                                            </div>
                                            
                                            <!-- Remove Button -->
                                            <button onclick="removeFromCart('{{ $item['id'] }}')" 
                                                    class="text-red-600 hover:text-red-800 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">$0.00</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total</span>
                                    <span class="text-lg font-semibold text-gray-900">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('checkout.index') }}" 
                           class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors text-center block">
                            <i class="fas fa-credit-card mr-2"></i>Proceed to Checkout
                        </a>
                        
                        <div class="mt-4 text-center">
                            <button onclick="clearCart()" 
                                    class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch(`/cart/update/${cartItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification('Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        showNotification('Error updating cart', 'error');
    });
}

function removeFromCart(cartItemId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch(`/cart/remove/${cartItemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showNotification('Failed to remove item', 'error');
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            showNotification('Error removing item', 'error');
        });
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showNotification('Failed to clear cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing cart:', error);
            showNotification('Error clearing cart', 'error');
        });
    }
}
</script>
@endsection
