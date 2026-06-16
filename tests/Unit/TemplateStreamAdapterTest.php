<?php

use Builtnoble\Mezzio\Inertia\Response\TemplateStreamAdapter;
use Builtnoble\VitePHP\ViteInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use MaskuLabs\InertiaPsr\Support\Header;
use Mezzio\Template\TemplateRendererInterface;

it('returns the JSON-encoded page data when the request is an Inertia request', function () {
    $renderer = $this->createMock(TemplateRendererInterface::class);
    $renderer->expects($this->never())->method('render');

    $adapter = new TemplateStreamAdapter(
        $renderer,
        $this->createStub(ViteInterface::class),
        new StreamFactory(),
    );

    $request = new ServerRequest(uri: '/', method: 'GET')
        ->withHeader(Header::Inertia->value, 'true');

    $pageData = ['component' => 'Home', 'props' => [], 'url' => '/', 'version' => '1'];

    $stream = $adapter->createStream($request, $pageData, 'app', []);

    expect((string) $stream)->toBe(json_encode($pageData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
});

it('renders the root view with page, vite, and view data when not an Inertia request', function () {
    $vite = $this->createStub(ViteInterface::class);
    $pageData = ['component' => 'Home', 'props' => [], 'url' => '/', 'version' => '1'];

    $renderer = $this->createMock(TemplateRendererInterface::class);
    $renderer->expects($this->once())
        ->method('render')
        ->with('app', ['page' => $pageData, 'vite' => $vite, 'title' => 'Welcome'])
        ->willReturn('<html>rendered</html>');

    $adapter = new TemplateStreamAdapter($renderer, $vite, new StreamFactory());

    $request = new ServerRequest(uri: '/', method: 'GET');

    $stream = $adapter->createStream($request, $pageData, 'app', ['title' => 'Welcome']);

    expect((string) $stream)->toBe('<html>rendered</html>');
});

it('throws when the root view is empty and the request is not an Inertia request', function () {
    $renderer = $this->createMock(TemplateRendererInterface::class);
    $renderer->expects($this->never())->method('render');

    $adapter = new TemplateStreamAdapter(
        $renderer,
        $this->createStub(ViteInterface::class),
        new StreamFactory(),
    );

    $request = new ServerRequest(uri: '/', method: 'GET');

    expect(fn () => $adapter->createStream($request, [], '', []))
        ->toThrow(\InvalidArgumentException::class, 'Root view template name must not be empty.');
});
