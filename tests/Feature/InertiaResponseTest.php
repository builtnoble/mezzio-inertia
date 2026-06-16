<?php

declare(strict_types=1);

use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use Builtnoble\VitePHP\ViteInterface;
use MaskuLabs\InertiaPsr\InertiaInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Builtnoble\Mezzio\Inertia\Testing\Pest\decodeInertiaPage;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\dispatch;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\inertiaRequest;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\withInertiaVersion;

beforeEach(function () {
    $this->getContainer()->setService(
        TemplateRendererInterface::class,
        $this->createStub(TemplateRendererInterface::class),
    );
    $this->getContainer()->setService(
        ViteInterface::class,
        $this->createStub(ViteInterface::class),
    );

    $this->defineRoutes(function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
        $app->get('/profile', [
            InertiaMiddleware::class,
            static function (ServerRequestInterface $request): ResponseInterface {
                /** @var InertiaInterface $inertia */
                $inertia = $request->getAttribute(InertiaInterface::class);

                return $inertia->render('Profile', ['name' => 'Amanda']);
            },
        ], 'profile');
    })->bootApp();
});

it('renders an Inertia component through the full Mezzio pipeline', function () {
    $response = dispatch(inertiaRequest('GET', '/profile'));

    expect($response->getStatusCode())->toBe(200);

    expect($response)
        ->toBeInertiaComponent('Profile')
        ->toHaveInertiaProps(['name' => 'Amanda']);
});

it('decodes the Inertia page payload from a response', function () {
    $response = dispatch(inertiaRequest('GET', '/profile'));

    $page = decodeInertiaPage($response);

    expect($page)
        ->toHaveKey('component', 'Profile')
        ->and($page['props'])->toHaveKey('name', 'Amanda');
});

it('triggers a version-mismatch redirect when withInertiaVersion differs from the server version', function () {
    $request = withInertiaVersion(inertiaRequest('GET', '/profile'), 'stale-version');

    $response = dispatch($request);

    expect($response->getStatusCode())->toBe(409)
        ->and($response->getHeaderLine('X-Inertia-Location'))->toBe('/profile');
});
