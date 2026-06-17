<?php

declare(strict_types=1);

use Builtnoble\Mezzio\Inertia\Response\InertiaResponse;
use MaskuLabs\InertiaPsr\InertiaInterface;
use MaskuLabs\InertiaPsr\Property\ProvidesInertiaPropertiesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Arrays\ArrayableInterface;

if (! function_exists('normalizePath')) {
    function normalizePath(string $path): string
    {
        $scheme = '';

        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+\-.]*://)(.*)$#', $path, $matches) === 1) {
            $scheme = $matches[1];
            $path = $matches[2];
        }

        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;
        $path = trim($path, '/');

        return $scheme . $path;
    }
}

if (! function_exists('inertia')) {
    /**
     * Resolves the current request's Inertia instance, or renders a component
     * directly when one is given:
     *
     * ```
     * inertia($request)->share('app.name', 'Acme');
     * return inertia($request, 'Profile', ['name' => $user->name]);
     * ```
     *
     * @param array<string, mixed>|ArrayableInterface|ProvidesInertiaPropertiesInterface $props
     */
    function inertia(
        ServerRequestInterface $request,
        ?string $component = null,
        array|ArrayableInterface|ProvidesInertiaPropertiesInterface $props = [],
    ): InertiaInterface|ResponseInterface {
        if ($component === null) {
            /** @var InertiaInterface|null $inertia */
            $inertia = $request->getAttribute(InertiaInterface::class);

            if (! $inertia instanceof InertiaInterface) {
                throw new \RuntimeException(
                    'No Inertia instance found on the request. Make sure InertiaMiddleware is piped before this handler.',
                );
            }

            return $inertia;
        }

        return new InertiaResponse($request, $component, $props);
    }
}
