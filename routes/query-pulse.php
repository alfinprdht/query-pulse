<?php

use Illuminate\Support\Facades\Route;
use Alfinprdht\QueryPulse\Controllers\DashboardController;

Route::middleware(['web'])
    ->prefix('query-pulse')
    ->name('query-pulse.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/report/{reportId}', [DashboardController::class, 'report'])->name('report')->where('any', '.*');
    });
