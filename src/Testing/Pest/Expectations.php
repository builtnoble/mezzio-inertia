<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Pest;

use Pest\Expectation;
use Psr\Http\Message\ResponseInterface;

/**
 * @param Expectation<mixed> $expectation
 */
function getTestableResponse(Expectation $expectation): ResponseInterface
{
    $response = $expectation->value;

    if (! $response instanceof ResponseInterface) {
        throw new \LogicException('Expected response expectation to contain a ResponseInterface.');
    }

    return $response;
};

expect()->extend('toBeInertiaOk', function (): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaOk($response);

    return $this;
});

expect()->extend('toBeInertiaFound', function (): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaFound($response);

    return $this;
});

expect()->extend('toBeInertiaSeeOther', function (): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaSeeOther($response);

    return $this;
});

expect()->extend('toBeInertiaConflict', function (): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaConflict($response);

    return $this;
});

expect()->extend('toBeInertiaComponent', function (string $component): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaComponent($response, $component);

    return $this;
});

expect()->extend('toHaveInertiaProp', function (string $key, mixed $expected): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaProp($response, $key, $expected);

    return $this;
});

expect()->extend('toHaveInertiaProps', function (array $subset): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaProps($response, $subset);

    return $this;
});

expect()->extend('toHaveInertiaVersion', function (string $version): Expectation {
    $response = getTestableResponse($this);

    test()->assertInertiaVersion($response, $version);

    return $this;
});
