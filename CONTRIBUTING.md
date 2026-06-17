# Contributing

## Architecture

This package is a **bridge**, not a reimplementation. All core Inertia protocol logic lives in `maskulabs/inertia-psr`. This package's job is to translate between that library and Mezzio's PSR-11/PSR-15 conventions — writing factories, middleware, and adapters so a Mezzio application can use `inertia-psr` without wiring it up manually.

The key seam is `TemplateStreamAdapter`, which implements `maskulabs/inertia-psr`'s `StreamFactoryInterface`. It decides whether to return a raw JSON stream (for XHR Inertia requests) or a fully rendered HTML stream (for first-page-load requests), delegating layout rendering to Mezzio's `TemplateRendererInterface`.

## Directory structure

```
src/
├── ConfigProvider.php          # Service wiring and config defaults
├── helpers.php                 # Global inertia() function
├── Factory/
│   ├── InertiaMiddlewareFactory.php
│   └── TemplateStreamAdapterFactory.php
├── Flash/
│   └── SessionFlashAdapter.php # Adapts Mezzio sessions to inertia-psr's FlashInterface
├── Middleware/
│   └── InertiaMiddleware.php   # Builds the Inertia instance and attaches it to the request
├── Response/
│   ├── InertiaResponse.php     # PSR-7 ResponseInterface decorator for handler use
│   └── TemplateStreamAdapter.php
├── Session/
│   └── MezzioSessionAdapter.php
└── Testing/
    ├── TestCase.php
    ├── PendingInertiaRequest.php
    ├── Concerns/
    │   ├── AssertsInertiaResponses.php
    │   ├── InteractsWithMezzio.php
    │   └── MakesInertiaRequests.php
    ├── Pest/
    │   ├── Autoload.php        # Conditionally registers Pest helpers
    │   ├── Expectations.php    # Custom expect()->extend() matchers
    │   └── Helpers.php         # Top-level Pest functions (get, post, withSession, ...)
    └── Stubs/
        ├── pipeline.php.stub
        └── routes.php.stub
```

## Tech stack

- **PHP 8.5+** — `declare(strict_types=1)` in every file, `readonly` where applicable
- **PHPStan Level 8** with strict rules — no suppressions without a reason comment
- **Laravel Pint** (`psr12` preset) — the authoritative code style enforcer
- **PestPHP 4** — test runner; PHPUnit `TestCase` is the base for framework-level tests

## Running quality tools

```bash
# Tests
./vendor/bin/pest

# Static analysis
./vendor/bin/phpstan analyse

# Code style check (dry-run)
./vendor/bin/pint --test

# Code style fix
./vendor/bin/pint
```

Run Pint on any file immediately after writing or editing it, before PHPStan or tests.

## Commit conventions

Commits follow [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): description
```

Use a scope when the change is specific to a component (`feat(response): ...`, `fix(middleware): ...`). Omit scope for project-wide changes (`chore: update composer.json`). Each commit must represent exactly one logical change — do not bundle unrelated work.

Include the co-author trailer only on AI-assisted commits:

```
Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

## Testing infrastructure

Tests are split into two suites:

- **Unit** (`tests/Unit/`) — isolated, no container, no HTTP stack
- **Feature** (`tests/Feature/`) — spins up a real `Mezzio\Application` using the stubs in `src/Testing/Stubs/`

Feature tests extend the package's own `TestCase` and configure the app in `beforeEach` via `setBasePath()->setContainer()->bootApp()`. The `defineRoutes()` hook is available for adding test-specific routes without modifying the stub files.

The Pest helpers in `src/Testing/Pest/` are loaded by `Autoload.php` via composer's `autoload.files`. The autoloader guards against loading them when Pest isn't installed (production installs that omit `require-dev`).

### Adding new test helpers or assertions

- Assertion methods belong on the `AssertsInertiaResponses` trait.
- Request-building methods (new configuration options) belong on `PendingInertiaRequest`.
- Pest-facing shims that wrap `test()->...()` calls belong in `Helpers.php` or `Expectations.php`.

All code in `src/Testing/` is subject to the same PHPStan Level 8 and Pint standards as production code.
