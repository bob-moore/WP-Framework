<?php

/**
 * Provider Controller
 *
 * Manages and coordinates various WordPress functionality providers,
 * including blocks, context handling, images, patterns, editor settings,
 * and template parts.
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

use Bmd\WPFramework\Providers, Bmd\WPFramework\Services\ServiceLocator, Bmd\WPFramework\Abstracts;
use DI\Attribute\Inject;
/**
 * Provider Controller Class
 *
 * Controls the registration and execution of WordPress functionality providers.
 * Manages various aspects of WordPress including blocks, contexts, images,
 * patterns, editor settings, and template parts.
 *
 * @subpackage Controllers
 * @since      1.0.0
 */
class ProviderController extends Abstracts\Controller
{
    /**
     * Get service container definitions
     *
     * Defines the providers that should be available in the service container.
     *
     * @since  1.0.0
     * @access public
     *
     * @return array<string, mixed> Array of service definitions.
     */
    public static function getServiceDefinitions(): array
    {
        return [Providers\Context::class => ServiceLocator::autowire()];
    }
    /**
     * Mount Context Provider
     *
     * Sets up context-aware functionality for both frontend and admin areas.
     *
     * @since  1.0.0
     * @access public
     *
     * @param Providers\Context $provider Instance of Context provider.
     *
     * @return void
     */
    #[Inject]
    public function mountContext(Providers\Context $provider): void
    {
        add_action('wp', [$provider, 'dispatch'], 4);
        add_action('current_screen', [$provider, 'dispatch'], 4);
    }
}
