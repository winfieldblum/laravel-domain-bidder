<?php

use App\Http\Controllers\BidVerificationController;
use App\Http\Controllers\DomainHomeController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\VerifiedOfferController;
use Illuminate\Support\Facades\Route;

Route::get('/', DomainHomeController::class)->name('home');

Route::get('/offer', [OfferController::class, 'create'])->name('offer.create');
Route::post('/offer', [OfferController::class, 'store'])->name('offer.store');

Route::get('/offer/verified/{token}', [VerifiedOfferController::class, 'create'])
    ->where('token', '[A-Za-z0-9]+')
    ->name('offer.verified.create');
Route::post('/offer/verified/{token}', [VerifiedOfferController::class, 'store'])
    ->where('token', '[A-Za-z0-9]+')
    ->name('offer.verified.store');

Route::get('/verify/{token}', BidVerificationController::class)
    ->where('token', '[A-Za-z0-9]+')
    ->name('bids.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin')->name('dashboard');
});

require __DIR__.'/settings.php';
