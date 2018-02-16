<?php

/**
 * The plugin file that controls the frontend (public) output
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wpcl_query_engine
 */

namespace WPCL\QueryEngine;

class Shortcode extends \WPCL\QueryEngine\Common\Plugin implements \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber {

	/**
	 * Get the shortcode hooks this class subscribes to.
	 * @return array
	 */
	public static function get_shortcodes() {
		return array(
			array( 'wpcl_query_engine' => 'do_shortcode_callback' ),
		);
	}

	public function do_shortcode_callback( $atts = array() ) {
		// Parse shortcode arguments for tax queries
		$atts['tax_query'] = $this->tax_query_from_string();
		// Get query instance
		$query = \WPCL\QueryEngine\Query::get_instance();
		// Open the output buffer
		ob_start();
		// Do the query
		$query->do_query( $atts );
		// Return the content
		return ob_get_clean();
	}

	protected function tax_query_from_string( $atts = array() ) {
		// Get default attributes
		$defaults = \WPCL\QueryEngine\Query::get_default_attributes();
		// Setup Tax Queries
		$tax_queries = array();
		// Construct unknowns as tax query
		foreach( $atts as $attr_name => $attr_value ) {
			// We only want unknowns
			if( array_key_exists( $attr_name, $defaults ) ) {
				continue;
			}
			// if it's a taxonomy already being handled elsewhere, bail
			if( in_array( strtolower( $attr_name ), array( 'category', 'post_tag', 'tag', 'author' ) ) ) {
				continue;
			}
			// Set default
			$temp = array(
				'taxonomy' => $attr_name,
				'operator' => 'IN',
				'terms' => $attr_value,
			);
			// Look for not operator
			if( strpos( $attr_name, '__not_in' ) !== false ) {
				$temp['taxonomy'] = str_replace( '__not_in', '', $attr_name );
				$temp['operator'] = 'NOT IN';
			}
			// Look for in operator
			else if( strpos( $attr_name, '__in' ) !== false ) {
				$temp['taxonomy'] = str_replace( '__in', '', $attr_name );
			}
			// If it's a taxonomy that exists
			if( taxonomy_exists( $temp['taxonomy'] ) ) {
				$tax_queries[] = $temp;
			}
		}
		return $tax_queries;
	}

} // end class