<?php

/**
 * The plugin file that controls the ACF integration
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wpcl_query_engine
 */

namespace WPCL\QueryEngine;

class Acf extends \WPCL\QueryEngine\Common\Plugin implements \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber {

	/**
	 * Get the action hooks this class subscribes to.
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_actions() {
		return array(
			array( 'acf/include_field_types' => 'include_field_types' ),
		);
	}

	function include_field_types( $version = false ) {
		new \WPCL\QueryEngine\Acf\V5();
	}

} // end class