<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImport extends Model
{
    protected $table = 'employee_imports';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'file_name',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'import_id', 'id');
    }
}
