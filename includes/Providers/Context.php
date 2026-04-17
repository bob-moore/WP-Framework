<?php
/**
 * Context Provider Class
 *
 * Determines and manages the current WordPress context (admin, frontend, editor)
 * and dispatches appropriate handlers.
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @subpackage Providers
 * @author  Bob Moore <bob.moore@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Providers;

use Bmd\WPFramework\Core\Abstracts;
use Bmd\WPFramework\Context\Handlers;

/**
 * Context Provider Class
 *
 * Determines the current WordPress context and dispatches appropriate handlers
 * based on the environment (admin, frontend, editor, etc.).
 *
 * @subpackage Providers
 */
class Context extends Abstracts\Module
{
	/**
	 * Check if we're in the block editor environment
	 *
	 * Determines if the current screen is a block editor interface
	 * (post editor, widgets, site editor, or customizer).
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if in block editor, false otherwise.
	 */
	protected function isBlockEditor(): bool
	{
		if (
			! is_admin()
			|| ! function_exists( 'get_current_screen' )
			|| ! get_current_screen()
		) {
			return false;
		}

		$screen = get_current_screen();
		
		return (
			'post' === $screen->base
			|| 'widgets' === $screen->base
			|| 'customize' === $screen->base
			|| 'site-editor' === $screen->base
		);
	}
	/**
	 * Define current context
	 *
	 * Determines the appropriate context handler based on the current
	 * WordPress environment and query conditions.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return Handlers The determined context handler enum value.
	 */
	public function getContext(): Handlers
	{
		$context = match ( true ) {
			$this->isBlockEditor()         => Handlers::EDITOR,
			is_front_page() && ! is_home() => Handlers::FRONTPAGE,
			is_home()                      => Handlers::BLOG,
			is_search()                    => Handlers::SEARCH,
			is_archive()                   => Handlers::ARCHIVE,
			is_singular()                  => Handlers::SINGLE,
			is_404()                       => Handlers::ERROR404,
			is_admin()                     => Handlers::ADMIN,
			wp_doing_ajax()                => Handlers::NONE,
			wp_doing_cron()                => Handlers::NONE,
			default                        => Handlers::FRONTEND,
		};

		return apply_filters(
			"{$this->package}_context_handler",
			$context
		);
	}
	/**
	 * Dispatch the current context
	 *
	 * Triggers the context handler dispatch action with the determined context.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function dispatch(): void
	{
		$context = $this->getContext();

		if ( Handlers::NONE === $context ) {
			return;
		}

		do_action(
			"{$this->package}_dispatch_context_handler",
			$context->value
		);
	}
}
