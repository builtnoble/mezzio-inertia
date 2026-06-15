<?php

declare(strict_types=1);

(static function () {
    $files = [
        'Expectations.php',
        'Helpers.php',
    ];

    foreach ($files as $file) {
        require_once __DIR__ . "/{$file}";
    }
})();
