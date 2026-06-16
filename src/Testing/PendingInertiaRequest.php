<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing;

use MaskuLabs\InertiaPsr\Support\Header;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Fluent builder for configuring an Inertia request before dispatching it.
 *
 * Config methods (withHeaders(), withSession(), withInertiaVersion()) are
 * immutable and return a new instance, mirroring PSR-7's own with*()
 * convention. The verb methods (get(), post(), ...) are terminal: they
 * build the request from whatever has been configured and dispatch it.
 *
 * Consumers can build their own helpers, like an actingAs(), on top of
 * withSession() without this class needing to know about them:
 *
 *     function actingAs(User $user): PendingInertiaRequest
 *     {
 *         return request()->withSession(['user_id' => $user->id]);
 *     }
 *
 *     actingAs($user)->get('/dashboard');
 */
final readonly class PendingInertiaRequest
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $session
     */
    public function __construct(
        private TestCase $testCase,
        private array $headers = [],
        private array $session = [],
    ) {}

    /**
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        return new self($this->testCase, [...$this->headers, ...$headers], $this->session);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withSession(array $data): self
    {
        return new self($this->testCase, $this->headers, [...$this->session, ...$data]);
    }

    public function withInertiaVersion(string $version): self
    {
        return $this->withHeaders([Header::Version->value => $version]);
    }

    public function get(string $uri): ResponseInterface
    {
        return $this->testCase->dispatch($this->build('GET', $uri));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function post(string $uri, array $data = []): ResponseInterface
    {
        return $this->testCase->dispatch($this->build('POST', $uri, $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function put(string $uri, array $data = []): ResponseInterface
    {
        return $this->testCase->dispatch($this->build('PUT', $uri, $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function patch(string $uri, array $data = []): ResponseInterface
    {
        return $this->testCase->dispatch($this->build('PATCH', $uri, $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function delete(string $uri, array $data = []): ResponseInterface
    {
        return $this->testCase->dispatch($this->build('DELETE', $uri, $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function build(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $request = $this->testCase->inertiaRequest($method, $uri, $this->headers, $data);

        if ($this->session !== []) {
            $request = $this->testCase->withSession($request, $this->session);
        }

        return $request;
    }
}
