<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::view('/', 'welcome')->name('home');

Route::resource('/urls', UrlController::class)->only('index', 'store', 'show');

Route::post('/urls/{id}/check', [UrlController::class, 'check'])->name('urls.check');
