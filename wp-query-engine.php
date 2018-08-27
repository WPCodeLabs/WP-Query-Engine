<?php
/**
 * The plugin bootstrap file
 * This file is read by WordPress to generate the plugin information in the plugin admin area.
 * This file also defines plugin parameters, registers the activation and deactivation functions, and defines a function that starts the plugin.
 * @link    https://github.com/WPCodeLabs/WP-Query-Engine
 * @since   1.0.0
 * @package wp_query_engine
 *
 * @wordpress-plugin
 * Plugin Name: WP Query Engine
 * Plugin URI:  http://docs.wpcodelabs.com/wp-query-engine/
 * Description: A plugin for querying and displaying any type of post from WordPress using a shortcode
 * Version:     1.0.1
 * Author:      WP Code Labs
 * Author URI:  https://www.wpcodelabs.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp_query_engine
 */

define( 'WP_QUERY_ENGINE_PLUGIN', __FILE__ );

// If this file is called directly, abort
if ( !defined( 'WPINC' ) ) {
    die( 'Bugger Off Script Kiddies!' );
}

/**
 * Class autoloader
 * Do some error checking and string manipulation to accomodate our namespace
 * and autoload the class based on path
 * @since 1.0.0
 * @see http://php.net/manual/en/function.spl-autoload-register.php
 * @param (string) $className : fully qualified classname to load
 */
function wp_query_engine_autoload_register( $className ) {
	// Reject it if not a string
	if( !is_string( $className ) ) {
		return false;
	}
	// Check and make damned sure we're only loading things from this namespace
	if( strpos( $className, 'WPCL\QueryEngine' ) === false ) {
		return false;
	}
	// Replace backslashes
	$className = strtolower( str_replace( '\\', '/', $className ) );
	// Ensure there is no slash at the beginning of the classname
	$className = ltrim( $className, '/' );
	// Replace some known constants
	$className = str_ireplace( 'WPCL/QueryEngine/', '', $className );
	// Append full path to class
	$path  = sprintf( '%1$sincludes/%2$s.php', plugin_dir_path( __FILE__ ), $className );
	// include the class...
	if( file_exists( $path ) ) {
		include_once( $path );
	}
}


/**
 * Kick off the plugin
 * Check PHP version and make sure our other funcitons will be supported
 * Register autoloader function
 * Register activation & deactivation hooks
 * Create an install of our controller
 * Finally, Burn Baby Burn...
 */
function run_wp_query_engine() {
	// If version is less than minimum, register notice
	if( version_compare( '5.3.0', phpversion(), '>=' ) ) {
		// Deactivate plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// Print message to user
		wp_die( 'Irks! WP Query Engine requires minimum PHP v5.3.0 to run. Please update your version of PHP.' );
	}
	// Register Autoloader
	spl_autoload_register( 'wp_query_engine_autoload_register' );
	// Load the text domain
	load_plugin_textdomain( 'wp_query_engine', false, dirname( __FILE__ ) . '/languages/' );
	// Instantiate our plugin
	$plugin = \WPCL\QueryEngine\Plugin::get_instance();
	// Test our plugin
	$plugin->burn_baby_burn();
}
run_wp_query_engine();