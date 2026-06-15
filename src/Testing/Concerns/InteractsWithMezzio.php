<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Concerns;

use Closure;
use Composer\Autoload\ClassLoader;
use Laminas\Diactoros\ConfigProvider as DiactorosConfigProvider;
use Laminas\HttpHandlerRunner\ConfigProvider as HttpHandlerRunnerConfigProvider;
use Laminas\Router\ConfigProvider as LaminasRouterConfigProvider;
use Mezzio\Application;
use Mezzio\ConfigProvider as MezzioConfigProvider;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\ConfigProvider as MezzioRouterConfigProvider;
use Mezzio\Router\LaminasRouter\ConfigProvider as LaminasRouterAdapterConfigProvider;
use Psr\Container\ContainerInterface;

trait InteractsWithMezzio
{
    protected Application $app;

    protected ContainerInterface $container;

    protected string $basePath;

    /**
     * @var list<callable|class-string>
     *
     * @phpstan-var list<callable|class-string>
     */
    protected array $configProviders = [
        LaminasRouterAdapterConfigProvider::class,
        LaminasRouterConfigProvider::class,
        HttpHandlerRunnerConfigProvider::class,
        MezzioConfigProvider::class,
        MezzioRouterConfigProvider::class,
        DiactorosConfigProvider::class,
    ];

    /**
     * @var (\Closure(Application, MiddlewareFactory, ContainerInterface): void)|null
     *
     * @phpstan-var (\Closure(Application, MiddlewareFactory, ContainerInterface): void)|null
     */
    protected ?\Closure $routeDefinition = null;

    protected function getApp(): Application
    {
        return $this->app;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return list<callable|class-string>
     *
     * @phpstan-return list<callable|class-string>
     */
    public function getConfigProviders(): array
    {
        return $this->configProviders;
    }

    /**
     * @param list<callable|class-string> $configProviders
     *
     * @phpstan-param list<callable|class-string> $configProviders
     */
    public function setConfigProviders(array $configProviders): self
    {
        $this->configProviders = $configProviders;

        return $this;
    }

    /**
     * @param \Closure(Application, MiddlewareFactory, ContainerInterface): void $definition
     *
     * @phpstan-param Closure(Application, MiddlewareFactory, ContainerInterface): void $definition
     */
    public function defineRoutes(\Closure $definition): self
    {
        $this->routeDefinition = $definition;

        return $this;
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

        if ($this->routeDefinition !== null) {
            ($this->routeDefinition)($this->app, $factory, $this->container);
        }

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
        $reflection = new \ReflectionClass(ClassLoader::class);
        $filename = $reflection->getFileName();

        if (! is_string($filename)) {
            throw new \RuntimeException('Cannot determine Reflection file location.');
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
