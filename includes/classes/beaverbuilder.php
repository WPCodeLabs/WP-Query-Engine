<?php

/**
 * The plugin file that controls the admin functions
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package mdm_wp_cornerstone
 */

namespace WPCL\QueryEngine\Classes;

class BeaverBuilder extends \WPCL\QueryEngine\Plugin implements \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber {


	/**
	 * Get the action hooks this class subscribes to.
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_actions() {
		return array(
			array( 'init' => 'setup_addon' ),
		);
	}

	public function setup_addon() {
		if( class_exists( 'FLBuilder' ) ) {
			// Instantiate our addon
			$bbmodule = new \WPCL\QueryEngine\Classes\BeaverBuilder\QueryEngine();
			// Register it with beaver builder
			$bbmodule->register_module();
		}
	}
} // end class