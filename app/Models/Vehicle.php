<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['model_id', 'year', 'color', 'vin', 'description'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getDisplayNameAttribute()
    {
        // Если model_id есть, ищем его в центральной базе
        if ($this->model_id) {
            $model = \Illuminate\Support\Facades\DB::connection('central')
                ->table('models')
                ->where('id', $this->model_id)
                ->first();

            if ($model) {
                $brand = \Illuminate\Support\Facades\DB::connection('central')
                    ->table('brands')
                    ->where('id', $model->brand_id)
                    ->first();

                return ($brand->name ?? $model->brand_id) . ' ' . $model->name;
            }
        }
        return $this->model_id ?? '—';
    }

}
