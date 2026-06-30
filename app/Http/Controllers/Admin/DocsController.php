<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function index(Request $request, $path = 'index.html')
    {
        if (empty($path)) {
            $path = 'index.html';
        }

        // Prevent directory traversal attacks
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            abort(404);
        }

        $basePath = base_path('docs-site/dist');
        $filePath = $basePath . '/' . $path;

        if (!file_exists($filePath)) {
            // Check if it's a route path without extension (e.g. core-concepts/architecture)
            if (file_exists($filePath . '.html')) {
                $filePath .= '.html';
            } else if (file_exists($filePath . '/index.html')) {
                $filePath .= '/index.html';
            } else {
                abort(404);
            }
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = match($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'html' => 'text/html',
            'json' => 'application/json',
            default => mime_content_type($filePath) ?: 'text/plain',
        };

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
