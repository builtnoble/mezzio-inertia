# mezzio-inertia

An [Inertia.js](https://inertiajs.com) adapter for [Mezzio](https://docs.mezzio.dev). It wires `maskulabs/inertia-psr` into a Mezzio application — handling middleware registration, Vite asset injection, session adapters, and template rendering — so you can focus on writing handlers instead of plumbing.

## Requirements

- PHP 8.5+
- Mezzio 3.x
- A Mezzio template adapter (`mezzio/mezzio-plates`, `mezzio/mezzio-twigrenderer`, etc.)
- `mezzio/mezzio-session` (and a session persistence implementation)
- `builtnoble/vite-php` wired into your container as `ViteInterface`

## Installation

```bash
composer require builtnoble/mezzio-inertia
```

If your application uses `laminas/laminas-component-installer`, the `ConfigProvider` is registered automatically. Otherwise, add it manually to your config aggregator:

```php
// config/config.php
use Builtnoble\Mezzio\Inertia\ConfigProvider;

new ConfigAggregator([
    ConfigProvider::class,
    // ...
]);
```

## Root view template

The package renders your Inertia root layout through Mezzio's `TemplateRendererInterface`. Two variables are always available inside the template:

| Variable | Type | Purpose |
|---|---|---|
| `$page` | `array` | The Inertia page payload — JSON-encode it inside a `<script type="application/json" data-page="app">` tag |
| `$vite` | `ViteInterface` | Your Vite service — invoke it with an array of entry paths: `($vite)(['resources/js/app.ts'])` |

A minimal Plates template looks like this:

```php
<!DOCTYPE html>
<html>
<head>
    <?= ($vite)(['resources/js/app.ts']) ?>
    <script type="application/json" data-page="app">
        <?= json_encode($page, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

The default template name is `app`. Override it in your config:

```php
// config/autoload/inertia.global.php
return [
    'inertia' => [
        'root_view' => 'layouts::app',
    ],
];
```

## Piping the middleware

`InertiaMiddleware` must be piped before any handler that renders an Inertia component. You can pipe it globally or per-route.

**Per-route** (recommended — only active where needed):

```php
use Builtnoble\Mezzio\Inertia\Middleware\InertiaMiddleware;

$app->get('/dashboard', [InertiaMiddleware::class, DashboardHandler::class], 'dashboard');
```

**Globally** (in `config/pipeline.php`, after session middleware):

```php
$app->pipe(SessionMiddleware::class);
$app->pipe(InertiaMiddleware::class);
$app->pipe(RouteMiddleware::class);
$app->pipe(DispatchMiddleware::class);
```

## Rendering components from handlers

There are three equivalent patterns — use whichever fits your style.

**Option A — direct `InertiaInterface` access:**

```php
use MaskuLabs\InertiaPsr\InertiaInterface;

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $inertia = $request->getAttribute(InertiaInterface::class);

    return $inertia->render('Dashboard', ['name' => $user->name]);
}
```

**Option B — `InertiaResponse` decorator** (mirrors Mezzio's own `HtmlResponse`/`JsonResponse` idiom):

```php
use Builtnoble\Mezzio\Inertia\Response\InertiaResponse;

public function handle(ServerRequestInterface $request): ResponseInterface
{
    return new InertiaResponse($request, 'Dashboard', ['name' => $user->name]);
}
```

**Option C — `inertia()` global helper:**

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    return inertia($request, 'Dashboard', ['name' => $user->name]);
}
```

All three produce identical responses. `InertiaMiddleware` must be piped before the handler in every case — it is what creates and attaches the `InertiaInterface` instance to the request.

## Redirects

Calling `inertia($request)` without a component returns the raw `InertiaInterface` instance, giving you access to its redirect methods directly from a handler:

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    // Redirect back to the previous URL (302 by default)
    return inertia($request)->back();

    // Inertia-aware redirect — issues a 303 so the client follows up with a GET
    return inertia($request)->redirect('/dashboard');

    // External redirect — forces a full page visit outside the Inertia context
    return inertia($request)->location('https://example.com');
}
```

Flash data can be chained before any redirect so it's available on the next response:

```php
return inertia($request)->flash('success', 'Profile updated.')->back();
```

## Sharing props

Props shared on every Inertia response can be declared in config or set at runtime.

**Config-based sharing** (registered once, applied to every request):

```php
// config/autoload/inertia.global.php
return [
    'inertia' => [
        'shared_data' => [
            // Scalar value shared under a fixed key
            'app.name' => 'My App',

            // FQCN string — resolved from the container.
            // Must implement ProvidesInertiaPropertiesInterface, ArrayableInterface,
            // or be callable (receives the current ServerRequestInterface).
            App\Inertia\SharedAuthProps::class,
        ],
    ],
];
```

**Runtime sharing** inside a middleware or handler (useful for request-scoped data):

```php
inertia($request)->share('flash', $flashMessages);
```

## Testing

The package ships a testing layer for both PHPUnit-based suites and Pest.

### PHPUnit / base `TestCase`

Extend `Builtnoble\Mezzio\Inertia\Testing\TestCase` and boot the application inside `setUp`:

```php
use Builtnoble\Mezzio\Inertia\Testing\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setBasePath()
             ->setContainer()
             ->setConfigProviders([...$this->getConfigProviders(), MyAppConfigProvider::class])
             ->bootApp();
    }

    public function test_dashboard_renders_correct_component(): void
    {
        $response = $this->request()->get('/dashboard');

        $this->assertInertiaOk($response);
        $this->assertInertiaComponent($response, 'Dashboard');
    }
}
```

### Pest

The package automatically registers Pest helpers and custom expectations when Pest is detected.

**Verb helpers** dispatch a request and return a `ResponseInterface`:

```php
it('renders the dashboard', function () {
    $response = get('/dashboard');

    expect($response)
        ->toBeInertiaOk()
        ->toBeInertiaComponent('Dashboard')
        ->toHaveInertiaProps(['name' => 'Amanda']);
});
```

**Fluent builder** for configuring session or headers before dispatching:

```php
it('shows the user profile when logged in', function () {
    $response = withSession(['user_id' => 42])->get('/profile');

    expect($response)
        ->toBeInertiaOk()
        ->toHaveInertiaProp('user.id', 42);
});
```

**`actingAs`-style helpers** can be built on top of `withSession()`:

```php
function actingAs(User $user): PendingInertiaRequest
{
    return request()->withSession(['user_id' => $user->id]);
}

actingAs($user)->get('/dashboard');
```

### Available Pest expectations

| Expectation | Asserts |
|---|---|
| `toBeInertiaOk()` | Status 200 |
| `toBeInertiaFound()` | Status 302 |
| `toBeInertiaSeeOther()` | Status 303 |
| `toBeInertiaConflict()` | Status 409 |
| `toBeInertiaComponent($name)` | Correct component name |
| `toHaveInertiaProp($key, $value)` | Single prop, supports dot-notation |
| `toHaveInertiaProps($subset)` | Multiple props subset |
| `toHaveInertiaVersion($version)` | Asset version in page payload |
