@extends('layouts.app')

@section('title', 'Search Companies')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25px 25px, rgba(255,255,255,0.2) 2px, transparent 0), radial-gradient(circle at 75px 75px, rgba(255,255,255,0.2) 2px, transparent 0); background-size: 100px 100px;"></div>
    </div>

    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-4xl">
            <!-- Instructions -->
            <div class="text-center mb-8">
                <div class="text-white text-lg mb-4">
                    <span class="bg-blue-600 px-3 py-1 rounded-full text-sm font-semibold mr-2">②</span>
                    Buy the latest report on shareholders, directors and capital structure
                </div>
                <div class="text-white text-lg">
                    <span class="bg-blue-600 px-3 py-1 rounded-full text-sm font-semibold mr-2">③</span>
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
                            <div id="suggestions-list" class="max-h-60 overflow-y-auto">
                                <!-- Suggestions will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Country Filter -->
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="country" value="" {{ request('country') == '' ? 'checked' : '' }} class="mr-2">
                            <span class="text-gray-700">All Countries</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="country" value="sg" {{ request('country') == 'sg' ? 'checked' : '' }} class="mr-2">
                            <span class="text-gray-700">🇸🇬 Singapore</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="country" value="mx" {{ request('country') == 'mx' ? 'checked' : '' }} class="mr-2">
                            <span class="text-gray-700">🇲🇽 Mexico</span>
                        </label>
                    </div>
                </form>

                <!-- Quick Search Examples -->
                <div class="mt-8">
                    <p class="text-gray-600 text-sm mb-3">Try searching for:</p>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="searchExample('tech')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Technology</button>
                        <button onclick="searchExample('finance')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Finance</button>
                        <button onclick="searchExample('consulting')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">Consulting</button>
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

function searchExample(query) {
    document.querySelector('input[name="q"]').value = query;
    document.querySelector('form').submit();
}

// Search suggestions functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Hide suggestions if query is too short
    if (query.length < 2) {
        hideSuggestions();
        return;
    }
    
    // Debounce the search
    searchTimeout = setTimeout(() => {
        fetchSuggestions(query);
    }, 300);
});

// Fetch suggestions from web route
function fetchSuggestions(query) {
    fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                showSuggestions(data.data);
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
function showSuggestions(suggestions) {
    const dropdown = document.getElementById('suggestions-dropdown');
    const list = document.getElementById('suggestions-list');
    
    currentSuggestions = suggestions;
    
    // Clear previous suggestions
    list.innerHTML = '';
    
    // Add suggestions
    suggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-900">${suggestion.name}</div>
                    <div class="text-sm text-gray-500">${suggestion.country_name || suggestion.country}</div>
                </div>
                <div class="text-sm text-gray-400">
                    ${suggestion.registration_number || ''}
                </div>
            </div>
        `;
        
        item.addEventListener('click', () => {
            selectSuggestion(suggestion);
        });
        
        list.appendChild(item);
    });
    
    dropdown.classList.remove('hidden');
}

// Hide suggestions dropdown
function hideSuggestions() {
    const dropdown = document.getElementById('suggestions-dropdown');
    dropdown.classList.add('hidden');
    currentSuggestions = [];
}

// Select a suggestion
function selectSuggestion(suggestion) {
    document.getElementById('search-input').value = suggestion.name;
    hideSuggestions();
    
    // Submit the form
    document.querySelector('form').submit();
}

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
            selectSuggestion(currentSuggestions[currentIndex]);
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