<?php
/**
 * Custom scoper file
 *
 * PHP Version 8.1
 *
 * @package PLUGIN_SLUG
 * @author  AUTHOR_NAME <AUTHOR_EMAIL>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://github.com/bob-moore/Devkit-Plugin-Boilerplate
 * @since   1.0.0
 */
declare( strict_types = 1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
/**
 * Create custom scoper config.
 * 
 * Called by wpify/scoper/scoper.php
 *
 * @param array<string, mixed> $config
 *
 * @return array<string, mixed>
 */
function customize_php_scoper_config( array $config ): array
{
	$scoper = new Bmd\WPFramework\Scoper\Package_Scoper();

	$config['exclude-functions'] = array_merge(
		$config['exclude-functions'] ?? [],
		$scoper->getSymbols( 'wordpress', 'functions' ),
		$scoper->getSymbols( 'woocommerce', 'functions' )
	);

	$config['exclude-constants'] = array_merge(
		$config['exclude-constants'] ?? [],
		$scoper->getSymbols( 'wordpress', 'constants' ),
		$scoper->getSymbols( 'woocommerce', 'constants' ),
		['WP_PLUGIN_DIR']
	);

	$config['exclude-classes'] = array_merge(
		$config['exclude-classes'] ?? [],
		$scoper->getSymbols( 'wordpress', 'classes' ),
		$scoper->getSymbols( 'woocommerce', 'classes' )
	);

	$config['exclude-namespaces'] = array_merge(
		$config['exclude-namespaces'] ?? [],
		$scoper->getSymbols( 'wordpress', 'namespaces' ),
		$scoper->getSymbols( 'woocommerce', 'namespaces' )
	);

	return $config;
}
