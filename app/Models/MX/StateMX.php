<?php

namespace App\Models\MX;

use Illuminate\Database\Eloquent\Model;

class StateMX extends Model
{
    protected $connection = 'companies_house_mx';
    protected $table = 'states';
    
    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function companies()
    {
        return $this->hasMany(CompanyMX::class, 'state_id');
    }

    public function reportStates()
    {
        return $this->hasMany(ReportStateMX::class, 'state_id');
    }
}
