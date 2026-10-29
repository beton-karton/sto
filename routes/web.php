<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders', function () {
    return view('welcome'); // Временно, чтобы просто проверить, что роут работает
});
