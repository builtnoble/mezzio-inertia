<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Tests;

use Builtnoble\Mezzio\Inertia;
use Builtnoble\Mezzio\Inertia\Testing;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

abstract class TestCase extends Testing\TestCase
{
    protected vfsStreamDirectory $vfs;

    protected function setUp(): void
    {
        $this->configProviders[] = Inertia\ConfigProvider::class;

        $this->vfs = vfsStream::setup('root', null, [
            'config' => [
                'pipeline.php' => $this->getPipelineContent(),
                'routes.php' => $this->getRoutesContent(),
            ],
        ]);

        $this->setBasePath(vfsStream::url('root'))
            ->setContainer($this->buildContainer())
            ->bootApp();
    }

    protected function tearDown(): void
    {
        unset($this->vfs, $this->app, $this->container, $this->basePath, $this->routeDefinition);

        parent::tearDown();
    }
}
