<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['part_brand_id', 'oem_number', 'article', 'name', 'sale_price', 'average_price'];

    public function partBrand()
    {
        return $this->belongsTo(PartBrand::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
