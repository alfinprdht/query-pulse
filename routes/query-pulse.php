<?php

use Illuminate\Support\Facades\Route;
use Alfinprdht\QueryPulse\Controllers\DashboardController;
use Alfinprdht\QueryPulse\Controllers\AssetController;
use Alfinprdht\QueryPulse\Middleware\QueryPulseDashboardMiddleware;

Route::middleware(['web', QueryPulseDashboardMiddleware::class])
    ->prefix('query-pulse')
    ->name('query-pulse.')
    ->group(function () {
        Route::get('/assets/{path}', AssetController::class)
            ->name('assets')
            ->where('path', '.*');

        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/report/{reportId}', [DashboardController::class, 'report'])
            ->name('report')
            ->where('reportId', '[0-9a-fA-F]{32}');
        Route::post('/delete/{reportId}', [DashboardController::class, 'delete'])
            ->name('delete')
            ->where('reportId', '[0-9a-fA-F]{32}');
    });
