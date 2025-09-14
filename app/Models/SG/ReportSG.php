<?php

namespace App\Models\SG;

use Illuminate\Database\Eloquent\Model;

class ReportSG extends Model
{
    protected $connection = 'companies_house_sg';
    protected $table = 'reports';
    
    protected $fillable = [
        'name',
        'amount',
        'info',
        'is_active',
        'default',
        'order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'default' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
