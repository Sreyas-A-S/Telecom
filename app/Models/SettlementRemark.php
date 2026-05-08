<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementRemark extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_id',
        'department',
        'remark',
        'signature',
        'is_filled',
        'manager_id',
        'file_path',
    ];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
