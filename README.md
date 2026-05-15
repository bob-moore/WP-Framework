# WP Framework

[![PHPUnit](https://github.com/bob-moore/WP-Framework/actions/workflows/phpunit.yml/badge.svg?branch=main)](https://github.com/bob-moore/WP-Framework/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/bob-moore/WP-Framework/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/bob-moore/WP-Framework/actions/workflows/phpstan.yml)
[![PHPCS](https://github.com/bob-moore/WP-Framework/actions/workflows/phpcs.yml/badge.svg?branch=main)](https://github.com/bob-moore/WP-Framework/actions/workflows/phpcs.yml)

A small Composer library for bootstrapping WordPress plugins and themes with a PHP-DI service container, auto-mounted controllers, context-aware asset loading, and shared path/URL helpers.

The framework is intentionally light: it does not own routing, templating, build tooling, or plugin headers. It gives a package a predictable lifecycle and a few reusable services so feature code can stay in small classes.

## Requirements

- PHP 8.1 or newer
- WordPress
- Composer

## Installation

Install the package with Composer:

```bash
composer require bmd/wp-framework
```

Then load Composer's autoloader from your plugin or theme entrypoint:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
```

## Quick Start

Create an application class that extends `Bmd\WPFramework\Main`, set a package slug, and mount it after WordPress has loaded enough for plugin path and URL helpers to work.

```php
<?php

namespace Acme\Plugin;

use Bmd\WPFramework\Main;

final class App extends Main
{
	public const PACKAGE = 'acme_plugin';
}

( new App(
	[
		'config.dir' => __DIR__,
		'config.url' => plugin_dir_url( __FILE__ ),
	]
) )->mount();
```

`config.package` defaults to `static::PACKAGE`, and the package slug is used to namespace WordPress actions and filters.

## Lifecycle

`Main::mount()` performs the framework boot sequence:

1. Registers configuration values in the service container.
2. Registers the core controllers.
3. Builds the PHP-DI container.
4. Mounts any registered service that implements `Bmd\WPFramework\Interfaces\Controller`.

Controllers extend `Bmd\WPFramework\Abstracts\Controller`. When a controller is mounted, its `mountActions()` and `mountFilters()` methods are called.

```php
<?php

namespace Acme\Plugin\Controllers;

use Bmd\WPFramework\Abstracts\Controller;

final class NoticesController extends Controller
{
	public function mountActions(): void
	{
		add_action( 'admin_notices', [ $this, 'renderNotice' ] );
	}

	public function renderNotice(): void
	{
		// Render your notice.
	}
}
```

## Service Container

Services are registered as PHP-DI definitions. The framework exposes convenience wrappers through `Bmd\WPFramework\Services\ServiceLocator`, including:

- `ServiceLocator::autowire()`
- `ServiceLocator::create()`
- `ServiceLocator::get()`
- `ServiceLocator::factory()`
- `ServiceLocator::decorate()`
- `ServiceLocator::value()`
- `ServiceLocator::string()`

Add your own services or controllers by extending `Main::getServiceDefinitions()`:

```php
<?php

namespace Acme\Plugin;

use Acme\Plugin\Controllers\NoticesController;
use Acme\Plugin\Services\ApiClient;
use Bmd\WPFramework\Main;
use Bmd\WPFramework\Services\ServiceLocator;

final class App extends Main
{
	public const PACKAGE = 'acme_plugin';

	public static function getServiceDefinitions(): array
	{
		return array_merge(
			parent::getServiceDefinitions(),
			[
				ApiClient::class => ServiceLocator::autowire(),
				NoticesController::class => ServiceLocator::autowire(),
			]
		);
	}
}
```

You can retrieve a service after boot with:

```php
$api_client = App::locateService( ApiClient::class );
```

## Configuration

The default configuration entries are:

- `config.package`: the package slug used for framework hooks.
- `config.dir`: the root directory used by `FilePathResolver`.
- `config.url`: the root URL used by `UrlResolver`.

Configuration is filterable before it is added to the container:

```php
add_filter(
	'acme_plugin_config',
	static function ( array $config ): array {
		$config['config.dir'] = plugin_dir_path( __FILE__ );
		$config['config.url'] = plugin_dir_url( __FILE__ );

		return $config;
	}
);
```

## Context Handling

The context provider determines where WordPress currently is and dispatches a chain of context names from most specific to broadest fallback.

Built-in context handlers:

- `ADMIN` maps to `Bmd\WPFramework\Context\Admin`
- `FRONTEND` maps to `Bmd\WPFramework\Context\Frontend`
- `LOGIN` maps to `Bmd\WPFramework\Context\Login`

Context chains include:

- Block editor: `EDITOR`, `ADMIN`
- Front page: `FRONTPAGE`, `SINGLE`, `FRONTEND`
- Blog home: `BLOG`, `ARCHIVE`, `FRONTEND`
- Search results: `SEARCH`, `ARCHIVE`, `FRONTEND`
- Archive: `ARCHIVE`, `FRONTEND`
- Singular content: `SINGLE`, `FRONTEND`
- 404: `ERROR404`, `FRONTEND`
- Admin: `ADMIN`
- Login: `LOGIN`
- Ajax: `AJAX`
- Cron: `CRON`

The first context name that resolves to a registered class implementing `Bmd\WPFramework\Interfaces\ContextHandler` is mounted through the package context hook.

```php
do_action( 'acme_plugin_mount_context', $handler );
```

Built-in handlers enqueue conventional bundles:

- `build/frontend.js` and `build/frontend.css`
- `build/admin.js` and `build/admin.css`
- `build/login.js` and `build/login.css`

## Asset Loading

Context handlers extend `Bmd\WPFramework\Abstracts\ContextHandler`, which provides:

- `enqueueScript( $handle, $path, $dependencies = [], $version = '', $in_footer = true )`
- `enqueueStyle( $handle, $path, $dependencies = [], $version = null, $screens = 'all' )`

Local files are resolved relative to `config.dir` and converted to URLs relative to `config.url`. Empty local files are skipped. Remote URLs and protocol-relative URLs are allowed.

For scripts, a sibling WordPress asset file is detected automatically:

```text
build/frontend.js
build/frontend.asset.php
```

When present, the asset file can provide dependencies and a version generated by WordPress build tooling.

Dependency arrays are filterable per handle:

```php
add_filter(
	'acme_plugin-frontend_script_dependencies',
	static fn ( array $dependencies ): array => array_merge( $dependencies, [ 'wp-i18n' ] )
);

add_filter(
	'acme_plugin-frontend_style_dependencies',
	static fn ( array $dependencies ): array => array_merge( $dependencies, [ 'wp-components' ] )
);
```

## Path And URL Services

The framework registers:

- `Bmd\WPFramework\Services\FilePathResolver`
- `Bmd\WPFramework\Services\UrlResolver`
- `Bmd\WPFramework\Services\ScriptLoader`
- `Bmd\WPFramework\Services\StyleLoader`

Example usage from an autowired service:

```php
<?php

namespace Acme\Plugin\Services;

use Bmd\WPFramework\Services\FilePathResolver;
use Bmd\WPFramework\Services\UrlResolver;

final class Manifest
{
	public function __construct(
		private FilePathResolver $paths,
		private UrlResolver $urls
	) {}

	public function path(): string
	{
		return $this->paths->resolve( 'build/manifest.json' );
	}

	public function url(): string
	{
		return $this->urls->resolve( 'build/manifest.json' );
	}
}
```

## Helpers

`Bmd\WPFramework\Helpers` includes small utility methods for:

- Class, interface, and trait checks: `classUses()`, `className()`, `implements()`, `uses()`, `getTraits()`
- Array handling: `isList()`, `arrayMerge()`
- Value normalization: `truthyFalsy()`
- WordPress plugin checks: `isPluginActive()`
- String formatting: `slugify()`, `hyphenate()`

## Development

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer run phpunit
```

Run static analysis:

```bash
composer run phpstan
```

Run PHPCS:

```bash
composer run phpsniff
```

## Changelog

### 0.2.7 - 2026-05-05

- Changed context mounting to dispatch the resolved context handler instance through the package-level mount action.
- Expanded README documentation for installation, bootstrapping, services, contexts, assets, helpers, and development commands.

### 0.2.6 - 2026-04-30

- Fixed PHP 8.2 deprecation warning from passing null to class_exists() in helper class checks.
