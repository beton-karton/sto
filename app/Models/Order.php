<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'vehicle_id', 'master_id',
        'status', 'reason', 'mileage', 'license_plate',
        'entered_at', 'exited_at'
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function works()
    {
        return $this->hasMany(OrderWork::class);
    }

    public function docs()
    {
        return $this->hasMany(Doc::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Doc::class);
    }
}
