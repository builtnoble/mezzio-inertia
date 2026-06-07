<?php

use Builtnoble\Mezzio\Inertia\ConfigProvider;
use Builtnoble\Mezzio\Inertia\Factory\InertiaMiddlewareFactory;
use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;

it('creates InertiaMiddleware from container', function () {
    $psrResponseFactory = new class () implements \Psr\Http\Message\ResponseFactoryInterface {
        public function createResponse(int $code = 200, string $reasonPhrase = ''): \Psr\Http\Message\ResponseInterface
        {
            throw new \LogicException('Not implemented');
        }
    };

    $streamFactory = new class () implements \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface {
        public function createStream(
            \Psr\Http\Message\ServerRequestInterface $request,
            array $pageData,
            string $rootView,
            array $viewData,
        ): \Psr\Http\Message\StreamInterface {
            throw new \LogicException('Not implemented');
        }
    };

    $defaultConfig = new ConfigProvider()->getDefaultConfig();

    $container = new class ($psrResponseFactory, $streamFactory, $defaultConfig) implements \Psr\Container\ContainerInterface {
        public function __construct(
            private readonly \Psr\Http\Message\ResponseFactoryInterface $psrResponseFactory,
            private readonly \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface $streamFactory,
            private readonly array $defaultConfig,
        ) {}

        public function get(string $id): mixed
        {
            return match ($id) {
                'config' => ['inertia' => $this->defaultConfig],
                \Psr\Http\Message\ResponseFactoryInterface::class => $this->psrResponseFactory,
                \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class => $this->streamFactory,
                default => throw new \RuntimeException("Service not found: {$id}"),
            };
        }

        public function has(string $id): bool
        {
            return in_array($id, [
                'config',
                \Psr\Http\Message\ResponseFactoryInterface::class,
                \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class,
            ], true);
        }
    };

    expect((new InertiaMiddlewareFactory())($container))
        ->toBeInstanceOf(InertiaMiddleware::class);
});

it('falls back to ConfigProvider defaults when inertia config key is absent', function () {
    $container = new class () implements \Psr\Container\ContainerInterface {
        public function get(string $id): mixed
        {
            return match ($id) {
                'config' => [],
                \Psr\Http\Message\ResponseFactoryInterface::class => new class () implements \Psr\Http\Message\ResponseFactoryInterface {
                    public function createResponse(int $code = 200, string $reasonPhrase = ''): \Psr\Http\Message\ResponseInterface
                    {
                        throw new \LogicException('Not implemented');
                    }
                },
                \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class => new class () implements \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface {
                    public function createStream(
                        \Psr\Http\Message\ServerRequestInterface $request,
                        array $pageData,
                        string $rootView,
                        array $viewData,
                    ): \Psr\Http\Message\StreamInterface {
                        throw new \LogicException('Not implemented');
                    }
                },
                default => throw new \RuntimeException("Service not found: {$id}"),
            };
        }

        public function has(string $id): bool
        {
            return true;
        }
    };

    expect((new InertiaMiddlewareFactory())($container))
        ->toBeInstanceOf(InertiaMiddleware::class);
});
