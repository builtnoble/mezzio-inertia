<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia;

use Builtnoble\Mezzio\Inertia\Factory\TemplateStreamAdapterFactory;

final readonly class ConfigProvider
{
    /**
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-return array{inertia: InertiaConfig, dependencies: DependenciesConfig}
     * */
    public function __invoke(): array
    {
        return [
            'inertia'      => $this->getDefaultConfig(),
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array<string, string>
     *
     * @phpstan-return InertiaConfig
     */
    public function getDefaultConfig(): array
    {
        return [
            'root_view' => 'app',
        ];
    }

    /**
     * @return array{factories: array<class-string, class-string>}
     *
     * @phpstan-return DependenciesConfig
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class => TemplateStreamAdapterFactory::class,
            ],
        ];
    }
}
