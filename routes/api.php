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
use App\Http\Controllers\Api\Client\TradingCashoutController;
use App\Http\Controllers\Api\Client\TradingContractController;
use App\Http\Controllers\Api\Client\TradingEarningController;
use App\Http\Controllers\Api\Client\TradingPeriodController;
use App\Http\Controllers\Api\Client\TradingStoredEarningController;
use App\Http\Controllers\Api\Client\TransactionController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ─── Public Auth ────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Public Content ──────────────────────────────────────────────────────────
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::post('/contact', [ContactController::class, 'store']);

// ─── Signed PDF Report Links (public — signed URL is the credential) ─────────
Route::get('/reports/earnings', [ReportController::class, 'allPeriods'])
    ->name('reports.earnings.all');
Route::get('/reports/earnings/{earning_period}', [ReportController::class, 'singlePeriod'])
    ->name('reports.earnings.single');

// ─── Authenticated ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // ─── Client Routes ───────────────────────────────────────────────────────
    Route::prefix('client')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/contracts', [ContractController::class, 'index']);
        Route::get('/earnings', [EarningController::class, 'index']);

        // Earning periods
        Route::get('/earning-periods/pending', [EarningPeriodController::class, 'pending']);
        Route::get('/earning-periods/chart', [EarningPeriodController::class, 'chart']);
        Route::get('/earning-periods/report', [EarningPeriodController::class, 'report']);
        Route::get('/earning-periods', [EarningPeriodController::class, 'index']);
        Route::get('/earning-periods/{earning_period}', [EarningPeriodController::class, 'show']);
        Route::get('/earning-periods/{earning_period}/chart', [EarningPeriodController::class, 'periodChart']);
        Route::get('/earning-periods/{earning_period}/report', [EarningPeriodController::class, 'reportSingle']);
        Route::post('/earning-periods/{earning_period}/request-cashout', [EarningPeriodController::class, 'requestCashout']);
        Route::post('/earning-periods/{earning_period}/request-store', [EarningPeriodController::class, 'requestStore']);

        // Transactions & history
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/cashouts', [CashoutController::class, 'index']);
        Route::get('/stored-earnings', [StoredEarningController::class, 'index']);

        // Trading module (separate from mining)
        Route::get('/trading-contracts', [TradingContractController::class, 'index']);
        Route::get('/trading-contracts/{trading_contract}', [TradingContractController::class, 'show']);
        Route::get('/trading-earnings', [TradingEarningController::class, 'index']);
        Route::get('/trading-periods/pending', [TradingPeriodController::class, 'pending']);
        Route::get('/trading-periods/chart', [TradingPeriodController::class, 'chart']);
        Route::get('/trading-periods', [TradingPeriodController::class, 'index']);
        Route::get('/trading-periods/{trading_period}', [TradingPeriodController::class, 'show']);
        Route::post('/trading-periods/{trading_period}/request-cashout', [TradingPeriodController::class, 'requestCashout']);
        Route::post('/trading-periods/{trading_period}/request-store', [TradingPeriodController::class, 'requestStore']);
        Route::get('/trading-cashouts', [TradingCashoutController::class, 'index']);
        Route::get('/trading-stored-earnings', [TradingStoredEarningController::class, 'index']);
        Route::get('/trading-stored-balance', [TradingStoredEarningController::class, 'balance']);

        // Cashout details management
        Route::get('/cashout-details', [CashoutDetailController::class, 'index']);
        Route::post('/cashout-details', [CashoutDetailController::class, 'store']);
        Route::put('/cashout-details/{cashout_detail}', [CashoutDetailController::class, 'update']);
        Route::delete('/cashout-details/{cashout_detail}', [CashoutDetailController::class, 'destroy']);
    });
});
