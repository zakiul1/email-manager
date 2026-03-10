<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SendPortal\PublicTrackingController;
use App\Http\Controllers\SendPortal\PublicUnsubscribeController;
use App\Http\Controllers\SendPortal\PublicWebhookController;

// --------------------------------------
// Dashboard-only app
// --------------------------------------

// keep name "home" because auth layout uses it
Route::get('/', function () {
    return redirect()->route('sendportal.workspace.dashboard');
})->name('home');

// keep name "dashboard" because starter kit uses it
Route::get('/dashboard', function () {
    return redirect()->route('sendportal.workspace.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --------------------------------------
// SendPortal public tracking routes
// --------------------------------------
Route::prefix('sp/public')->name('sendportal.public.')->group(function () {
    Route::get('/open/{token}', [PublicTrackingController::class, 'open'])->name('track.open');
    Route::get('/click/{token}', [PublicTrackingController::class, 'click'])->name('track.click');
    Route::get('/unsubscribe/{token}', PublicUnsubscribeController::class)->name('unsubscribe');
    Route::post('/webhook', PublicWebhookController::class)->name('webhook');
});

// --------------------------------------
// Other route files
// --------------------------------------
require __DIR__ . '/queue-trigger.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/email-manager.php';
require __DIR__ . '/sendportal.php';