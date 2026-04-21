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

use Bmd\WPFramework\Abstracts;
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
        if (!is_admin() || !function_exists('get_current_screen') || !get_current_screen()) {
            return \false;
        }
        $screen = get_current_screen();
        return 'post' === $screen->base || 'widgets' === $screen->base || 'customize' === $screen->base || 'site-editor' === $screen->base;
    }
    /**
     * Define current context
     *
     * Builds a chain of case names from most-specific to top-level fallback,
     * then walks the chain applying the context_handler filter at each step.
     * Returns the first override from a filter, or the framework fallback.
     *
     * @since  1.0.0
     * @access public
     *
     * @return \BackedEnum The resolved context handler enum case.
     */
    public function getContext(): \BackedEnum
    {
        $chain = match (\true) {
            $this->isBlockEditor()          => ['EDITOR', 'ADMIN'],
            is_front_page() && !is_home()   => ['FRONTPAGE', 'SINGLE', 'FRONTEND'],
            is_home()                       => ['BLOG', 'ARCHIVE', 'FRONTEND'],
            is_search()                     => ['SEARCH', 'ARCHIVE', 'FRONTEND'],
            is_archive()                    => ['ARCHIVE', 'FRONTEND'],
            is_singular()                   => ['SINGLE', 'FRONTEND'],
            is_404()                        => ['ERROR404', 'FRONTEND'],
            is_admin()                      => ['ADMIN'],
            is_login()                      => ['LOGIN'],
            wp_doing_ajax()                 => ['NONE'],
            wp_doing_cron()                 => ['NONE'],
            default                         => ['FRONTEND'],
        };

        $fallback_name = $chain[ count( $chain ) - 1 ];
        $fallback      = constant( Handlers::class . '::' . $fallback_name );

        foreach ( $chain as $case_name ) {
            $result = apply_filters( "{$this->package}_context_handler", $fallback, $case_name );
            if ( $result !== $fallback ) {
                return $result;
            }
        }

        return $fallback;
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
        if ( '' === $context->value ) {
            return;
        }
        do_action("{$this->package}_dispatch_context_handler", $context->value);
    }
}
