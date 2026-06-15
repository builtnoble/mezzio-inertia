<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    use Concerns\InteractsWithMezzio;
    use Concerns\AssertsInertiaResponses;
    use Concerns\MakesInertiaRequests;

    protected function buildContainer(): ContainerInterface
    {
        $config = new ConfigAggregator($this->getConfigProviders())->getMergedConfig();
        $dependencies = $config['dependencies'];

        $dependencies['services']['config'] = $config;

        return new ServiceManager($dependencies);
    }

    protected function getPipelineContent(): string
    {
        return $this->readStub('pipeline.php.stub');
    }

    protected function getRoutesContent(): string
    {
        return $this->readStub('routes.php.stub');
    }

    private function readStub(string $filename): string
    {
        $content = file_get_contents(__DIR__ . '/Stubs/' . $filename);

        if ($content === false) {
            throw new \RuntimeException("Could not read stub file: {$filename}.");
        }

        return $content;
    }
}
