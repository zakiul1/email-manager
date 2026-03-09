<?php

namespace App\Services\SendPortal;

class TemplatePreviewSanitizer
{
    public function sanitize(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/on[a-z]+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace("/on[a-z]+\s*=\s*'[^']*'/i", '', $html) ?? $html;
        $html = preg_replace('/javascript:/i', '', $html) ?? $html;

        return $html;
    }
}