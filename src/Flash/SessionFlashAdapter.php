<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Flash;

use MaskuLabs\InertiaPsr\Flash\FlashInterface;
use MaskuLabs\InertiaPsr\Support\SessionKey;
use Mezzio\Session\RetrieveSession;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class SessionFlashAdapter implements FlashInterface
{
    public function __construct(
        private SessionInterface $session
    ) {}

    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new self(RetrieveSession::fromRequest($request));
    }

    public function get(string $key): mixed
    {
        $flashed = $this->session->get(SessionKey::FlashData->value, []);

        if (is_array($flashed) && array_key_exists($key, $flashed)) {
            return $flashed[$key];
        }

        return $this->session->get($key);
    }

    public function set(string $key, mixed $value = true): void
    {
        $this->session->set($key, $value);
    }

    // Mezzio session data persists until explicitly unset, so no action is needed to keep flash alive for the next request.
    public function reflash(): void {}
}
