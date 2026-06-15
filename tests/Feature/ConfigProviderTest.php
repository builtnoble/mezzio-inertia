<?php

declare(strict_types=1);

use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;

it('registers InertiaMiddleware in the container', function () {
    expect($this->getContainer()->has(InertiaMiddleware::class))->toBeTrue();
});

it('registers StreamFactoryInterface in the container', function () {
    expect($this->getContainer()->has(StreamFactoryInterface::class))->toBeTrue();
});

it('exposes inertia config in the container', function () {
    /** @var array{inertia?: array<string, mixed>} $config */
    $config = $this->getContainer()->get('config');

    expect($config)
        ->toHaveKey('inertia')
        ->and($config['inertia'])
        ->toHaveKey('root_view', 'app')
        ->toHaveKey('shared_data', []);
});
