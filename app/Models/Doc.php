<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'number',
        'type',
        'user_id',
        'warehouse_id', // <--- Добавили
        'customer_id',  // <--- Добавили
        'order_id',
        'notes',        // <--- Добавили
        'is_confirmed',
        'created_at',
        'confirmed_at'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'created_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
