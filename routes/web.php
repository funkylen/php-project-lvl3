<?php

use App\Http\Controllers\UrlCheckController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::view('/', 'welcome')->name('home');

Route::resource('/urls', UrlController::class)->only('index', 'store', 'show');
Route::resource('/urls/{url}/checks', UrlCheckController::class)->only('store');
