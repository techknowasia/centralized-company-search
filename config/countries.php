<?php

return [
    'sg' => [
        'name' => 'Singapore',
        'flag' => '🇸🇬',
        'connection' => 'companies_house_sg',
        'currency' => 'SGD',
        'timezone' => 'Asia/Singapore',
        'repository' => \App\Repositories\SG\CompanyRepositorySG::class,
        'models' => [
            'company' => \App\Models\SG\CompanySG::class,
            'report' => \App\Models\SG\ReportSG::class,
        ],
        'schema' => [
            'has_states' => false,
            'reports_direct' => true,
            'pricing_table' => 'reports', // Direct pricing from reports table
        ]
    ],
    
    'mx' => [
        'name' => 'Mexico',
        'flag' => '🇲🇽',
        'connection' => 'companies_house_mx',
        'currency' => 'MXN',
        'timezone' => 'America/Mexico_City',
        'repository' => \App\Repositories\MX\CompanyRepositoryMX::class,
        'models' => [
            'company' => \App\Models\MX\CompanyMX::class,
            'report' => \App\Models\MX\ReportMX::class,
            'state' => \App\Models\MX\StateMX::class,
        ],
        'schema' => [
            'has_states' => true,
            'reports_direct' => false,
            'pricing_table' => 'report_state', // Pricing from report_state table
        ]
    ],
    
    // Future countries can be easily added:
];
