<?php

use Illuminate\Support\Facades\Route;

// --------------------------------------
// Dashboard-only app
// --------------------------------------

// keep name "home" because auth layout uses it
Route::get('/', function () {
    return redirect()->route('email-manager.dashboard');
})->name('home');

// keep name "dashboard" because starter kit uses it
Route::get('/dashboard', function () {
    return redirect()->route('email-manager.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// --------------------------------------
// Other route files
// --------------------------------------
require __DIR__ . '/queue-trigger.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/email-manager.php';