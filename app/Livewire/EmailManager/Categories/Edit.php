<?php

namespace App\Livewire\EmailManager\Categories;

use App\Models\Category;
use Livewire\Component;

class Edit extends Component
{
    public Category $category;

    public string $name = '';
    public string $slug = '';
    public ?string $notes = null;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->notes = $category->notes;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $this->category->id,
            'slug' => 'required|string|max:255|unique:categories,slug,' . $this->category->id,
            'notes' => 'nullable|string',
        ]);

        $this->category->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'notes' => $this->notes,
        ]);

        $this->redirect(route('email-manager.categories'), navigate: true);
    }

    public function render()
    {
        return view('livewire.email-manager.categories.edit')
            ->layout('layouts.app');
    }
}