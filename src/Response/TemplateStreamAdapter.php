<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Response;

use Builtnoble\VitePHP\ViteInterface;
use MaskuLabs\InertiaPsr\Helper\RequestHelper;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

final readonly class TemplateStreamAdapter implements StreamFactoryInterface
{
    public function __construct(
        private TemplateRendererInterface $renderer,
        private ViteInterface $vite,
        private \Psr\Http\Message\StreamFactoryInterface $streamFactory,
    ) {}

    /**
     * @param array<mixed> $pageData
     * @param array<mixed> $viewData
     */
    public function createStream(ServerRequestInterface $request, array $pageData, string $rootView, array $viewData): StreamInterface
    {
        if (RequestHelper::isInertia($request)) {
            return $this->streamFactory->createStream(
                json_encode($pageData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            );
        }

        if ($rootView === '') {
            throw new InvalidArgumentException('Root view template name must not be empty.');
        }

        $html = $this->renderer->render($rootView, [
            'page' => $pageData,
            'vite' => $this->vite,
            ...$viewData,
        ]);

        return $this->streamFactory->createStream($html);
    }
}
