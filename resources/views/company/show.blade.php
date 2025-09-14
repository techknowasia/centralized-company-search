@extends('layouts.app')

@section('title', $company->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Company Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $company->name }}</h1>
                    
                    <div class="prose max-w-none">
                        <p class="text-gray-600 mb-6">
                            {{ $company->name }} is a company registered in {{ $company->country_name }}.
                            @if(isset($company->registration_number) && $company->registration_number)
                                Registration number: {{ $company->registration_number }}.
                            @endif
                            @if(isset($company->address) && $company->address)
                                Located at {{ $company->address }}.
                            @endif
                        </p>
                    </div>

                    <!-- Company Information -->
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Company Information</h2>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registered Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $company->name }}</dd>
                            </div>
                            
                            @if(isset($company->registration_number) && $company->registration_number)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $company->registration_number }}</dd>
                                </div>
                            @endif
                            
                            @if(isset($company->former_names) && $company->former_names)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Former Names</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $company->former_names }}</dd>
                                </div>
                            @endif
                            
                            @if(isset($company->brand_name) && $company->brand_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Brand Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $company->brand_name }}</dd>
                                </div>
                            @endif
                            
                            @if(isset($company->state_name) && $company->state_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">State</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $company->state_name }}</dd>
                                </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Country</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $company->country_name }}</dd>
                            </div>
                            
                            @if(isset($company->address) && $company->address)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $company->address }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Buy Company Report</h2>
                    <p class="text-gray-600 mb-4">
                        Official company report of {{ $company->name }} as provided by the regulatory authority.
                    </p>
                    
                    <div class="flex items-center text-green-600 mb-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="text-sm font-medium">Delivered in 1 working day</span>
                    </div>

                    @if($reports->isNotEmpty())
                        <div class="space-y-4 mb-6">
                            @foreach($reports as $report)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900">{{ $report->name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $report->info ?? $report->description ?? 'Company report' }}</p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <div class="text-lg font-semibold text-gray-900">
                                                ${{ number_format($report->amount ?? 0, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button onclick="addToCart({{ $company->id }}, {{ $report->id }}, '{{ $company->country }}')" 
                                            class="w-full mt-3 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>Add to cart
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">No reports available for this company</p>
                        </div>
                    @endif

                    <div class="flex items-center text-green-600 mb-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="text-sm font-medium">Latest information from the government</span>
                    </div>

                    <div class="mt-6">
                        <h3 class="font-medium text-gray-900 mb-2">What's included in the report?</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Incorporation date</li>
                            <li>• Business activities</li>
                            <li>• Capital and share structure</li>
                            <li>• Directors and shareholders</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection