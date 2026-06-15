<?php

declare(strict_types=1);

if (! function_exists('normalizePath')) {
    function normalizePath(string $path): string
    {
        $scheme = '';

        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+\-.]*://)(.*)$#', $path, $matches) === 1) {
            $scheme = $matches[1];
            $path = $matches[2];
        }

        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;
        $path = trim($path, '/');

        return $scheme . $path;
    }
}
