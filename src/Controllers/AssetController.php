<?php

namespace Alfinprdht\QueryPulse\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetController extends Controller
{
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        $path = ltrim($path, '/');

        if (
            $path === ''
            || str_contains($path, '..')
            || str_contains($path, '\\')
            || str_starts_with($path, '.')
        ) {
            abort(404);
        }

        $base = realpath(__DIR__ . '/../../public');
        if ($base === false) {
            abort(404);
        }

        $file = realpath($base . DIRECTORY_SEPARATOR . $path);
        if ($file === false || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
            abort(404);
        }

        $contentType = $this->guessContentType($file);

        return response()->file($file, array_filter([
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=3600',
        ]));
    }

    private function guessContentType(string $file): ?string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return match ($ext) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            default => null,
        };
    }
}

