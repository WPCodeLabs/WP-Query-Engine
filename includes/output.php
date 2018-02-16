<?php

/**
 * The plugin file that controls the frontend (public) output
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wpcl_query_engine
 */

namespace WPCL\QueryEngine;

class Output extends \WPCL\QueryEngine\Common\Plugin implements \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber,\WPCL\QueryEngine\Interfaces\Filter_Hook_Subscriber, \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber {

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public static function get_actions() {
		return array(
			array( 'wpcl_query_engine_output' => array( 'do_output', 10, 4 ) ),
			array( 'wpcl_query' => 'do_action_callback' ),
			// array( 'wp_enqueue_scripts' => 'enqueue_scripts' ), // Not enqueing scripts at this time
		);
	}

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public static function get_filters() {
		return array(
			array( 'redirect_canonical' => 'pagination_redirect' ),
		);
	}

	/**
	 * Get the shortcode hooks this class subscribes to.
	 * @return array
	 */
	public static function get_shortcodes() {
		return array(
			array( 'wpcl_query' => 'do_shortcode_callback' ),
		);
	}

	public function do_action_callback( $atts = array() ) {
		// Make sure we have an array
		$atts = (array)$atts;
		// Format the tax query array
		if( isset( $atts['tax_query'] ) && !empty( $atts['tax_query'] ) ) {
			$atts['tax_query'] = $this->format_tax_query_array( (array)$atts['tax_query'] );
		}

		// Get query instance
		$query = \WPCL\QueryEngine\Query::get_instance();
		// Do the query
		$query->do_query( $atts );
	}

	public function do_shortcode_callback( $atts = array() ) {
		// Make sure we have an array
		$atts = (array)$atts;
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

	protected function format_tax_query_array( $tax_query = array() ) {
		// Don't waste the effort if we don't need to
		if( !is_array( $tax_query ) || empty( $tax_query ) ) {
			return array();
		}
		$formatted = array();
		// Loop through each tax_query
		foreach( $tax_query as $index => $query ) {

			// Set default
			$temp = array(
				'taxonomy' => null,
				'operator' => 'IN',
				'terms' => array(),
			);
			// If we already have what we need
			if( is_numeric( $index ) ) {
				$temp = wp_parse_args( $query, $temp );
			}
			// Else see if the index is a taxonomy
			else if( taxonomy_exists( $index ) ) {
				$temp = wp_parse_args( $query, $temp );
				$temp['taxonomy'] = $index;
			}
			// Make sure we have everything we need
			if( empty( $temp['taxonomy'] ) || empty( $temp['terms'] ) ) {
				continue;
			}
			// Add the tax_query
			$formatted[] = $temp;
		}
		return $formatted;
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

	public function do_output( $template_name, $context, $wp_query, $atts ) {
		// Get the template
		$template = $this->get_template_file( $template_name );
		// do pre include action
		if( isset( $template ) && file_exists( $template ) ) {
			include $template;
		}
	}

	private function get_template_file( $template_name ) {
		// Allow themes to force a template in certain scenarios
		$template_name = apply_filters( 'wpcl_query_engine_template', $template_name );
		// Get all templates
		$all_templates = self::get_templates();
		// tack on all lowercase version
		foreach( $all_templates as $original_name => $path ) {
			$all_templates[ strtolower($original_name) ] = $path;
		}
		// If name is set and it exists...
		if( isset( $all_templates[$template_name] ) && file_exists( $all_templates[$template_name] ) ) {
			$template = $all_templates[$template_name];
		} else if( !empty( $template_name ) ) {
			$paths = array(
				$template_name,
				sprintf( '%s.php', $template_name ),
				sprintf( 'templates/%s', $template_name ),
				sprintf( 'templates/%s.php', $template_name ),
			);
			// Search for the template
			foreach( $paths as $path ) {

				if( locate_template( $path ) ) {
					$template = locate_template( $path );
					break;
				}
			}
		}
		// If we still haven't located a template, set default
		if( !isset( $template ) ) {
			$template = $all_templates['Default'];
		}
		// var_dump($template);
		// Return with filter that allows themes to force a template
		return $template;
	}

	public static function get_templates() {
		// Set up defaults
		$default_templates = array(
			'Default' => 'genesis' === basename( TEMPLATEPATH ) ? self::path( 'templates/genesis.php' ) : self::path( 'templates/default.php' ),
			'List'    => self::path( 'templates/list.php' ),
		);
		// Allow themes / plugins / etc to add templates
		$templates = apply_filters( 'wpcl_query_engine_templates', array() );
		// Merge with default template, prepending default to the front of the array
		$templates = wp_parse_args( $default_templates, $templates );
		// Return
		return $templates;
	}

	public static function get_template_names() {
		$templates = self::get_templates();
		$templates_names = array();
		foreach( $templates as $name => $path ) {
			$template_names[] = $name;
		}
		return $template_names;
	}

	public function pagination_redirect( $redirect_url ) {
		global $post;
		if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wpcl_query_engine') ) {
			return false;
		}
		return $redirect_url;
	}

} // end class