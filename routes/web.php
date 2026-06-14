<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'KidneyVision AI API',
        'status' => 'running',
        'version' => '1.0.0',
    ]);
});
