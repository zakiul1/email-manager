<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::middleware(['auth', 'verified'])->group(function () {

    // ✅ Optional manual queue runner (secure)
    Route::get('email-manager/queue/run', function () {

        // simple protection: set QUEUE_TRIGGER_TOKEN in .env
        $token = request('token');
        abort_unless(
            $token && hash_equals((string) config('app.queue_trigger_token'), (string) $token),
            403
        );

        // run a few jobs quickly
        $runs = (int) request('runs', 3);
        $runs = max(1, min($runs, 10)); // 1..10

        for ($i = 0; $i < $runs; $i++) {
            Artisan::call('queue:work', [
                '--once' => true,
                '--sleep' => 0,
                '--tries' => 1,
                '--timeout' => 180,
            ]);
        }

        return back()->with('status', "Queue executed {$runs} time(s).");
    })->name('queue.run');

});