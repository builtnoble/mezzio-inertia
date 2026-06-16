<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Pest;

use Builtnoble\Mezzio\Inertia\Testing\PendingInertiaRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Starts a fluent, chainable request: request()->withSession([...])->get($uri).
 */
function request(): PendingInertiaRequest
{
    return test()->request();
}

/**
 * @param array<string, string> $headers
 */
function withHeaders(array $headers): PendingInertiaRequest
{
    return test()->request()->withHeaders($headers);
}

/**
 * @param array<string, mixed> $data
 */
function withSession(array $data): PendingInertiaRequest
{
    return test()->request()->withSession($data);
}

function withInertiaVersion(string $version): PendingInertiaRequest
{
    return test()->request()->withInertiaVersion($version);
}

/**
 * @param array<string, string> $headers
 */
function get(string $uri, array $headers = []): ResponseInterface
{
    return test()->request()->withHeaders($headers)->get($uri);
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function post(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->request()->withHeaders($headers)->post($uri, $data);
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function put(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->request()->withHeaders($headers)->put($uri, $data);
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function patch(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->request()->withHeaders($headers)->patch($uri, $data);
}

/**
 * @param array<string, mixed> $data
 * @param array<string, string> $headers
 */
function delete(string $uri, array $data = [], array $headers = []): ResponseInterface
{
    return test()->request()->withHeaders($headers)->delete($uri, $data);
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
