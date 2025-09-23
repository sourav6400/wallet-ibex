<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Wallet creation and restoration
    Route::get('/wallet/generate-phrase', [WalletApiController::class, 'getPhrase']);
    Route::post('/wallet/create', [WalletApiController::class, 'createWallet']);
    Route::post('/wallet/restore', [WalletApiController::class, 'restoreWallet']);
    
    // Authentication
    Route::post('/login', [WalletApiController::class, 'login']);
});

// Protected API routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/profile', [WalletApiController::class, 'getProfile']);
    Route::post('/logout', [WalletApiController::class, 'logout']);
    
    // Dashboard
    Route::get('/dashboard', [WalletApiController::class, 'dashboard']);
    
    // Wallet operations
    Route::get('/wallet/{symbol?}', [WalletApiController::class, 'getWallet']);
    Route::get('/wallet/{symbol}/send-data', [WalletApiController::class, 'getSendTokenData']);
    Route::post('/wallet/send', [WalletApiController::class, 'sendToken']);
    Route::get('/wallet/{symbol}/receive-data', [WalletApiController::class, 'getReceiveTokenData']);
    
    // Transactions
    Route::get('/transactions/{symbol?}', [WalletApiController::class, 'getTransactionsApi']);
    
    // Security
    Route::post('/pin/update', [WalletApiController::class, 'updatePin']);
    Route::post('/pin/verify', [WalletApiController::class, 'verifyPin']);
    
    // Backup
    Route::get('/backup/seed', [WalletApiController::class, 'getBackupSeed']);
});