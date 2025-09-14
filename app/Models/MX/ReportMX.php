<?php

namespace App\Models\MX;

use Illuminate\Database\Eloquent\Model;

class ReportMX extends Model
{
    protected $connection = 'companies_house_mx';
    protected $table = 'reports';
    
    protected $fillable = [
        'name',
        'info',
        'order',
        'default',
        'status'
    ];

    protected $casts = [
        'order' => 'integer',
        'default' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reportStates()
    {
        return $this->hasMany(ReportStateMX::class, 'report_id');
    }
}
