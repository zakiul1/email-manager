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

// Exports (Direct)
use App\Livewire\EmailManager\Exports\Create as ExportCreate;

use App\Http\Controllers\EmailManager\CategoryDownloadController;
use App\Http\Controllers\EmailManager\DirectExportDownloadController;

// ✅ DB Backup (Direct Download)
use App\Http\Controllers\EmailManager\DbBackupDownloadController;

Route::middleware(['auth', 'verified'])->group(function () {

    // -------------------------
    // Dashboard
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

    // ✅ Category-wise download
    Route::get('email-manager/categories/{category}/download', [CategoryDownloadController::class, 'download'])
        ->name('email-manager.categories.download');

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
    // Emails
    // -------------------------
    Route::get('email-manager/emails', EmailsIndex::class)
        ->name('email-manager.emails');

    // -------------------------
    // Exports (DIRECT)
    // -------------------------

    /**
     * ✅ Sidebar "Exports" should open the export form directly
     * So we point /exports to the form component.
     */
    Route::get('email-manager/exports', ExportCreate::class)
        ->name('email-manager.exports');

    /**
     * ✅ Direct download endpoint (no create-export record)
     * Livewire form will redirect user to this URL with query params.
     */
    Route::get('email-manager/exports/download', [DirectExportDownloadController::class, 'download'])
        ->name('email-manager.exports.download');

    /**
     * (Optional) Keep old Create route to avoid breaking old links.
     * You can remove later after updating sidebar links.
     */
    Route::get('email-manager/exports/create', ExportCreate::class)
        ->name('email-manager.exports.create');

    /**
     * (Optional) Keep old secure download for stored exports
     * If you no longer use Export records, you can delete this route later.
     */
    Route::get('email-manager/exports/{export}/download-file', function (Export $export) {
        abort_unless($export->user_id === auth()->id(), 403);

        $file = $export->file;
        abort_unless($export->status === 'completed' && $file, 404);

        return Storage::disk($file->disk)->download($file->path, $file->filename);
    })->name('email-manager.exports.download-file');

    // -------------------------
    // ✅ Database Backup (DIRECT DOWNLOAD)
    // -------------------------

    /**
     * Shows a simple page with "Download ZIP Backup" button
     */
    Route::get('email-manager/db-backup', [DbBackupDownloadController::class, 'index'])
        ->name('email-manager.db-backup.index');

    /**
     * Generates SQL dump + ZIP and downloads immediately (no storing as records)
     */
    Route::post('email-manager/db-backup/download', [DbBackupDownloadController::class, 'download'])
        ->name('email-manager.db-backup.download');
});