<?php

declare(strict_types=1);

use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', fn () => 'ok')->middleware('auth');

Route::resource('workshops', WorkshopController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('admin');
