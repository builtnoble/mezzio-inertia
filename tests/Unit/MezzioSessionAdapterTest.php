<?php

use Builtnoble\Mezzio\Inertia\Session\MezzioSessionAdapter;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;

it('stores a value in the underlying session', function () {
    $session = new Session([]);
    $adapter = new MezzioSessionAdapter($session);

    $adapter->set('key', 'value');

    expect($session->get('key'))->toBe('value');
});

it('pulls a value out of the session, removing it', function () {
    $session = new Session(['key' => 'value']);
    $adapter = new MezzioSessionAdapter($session);

    expect($adapter->pull('key'))->toBe('value')
        ->and($session->has('key'))->toBeFalse();
});

it('falls back to the given default when pulling a missing key', function () {
    $adapter = new MezzioSessionAdapter(new Session([]));

    expect($adapter->pull('missing', 'fallback'))->toBe('fallback');
});

it('resolves the adapter from the session attribute on the request', function () {
    $session = new Session(['foo' => 'bar']);
    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);

    $adapter = MezzioSessionAdapter::fromRequest($request);
    $adapter->set('foo', 'baz');

    expect($session->get('foo'))->toBe('baz');
});
