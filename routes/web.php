<?php

use Illuminate\Support\Facades\Route;

// keep name "home" because auth layout uses it
Route::get('/', fn() => redirect()->route('dashboard'))->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/email-manager.php';