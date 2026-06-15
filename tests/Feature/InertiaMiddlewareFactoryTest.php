<?php

declare(strict_types=1);

use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;
use Builtnoble\VitePHP\ViteInterface;
use Mezzio\Template\TemplateRendererInterface;

it('resolves InertiaMiddleware from the container', function () {
    $this->getContainer()->setService(
        TemplateRendererInterface::class,
        $this->createStub(TemplateRendererInterface::class)
    );
    $this->getContainer()->setService(
        ViteInterface::class,
        $this->createStub(ViteInterface::class)
    );

    expect($this->getContainer()->get(InertiaMiddleware::class))
        ->toBeInstanceOf(InertiaMiddleware::class);
});
