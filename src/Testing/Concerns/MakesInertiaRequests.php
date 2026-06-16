<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Testing\Concerns;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use MaskuLabs\InertiaPsr\Support\Header;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait MakesInertiaRequests
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getApp()->handle($request);
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     */
    public function inertiaRequest(string $method, string $uri, array $headers = [], array $data = []): ServerRequestInterface
    {
        $request = new ServerRequest(uri: $uri, method: $method);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $request = $request->withHeader(Header::Inertia->value, 'true');

        if ($data !== []) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody(new StreamFactory()->createStream(json_encode($data, JSON_THROW_ON_ERROR)))
                ->withParsedBody($data);
        }

        return $this->withSession($request);
    }

    public function withInertiaHeader(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withHeader(Header::Inertia->value, 'true');
    }

    public function withInertiaVersion(ServerRequestInterface $request, string $version): ServerRequestInterface
    {
        return $request->withHeader(Header::Version->value, $version);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withSession(ServerRequestInterface $request, array $data = []): ServerRequestInterface
    {
        return $request->withAttribute(
            SessionMiddleware::SESSION_ATTRIBUTE,
            new Session($data),
        );
    }
}
