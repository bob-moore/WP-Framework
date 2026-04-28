<?php
/**
 * Context Controller
 *
 * Manages different WordPress context handlers (frontend, admin, editor)
 * and their respective asset loading.
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Controllers;

use Bmd\WPFramework\ {
	Services\ServiceLocator,
	Main,
	Context,
	Interfaces,
	Abstracts,
	Helpers
};
/**
 * Controls the registration and execution of context-specific handlers
 *
 * @subpackage Controllers
 */
class ContextController extends Abstracts\Controller
{
	/**
	 * Get definitions for the service container
	 *
	 * Registers all context handlers and their aliases in the service container
	 *
	 * @return array<string, mixed>
	 */
	public static function getServiceDefinitions(): array
	{
		$context_handlers = [];
		foreach ( Context\Handlers::cases() as $handler ) {
			if ( ! empty( $handler->value ) ) {
				$context_handlers[ $handler->value ] = ServiceLocator::autowire();
				$context_handlers[ $handler->name ]  = $handler->value;
			}
		}
		return $context_handlers;
	}
	/**
	 * Mount WordPress actions for context handling
	 *
	 * @return void
	 */
	public function mountActions(): void
	{
		add_action( "{$this->package}_dispatch_context_handler", [ $this, 'loadContextHandler' ] );
	}
	/**
	 * Load a context handler by type
	 *
	 * @param array<int, string> $contexts Context name chain to resolve, most-specific first.
	 *
	 * @return void
	 */
	public function loadContextHandler( array $contexts ): void
	{
		foreach ( $contexts as $context ) {
			$handler = Main::locateService( service_name: $context );

			if ( ! Helpers::implements( instance_or_class: $handler, interface_class: Interfaces\ContextHandler::class ) ) {
				continue;
			}

			$slug = Helpers::slugify( $handler );
			add_action( "{$slug}_mount", [ $this, 'mountContextHandler' ] );
			Main::locateService( service_name: $handler );
			break;
		}
	}
	/**
	 * Mount the appropriate context handler based on type
	 *
	 * @param Interfaces\ContextHandler $handler The context handler to mount.
	 *
	 * @return void
	 */
	public function mountContextHandler( Interfaces\ContextHandler $handler ): void
	{
		if ( $handler instanceof Context\Frontend ) {
			$this->mountFrontendHandler( handler: $handler );
		}
		if ( $handler instanceof Context\Admin ) {
			$this->mountAdminHandler( handler: $handler );
		}
		if ( $handler instanceof Context\Login ) {
			$this->mountLoginHandler( handler: $handler );
		}
	}
	/**
	 * Mount frontend-specific assets and handlers
	 *
	 * @param Context\Frontend $handler Frontend context handler instance.
	 *
	 * @return void
	 */
	public function mountFrontendHandler( Context\Frontend $handler ): void
	{
		add_action( 'wp_enqueue_scripts', [ $handler, 'enqueueAssets' ] );
	}
	/**
	 * Mount admin-specific assets and handlers
	 *
	 * @param Context\Admin $handler Admin context handler instance.
	 *
	 * @return void
	 */
	public function mountAdminHandler( Context\Admin $handler ): void
	{
		add_action( 'admin_enqueue_scripts', [ $handler, 'enqueueAssets' ] );
	}
	/**
	 * Mount login-specific assets and handlers
	 *
	 * @param Context\Login $handler Login context handler instance.
	 *
	 * @return void
	 */
	public function mountLoginHandler( Context\Login $handler ): void
	{
		add_action( 'login_enqueue_scripts', [ $handler, 'enqueueAssets' ] );
	}
}
