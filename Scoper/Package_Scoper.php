<?php
/**
 * Scoper to generate custom PHP-Scoper config
 *
 * PHP Version 8.1

 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @see     https://github.com/OnTheGoSystems/twig-scoper
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Scoper;

class Package_Scoper
{
	/**
	 * Working directory where the prefixer is running.
	 *
	 * @var string
	 */
	protected string $working_dir = '';
	/**
	 * Directory where vendor packages are installed.
	 *
	 * @var string
	 */
	protected string $vendor_dir = '';
	/**
	 * Prefix to use.
	 *
	 * @var string
	 */
	protected string $prefix = '';
	/**
	 * Slug to use.
	 *
	 * @var string
	 */
	protected string $slug = '';
	/**
	 * Collection of symbols to expose.
	 *
	 * @var array<string>
	 */
	protected array $symbols = [];
	/**
	 * Construct new instance and set prefix
	 *
	 */
	public function __construct() {

		$this->setWorkingDirectory( dirname( getcwd(), 2 ) );

		$composer_file = $this->working_dir . '/composer.json';

		$composer_config = json_decode( file_get_contents( $composer_file ), true );

		$this->setVendorDir( $composer_config['config']['vendor-dir'] ?? 'vendor' );

		$this->setPrefix( $composer_config['extra']['wpify-scoper']['prefix'] ?? '' );

		$this->setSlug( $composer_config['extra']['wpify-scoper']['slug'] ?? '' );

		$this->setSymbols( 'wordpress', $this->wpifySymbols( 'wordpress' ) );

		$this->setSymbols( 'woocommerce', $this->wpifySymbols( 'woocommerce' ) );
	}
	/**
	 * Get symbols from WPify scoper
	 *
	 * @param string $key : which symbols to use
	 *
	 * @return array<string, array<string>>
	 */
	protected function wpifySymbols( string $key ): array
	{
		$symbols = [
			'functions'  => [],
			'constants'  => [],
			'classes'    => [],
			'namespaces' => []
		];

		if ( empty( $this->vendor_dir ) || ! is_file( "{$this->vendor_dir}/wpify/scoper/symbols/{$key}.php" ) ) {
			return $symbols;
		}

		$wpify = include "{$this->vendor_dir}/wpify/scoper/symbols/{$key}.php";

		foreach ( $symbols as $key => $symbol ) {
			$symbols[$key] = $wpify["expose-{$key}"] ?? [];
		}

		return $symbols;
	}
	/**
	 * Setter for working directory
	 *
	 * @param string $dir : directory to use.
	 *
	 * @return void
	 */
	public function setWorkingDirectory( string $dir ) {
		$this->working_dir = rtrim( $dir, '/' );
	}
	/**
	 * Setter for vendor directory
	 *
	 * @param string $dir vendor directory to use.
	 *
	 * @return void
	 */
	public function setVendorDir( string $dir ): void {
		$this->vendor_dir = realpath( $this->working_dir . '/' . ltrim( $dir, '/' ) );
	}
	/**
	 * Setter for slug
	 *
	 * @param string $slug : slug to use.
	 *
	 * @return void
	 */
	public function setSlug( string $slug ): void {
		$this->slug = $slug;
	}
	/**
	 * Setter for prefix
	 *
	 * @param string $prefix : prefix to use.
	 *
	 * @return void
	 */
	public function setPrefix( string $prefix ): void
	{
		$this->prefix = $prefix;
	}
	/**
	 * Getter for the prefix.
	 *
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}
	/**
	 * Setter for symbols
	 *
	 * @param string        $key : which symbols to set.
	 * @param array<string> $symbols : symbols to set.
	 *
	 * @return void
	 */
	public function setSymbols( string $key, array $symbols ): void
	{
		$this->symbols[$key] = $symbols;
	}
	/**
	 * Getter for symbols
	 *
	 * @param string $key : which symbols to get.
	 * @param string $type : which type of symbols to get.
	 *
	 * @return array<string>
	 */
	public function getSymbols( string $key, string $type ): array
	{
		return ! empty( $this->symbols[$key][$type] ?? [] ) ? $this->symbols[$key][$type] : [];
	}
	/**
	 * Return patcher function for timber
	 *
	 * @return callable
	 */
	public function timberPatcher(): callable
	{
		return function( string $filePath, string $prefix, string $contents ): string
		{
			$contents = preg_replace(
				"/\(\s*\\'timber\/(\w+)/", "('{$this->slug}_timber/$1", $contents
			);
			
			return $contents;
		};
	}
	/**
	 * Return patcher function for twig
	 *
	 * @return callable
	 */
	public function twigPatcher(): callable
	{
		

		return function ( string $filePath, string $prefix, string $contents ): string
		{
			$twigPrefixer = new Package_Twig_Prefixer( $prefix );
			$contents = $twigPrefixer->patchForAll( $contents );
			$contents = $twigPrefixer->patchForModuleNode( $contents, $filePath );
			$contents = $twigPrefixer->patchForCoreExtension( $contents, $filePath );
			$contents = $twigPrefixer->patchForEnvironment( $contents, $filePath );
			$contents = $twigPrefixer->patchForCallExpression( $contents, $filePath );
			$contents = $twigPrefixer->patchForCaptureNode( $contents, $filePath );

			return $contents;
		};
	}
	/**
	 * Custom array merge function
	 *
	 * @param mixed $custom_array : custom array to merge.
	 * @param mixed $default_array : default array to merge.
	 *
	 * @return mixed
	 */
	public function mergeConfig( mixed $custom_array, mixed $default_array ): mixed
	{
		foreach ( $custom_array as $key => $value ) {
			if ( ! isset( $default_array[ $key ] ) ) {
				$default_array[ $key ] = $value;
			}
			elseif ( is_array( $value ) && is_array( $default_array[ $key ] ) ) {
				$default_array[ $key ] = array_merge( $value, $default_array[ $key ] );
			}
			else {
				$default_array[ $key ] = $value;
			}
		}
		return $default_array;
	}
}
