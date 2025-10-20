<?php

use Illuminate\Support\Facades\Route;
use Obrainwave\Paygate\Http\Controllers\Api\PaymentController;
use Obrainwave\Paygate\Http\Controllers\WebhookController;
use Obrainwave\Paygate\Http\Middleware\PaymentMiddleware;
use Obrainwave\Paygate\Http\Middleware\VerifyPaymentMiddleware;

Route::group(['prefix' => 'paygate', 'as' => 'paygate.api.', 'middleware' => ['api', PaymentMiddleware::class]], function () {
    // Payment initiation
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('initiate');
    
    // Payment verification
    Route::get('/verify/{reference}', [PaymentController::class, 'verify'])->name('verify');
    
    // Payment refund
    Route::post('/refund', [PaymentController::class, 'refund'])->name('refund');
    
    // Payment status
    Route::get('/status/{reference}', [PaymentController::class, 'status'])->name('status');
    
    // Payment history
    Route::get('/history', [PaymentController::class, 'history'])->name('history');
    
    // Available gateways
    Route::get('/gateways', [PaymentController::class, 'gateways'])->name('gateways');
    
    // Webhook endpoint
    Route::post('/webhook', [WebhookController::class, 'handle'])->name('webhook');
    
    // Protected routes that require successful payment
    Route::group(['middleware' => [VerifyPaymentMiddleware::class]], function () {
        Route::get('/success/{reference}', [PaymentController::class, 'success'])->name('success');
    });
});
