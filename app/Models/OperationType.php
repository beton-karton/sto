<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationType extends Model
{
    protected $fillable = ['name', 'direction', 'sign', 'is_active'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
