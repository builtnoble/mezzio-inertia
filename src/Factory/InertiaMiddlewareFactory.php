<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Factory;

use Builtnoble\Mezzio\Inertia\ConfigProvider;
use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final readonly class InertiaMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): InertiaMiddleware
    {
        /** @var array{inertia?: InertiaConfig} $config */
        $config = $container->get('config');

        return new InertiaMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $config['inertia'] ?? new ConfigProvider()->getDefaultConfig(),
            $container,
        );
    }
}
