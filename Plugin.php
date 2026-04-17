<?php
/**
 * Plugin bootstrap file
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Mid-West Family <digital@midwestfamilymadison.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: BMD WP Framework
 * Plugin URI:  https://www.midwestfamilymadison.com
 * Description: Generic WordPress framework plugin
 * Version:     6.1.0
 * Author:      Mid-West Family Madison <bob.moore@midwestfamilymadison.com>
 * Author URI:  https://www.midwestfamilymadison.com
 * Requires at least: 6.0
 * Tested up to: 6.3
 * Requires PHP: 8.2
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bmd_wpframework
 */

namespace Bmd\WPFramework;

defined( 'ABSPATH' ) || exit;

try {
    require_once plugin_dir_path( '/vendor/scoped/autoload.php' );
    require_once plugin_dir_path( '/vendor/scoped/scoper-autoload.php' );
    require_once plugin_dir_path( '/vendor/autoload.php' );
    $plugin = new Main();
    $plugin->mount();
} catch ( \Error $e ) {
    error_log( $e->getMessage() );
}