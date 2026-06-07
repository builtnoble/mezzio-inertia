<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Session;

use MaskuLabs\InertiaPsr\Session\SessionInterface;
use Mezzio\Session\RetrieveSession;
use Psr\Http\Message\ServerRequestInterface;

final readonly class MezzioSessionAdapter implements SessionInterface
{
    public function __construct(
        private \Mezzio\Session\SessionInterface $session
    ) {}

    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new self(RetrieveSession::fromRequest($request));
    }

    public function set(string $key, mixed $value): void
    {
        $this->session->set($key, $value);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->session->get($key, $default);
        $this->session->unset($key);

        return $value;
    }
}
