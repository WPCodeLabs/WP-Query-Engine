<?php

/**
 * The plugin file that controls core wp tweaks and configurations
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package mdm_wp_cornerstone
 */

namespace WPCL\QueryEngine\Classes;

use \WPCL\QueryEngine\Classes\Utilities as Utilities;

class FrontEnd extends \WPCL\QueryEngine\Plugin implements \WPCL\QueryEngine\Interfaces\Filter_Hook_Subscriber, \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber, \WPCL\QueryEngine\Interfaces\Shortcode_Hook_Subscriber {

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
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public static function get_actions() {
		return array(
			array( 'wp_query_engine_output' => array( 'do_output', 10, 4 ) ),
			array( 'wp_query' => 'do_action_callback' ),
			/**
			 * Default Template Actions
			 *
			 * Defined in templates/default.php
			 */
			array( 'wp_query_default_content' => array( 'wp_query_default_content_wrap_open', 5, 4 ) ),
			array( 'wp_query_default_content' => array( 'wp_query_default_content', 10, 4 ) ),
			array( 'wp_query_default_content' => array( 'wp_query_default_content_wrap_close', 15, 4 ) ),
			/**
			 * List Template Actions
			 *
			 * Defined in templates/list.php
			 */
			array( 'wp_query_list_content' => array( 'wp_query_list_content', 10, 4 ) ),
			array( 'wp_query_before_list_while' => array( 'wp_query_list_content_wrap_open', 10, 4 ) ),
			array( 'wp_query_after_list_while' => array( 'wp_query_list_content_wrap_close', 10, 4 ) ),
			/**
			 * Basic Archive Template Actions
			 *
			 * Defined in templates/basic-archive.php
			 */
			array( 'wp_query_basic_archive_content' => array( 'wp_query_basic_archive_content_wrap_open', 5, 4 ) ),
			array( 'wp_query_basic_archive_content' => array( 'wp_query_basic_archive_content', 10, 4 ) ),
			array( 'wp_query_basic_archive_content' => array( 'wp_query_basic_archive_content_wrap_close', 15, 4 ) ),
		);
	}


	/**
	 * Get the shortcode hooks this class subscribes to.
	 * @return array
	 */
	public static function get_shortcodes() {
		return array(
			array( 'wp_query' => 'do_shortcode_callback' ),
		);
	}

	public function do_action_callback( $atts = array() ) {
		// Make sure we have an array
		$atts = (array)$atts;
		// Parse shortcode arguments for tax queries
		$atts['tax_query'] = Utilities::tax_query_from_string( $atts );
		// Format the tax query array
		if( isset( $atts['tax_query'] ) && !empty( $atts['tax_query'] ) ) {
			$atts['tax_query'] = Utilities::format_tax_query_array( (array)$atts['tax_query'] );
		}
		// Get query instance
		\WPCL\QueryEngine\Classes\Query::do_query( $atts );
	}

	public function do_shortcode_callback( $atts = array() ) {
		// Make sure we have an array
		$atts = (array)$atts;
		// Parse shortcode arguments for tax queries
		$atts['tax_query'] = Utilities::tax_query_from_string( $atts );
		// Open the output buffer
		ob_start();
		// Do the query
		\WPCL\QueryEngine\Classes\Query::do_query( $atts );
		// Return the content
		return ob_get_clean();
	}

	public function do_output( $template_name, $context, $wp_query, $atts ) {
		// Get the template
		$template = $this->get_template_file( $template_name, $context );
		// do pre include action
		if( isset( $template ) && file_exists( $template ) ) {
			include $template;
		}
	}

	private function get_template_file( $template_name, $context = '' ) {
		// Allow themes to force a template in certain scenarios
		$template_name = apply_filters( 'wp_query_engine_template', $template_name, $context );
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
		// Return with filter that allows themes to force a template
		return $template;
	}

	public static function get_templates() {
		// Set up defaults
		$default_templates = array(
			'Default'       => self::path( 'templates/default.php' ),
			'List'          => self::path( 'templates/list.php' ),
			'Basic Archive' => self::path( 'templates/basic-archive.php' ),
		);
		// Add standard genesis loop file
		if( 'genesis' === basename( TEMPLATEPATH ) ) {
			$default_templates['Genesis Loop'] = self::path( 'templates/genesis.php' );
		}
		// Allow themes / plugins / etc to add templates
		$templates = apply_filters( 'wp_query_engine_templates', $default_templates );
		// Return
		return $templates;
	}

	public function pagination_redirect( $redirect_url ) {
		global $post;
		if( is_a( $post, 'WP_Post' ) ) {
			if( has_shortcode( $post->post_content, 'wp_query') ) {
				return false;
			}
		}
		return $redirect_url;
	}

} // end class