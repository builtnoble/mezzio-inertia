<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Concerns;

use Composer\Autoload\ClassLoader;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

trait InteractsWithMezzio
{
    protected Application $app;

    protected ContainerInterface $container;

    protected string $basePath;

    protected function getApp(): Application
    {
        return $this->app;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setApp(Application $app): self
    {
        $this->app = $app;

        return $this;
    }

    public function setContainer(null|ContainerInterface|string $container = null): self
    {
        if ($container instanceof ContainerInterface) {
            $this->container = $container;

            return $this;
        }

        $path = $this->basePath . '/' . ($container ?? 'config/container.php');
        $path = normalizePath($path);

        $this->container = require $path;

        return $this;
    }

    public function bootApp(): self
    {
        $this->app = $this->container->get(Application::class);
        $factory = $this->container->get(MiddlewareFactory::class);

        (require normalizePath($this->basePath . '/config/pipeline.php'))($this->app, $factory, $this->container);
        (require normalizePath($this->basePath . '/config/routes.php'))($this->app, $factory, $this->container);

        return $this;
    }

    public function setBasePath(?string $path = null): self
    {
        if (! is_null($path)) {
            $this->basePath = $path;

            return $this;
        }

        // Try to find the autoloader, via Reflection, for consumers of this
        // package.
        $reflection = new ReflectionClass(ClassLoader::class);
        $filename = $reflection->getFileName();

        if (! is_string($filename)) {
            throw new RuntimeException('Cannot determine Reflection file location.');
        }

        $vendor = dirname(dirname($filename));
        $basePath = dirname($vendor);

        // Ensure we don't end up in this package's internal `vendor/` when
        // testing locally.
        $realPath = realpath($basePath);
        if (is_string($realPath) && (! str_ends_with($realPath, 'mezzio-inertia'))) {
            $this->basePath = $basePath;

            return $this;
        }

        // Fallback for package level tests
        $this->basePath = dirname(__DIR__, 2);

        return $this;
    }
}
