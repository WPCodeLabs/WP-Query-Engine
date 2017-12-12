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
			array( 'wpcl_query_engine_output' => 'do_output' ),
			array( 'wpcl_query_engine' => 'do_action_callback' ),
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
			array( 'wpcl_query_engine' => 'do_shortcode_callback' ),
		);
	}

	public function enqueue_scripts() {
		// Register all public scripts, including dependencies
		wp_register_script( sprintf( '%s_public', self::$name ), self::url( 'scripts/public.js' ), array( 'jquery' ), self::$version, true );
		// Enqueue public script
		if( is_active_widget( '', '', 'wpcl_query_engine' ) ) {
			wp_enqueue_script( sprintf( '%s_public', self::$name ) );
		}
	}

	public function do_action_callback( $atts = array() ) {
		$query = new \WPCL\QueryEngine\Query();
		$query->do_query( $atts );
	}

	public function do_shortcode_callback( $atts = array() ) {
		ob_start();
		$this->do_action_callback( $atts );
		return ob_get_clean();
	}

	public function do_output( $template_name ) {
		$template = $this->get_template_file( $template_name );
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

	public function get_template_names() {
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