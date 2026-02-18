<?php

namespace App\Livewire\EmailManager\Exports;

use App\Models\Export;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Download extends Component
{
    public Export $export;

    public function mount(Export $export): void
    {
        $this->export = $export;

        $file = $export->file;

        abort_unless($export->status === 'completed' && $file, 404);

        // Force download response
        $this->redirect(
            Storage::disk($file->disk)->url($file->path),
            navigate: false
        );
    }

    public function render()
    {
        return view('livewire.email-manager.exports.download')->layout('layouts.app');
    }
}