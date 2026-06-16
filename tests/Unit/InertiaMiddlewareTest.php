<?php

use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use MaskuLabs\InertiaPsr\InertiaInterface;
use MaskuLabs\InertiaPsr\Property\ProvidesInertiaPropertiesInterface;
use MaskuLabs\InertiaPsr\Property\RenderContext;
use MaskuLabs\InertiaPsr\Response\StreamFactoryInterface;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Arrays\ArrayableInterface;

it('shares scalar config values under their string key', function () {
    $container = new class () implements ContainerInterface {
        public function get(string $id): mixed
        {
            throw new \RuntimeException("Service not found: {$id}");
        }

        public function has(string $id): bool
        {
            return false;
        }
    };

    $middleware = new InertiaMiddleware(
        new ResponseFactory(),
        new class () implements StreamFactoryInterface {
            public function createStream(ServerRequestInterface $request, array $pageData, string $rootView, array $viewData): StreamInterface
            {
                throw new \LogicException('Not implemented');
            }
        },
        ['root_view' => 'app', 'shared_data' => ['appName' => 'Acme']],
        $container,
    );

    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, new Session([]));

    $handler = new class () implements RequestHandlerInterface {
        public ?InertiaInterface $inertia = null;

        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->inertia = $request->getAttribute(InertiaInterface::class);

            return new Response();
        }
    };

    $middleware->process($request, $handler);

    expect($handler->inertia)
        ->toBeInstanceOf(InertiaInterface::class)
        ->and($handler->inertia->getShared())
        ->toHaveKey('appName', 'Acme');
});

it('merges an ArrayableInterface provider resolved from the container', function () {
    $provider = new class () implements ArrayableInterface {
        public function fields(): array
        {
            return [];
        }

        public function extraFields(): array
        {
            return [];
        }

        public function toArray(array $fields = [], array $expand = [], bool $recursive = true): array
        {
            return ['locale' => 'en'];
        }
    };

    $container = new class ($provider) implements ContainerInterface {
        public function __construct(
            private object $provider
        ) {}

        public function get(string $id): mixed
        {
            return $id === $this->provider::class ? $this->provider : throw new \RuntimeException("Service not found: {$id}");
        }

        public function has(string $id): bool
        {
            return $id === $this->provider::class;
        }
    };

    $middleware = new InertiaMiddleware(
        new ResponseFactory(),
        new class () implements StreamFactoryInterface {
            public function createStream(ServerRequestInterface $request, array $pageData, string $rootView, array $viewData): StreamInterface
            {
                throw new \LogicException('Not implemented');
            }
        },
        ['root_view' => 'app', 'shared_data' => [$provider::class]],
        $container,
    );

    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, new Session([]));

    $handler = new class () implements RequestHandlerInterface {
        public ?InertiaInterface $inertia = null;

        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->inertia = $request->getAttribute(InertiaInterface::class);

            return new Response();
        }
    };

    $middleware->process($request, $handler);

    expect($handler->inertia->getShared())->toHaveKey('locale', 'en');
});

it('shares a ProvidesInertiaPropertiesInterface provider resolved from the container as-is', function () {
    $provider = new class () implements ProvidesInertiaPropertiesInterface {
        public function toInertiaProperties(RenderContext $context): iterable
        {
            return ['user' => 'Amanda'];
        }
    };

    $container = new class ($provider) implements ContainerInterface {
        public function __construct(
            private object $provider
        ) {}

        public function get(string $id): mixed
        {
            return $id === $this->provider::class ? $this->provider : throw new \RuntimeException("Service not found: {$id}");
        }

        public function has(string $id): bool
        {
            return $id === $this->provider::class;
        }
    };

    $middleware = new InertiaMiddleware(
        new ResponseFactory(),
        new class () implements StreamFactoryInterface {
            public function createStream(ServerRequestInterface $request, array $pageData, string $rootView, array $viewData): StreamInterface
            {
                throw new \LogicException('Not implemented');
            }
        },
        ['root_view' => 'app', 'shared_data' => [$provider::class]],
        $container,
    );

    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, new Session([]));

    $handler = new class () implements RequestHandlerInterface {
        public ?InertiaInterface $inertia = null;

        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->inertia = $request->getAttribute(InertiaInterface::class);

            return new Response();
        }
    };

    $middleware->process($request, $handler);

    expect($handler->inertia->getShared()[0])->toBe($provider);
});

it('invokes a callable provider resolved from the container with the request', function () {
    $provider = new class () {
        public function __invoke(ServerRequestInterface $request): array
        {
            return ['method' => $request->getMethod()];
        }
    };

    $container = new class ($provider) implements ContainerInterface {
        public function __construct(
            private object $provider
        ) {}

        public function get(string $id): mixed
        {
            return $id === $this->provider::class ? $this->provider : throw new \RuntimeException("Service not found: {$id}");
        }

        public function has(string $id): bool
        {
            return $id === $this->provider::class;
        }
    };

    $middleware = new InertiaMiddleware(
        new ResponseFactory(),
        new class () implements StreamFactoryInterface {
            public function createStream(ServerRequestInterface $request, array $pageData, string $rootView, array $viewData): StreamInterface
            {
                throw new \LogicException('Not implemented');
            }
        },
        ['root_view' => 'app', 'shared_data' => [$provider::class]],
        $container,
    );

    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, new Session([]));

    $handler = new class () implements RequestHandlerInterface {
        public ?InertiaInterface $inertia = null;

        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->inertia = $request->getAttribute(InertiaInterface::class);

            return new Response();
        }
    };

    $middleware->process($request, $handler);

    expect($handler->inertia->getShared())->toHaveKey('method', 'GET');
});
