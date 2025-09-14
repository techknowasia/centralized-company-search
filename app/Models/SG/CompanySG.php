<?php

namespace App\Models\SG;

use Illuminate\Database\Eloquent\Model;

class CompanySG extends Model
{
    protected $connection = 'companies_house_sg';
    protected $table = 'companies';
    
    protected $fillable = [
        'slug',
        'name',
        'former_names',
        'registration_number',
        'address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
