<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Concerns;

use Psr\Http\Message\ResponseInterface;

trait AssertsInertiaResponses
{
    /**
     * Decodes the Inertia page payload from the response body.
     * Available publicly for writing custom assertions not covered by this trait.
     *
     * @return array{component: string, props: array<string, mixed>, url: string, version?: string}
     *
     * @phpstan-return array{component: string, props: array<string, mixed>, url: string, version?: string}
     */
    public function decodeInertiaPage(ResponseInterface $response): array
    {
        /** @var array<string, mixed>|null $page */
        $page = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);

        static::assertIsArray($page);
        static::assertArrayHasKey('component', $page);
        static::assertArrayHasKey('props', $page);
        static::assertArrayHasKey('url', $page);

        /** @var array{component: string, props: array<string, mixed>, url: string, version?: string} $page */
        return $page;
    }

    /**
     * Assert a 200 "OK" status code, Inertia's status for a standard,
     * successful page visit.
     */
    public function assertInertiaOk(ResponseInterface $response): void
    {
        static::assertSame(200, $response->getStatusCode());
    }

    /**
     * Assert a 302 "Found" status code, the default status Inertia uses
     * for redirects (e.g. Inertia::back()).
     */
    public function assertInertiaFound(ResponseInterface $response): void
    {
        static::assertSame(302, $response->getStatusCode());
    }

    /**
     * Assert a 303 "See Other" status code. Inertia upgrades redirects
     * to PUT/PATCH/DELETE requests to this status so the client follows
     * up with a GET request instead of repeating the original method,
     * and Inertia::redirect() uses it directly.
     */
    public function assertInertiaSeeOther(ResponseInterface $response): void
    {
        static::assertSame(303, $response->getStatusCode());
    }

    /**
     * Assert a 409 "Conflict" status code, the status Inertia uses to
     * signal an asset version mismatch so the client performs a full
     * page visit instead of an XHR-based one.
     */
    public function assertInertiaConflict(ResponseInterface $response): void
    {
        static::assertSame(409, $response->getStatusCode());
    }

    public function assertInertiaComponent(ResponseInterface $response, string $component): void
    {
        $page = $this->decodeInertiaPage($response);

        static::assertSame($component, $page['component']);
    }

    /**
     * @param array<string, mixed> $subset
     */
    public function assertInertiaProps(ResponseInterface $response, array $subset): void
    {
        $page = $this->decodeInertiaPage($response);

        foreach ($subset as $key => $expected) {
            static::assertArrayHasKey($key, $page['props']);
            static::assertEquals($expected, $page['props'][$key]);
        }
    }

    public function assertInertiaProp(ResponseInterface $response, string $key, mixed $expected): void
    {
        $page = $this->decodeInertiaPage($response);

        static::assertEquals($expected, $this->resolveNestedProp($page['props'], $key));
    }

    public function assertInertiaVersion(ResponseInterface $response, string $version): void
    {
        $page = $this->decodeInertiaPage($response);

        static::assertArrayHasKey('version', $page);
        static::assertSame($version, $page['version'] ?? null);
    }

    /**
     * @param array<string, mixed> $props
     */
    private function resolveNestedProp(array $props, string $key): mixed
    {
        $segments = explode('.', $key);
        $current = $props;

        foreach ($segments as $segment) {
            static::assertIsArray($current, "Prop path '{$key}' could not be resolved.");
            static::assertArrayHasKey($segment, $current, "Prop '{$segment}' not found in path '{$key}'.");
            $current = $current[$segment];
        }

        return $current;
    }
}
