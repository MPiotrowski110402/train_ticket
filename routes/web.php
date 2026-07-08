<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\SeatSelectionController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')
    ->name('home');

Route::get('/polaczenia', [ConnectionController::class, 'index'])
    ->name('connections');

Route::post('/polaczenia/{trip}/miejsca', [SeatSelectionController::class, 'store'])
    ->name('connections.seats.store');

Route::get('/rezerwacja/{trip}', [CheckoutController::class, 'show'])
    ->name('checkout.show');

Route::post('/rezerwacja/{trip}', [CheckoutController::class, 'store'])
    ->name('checkout.store');
    
Route::view('/qr/zatrudnij-mnie', 'qr.hire-me')
    ->name('qr.hire-me');

Route::get('/potwierdzenie', [CheckoutController::class, 'success'])
    ->name('checkout.success');

Route::view('/promocje', 'promotions.index')
    ->name('promotions');

Route::view('/kontakt', 'contact.index')
    ->name('contact');