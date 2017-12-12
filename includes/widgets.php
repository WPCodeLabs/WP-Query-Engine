<?php

/**
 * The plugin file that controls the widget hooks
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wp_query_engine
 */

namespace WPCL\QueryEngine;

class Widgets extends \WPCL\QueryEngine\Common\Plugin implements \WPCL\QueryEngine\Interfaces\Action_Hook_Subscriber {

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public static function get_actions() {
		return array(
			array( 'widgets_init' => 'add_widgets' ),
			array( 'admin_enqueue_scripts' => 'enqueue_scripts' ),
		);
	}

	public function enqueue_scripts() {
		if( !function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if( $screen->id === 'widgets' ) {
			// Register all public scripts, including dependencies
			wp_register_script( sprintf( '%s_admin', self::$name ), self::url( 'scripts/admin.js' ), array( 'jquery' ), self::$version, true );
			// Enqueue public script
			wp_enqueue_script( sprintf( '%s_admin', self::$name ) );

			$localized_args = array();

			// Add Taxonomies to localized args
			$taxonomies = get_taxonomies();

			foreach( $taxonomies as $name => $taxonomy ) {
				$localized_args[$name] = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'id=>slug' ) );
				// $localized_args[$name] = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'slug' ) );
			}
			// Add Post Types to localized args
			$localized_args['post_type'] = get_post_types( array( 'public' => true ) );


			// Localize public script
			wp_localize_script( sprintf( '%s_admin', self::$name ), sprintf( '%s_admin', self::$name ), $localized_args );
		}
	}

	public function add_widgets() {
		register_widget( '\\WPCL\\QueryEngine\\Widgets\\Widget' );
	}

} // end class