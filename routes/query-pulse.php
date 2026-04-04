<?php

use Illuminate\Support\Facades\Route;
use Alfinprdht\QueryPulse\Controllers\DashboardController;

Route::middleware(['web', 'auth'])
    ->prefix('query-pulse')
    ->name('query-pulse.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/report/{reportId}', [DashboardController::class, 'report'])
            ->name('report')
            ->where('reportId', '[0-9a-fA-F]{32}');
        Route::post('/delete/{reportId}', [DashboardController::class, 'delete'])
            ->name('delete')
            ->where('reportId', '[0-9a-fA-F]{32}');
    });
