# Mezzio with Inertia

## Project Overview
* **Purpose:** A lightweight integration bridge to seamlessly connect `maskulabs/inertia-psr` with Mezzio applications.
* **Core Goal:** Eliminate boilerplate setup by automating Mezzio template injection and integrating custom Vite assets.
* **Package Scope:** `inertia-psr` focuses on server-side Inertia protocol handling and response generation. It does **not** provide a frontend application, asset bundling, framework-specific routing, framework-specific dependency injection setup, a built-in session implementation, or a built-in Vite integration.
* **Integration Notes:** Use a real asset version string or build hash in production. Ensure the root view includes the frontend assets required to boot the Inertia app. When targeting a specific framework, integrate the package through framework-specific service wiring and middleware registration.

## Tech Stack & Standards
* **PHP Version:** PHP 8.5+ (Enforce strict types: `declare(strict_types=1);` in all files)
* **Framework Target:** Mezzio 3.x+ / Laminas Components
* **Primary Dependencies:** `maskulabs/inertia-psr`, `mezzio/mezzio-template`, `mezzio/mezzio-session`, `builtnoble/vite-php`
* **Standards:** PSR-4 (Autoloading), PSR-11 (Containers), PSR-15 (Middleware), PSR-7 (HTTP Messages)
* **Quality Tools:** PestPHP, PHPStan (Level 8), Laravel Pint (with `psr12` preset and additional rules, see: `pint.json`)

## Testing
* **Test Helpers & Expectations:** Pest helpers and expectations for Feature/Integration tests live in `src/Testing/`. Do not write or modify files in this directory — the package author maintains them. All standard quality tools (PHPStan, Pint) apply to this directory.

## Architecture & Integration Rules
1. **Bridge, Don't Re-invent:** Delegate all core Inertia lifecycle logic to `maskulabs/inertia-psr`. Focus entirely on writing Mezzio PSR-11 factories and PSR-15 middleware layers.
2. **Template Decoupling:** Use Mezzio's `TemplateRendererInterface` to render the root HTML layout. Never hardcode HTML formatting or layout choices inside the library code.
3. **Immutability:** Use `readonly` properties for classes and prioritize immutable designs for value objects or middleware configuration states.

## View Data & Template Rules
1. **The `$page` Array Variable:** The core response engine must always pass the raw Inertia page payload into the Mezzio Template Renderer as an array named exactly `page` (to satisfy the `<script data-page="app">` template block).
2. **Vite Service Injection:** The template renderer must also receive a custom Vite service instance as a root-level template variable (e.g., `vite`) so script tags can be parsed inside the same template file.
3. **Config-Driven Defaults:** Provide a default configuration structure via a Mezzio `ConfigProvider` so the default layout template name (e.g., `'app'`) can be changed by the end-user.

## Git & Commit Rules
* **Atomic commits:** Each commit must represent exactly one logical change. Never bundle unrelated work.
* **Format:** [Conventional Commits](https://www.conventionalcommits.org/) — `type(scope): description`. Use a scope when the change is specific to a component (e.g., `feat(adapter): add TemplateStreamAdapter`). Omit scope for project-wide changes (e.g., `chore: update composer.json dependencies`).
* **Co-author trailer:** Include `Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>` only on AI-assisted commits. Never add it to commits written solely by the author.
* **Triggering:** Commits are always triggered manually. Never commit without an explicit instruction to do so.

## Command Reference
* **Run Tests:** `./vendor/bin/pest`
* **Static Analysis:** `./vendor/bin/phpstan analyse`
* **Code Style Check:** `./vendor/bin/pint --test`
