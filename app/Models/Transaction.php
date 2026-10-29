<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'warehouse_id', 'product_id', 'operation_type_id',
        'user_id', 'customer_id', 'doc_id',
        'amount', 'purchase_price', 'sale_price', 'sign',
        'is_confirmed', 'created_at', 'confirmed_at'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'created_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function operationType() { return $this->belongsTo(OperationType::class); }
    public function doc() { return $this->belongsTo(Doc::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
