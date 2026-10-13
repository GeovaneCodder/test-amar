<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Imports\ImportController;
use App\Http\Controllers\Transactions\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', LogoutController::class);
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/dashboard', DashboardController::class);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/imports', [ImportController::class, 'store']);
    Route::get('/imports/{import}', [ImportController::class, 'show']);
    Route::post('/imports/{import}/pause', [ImportController::class, 'pause']);
    Route::post('/imports/{import}/resume', [ImportController::class, 'resume']);
    Route::post('/imports/{import}/cancel', [ImportController::class, 'cancel']);
});
