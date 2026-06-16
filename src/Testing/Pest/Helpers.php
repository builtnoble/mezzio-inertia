<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Pest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @param array<string, string> $headers
 */
function get(string $uri, array $headers = []): ResponseInterface
{
    return test()->dispatch(test()->inertiaRequest('GET', $uri, $headers));
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function post(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->dispatch(test()->inertiaRequest('POST', $uri, $headers, $data));
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function put(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->dispatch(test()->inertiaRequest('PUT', $uri, $headers, $data));
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function patch(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->dispatch(test()->inertiaRequest('PATCH', $uri, $headers, $data));
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function delete(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->dispatch(test()->inertiaRequest('DELETE', $uri, $headers, $data));
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
