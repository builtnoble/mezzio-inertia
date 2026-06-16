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

it('renders an Inertia component through the full Mezzio pipeline', function () {
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

    $response = $this->dispatch($this->inertiaRequest('GET', '/profile'));

    expect($response->getStatusCode())->toBe(200);

    $this->assertInertiaComponent($response, 'Profile');
    $this->assertInertiaProps($response, ['name' => 'Amanda']);
});
