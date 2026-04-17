<?php
/**
 * Service Controller
 *
 * Manages and coordinates core service components of the application,
 * including script loading, style loading, and path resolution services.
 *
 * PHP Version 8.2
 *
 * @package    Bmd_WPFramework
 * @subpackage Controllers
 * @author     Bob Moore <bob@bobmoore.dev>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       https://www.bobmoore.dev
 * @since      1.0.0
 */

namespace Bmd\WPFramework\Controllers;

use Bmd\WPFramework\Services\ServiceLocator,
	Bmd\WPFramework\Services,
	Bmd\WPFramework\Core\Abstracts;

/**
 * Service Controller Class
 *
 * Controls the registration and execution of core application services.
 * Manages essential services like asset loading and path resolution.
 *
 * @subpackage Controllers
 * @since      1.0.0
 */
class ServiceController extends Abstracts\Controller
{
	/**
	 * Get service container definitions
	 *
	 * Defines the core services that should be available in the service container,
	 * including asset loaders and path resolvers.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array<string, mixed> Array of service definitions.
	 */
	public static function getServiceDefinitions(): array
	{
		return [
			Services\ScriptLoader::class     => ServiceLocator::autowire(),
			Services\StyleLoader::class      => ServiceLocator::autowire(),
			Services\FilePathResolver::class => ServiceLocator::autowire(),
			Services\UrlResolver::class      => ServiceLocator::autowire(),
		];
	}
}
