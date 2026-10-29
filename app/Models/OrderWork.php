<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderWork extends Model
{
    protected $fillable = ['order_id', 'mechanic_id', 'name', 'hours', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
