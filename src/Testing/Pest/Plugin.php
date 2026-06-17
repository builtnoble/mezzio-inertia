<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Pest;

use Pest\Contracts\Plugins\Bootable;

final class Plugin implements Bootable
{
    public function boot(): void
    {
        require_once __DIR__ . '/Expectations.php';
        require_once __DIR__ . '/Helpers.php';
    }
}
