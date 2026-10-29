<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
