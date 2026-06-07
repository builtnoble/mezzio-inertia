<?php

use Builtnoble\Mezzio\Inertia\ConfigProvider;
use Builtnoble\Mezzio\Inertia\Factory\TemplateStreamAdapterFactory;

it('exposes inertia config key with default root_view', function () {
    $config = (new ConfigProvider())();

    expect($config)
        ->toHaveKey('inertia')
        ->and($config['inertia'])
        ->toHaveKey('root_view', 'app');
});

it('registers TemplateStreamAdapterFactory under StreamFactoryInterface', function () {
    $config = (new ConfigProvider())();

    expect($config['dependencies']['factories'])
        ->toHaveKey(
            \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class,
            TemplateStreamAdapterFactory::class,
        );
});

test('getDefaultConfig returns default root_view independently', function () {
    expect(new ConfigProvider()->getDefaultConfig())
        ->toBe(['root_view' => 'app', 'shared_data' => []]);
});

it('includes shared_data in default config', function () {
    expect(new ConfigProvider()->getDefaultConfig())
        ->toHaveKey('shared_data', []);
});
