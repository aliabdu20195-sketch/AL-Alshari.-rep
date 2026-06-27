<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app'     => 'Al-Ashari ERP',
        'version' => '1.0.0',
        'status'  => 'running',
    ]);
});
