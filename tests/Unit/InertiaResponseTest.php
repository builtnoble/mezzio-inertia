<?php

use Builtnoble\Mezzio\Inertia\Response\InertiaResponse;
use Laminas\Diactoros\ServerRequest;

it('throws when no Inertia instance is attached to the request', function () {
    $request = new ServerRequest(uri: '/', method: 'GET');

    expect(fn () => new InertiaResponse($request, 'Profile'))
        ->toThrow(\RuntimeException::class, 'Make sure InertiaMiddleware is piped before this handler.');
});
