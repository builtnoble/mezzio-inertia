<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Middleware;

use Builtnoble\Mezzio\Inertia\Flash\SessionFlashAdapter;
use Builtnoble\Mezzio\Inertia\Session\MezzioSessionAdapter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InertiaMiddleware implements MiddlewareInterface
{
    /** @param InertiaConfig $config */
    public function __construct(
        private readonly \Psr\Http\Message\ResponseFactoryInterface $psrResponseFactory,
        private readonly \MaskuLabs\InertiaPsr\Response\StreamFactoryInterface $streamFactory,
        private readonly array $config,
        private readonly \Psr\Container\ContainerInterface $container,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session      = MezzioSessionAdapter::fromRequest($request);
        $flashAdapter = SessionFlashAdapter::fromRequest($request);
        $flash        = new \MaskuLabs\InertiaPsr\Flash\Flash($flashAdapter);

        $callableResolver   = new \MaskuLabs\InertiaPsr\Service\CallableResolver\CallableResolver();
        $customPropResolver = new \MaskuLabs\InertiaPsr\Service\CustomPropResolver\CustomPropResolver($callableResolver);
        $propsResolver      = new \MaskuLabs\InertiaPsr\Service\PropsResolver\PropsResolver(
            $callableResolver,
            $customPropResolver,
        );
        $flashResolver = new \MaskuLabs\InertiaPsr\Service\FlashResolver\FlashResolver($flash);
        $pageResolver  = new \MaskuLabs\InertiaPsr\Service\PageResolver\PageResolver($propsResolver, $flashResolver);

        $responseFactory = new \MaskuLabs\InertiaPsr\Response\ResponseFactory(
            $this->psrResponseFactory,
            $this->streamFactory,
            $pageResolver,
            $flash,
        );

        $inertia = new \MaskuLabs\InertiaPsr\Inertia(
            $request,
            $this->psrResponseFactory,
            $responseFactory,
            $session,
            $callableResolver,
            $flash,
        );

        $inertia->setRootView($this->config['root_view']);

        foreach ($this->config['shared_data'] as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $provider = $this->container->get($value);

                if ($provider instanceof \MaskuLabs\InertiaPsr\Property\ProvidesInertiaPropertiesInterface
                    || $provider instanceof \Yiisoft\Arrays\ArrayableInterface) {
                    $inertia->share($provider);
                } elseif (is_callable($provider)) {
                    $inertia->share(($provider)($request));
                }
            } else {
                $inertia->share((string) $key, $value);
            }
        }

        return new \MaskuLabs\InertiaPsr\Middleware\Middleware(
            $inertia,
            $this->psrResponseFactory,
            $flashAdapter,
            $session,
        )->process($request, $handler);
    }
}
