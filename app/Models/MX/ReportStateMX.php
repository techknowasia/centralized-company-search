<?php

namespace App\Models\MX;

use Illuminate\Database\Eloquent\Model;

class ReportStateMX extends Model
{
    protected $connection = 'companies_house_mx';
    protected $table = 'report_state';
    
    protected $fillable = [
        'report_id',
        'state_id',
        'amount'
    ];

    protected $casts = [
        'report_id' => 'integer',
        'state_id' => 'integer',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(ReportMX::class, 'report_id');
    }

    public function state()
    {
        return $this->belongsTo(StateMX::class, 'state_id');
    }
}
