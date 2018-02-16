<?php
/**
 * The plugin bootstrap file
 * This file is read by WordPress to generate the plugin information in the plugin admin area.
 * This file also defines plugin parameters, registers the activation and deactivation functions, and defines a function that starts the plugin.
 * @link    https://github.com/WPCodeLabs/WP-Query-Engine
 * @since   1.0.0
 * @package wpcl_query_engine
 *
 * @wordpress-plugin
 * Plugin Name: WP Query Engine
 * Plugin URI:  https://github.com/WPCodeLabs/WP-Query-Engine
 * Description: A plugin for querying and displaying any type of post from WordPress
 * Version:     0.1.0
 * Author:      WP Code Labs
 * Author URI:  http://www.wpcodelabs.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wpcl_query_engine
 */

define( 'WPCL_QUERY_ENGINE_PLUGIN', __FILE__ );

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
function wpcl_query_engine_autoload_register( $className ) {
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
 * Get plugin file
 *
 * Small helper function to get main plugin file
 * @since 1.0.0
 */
function wpcl_query_engine_get_plugin_file() {
	return __FILE__;
}

/**
 * Activation function
 * Currently we aren't doing anything during activation, but leaving this stub
 * here for future ease of implementation
 */
function activate_wpcl_query_engine() {
}

/**
 * Deactivation function
 * Currently we aren't doing anything during deactivate, but leaving this stub
 * here for future ease of implementation
 */
// function deactivate_wpcl_query_engine() {

// }

/**
 * Kick off the plugin
 * Check PHP version and make sure our other funcitons will be supported
 * Register autoloader function
 * Register activation & deactivation hooks
 * Create an install of our controller
 * Finally, Burn Baby Burn...
 */
function run_wpcl_query_engine() {
	// If version is less than minimum, register notice
	if( version_compare( '5.3.0', phpversion(), '>=' ) ) {
		// Deactivate plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// Print message to user
		wp_die( 'Irks! WP Query Engine requires minimum PHP v5.3.0 to run. Please update your version of PHP.' );
	}
	// Register Autoloader
	spl_autoload_register( 'wpcl_query_engine_autoload_register' );
	// Register activation hook
	register_activation_hook( __FILE__, 'activate_wpcl_query_engine' );
	// Register our output class
	call_user_func( array( '\\WPCL\\QueryEngine\\Output', 'register' ) );
	// Register Advanced Custom Fields Support
	call_user_func( array( '\\WPCL\\QueryEngine\\Acf', 'register' ) );

	// call_user_func( array( '\\WPCL\\QueryEngine\\Widgets', 'register' ) );
}
run_wpcl_query_engine();