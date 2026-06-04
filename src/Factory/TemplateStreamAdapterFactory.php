<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Factory;

use Builtnoble\Mezzio\Inertia\Response\TemplateStreamAdapter;
use Builtnoble\VitePHP\ViteInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class TemplateStreamAdapterFactory
{
    public function __invoke(ContainerInterface $container): TemplateStreamAdapter
    {
        return new TemplateStreamAdapter(
            $container->get(TemplateRendererInterface::class),
            $container->get(ViteInterface::class),
            $container->get(StreamFactoryInterface::class),
        );
    }
}
