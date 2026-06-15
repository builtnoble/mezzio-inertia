<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Pest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @param array<string, string> $headers
 */
function inertiaRequest(string $method, string $uri, array $headers = []): ServerRequestInterface
{
    return test()->inertiaRequest($method, $uri, $headers);
}

function dispatch(ServerRequestInterface $request): ResponseInterface
{
    return test()->dispatch($request);
}

/**
 * @param array<string, mixed> $data
 */
function withSession(ServerRequestInterface $request, array $data = []): ServerRequestInterface
{
    return test()->withSession($request, $data);
}
