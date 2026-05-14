<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Client\CashoutController;
use App\Http\Controllers\Api\Client\CashoutDetailController;
use App\Http\Controllers\Api\Client\ContractController;
use App\Http\Controllers\Api\Client\DashboardController;
use App\Http\Controllers\Api\Client\EarningController;
use App\Http\Controllers\Api\Client\EarningPeriodController;
use App\Http\Controllers\Api\Client\ProfileController;
use App\Http\Controllers\Api\Client\StoredEarningController;
use App\Http\Controllers\Api\Client\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── Public Auth ────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Authenticated ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // ─── Client Routes ───────────────────────────────────────────────────────
    Route::prefix('client')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/contracts', [ContractController::class, 'index']);
        Route::get('/earnings', [EarningController::class, 'index']);

        // Earning periods
        Route::get('/earning-periods/pending', [EarningPeriodController::class, 'pending']);
        Route::get('/earning-periods/chart', [EarningPeriodController::class, 'chart']);
        Route::get('/earning-periods', [EarningPeriodController::class, 'index']);
        Route::get('/earning-periods/{earning_period}', [EarningPeriodController::class, 'show']);
        Route::post('/earning-periods/{earning_period}/request-cashout', [EarningPeriodController::class, 'requestCashout']);
        Route::post('/earning-periods/{earning_period}/request-store', [EarningPeriodController::class, 'requestStore']);

        // Transactions & history
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/cashouts', [CashoutController::class, 'index']);
        Route::get('/stored-earnings', [StoredEarningController::class, 'index']);

        // Cashout details management
        Route::get('/cashout-details', [CashoutDetailController::class, 'index']);
        Route::post('/cashout-details', [CashoutDetailController::class, 'store']);
        Route::put('/cashout-details/{cashout_detail}', [CashoutDetailController::class, 'update']);
        Route::delete('/cashout-details/{cashout_detail}', [CashoutDetailController::class, 'destroy']);
    });
});
