<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Factory;

use Builtnoble\Mezzio\Inertia\ConfigProvider;
use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use Psr\Container\ContainerInterface;

final readonly class InertiaMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): InertiaMiddleware
    {
        /** @var array{inertia?: InertiaConfig} $config */
        $config = $container->get('config');

        return new InertiaMiddleware(
            $container->get(\Psr\Http\Message\ResponseFactoryInterface::class),
            $container->get(\MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class),
            $config['inertia'] ?? new ConfigProvider()->getDefaultConfig(),
            $container,
        );
    }
}
