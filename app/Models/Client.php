<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'salutation',
        'name',
        'email',
        'profile_pic',
        'phone_number',
        'alternate_contact_number',
        'address',
        'gps_location',
        'dealership_id',
        'employee_id',
        'agent_type',
        'agent_id',
        'lead_source_id',
        'lead_category_id',
        'notes',
        'lead_id',
        'latitude',
        'longitude',
        'state_id',
        'district_id',
        'import_id',
    ];

    public function agent()
    {
        return $this->morphTo();
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function products()
    {
        return $this->hasMany(ClientProduct::class);
    }
}
