<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\EmailManager\Categories\Index;
use App\Livewire\EmailManager\Categories\Create;
use App\Livewire\EmailManager\Categories\Edit;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('email-manager/categories', Index::class)->name('email-manager.categories');
    Route::get('email-manager/categories/create', Create::class)->name('email-manager.categories.create');
    Route::get('email-manager/categories/{category}/edit', Edit::class)->name('email-manager.categories.edit');
});