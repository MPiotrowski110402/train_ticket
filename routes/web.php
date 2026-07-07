<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/polaczenia', 'connections.index')->name('connections');

Route::view('/promocje', 'promotions.index')->name('promotions');

Route::view('/kontakt', 'contact.index')->name('contact');
