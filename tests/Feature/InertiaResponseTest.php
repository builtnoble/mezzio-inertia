<?php

declare(strict_types=1);

use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use Builtnoble\Mezzio\Inertia\Response\InertiaResponse;
use Builtnoble\VitePHP\ViteInterface;
use MaskuLabs\InertiaPsr\InertiaInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Session\RetrieveSession;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Builtnoble\Mezzio\Inertia\Testing\Pest\decodeInertiaPage;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\get;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\post;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\withInertiaVersion;
use function Builtnoble\Mezzio\Inertia\Testing\Pest\withSession;

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

        $app->post('/contact', [
            InertiaMiddleware::class,
            static function (ServerRequestInterface $request): ResponseInterface {
                /** @var InertiaInterface $inertia */
                $inertia = $request->getAttribute(InertiaInterface::class);

                /** @var array<string, mixed> $body */
                $body = (array) $request->getParsedBody();

                return $inertia->render('Contact', $body);
            },
        ], 'contact');

        $app->get('/back', [
            InertiaMiddleware::class,
            static function (ServerRequestInterface $request): ResponseInterface {
                /** @var InertiaInterface $inertia */
                $inertia = $request->getAttribute(InertiaInterface::class);

                return $inertia->back();
            },
        ], 'back');

        $app->get('/redirect', [
            InertiaMiddleware::class,
            static function (ServerRequestInterface $request): ResponseInterface {
                /** @var InertiaInterface $inertia */
                $inertia = $request->getAttribute(InertiaInterface::class);

                return $inertia->redirect('/profile');
            },
        ], 'redirect');

        $app->get('/session', [
            InertiaMiddleware::class,
            static function (ServerRequestInterface $request): ResponseInterface {
                /** @var InertiaInterface $inertia */
                $inertia = $request->getAttribute(InertiaInterface::class);

                $userId = RetrieveSession::fromRequest($request)->get('user_id');

                return $inertia->render('Session', ['userId' => $userId]);
            },
        ], 'session');

        $app->get('/dashboard', [
            InertiaMiddleware::class,
            static fn (ServerRequestInterface $request): ResponseInterface => new InertiaResponse($request, 'Dashboard', ['name' => 'Amanda']),
        ], 'dashboard');

        $app->get('/settings', [
            InertiaMiddleware::class,
            static fn (ServerRequestInterface $request): ResponseInterface => inertia($request, 'Settings', ['name' => 'Amanda']),
        ], 'settings');
    })->bootApp();
});

it('renders an Inertia component through the full Mezzio pipeline', function () {
    $response = get('/profile');

    expect($response)
        ->toBeInertiaOk()
        ->toBeInertiaComponent('Profile')
        ->toHaveInertiaProps(['name' => 'Amanda']);
});

it('sends JSON-encoded data on POST requests', function () {
    $response = post('/contact', ['email' => 'amanda@example.com']);

    expect($response)
        ->toBeInertiaOk()
        ->toBeInertiaComponent('Contact')
        ->toHaveInertiaProps(['email' => 'amanda@example.com']);
});

it('asserts a 302 Found status code for Inertia::back()', function () {
    $response = get('/back');

    expect($response)->toBeInertiaFound();
});

it('asserts a 303 See Other status code for Inertia::redirect()', function () {
    $response = get('/redirect');

    expect($response)->toBeInertiaSeeOther();
});

it('decodes the Inertia page payload from a response', function () {
    $response = get('/profile');

    $page = decodeInertiaPage($response);

    expect($page)
        ->toHaveKey('component', 'Profile')
        ->and($page['props'])->toHaveKey('name', 'Amanda');
});

it('triggers a version-mismatch redirect when the request version differs from the server version', function () {
    $response = withInertiaVersion('stale-version')->get('/profile');

    expect($response)
        ->toBeInertiaConflict()
        ->and($response->getHeaderLine('X-Inertia-Location'))->toBe('/profile');
});

it('seeds session data via the fluent request builder', function () {
    $response = withSession(['user_id' => 42])->get('/session');

    expect($response)
        ->toBeInertiaOk()
        ->toHaveInertiaProps(['userId' => 42]);
});

it('renders an Inertia component via the new InertiaResponse() handler convention', function () {
    $response = get('/dashboard');

    expect($response)
        ->toBeInertiaOk()
        ->toBeInertiaComponent('Dashboard')
        ->toHaveInertiaProps(['name' => 'Amanda']);
});

it('renders an Inertia component via the inertia() helper function', function () {
    $response = get('/settings');

    expect($response)
        ->toBeInertiaOk()
        ->toBeInertiaComponent('Settings')
        ->toHaveInertiaProps(['name' => 'Amanda']);
});
