<?php

declare(strict_types=1);

(static function () {
    // Loaded unconditionally via composer's `autoload.files` for every
    // consumer, so this must stay a no-op when Pest isn't installed
    // (e.g. production installs that omit `require-dev`).
    if (! function_exists('expect')) {
        return;
    }

    $files = [
        'Expectations.php',
        'Helpers.php',
    ];

    foreach ($files as $file) {
        require_once __DIR__ . "/{$file}";
    }
})();
