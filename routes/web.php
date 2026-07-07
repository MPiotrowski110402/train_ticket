<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/polaczenia', function () {

    return view('connections.index');

})->name('connections');