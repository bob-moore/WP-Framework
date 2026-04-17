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

use Bmd\WPFramework\Providers,
	Bmd\WPFramework\Services\ServiceLocator,
	Bmd\WPFramework\Core\Abstracts;

use Bmd\WPFramework\Deps\DI\Attribute\Inject,
	Bmd\WPFramework\Deps\Bmd\BlockPresetClasses,
 	Bmd\WPFramework\Deps\Bmd\ResponsiveGridExtension;


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
		return [
			Providers\Context::class => ServiceLocator::autowire(),
		];
	}

	/**
	 * Mount Blocks Provider
	 *
	 * Sets up block-related functionality including styles, stylesheets,
	 * and block presets.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Providers\Blocks $provider Instance of Blocks provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function mountBlocks( Providers\Blocks $provider ): void
	{
		/**
		* Actions
		*/
		add_action( 'init', [ $provider, 'registerBlockStyleSheets' ] );
		add_action( 'init', [ $provider, 'registerBlockStyles' ] );
		/**
		* Filters
		*/
		add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		add_filter( 'get_block_type_variations', [ $provider, 'registerBlockVariations' ], 10, 2 );
		add_filter( 'block_preset_classes', [ $provider, 'registerBlockPresets' ] );
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
	public function mountContext( Providers\Context $provider ): void
	{
		add_action( 'wp', [ $provider, 'dispatch' ], 4 );
		add_action( 'current_screen', [ $provider, 'dispatch' ], 4 );
	}

	/**
	 * Mount Images Provider
	 *
	 * Sets up image-related functionality including theme support
	 * and custom image sizes.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Providers\Images $provider Instance of Images provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function mountImages( Providers\Images $provider ): void
	{
		add_action( 'after_setup_theme', [ $provider, 'addThemeSupport' ] );
		add_action( 'after_setup_theme', [ $provider, 'addImageSizes' ] );
		add_filter( 'post_thumbnail_size', [ $provider, 'thumbnailImageSize' ] );
	}

	/**
	 * Mount Patterns Provider
	 *
	 * Sets up block pattern functionality including categories
	 * and custom patterns.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Providers\Patterns $provider Instance of Patterns provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function mountPatterns( Providers\Patterns $provider ): void
	{
		add_action( 'after_setup_theme', [ $provider, 'registerPatternCategories' ] );
	}

	/**
	 * Mount Editor Provider
	 *
	 * Sets up editor-specific functionality including theme support,
	 * styles, and default colors.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Providers\Editor $provider Instance of Editor provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function mountEditor( Providers\Editor $provider ): void
	{
		add_filter( 'wp_theme_json_data_default', [ $provider, 'defaultColors' ] );
	}

	/**
	 * Mount TemplateParts Provider
	 *
	 * Sets up template parts functionality including custom
	 * template part areas.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Providers\TemplateParts $provider Instance of TemplateParts provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function mountTemplateParts( Providers\TemplateParts $provider ): void
	{
		add_filter( 'default_wp_template_part_areas', [ $provider, 'templatePartAreas' ] );
	}

	/**
	* Mount the Responsive Grid Extension provider
	*
	* @param ResponsiveGridExtension $provider instance of the Responsive Grid Extension middleware
	*
	* @return void
	*/
	#[Inject]
	public function mountResponsiveGridExtension( ResponsiveGridExtension $provider ): void
	{
		add_action( 'enqueue_block_editor_assets', [ $provider, 'enqueueEditorScript' ] );
		add_action( 'wp_enqueue_scripts', [ $provider, 'enqueueFrontendStyle' ] );
		add_filter( 'render_block_core/group', [ $provider, 'processGridBlock' ], 10, 2 );
	}
	/**
	 * Mount the preset block class provider
	 *
	 * @param BlockPresetClasses $provider instance of the preset block class provider
	 *
	 * @return void
	 */
	#[Inject]
	public function mountBlockPresetClasses( BlockPresetClasses $provider ): void
	{
		add_action( 'enqueue_block_editor_assets', [ $provider, 'enqueueEditorScript' ] );
		add_action( 'rest_api_init', [ $provider, 'registerRestRoute' ] );
	}

}