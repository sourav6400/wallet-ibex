<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LockController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::fallback(function () {
//     return redirect('/');
// });

Route::get('/test-mail-host', function () {
    $hosts = [
        'mail.styxwallet.com',
        'styxwallet.com',
        'smtp.styxwallet.com',
        'server.styxwallet.com'
    ];
    
    echo "<h3>Testing Mail Host Resolution:</h3>";
    
    foreach ($hosts as $host) {
        $ip = gethostbyname($host);
        if ($ip !== $host) {
            echo "<p style='color: green;'>✅ {$host} resolves to: {$ip}</p>";
        } else {
            echo "<p style='color: red;'>❌ {$host} does not resolve</p>";
        }
    }
    
    // Also test MX records
    echo "<h3>MX Records for styxwallet.com:</h3>";
    if (getmxrr('styxwallet.com', $mxhosts)) {
        foreach ($mxhosts as $mx) {
            echo "<p>MX: {$mx}</p>";
        }
    } else {
        echo "<p>No MX records found</p>";
    }
});

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return 'Cache and config cleared successfully!';
});

Route::get('/test', [WalletController::class, 'test']);
Route::get('/create-wallet-env', [WalletController::class, 'create_wallet_env']);

Route::middleware(['guest'])->group(function () {
    Route::get('/', [UserController::class, 'onboarding1'])->name('onboarding1');
    Route::get('/home', [UserController::class, 'onboarding1'])->name('login');
    Route::get('/onboarding-1', [UserController::class, 'onboarding1'])->name('onboarding1');
    Route::get('/onboarding-2', [UserController::class, 'onboarding2'])->name('onboarding2');
    Route::get('/onboarding-3', [UserController::class, 'onboarding3'])->name('onboarding3');

    Route::get('/wallet-create-or-restore', [WalletController::class, 'index'])->name('wallet.selection');
    Route::get('/wallet-restore', [WalletController::class, 'restore'])->name('wallet.restore');
    Route::post('/wallet-restore', [WalletController::class, 'restorePost'])->name('wallet.restorePost');
    Route::get('/create-new-wallet', [WalletController::class, 'create'])->name('wallet.new');
    Route::get('/wallet-pin-set', [WalletController::class, 'wallet_pin_set'])->name('wallet.pin');
    Route::post('/wallet-pin-confirm', [WalletController::class, 'wallet_pin_confirm'])->name('wallet.pin_confirm');
    Route::post('/word-seed-phrase', [WalletController::class, 'word_seed_phrase'])->name('word.seed_phrase');
    Route::post('/download-seed-phrase', [WalletController::class, 'download_seed_phrase'])->name('phrase.download');
    Route::post('/create-wallet', [WalletController::class, 'store'])->name('wallet.create');
});

Route::middleware('auth', 'check.user.status')->group(function () {
    Route::post('/session/lock', [LockController::class, 'lock'])->name('lock.store');
    Route::get('/lock', [LockController::class, 'show'])->name('lock.show');
    Route::post('/unlock', [LockController::class, 'unlock'])->name('lock.unlock');
    
    Route::get('/forward-to-restore-wallet', [WalletController::class, 'forward_to_restore_wallet'])->name('wallet.forward_to_restore_wallet');
    Route::get('/forward-to-create-wallet', [WalletController::class, 'forward_to_create_wallet'])->name('wallet.forward_to_create_wallet');
});

Route::middleware(['auth', 'check.user.status', 'never.logout', 'pin.lock'])->group(function () {
    Route::get('/dashboard', [WalletController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');

    Route::get('/my-wallet', [WalletController::class, 'my_wallet'])->name('wallet.landing');
    Route::get('/my-wallet/{symbol}', [WalletController::class, 'my_wallet'])->name('wallet.by_token');
    Route::get('/send/{symbol}', [WalletController::class, 'send_view'])->name('wallet.send_token_s1');
    Route::post('/send-token/response', [WalletController::class, 'send_token'])->name('wallet.send_token');
    Route::get('/receive/{symbol}', [WalletController::class, 'receive_token'])->name('wallet.receive_token');

    Route::get('/backup-seed', [SettingsController::class, 'backup_seed'])->name('settings.backup_seed');
    Route::get('/change-pin', [SettingsController::class, 'change_pin_view'])->name('settings.change_pin_view');
    Route::post('/update-pin', [SettingsController::class, 'store_new_pin'])->name('settings.store_new_pin');
    Route::post('/check-pin', [SettingsController::class, 'checkPin'])->name('settings.check_pin');
    Route::get('/faq', [SettingsController::class, 'faq'])->name('settings.faq');
    Route::get('/terms-conditions', [SettingsController::class, 'terms_conditions'])->name('settings.terms_conditions');
    Route::get('/support', [SettingsController::class, 'support'])->name('support');
    Route::post('/support-email', [UserController::class, 'send_support_mail'])->name('send_support_mail');
    
    Route::post('/logout', [WalletController::class, 'logout'])->name('logout');
});
