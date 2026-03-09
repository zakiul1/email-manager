<?php

namespace App\Services\SendPortal;

class TemplatePlaceholderService
{
    public function supported(): array
    {
        return [
            'recipient_email' => 'Recipient email address',
            'category_name' => 'Primary category name',
            'unsubscribe_url' => 'Signed unsubscribe URL',
            'webview_url' => 'Signed webview URL',
            'campaign_name' => 'Campaign name',
            'app_name' => 'Application name',
            'first_name' => 'Future-ready recipient first name',
        ];
    }

    public function extract(string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $value) => trim($value))
            ->unique()
            ->values()
            ->all();
    }

    public function unsupported(array $placeholders): array
    {
        $supported = array_keys($this->supported());

        return collect($placeholders)
            ->reject(fn (string $placeholder) => in_array($placeholder, $supported, true))
            ->values()
            ->all();
    }

    public function validateContent(string $html, ?string $text = null): array
    {
        $placeholders = array_values(array_unique(array_merge(
            $this->extract($html),
            $this->extract((string) $text)
        )));

        return [
            'placeholders' => $placeholders,
            'unsupported' => $this->unsupported($placeholders),
        ];
    }

    public function sampleValues(): array
    {
        return [
            'recipient_email' => 'john@example.com',
            'category_name' => 'Premium Leads',
            'unsubscribe_url' => 'https://example.test/unsubscribe/token',
            'webview_url' => 'https://example.test/webview/token',
            'campaign_name' => 'Spring Promo Campaign',
            'app_name' => config('app.name'),
            'first_name' => 'John',
        ];
    }

    public function render(string $content, array $values = []): string
    {
        $payload = array_merge($this->sampleValues(), $values);

        foreach ($payload as $key => $value) {
            $content = preg_replace('/\{\{\s*'.preg_quote($key, '/').'\s*\}\}/', (string) $value, $content);
        }

        return $content;
    }
}