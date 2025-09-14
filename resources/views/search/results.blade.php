@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Search Results</h1>
                    @if($query)
                        <p class="text-gray-600 mt-1">Results for "{{ $query }}"</p>
                    @endif
                </div>
                <a href="{{ route('cart.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-shopping-cart mr-2"></i>View Cart ({{ app(\App\Services\CartService::class)->getCartCount() }})
                </a>
            </div>
        </div>

        @if($companies->isEmpty())
            <!-- No Results -->
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No companies found</h2>
                <p class="text-gray-600 mb-4">Try adjusting your search terms or filters</p>
                <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    New Search
                </a>
            </div>
        @else
            <!-- Results -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <p class="text-gray-600">Showing {{ ($currentPage - 1) * $perPage + 1 }} to {{ min($currentPage * $perPage, $totalResults) }} of {{ $totalResults }} results</p>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($companies as $company)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                            <a href="{{ route('company.show', $company->slug) }}">{{ $company->name }}</a>
                                        </h3>
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                            {{ $company->country_name ?? strtoupper($company->country ?? 'Unknown') }}
                                        </span>
                                    </div>
                                    
                                    @if(isset($company->registration_number) && $company->registration_number)
                                        <p class="text-sm text-gray-600 mt-1">
                                            Registration: {{ $company->registration_number }}
                                        </p>
                                    @endif
                                    
                                    @if(isset($company->address) && $company->address)
                                        <p class="text-sm text-gray-500 mt-1">{{ $company->address }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-3">
                                    <button onclick="addToCart({{ $company->id }}, 1, '{{ $company->country }}')" 
                                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                                        <i class="fas fa-plus mr-1"></i>Add to cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($totalPages > 1)
                    <div class="p-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ ($currentPage - 1) * $perPage + 1 }} to {{ min($currentPage * $perPage, $totalResults) }} of {{ $totalResults }} results
                            </div>
                            
                            <div class="flex space-x-2">
                                @if($currentPage > 1)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif

                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                                       class="px-3 py-2 text-sm font-medium {{ $i == $currentPage ? 'bg-blue-600 text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50' }} rounded-md">
                                        {{ $i }}
                                    </a>
                                @endfor

                                @if($currentPage < $totalPages)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Next
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection