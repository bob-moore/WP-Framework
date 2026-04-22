<?php
/**
 * Script Loader Service Definition
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Services;

use Bmd\WPFramework\ {
	Abstracts,
	Helpers
};
/**
 * Service class for script loading and management
 *
 * Handles registration and enqueuing of JavaScript files
 *
 * @subpackage Services
 */
class ScriptLoader extends Abstracts\Module
{
	/**
	 * Public constructor
	 *
	 * @param UrlResolver      $url_resolver       : instance of the UrlResolver Class.
	 * @param FilePathResolver $file_path_resolver : instance of the FilePathResolver Class.
	 * @param string           $package            : package name, optional.
	 */
	public function __construct( protected UrlResolver $url_resolver, protected FilePathResolver $file_path_resolver, string $package = '' )
	{
		parent::__construct( $package );
	}
	/**
	 * Get script assets from {handle}.asset.php
	 *
	 * @param string             $path : relative path to script.
	 * @param array<int, string> $dependencies : current dependencies passed, if any.
	 * @param string             $version : current version passed, if any.
	 *
	 * @return array<string, mixed>
	 */
	private function scriptAssets( string $path, array $dependencies = [], string $version = '' )
	{
		$asset_file = $this->file_path_resolver->resolve( sprintf( '%s.asset.php', str_ireplace( '.js', '', $path ) ) );
		if ( is_file( $asset_file ) ) {
			$args = include $asset_file;
			$assets = [ 'dependencies' => wp_parse_args( $args['dependencies'], $dependencies ), 'version' => empty( $version ) ? $args['version'] : $version ];
		} else {
			$assets = [ 'dependencies' => $dependencies, 'version' => $version ];
		}
		return $assets;
	}
	/**
	 * Create the data for registering a script
	 *
	 * @param string             $handle : handle to register.
	 * @param string             $src : relative path to script.
	 * @param array<int, string> $dependencies : any set dependencies not in assets file, optional.
	 * @param string|null        $version : version of JS file, optional.
	 * @param string|bool        $in_footer : whether to enqueue in footer, optional.
	 *
	 * @return array<string, mixed>|false
	 */
	public function scriptData( string $handle, string $src, array $dependencies = [], string|null $version = '', string|bool $in_footer = \true ): array|false
	{
		/**
		 * Get full file path
		 */
		$file = $this->file_path_resolver->resolve( $src );
		/**
		 * Bail if local file, but empty
		 */
		if ( is_file( $file ) && ! filesize( $file ) ) {
			return \false;
		}
		/**
		 * Load local assets if local file
		 */
		if ( is_file( $file ) ) {
			$assets = $this->scriptAssets( $src, $dependencies, $version );
			$dependencies = $assets['dependencies'];
			$version = ! empty( $assets['version'] ) ? $assets['version'] : filemtime( $file );
			$src = $this->url_resolver->resolve( $src );
		}
		$valid = str_starts_with( $src, '//' ) || filter_var( $src, \FILTER_VALIDATE_URL );
		if ( ! $valid ) {
			return \false;
		}
		return [ 'handle' => Helpers::hyphenate( $handle ), 'src' => $src, 'dependencies' => apply_filters( "{$handle}_script_dependencies", $dependencies ), 'version' => $version, 'in_footer' => $in_footer ];
	}
	/**
	 * Register a JS file with WordPress
	 *
	 * @param string             $handle : handle to register.
	 * @param string             $src : relative path to script.
	 * @param array<int, string> $dependencies : any set dependencies not in assets file, optional.
	 * @param string|null        $version : version of JS file, optional.
	 * @param string|bool        $in_footer : whether to enqueue in footer, optional.
	 *
	 * @return void
	 */
	public function register( string $handle, string $src, array $dependencies = [], string|null $version = '', string|bool $in_footer = \true ): void
	{
		$script_data = $this->scriptData( $handle, $src, $dependencies, $version, $in_footer );
		if ( ! $script_data ) {
			return;
		}
		wp_register_script( $script_data['handle'], $script_data['src'], $script_data['dependencies'], $script_data['version'], $script_data['in_footer'] );
	}
	/**
	 * Enqueue a script in the build/dist directories
	 *
	 * @param string             $handle : handle to register.
	 * @param string             $src : relative path to script.
	 * @param array<int, string> $dependencies : any set dependencies not in assets file, optional.
	 * @param string|null        $version : version of JS file, optional.
	 * @param string|bool        $in_footer : whether to enqueue in footer, optional.
	 *
	 * @return void
	 */
	public function enqueue( string $handle, string $src, array $dependencies = [], string|null $version = '', string|bool $in_footer = \true ): void
	{
		$script_data = $this->scriptData( $handle, $src, $dependencies, $version, $in_footer );
		if ( ! $script_data ) {
			return;
		}
		wp_enqueue_script( $script_data['handle'], $script_data['src'], $script_data['dependencies'], $script_data['version'], $script_data['in_footer'] );
	}
}
