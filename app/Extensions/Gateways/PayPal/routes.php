<?php

use App\Extensions\Gateways\PayPal\PayPal;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::post('/capture', [PayPal::class, 'capture'])->withoutMiddleware([VerifyCsrfToken::class])->name('capture');