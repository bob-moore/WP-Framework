<?php
/**
 * Main app file
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework;

use Bmd\WPFramework\Services\ServiceLocator;
/**
 * Main App Class
 *
 * Defines the service container and mounts the plugin.
 *
 * @subpackage Traits
 */
class Main
{
	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	public const PACKAGE = 'bmd_wp_framework';
	/**
	 * Controllers to register and mount.
	 *
	 * @var array<int, class-string>
	 */
	protected const CONTROLLERS = [
		Controllers\ServiceController::class,
		Controllers\ContextController::class,
		Controllers\ProviderController::class,
	];
	/**
	 * Service Locator, used to set/retrieve services from the DI container.
	 *
	 * @var ServiceLocator|null
	 */
	protected static ?ServiceLocator $service_locator;
	/**
	 * Public constructor
	 *
	 * @param array<string, mixed> $config : optional configuration array.
	 */
	public function __construct( protected array $config = [] )
	{
		if ( ! isset( self::$service_locator ) ) {
			self::$service_locator = new ServiceLocator();
		}
	}
	/**
	 * Set the configuration array
	 *
	 * @param array<string, mixed> $config Configuration array to merge with existing config.
	 */
	public function setConfig( array $config = [] ): void
	{
		$this->config = wp_parse_args(
			args: $config,
			defaults: $this->config ?? []
		);
	}
	/**
	 * Register the configuration array
	 *
	 * @return void
	 */
	private function registerConfig(): void
	{
		self::$service_locator->addDefinitions(
			definitions: wp_parse_args(
				args: $this->config,
				defaults: [
					'config.dir'     => untrailingslashit(
						plugin_dir_path( __DIR__ )
					),
					'config.url'     => untrailingslashit(
						plugin_dir_url( __DIR__ )
					),
					'config.package' => static::PACKAGE,
				]
			)
		);
	}
	/**
	 * Get controller definitions to add to service container
	 *
	 * @return void
	 */
	public function registerControllers(): void
	{
		self::$service_locator->addDefinitions(
			array_reduce(
				static::CONTROLLERS,
				function ( $carry, $controller ) {
					return array_merge( $carry, [ $controller => ServiceLocator::autowire() ] );
				},
				[]
			)
		);
	}
	/**
	 * Fire Mounted action on mount
	 *
	 * @return void
	 */
	public function mount(): void
	{
		/**
		 * Register the configuration and controllers.
		 */
		$this->registerConfig();
		$this->registerControllers();
		/**
		 * Build the service locator.
		 */
		self::$service_locator->build();
		/**
		 * Instantiate the controllers.
		 */
		foreach ( static::CONTROLLERS as $controller ) {
			self::$service_locator->mountService( service: $controller );
		}
	}
	/**
	 * Locate a specific service
	 *
	 * Use primarily by 3rd party interactions to remove actions/filters
	 *
	 * @param string $service_name : name of service to locate.
	 *
	 * @return mixed
	 */
	public static function locateService( string $service_name ): mixed
	{
		if ( ! isset( self::$service_locator ) ) {
			return null;
		}
		$services = [
			trim( $service_name ),
			trim( __NAMESPACE__ . '\\' . $service_name ),
		];
		foreach ( $services as $service ) {
			$resolved = self::$service_locator->getService( service: $service );
			if ( is_wp_error( thing: $resolved ) ) {
				continue;
			}
			return $resolved;
		}
		return null;
	}
}
