<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::resource('workshops', WorkshopController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('admin');

Route::post('/workshops/{workshop}/registrations', [RegistrationController::class, 'store'])
    ->middleware('auth')
    ->name('workshops.registrations.store');

Route::get('/admin/statistics', [StatisticsController::class, 'index'])
    ->middleware('admin')
    ->name('admin.statistics');
