<?php

namespace App\Models\MX;

use Illuminate\Database\Eloquent\Model;

class CompanyMX extends Model
{
    protected $connection = 'companies_house_mx';
    protected $table = 'companies';
    
    protected $fillable = [
        'state_id',
        'slug',
        'name',
        'brand_name',
        'address'
    ];

    protected $casts = [
        'state_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function state()
    {
        return $this->belongsTo(StateMX::class, 'state_id');
    }

    public function reportStates()
    {
        return $this->hasManyThrough(
            ReportStateMX::class,
            StateMX::class,
            'id', // Foreign key on states table
            'state_id', // Foreign key on report_state table
            'state_id', // Local key on companies table
            'id' // Local key on states table
        );
    }
}
