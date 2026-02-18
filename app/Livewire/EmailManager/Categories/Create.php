<?php

namespace App\Livewire\EmailManager\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $slug = '';
    public ?string $notes = null;

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'notes' => 'nullable|string',
        ]);

        $slug = $this->slug !== '' ? $this->slug : Str::slug($this->name);

        Category::create([
            'name' => $this->name,
            'slug' => $slug,
            'notes' => $this->notes,
        ]);

        $this->redirect(route('email-manager.categories'), navigate: true);
    }

    public function render()
    {
        return view('livewire.email-manager.categories.create')
            ->layout('layouts.app');
    }
}