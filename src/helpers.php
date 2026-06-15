<?php

declare(strict_types=1);

if (! function_exists('normalizePath')) {
    function normalizePath(string $path): string
    {
        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;
        $path = trim($path, '/');

        return $path;
    }
}
