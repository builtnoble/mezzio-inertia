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

function withInertiaVersion(ServerRequestInterface $request, string $version): ServerRequestInterface
{
    return test()->withInertiaVersion($request, $version);
}

/**
 * @return array{component: string, props: array<string, mixed>, url: string, version?: string}
 *
 * @phpstan-return array{component: string, props: array<string, mixed>, url: string, version?: string}
 */
function decodeInertiaPage(ResponseInterface $response): array
{
    return test()->decodeInertiaPage($response);
}
