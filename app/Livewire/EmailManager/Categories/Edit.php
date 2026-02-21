<?php

namespace App\Livewire\EmailManager\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        $this->slug = $category->slug ?? '';
        $this->notes = $category->notes;
    }

    public function save(): void
    {
        // Normalize inputs
        $this->name = $this->normalizeName($this->name);
        $this->slug = trim($this->slug);

        // Basic validation first
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'slug'  => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        // Case-insensitive duplicate name check (ignore current category)
        if ($this->categoryNameExistsForOther($this->name, (int) $this->category->id)) {
            throw ValidationException::withMessages([
                'name' => 'This category name already exists.',
            ]);
        }

        // Slug: keep if provided, otherwise regenerate from name
        $baseSlug = $this->slug !== '' ? Str::slug($this->slug) : Str::slug($this->name);

        // Ensure slug is unique (ignore current category)
        $finalSlug = $this->uniqueSlugForUpdate($baseSlug, (int) $this->category->id);

        $this->category->update([
            'name'  => $this->name,
            'slug'  => $finalSlug,
            'notes' => $this->notes,
        ]);

        // ✅ Use session flash so toast shows AFTER redirect
        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Category updated successfully.',
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

    private function categoryNameExistsForOther(string $name, int $ignoreId): bool
    {
        $lower = mb_strtolower($name);

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->where('id', '!=', $ignoreId)
            ->exists();
    }

    private function uniqueSlugForUpdate(string $baseSlug, int $ignoreId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $original = $slug;
        $i = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->where('id', '!=', $ignoreId)
                ->exists()
        ) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.email-manager.categories.edit')
            ->layout('layouts.app');
    }
}