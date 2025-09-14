<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Companies House')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                            <span class="text-white font-bold text-sm">H</span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-gray-900">Companies House</h1>
                    </div>
                </a>

                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Search</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">About</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Enterprise</a>
                </nav>

                <!-- Search Bar -->
                <div class="flex-1 max-w-lg mx-8">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" 
                               name="q" 
                               value="{{ request('q') }}"
                               placeholder="Type the name of the company" 
                               class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Cart -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ app(\App\Services\CartService::class)->getCartCount() }}</span>
                    </a>
                    {{-- <a href="#" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Login</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Register</a> --}}
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                            <span class="text-white font-bold text-sm">H</span>
                        </div>
                        <span class="ml-3 text-xl font-bold">Companies House</span>
                    </div>
                    <p class="text-gray-400">Creating a more transparent business environment.</p>
                </div>

                <!-- Countries -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">ASIA</h3>
                    <div class="space-y-2 text-gray-400">
                        <div>🇭🇰 Hong Kong</div>
                        <div>🇮🇩 Indonesia</div>
                        <div>🇲🇾 Malaysia</div>
                        <div>🇵 Pakistan</div>
                        <div>🇵 Philippines</div>
                        <div>🇹🇭 Thailand</div>
                        <div>🇻🇳 Vietnam</div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">LATAM</h3>
                    <div class="space-y-2 text-gray-400">
                        <div>🇨 Colombia</div>
                        <div>🇲🇽 Mexico</div>
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Company</h3>
                    <div class="space-y-2">
                        <a href="#" class="block text-gray-400 hover:text-white">About</a>
                        <a href="#" class="block text-gray-400 hover:text-white">Contact</a>
                        <a href="#" class="block text-gray-400 hover:text-white">Privacy Policy</a>
                        <a href="#" class="block text-gray-400 hover:text-white">Return Policy</a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Companies House Insights Pte. Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // CSRF Token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        // Cart functionality
        function updateCartCount() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cart-count').textContent = data.count;
                })
                .catch(error => console.error('Error updating cart count:', error));
        }

        // Add to cart function
        function addToCart(companyId, reportId, country, quantity = 1) {
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    company_id: companyId,
                    report_id: reportId,
                    country: country,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cartCount;
                    
                    // Show appropriate message based on whether it's a duplicate
                    if (data.isDuplicate) {
                        showNotification(data.message, 'warning');
                    } else {
                        showNotification(data.message, 'success');
                    }
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showNotification('Error adding to cart', 'error');
            });
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>

    @yield('scripts')
</body>
</html>