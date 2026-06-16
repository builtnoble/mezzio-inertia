<?php

use Builtnoble\Mezzio\Inertia\Flash\SessionFlashAdapter;
use Laminas\Diactoros\ServerRequest;
use MaskuLabs\InertiaPsr\Support\SessionKey;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;

it('returns the value from flash data when present', function () {
    $session = new Session([SessionKey::FlashData->value => ['success' => 'Saved!']]);
    $adapter = new SessionFlashAdapter($session);

    expect($adapter->get('success'))->toBe('Saved!');
});

it('falls back to the session key directly when not present in flash data', function () {
    $session = new Session(['errors' => ['name' => 'Required']]);
    $adapter = new SessionFlashAdapter($session);

    expect($adapter->get('errors'))->toBe(['name' => 'Required']);
});

it('stores a value directly on the session', function () {
    $session = new Session([]);
    $adapter = new SessionFlashAdapter($session);

    $adapter->set('success', 'Saved!');

    expect($session->get('success'))->toBe('Saved!');
});

it('does nothing on reflash, since Mezzio session data persists by default', function () {
    $session = new Session(['foo' => 'bar']);
    $adapter = new SessionFlashAdapter($session);

    $adapter->reflash();

    expect($session->toArray())->toBe(['foo' => 'bar']);
});

it('resolves the adapter from the session attribute on the request', function () {
    $session = new Session([SessionKey::FlashData->value => ['success' => 'Saved!']]);
    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);

    $adapter = SessionFlashAdapter::fromRequest($request);

    expect($adapter->get('success'))->toBe('Saved!');
});
