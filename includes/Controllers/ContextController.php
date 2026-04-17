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

use Bmd\WPFramework\Services\ServiceLocator,
	Bmd\WPFramework\Main,
	Bmd\WPFramework\Context,
	Bmd\WPFramework\Core\Interfaces,
	Bmd\WPFramework\Core\Abstracts,
	Bmd\WPFramework\Core\Helpers;

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
		return [
			Context\Archive::class   => ServiceLocator::autowire(),
			Context\Search::class    => ServiceLocator::autowire(),
			Context\Blog::class      => ServiceLocator::autowire(),
			Context\Single::class    => ServiceLocator::autowire(),
			Context\Frontpage::class => ServiceLocator::autowire(),
			Context\Frontend::class  => ServiceLocator::autowire(),
			Context\Admin::class     => ServiceLocator::autowire(),
			Context\Editor::class    => ServiceLocator::autowire(),
			Context\Login::class     => ServiceLocator::autowire(),
		];
	}

	/**
	 * Mount WordPress actions for context handling
	 *
	 * @return void
	 */
	public function mountActions(): void
	{
		add_action( "{$this->package}_dispatch_context_handler", [ $this, 'loadContextHandler' ] );
		add_action( "{$this->package}_mount_context_handler", [ $this, 'mountContextHandler' ] );
	}

	/**
	 * Set the active context handler
	 *
	 * @param Interfaces\ContextHandler $handler Context handler instance.
	 *
	 * @return void
	 */
	// public function setContextHandler( Interfaces\ContextHandler $handler ): void
	// {
	// 	$this->context_handler = $handler;
	// 	do_action( "{$this->package}_mount_context_handler", $this->context_handler );
	// }

	/**
	 * Load a context handler by type
	 *
	 * @param string $handler Context type to load.
	 *
	 * @return void
	 */
	public function loadContextHandler( string $handler ): void
	{
		
		if (
			! Helpers::implements(
				instance_or_class: $handler,
				interface_class: Interfaces\ContextHandler::class
			)
		) {
			return;
		}

		

		$instance = Main::locateService( service_name: $handler );

		if ( $instance && ! is_wp_error( thing: $instance ) ) {
			do_action( "{$this->package}_mount_context_handler", $instance );
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

		// if ( $handler instanceof Context\Editor ) {
		// 	$this->mountEditorHandler( handler: $handler );
		// }

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
	 * Mount editor-specific assets and handlers
	 *
	 * @param Context\Editor $handler Editor context handler instance.
	 *
	 * @return void
	 */
	// public function mountEditorHandler( Context\Editor $handler ): void
	// {
	// 	add_action( 'enqueue_block_editor_assets', [ $handler, 'enqueueBlockAssets' ] );
	// }
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
		add_action( 'login_header', [ $handler, 'openLoginContainer' ], 2 );
		add_filter( 'login_message', [ $handler, 'loginMessage' ] );
		add_action( 'login_footer', [ $handler, 'closeLoginContainer' ], 20 );
	}
}
