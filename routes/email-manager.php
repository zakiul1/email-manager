<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\Export;

use App\Livewire\EmailManager\Dashboard\Index as DashboardIndex;

use App\Livewire\EmailManager\Categories\Index as CategoriesIndex;
use App\Livewire\EmailManager\Categories\Create as CategoriesCreate;
use App\Livewire\EmailManager\Categories\Edit as CategoriesEdit;

use App\Livewire\EmailManager\Imports\Upload;


use App\Livewire\EmailManager\Suppression\GlobalList;
use App\Livewire\EmailManager\Suppression\DomainList;

use App\Livewire\EmailManager\Emails\Index as EmailsIndex;

use App\Livewire\EmailManager\Exports\Create as ExportCreate;
use App\Livewire\EmailManager\Exports\Index as ExportIndex;

Route::middleware(['auth', 'verified'])->group(function () {

    // -------------------------
    // Dashboard (Phase 6)
    // -------------------------
    Route::get('email-manager/dashboard', DashboardIndex::class)
        ->name('email-manager.dashboard');


    // -------------------------
    // Categories
    // -------------------------
    Route::get('email-manager/categories', CategoriesIndex::class)
        ->name('email-manager.categories');

    Route::get('email-manager/categories/create', CategoriesCreate::class)
        ->name('email-manager.categories.create');

    Route::get('email-manager/categories/{category}/edit', CategoriesEdit::class)
        ->name('email-manager.categories.edit');


    // -------------------------
    // Imports
    // -------------------------
    Route::get('email-manager/imports/upload', Upload::class)
        ->name('email-manager.imports.upload');




    // -------------------------
    // Suppression / Unsubscribe
    // -------------------------
    Route::get('email-manager/suppressions', GlobalList::class)
        ->name('email-manager.suppressions');

    Route::get('email-manager/domain-unsubscribes', DomainList::class)
        ->name('email-manager.domain-unsubscribes');


    // -------------------------
    // Emails (Phase 4)
    // -------------------------
    Route::get('email-manager/emails', EmailsIndex::class)
        ->name('email-manager.emails');


    // -------------------------
    // Exports (Phase 5)
    // -------------------------
    Route::get('email-manager/exports', ExportIndex::class)
        ->name('email-manager.exports');

    Route::get('email-manager/exports/create', ExportCreate::class)
        ->name('email-manager.exports.create');

    // ✅ Secure download (private storage/app files)
    Route::get('email-manager/exports/{export}/download-file', function (Export $export) {
        abort_unless($export->user_id === auth()->id(), 403);

        $file = $export->file;
        abort_unless($export->status === 'completed' && $file, 404);

        return Storage::disk($file->disk)->download($file->path, $file->filename);
    })->name('email-manager.exports.download-file');
});