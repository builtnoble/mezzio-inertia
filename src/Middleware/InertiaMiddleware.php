<?php

declare(strict_types=1);

namespace Builtnoble\Mezzio\Inertia\Middleware;

use Builtnoble\Mezzio\Inertia\Flash\SessionFlashAdapter;
use Builtnoble\Mezzio\Inertia\Session\MezzioSessionAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use MaskuLabs\InertiaPsr\Flash\Flash;
use MaskuLabs\InertiaPsr\Inertia;
use MaskuLabs\InertiaPsr\Middleware\Middleware;
use MaskuLabs\InertiaPsr\Property\ProvidesInertiaPropertiesInterface;
use MaskuLabs\InertiaPsr\Response\ResponseFactory;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;
use MaskuLabs\InertiaPsr\Service\CallableResolver\CallableResolver;
use MaskuLabs\InertiaPsr\Service\CustomPropResolver\CustomPropResolver;
use MaskuLabs\InertiaPsr\Service\FlashResolver\FlashResolver;
use MaskuLabs\InertiaPsr\Service\PageResolver\PageResolver;
use MaskuLabs\InertiaPsr\Service\PropsResolver\PropsResolver;
use Yiisoft\Arrays\ArrayableInterface;

final class InertiaMiddleware implements MiddlewareInterface
{
    /**
     * @param InertiaConfig $config
     */
    public function __construct(
        private readonly ResponseFactoryInterface $psrResponseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly array $config,
        private readonly ContainerInterface $container,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = MezzioSessionAdapter::fromRequest($request);
        $flashAdapter = SessionFlashAdapter::fromRequest($request);
        $flash = new Flash($flashAdapter);

        $callableResolver = new CallableResolver();
        $customPropResolver = new CustomPropResolver($callableResolver);
        $propsResolver = new PropsResolver(
            $callableResolver,
            $customPropResolver,
        );
        $flashResolver = new FlashResolver($flash);
        $pageResolver = new PageResolver($propsResolver, $flashResolver);

        $responseFactory = new ResponseFactory(
            $this->psrResponseFactory,
            $this->streamFactory,
            $pageResolver,
            $flash,
        );

        $inertia = new Inertia(
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

                if ($provider instanceof ProvidesInertiaPropertiesInterface || $provider instanceof ArrayableInterface) {
                    $inertia->share($provider);
                } elseif (is_callable($provider)) {
                    $inertia->share(($provider)($request));
                }
            } else {
                $inertia->share((string) $key, $value);
            }
        }

        return new Middleware(
            $inertia,
            $this->psrResponseFactory,
            $flashAdapter,
            $session,
        )->process($request, $handler);
    }
}
