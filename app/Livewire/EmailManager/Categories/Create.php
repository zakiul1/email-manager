<?php

namespace App\Livewire\EmailManager\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $slug = '';
    public ?string $notes = null;

    public function save(): void
    {
        // Normalize inputs (trim, collapse spaces)
        $this->name = $this->normalizeName($this->name);
        $this->slug = trim($this->slug);

        // Basic validation first
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'slug'  => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        // Case-insensitive duplicate name check
        if ($this->categoryNameExists($this->name)) {
            throw ValidationException::withMessages([
                'name' => 'This category name already exists.',
            ]);
        }

        // Build slug: user-provided OR from name
        $baseSlug  = $this->slug !== '' ? Str::slug($this->slug) : Str::slug($this->name);
        $finalSlug = $this->uniqueSlug($baseSlug);

        Category::create([
            'name'  => $this->name,
            'slug'  => $finalSlug,
            'notes' => $this->notes,
        ]);

        // ✅ Use session flash so toast shows AFTER redirect (no millisecond flash)
        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Category created successfully.',
            'timeout' => 5000,
        ]);

        $this->redirect(route('email-manager.categories'), navigate: true);
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    private function categoryNameExists(string $name): bool
    {
        $lower = mb_strtolower($name);

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->exists();
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $original = $slug;
        $i = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.email-manager.categories.create')
            ->layout('layouts.app');
    }
}