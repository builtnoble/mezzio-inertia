<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Response;

use MaskuLabs\InertiaPsr\InertiaInterface;
use MaskuLabs\InertiaPsr\Property\ProvidesInertiaPropertiesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Yiisoft\Arrays\ArrayableInterface;

/**
 * Lets a handler return an Inertia response using Mezzio's own
 * `return new XResponse(...)` convention (e.g. JsonResponse, HtmlResponse),
 * as an alternative to `$inertia->render(...)`.
 *
 * InertiaMiddleware must be piped before any handler using this, since it's
 * what attaches the InertiaInterface instance this class resolves from the
 * request.
 */
final readonly class InertiaResponse implements ResponseInterface
{
    private ResponseInterface $response;

    /**
     * @param array<string, mixed>|ArrayableInterface|ProvidesInertiaPropertiesInterface $props
     */
    public function __construct(
        ServerRequestInterface $request,
        string $component,
        array|ArrayableInterface|ProvidesInertiaPropertiesInterface $props = [],
    ) {
        /** @var InertiaInterface|null $inertia */
        $inertia = $request->getAttribute(InertiaInterface::class);

        if (! $inertia instanceof InertiaInterface) {
            throw new \RuntimeException(
                'No Inertia instance found on the request. Make sure InertiaMiddleware is piped before this handler.',
            );
        }

        $this->response = $inertia->render($component, $props);
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withProtocolVersion($version)]);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withHeader($name, $value)]);
    }

    public function withAddedHeader(string $name, $value): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withAddedHeader($name, $value)]);
    }

    public function withoutHeader(string $name): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withoutHeader($name)]);
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withBody($body)]);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        /** @var static */
        return clone($this, ['response' => $this->response->withStatus($code, $reasonPhrase)]);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}
