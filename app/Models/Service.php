<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'client_id', 'product_id', 'product_model_id', 'is_service', 'requested_location', 'referral_id', 'machine_status', 'type_of_service', 'service_interval', 'contact_info', 'service_engineer_id', 'service_engineer_id_2', 'model_series_id', 'dealership_id', 'zone_id', 'latitude', 'longitude', 'price', 'import_id', 'due_date_1', 'due_date_2', 'contact_person', 'doc', 'engine_model', 'engine_serial_number', 'failure_date', 'failure_hmr', 'employee_id', 'call_status', 'call_remarks', 'assigned_at'];

    protected $casts = [
        'due_date_1' => 'date:Y-m-d',
        'due_date_2' => 'date:Y-m-d',
        'doc' => 'date:Y-m-d',
        'failure_date' => 'date:Y-m-d',
        'assigned_at' => 'datetime',
    ];

    protected $appends = ['status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function modelSeries()
    {
        return $this->belongsTo(ModelSeries::class);
    }

    public function serviceEngineer()
    {
        return $this->belongsTo(Employee::class, 'service_engineer_id');
    }

    public function serviceEngineer2()
    {
        return $this->belongsTo(Employee::class, 'service_engineer_id_2');
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'entry_id')->where('entry_type', Service::class);
    }

    public function getStatusAttribute()
    {
        $tasks = $this->tasks;

        if ($tasks->isEmpty()) {
            return 'open';
        }

        $allCompleted = $tasks->every(function ($task) {
            return $task->status === 'completed';
        });

        return $allCompleted ? 'closed' : 'open';
    }
}
