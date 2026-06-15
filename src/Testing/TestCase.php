<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use Concerns\InteractsWithMezzio;
    use Concerns\AssertsInertiaResponses;
    use Concerns\MakesInertiaRequests;
}
