<?php

use App\Http\Controllers\ConnectionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')
    ->name('home');

Route::get('/polaczenia', [ConnectionController::class, 'index'])
    ->name('connections');

Route::view('/promocje', 'promotions.index')
    ->name('promotions');

Route::view('/kontakt', 'contact.index')
    ->name('contact');