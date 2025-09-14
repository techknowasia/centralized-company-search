@extends('layouts.app')

@section('title', 'Search Companies')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Background Pattern -->
    <div class="absolute opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25px 25px, rgba(255,255,255,0.2) 2px, transparent 0), radial-gradient(circle at 75px 75px, rgba(255,255,255,0.2) 2px, transparent 0); background-size: 100px 100px;"></div>
    </div>

    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-4xl">
            <!-- Instructions -->
            <div class="text-center mb-8">
                <div class="text-gray-900 text-lg mb-4">
                    <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold mr-2">1</span>
                    Buy the latest report on shareholders, directors and capital structure
                </div>
                <div class="text-gray-900 text-lg">
                    <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold mr-2">2</span>
                    Receive the report directly in your inbox
                </div>
            </div>

            <!-- Search Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Search Companies</h1>
                    <p class="text-gray-600">Find company information across Singapore and Mexico</p>
                </div>

                <!-- Search Form -->
                <form action="{{ route('search') }}" method="GET" class="space-y-4">
                    <div class="relative">
                        <input type="text" 
                               name="q" 
                               id="search-input"
                               value="{{ request('q') }}"
                               placeholder="Enter company name..." 
                               class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               autocomplete="off">
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        
                        <!-- Search Suggestions Dropdown -->
                        <div id="suggestions-dropdown" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg mt-1 z-50 hidden">
                            <div id="suggestions-list" class="max-h-80 overflow-y-auto">
                                <!-- Suggestions will be populated here -->
                            </div>
                            
                            <!-- Pagination for suggestions -->
                            <div id="suggestions-pagination" class="border-t border-gray-200 p-3 hidden">
                                <div class="flex items-center justify-between">
                                    <button id="prev-page" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-chevron-left mr-1"></i>Previous
                                    </button>
                                    <span id="page-info" class="text-sm text-gray-600"></span>
                                    <button id="next-page" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Next<i class="fas fa-chevron-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Country Filter - Now Dynamic -->
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="country" value="" {{ request('country') == '' ? 'checked' : '' }} class="mr-2">
                            <span class="text-gray-700">All Countries</span>
                        </label>
                        
                        @foreach($countries as $code => $config)
                            <label class="flex items-center">
                                <input type="radio" name="country" value="{{ $code }}" {{ request('country') == $code ? 'checked' : '' }} class="mr-2">
                                <span class="text-gray-700">{{ $config['flag'] }} {{ $config['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </form>

                <!-- Quick Search Examples -->
                <div class="mt-8">
                    <p class="text-gray-600 text-sm mb-3">Try searching for:</p>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="searchExample('tech')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Technology</button>
                        <button onclick="searchExample('investment')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Investment</button>
                        <button onclick="searchExample('capital')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Capital</button>
                        <button onclick="searchExample('trading')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Trading</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
let currentSuggestions = [];
let currentPage = 1;
let totalPages = 1;
let currentQuery = '';

function searchExample(query) {
    document.querySelector('input[name="q"]').value = query;
    document.querySelector('form').submit();
}

// Search suggestions functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    currentQuery = query;
    currentPage = 1; // Reset to first page for new query
    
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Hide suggestions if query is too short
    if (query.length < 1) {
        hideSuggestions();
        return;
    }
    
    // Debounce the search
    searchTimeout = setTimeout(() => {
        fetchSuggestions(query, currentPage);
    }, 300);
});

// Fetch suggestions from web route
function fetchSuggestions(query, page = 1) {
    const url = `{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}&page=${page}&per_page=8`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Suggestions response:', data); // Debug log
            if (data.success && data.data && data.data.length > 0) {
                showSuggestions(data.data, data.meta || {});
            } else {
                hideSuggestions();
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            hideSuggestions();
        });
}

// Show suggestions dropdown
function showSuggestions(suggestions, meta = {}) {
    const dropdown = document.getElementById('suggestions-dropdown');
    const list = document.getElementById('suggestions-list');
    const pagination = document.getElementById('suggestions-pagination');
    
    currentSuggestions = suggestions;
    currentPage = meta.current_page || 1;
    totalPages = meta.last_page || 1;
    
    // Clear previous suggestions
    list.innerHTML = '';
    
    // Add suggestions
    suggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
        
        // Determine country flag and name
        const countryFlag = suggestion.country === 'sg' ? '🇸🇬' : '🇲🇽';
        const countryName = suggestion.country === 'sg' ? 'Singapore' : 'Mexico';
        
        item.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">${countryFlag}</span>
                        <div>
                            <div class="font-medium text-gray-900 hover:text-blue-600 transition-colors" onclick="selectSuggestion('${suggestion.slug}')">
                                ${suggestion.name}
                            </div>
                            <div class="text-sm text-gray-500">${countryName}</div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    ${suggestion.registration_number ? `<div class="text-sm text-gray-400">${suggestion.registration_number}</div>` : ''}
                    <button onclick="addToCartFromSuggestion(${suggestion.id}, '${suggestion.country}')" 
                            class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        `;
        
        // Add click handler for the entire row
        item.addEventListener('click', (e) => {
            // Don't trigger if clicking on the add to cart button
            if (!e.target.closest('button')) {
                selectSuggestion(suggestion.slug);
            }
        });
        
        list.appendChild(item);
    });
    
    // Show/hide pagination
    if (totalPages > 1) {
        updatePaginationInfo();
        pagination.classList.remove('hidden');
    } else {
        pagination.classList.add('hidden');
    }
    
    dropdown.classList.remove('hidden');
}

// Update pagination info
function updatePaginationInfo() {
    document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages}`;
    document.getElementById('prev-page').disabled = currentPage <= 1;
    document.getElementById('next-page').disabled = currentPage >= totalPages;
}

// Hide suggestions dropdown
function hideSuggestions() {
    const dropdown = document.getElementById('suggestions-dropdown');
    dropdown.classList.add('hidden');
    currentSuggestions = [];
    currentPage = 1;
    totalPages = 1;
}

// Select a suggestion
function selectSuggestion(slug) {
    // Navigate to company page
    window.location.href = `/company/${slug}`;
}

// Add to cart from suggestion
function addToCartFromSuggestion(companyId, country) {
    // First, get available reports for this company
    fetch(`/cart/company/${companyId}/reports?country=${country}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.reports && data.reports.length > 0) {
                // Use the first available report
                const firstReport = data.reports[0];
                addToCart(companyId, firstReport.id, country, 1);
            } else {
                showNotification('No reports available for this company', 'warning');
            }
        })
        .catch(error => {
            console.error('Error fetching company reports:', error);
            showNotification('Error loading company reports', 'error');
        });
}

// Pagination event handlers
document.getElementById('prev-page').addEventListener('click', function() {
    if (currentPage > 1) {
        currentPage--;
        fetchSuggestions(currentQuery, currentPage);
    }
});

document.getElementById('next-page').addEventListener('click', function() {
    if (currentPage < totalPages) {
        currentPage++;
        fetchSuggestions(currentQuery, currentPage);
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('suggestions-dropdown');
    const input = document.getElementById('search-input');
    
    if (!dropdown.contains(e.target) && !input.contains(e.target)) {
        hideSuggestions();
    }
});

// Handle keyboard navigation
document.getElementById('search-input').addEventListener('keydown', function(e) {
    const dropdown = document.getElementById('suggestions-dropdown');
    
    if (dropdown.classList.contains('hidden') || currentSuggestions.length === 0) {
        return;
    }
    
    const items = dropdown.querySelectorAll('.hover\\:bg-gray-50');
    let currentIndex = -1;
    
    // Find currently selected item
    items.forEach((item, index) => {
        if (item.classList.contains('bg-blue-50')) {
            currentIndex = index;
        }
    });
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        currentIndex = Math.min(currentIndex + 1, items.length - 1);
        updateSelection(items, currentIndex);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        currentIndex = Math.max(currentIndex - 1, -1);
        updateSelection(items, currentIndex);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (currentIndex >= 0) {
            const suggestion = currentSuggestions[currentIndex];
            selectSuggestion(suggestion.slug);
        }
    } else if (e.key === 'Escape') {
        hideSuggestions();
    }
});

function updateSelection(items, index) {
    items.forEach((item, i) => {
        if (i === index) {
            item.classList.add('bg-blue-50');
        } else {
            item.classList.remove('bg-blue-50');
        }
    });
}
</script>
@endsection