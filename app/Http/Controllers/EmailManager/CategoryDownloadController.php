<?php

namespace App\Http\Controllers\EmailManager;

use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryDownloadController
{
    public function download(Request $request, Category $category): StreamedResponse
    {
        $filename = ($category->slug ?: 'category-' . $category->id) . '-emails.csv';

        // Must exist: Category::emails() relationship to EmailAddress model
        $query = $category->emails()->select('email');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // header row
            fputcsv($out, ['email']);

            $query->orderBy('email')->chunk(2000, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [$row->email]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}