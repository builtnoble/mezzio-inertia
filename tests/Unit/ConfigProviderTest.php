<?php

use Builtnoble\Mezzio\Inertia;

covers(Inertia\ConfigProvider::class);

it('exposes inertia config key with default root_view', function () {
    $config = (new Inertia\ConfigProvider())();

    expect($config)
        ->toHaveKey('inertia')
        ->and($config['inertia'])
        ->toHaveKey('root_view', 'app');
});

it('registers TemplateStreamAdapterFactory under StreamFactoryInterface', function () {
    $config = (new Inertia\ConfigProvider())();

    expect($config['dependencies']['factories'])
        ->toHaveKey(
            \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface::class,
            Inertia\Factory\TemplateStreamAdapterFactory::class,
        );
});

test('getDefaultConfig returns default root_view independently', function () {
    expect(new Inertia\ConfigProvider()->getDefaultConfig())
        ->toBe(['root_view' => 'app', 'shared_data' => []]);
});

it('includes shared_data in default config', function () {
    expect(new Inertia\ConfigProvider()->getDefaultConfig())
        ->toHaveKey('shared_data', []);
});
