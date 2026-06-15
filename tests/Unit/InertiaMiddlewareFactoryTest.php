<?php

use Builtnoble\Mezzio\Inertia\Factory\InertiaMiddlewareFactory;
use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

it('falls back to ConfigProvider defaults when inertia config key is absent', function () {
    $container = new class () implements ContainerInterface {
        public function get(string $id): mixed
        {
            return match ($id) {
                'config' => [],
                ResponseFactoryInterface::class => new class () implements ResponseFactoryInterface {
                    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
                    {
                        throw new \LogicException('Not implemented');
                    }
                },
                StreamFactoryInterface::class => new class () implements StreamFactoryInterface {
                    public function createStream(
                        ServerRequestInterface $request,
                        array $pageData,
                        string $rootView,
                        array $viewData,
                    ): StreamInterface {
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
