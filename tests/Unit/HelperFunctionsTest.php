<?php

declare(strict_types=1);

use Laminas\Diactoros\ServerRequest;

it('normalizes path string', function (string $input, string $output) {
    expect(normalizePath($input))->toBe($output);
})->with([
    'duplicates slashes' => ['///path//with///duplicate/slashes//', 'path/with/duplicate/slashes'],
    'outer left slash' => ['/dir/subdir/filename.ext', 'dir/subdir/filename.ext'],
    'outer right slash' => ['dir/subdir/filename.ext/', 'dir/subdir/filename.ext'],
    'outer slashes' => ['/dir/subdir/filename.ext/', 'dir/subdir/filename.ext'],
    'only a slash' => ['/', ''],
    'vfs uri scheme' => ['vfs://root/config/container.php', 'vfs://root/config/container.php'],
    'vfs duplicate slashes in path' => ['vfs://root//config//container.php', 'vfs://root/config/container.php'],
]);

it('throws from inertia() when no Inertia instance is attached to the request', function () {
    $request = new ServerRequest(uri: '/', method: 'GET');

    expect(fn () => inertia($request))
        ->toThrow(\RuntimeException::class, 'Make sure InertiaMiddleware is piped before this handler.');
});

it('throws from inertia() when rendering without an Inertia instance attached to the request', function () {
    $request = new ServerRequest(uri: '/', method: 'GET');

    expect(fn () => inertia($request, 'Profile'))
        ->toThrow(\RuntimeException::class, 'Make sure InertiaMiddleware is piped before this handler.');
});
