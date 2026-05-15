<?php
/**
 * Blocks Service Definition
 *
 * Manages block-related functionality including styles, variations,
 * and preset classes for WordPress block editor.
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
use Bmd\WPFramework\Services\UrlResolver;
use Bmd\WPFramework\Services\FilePathResolver;

/**
 * Service class for blocks
 *
 * Handles registration and management of block styles, variations,
 * and preset classes for the WordPress block editor.
 *
 * @subpackage Providers
 */
class Blocks extends Abstracts\Module
{
	/**
	 * Array of blocks styles to register
	 *
	 * @var array<string, mixed>
	 */
	protected array $block_styles = [];
	/**
	 * Array of blocks styles to register
	 *
	 * @var array<string, mixed>
	 */
	protected array $block_variations = [];
	/**
	 * Array of block presets to register
	 *
	 * @var array<string, mixed>
	 */
	protected array $block_preset_classes = [];
	/**
	 * Public constructor
	 *
	 * @param FilePathResolver $file_path_resolver : instance of the FilePathResolver Class.
	 * @param UrlResolver      $url_resolver : instance of the UrlResolver Class.
	 * @param string           $package : package name.
	 */
	public function __construct(
		protected FilePathResolver $file_path_resolver,
		protected UrlResolver $url_resolver,
		string $package = ''
	) {
		parent::__construct( $package );
	}
	/**
	 * Glob all blocks and register them
	 *
	 * @return void
	 */
	public function registerBlocks(): void
	{
		$blocks = glob( $this->file_path_resolver->resolve( 'build/blocks/**/block.json' ) );

		foreach ( $blocks as $block ) {
			register_block_type( $block );
		}
	}
}
