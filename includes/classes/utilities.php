<?php

/**
 * The plugin file that controls core wp tweaks and configurations
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wp_query_engine
 */

namespace WPCL\QueryEngine\Classes;

class Utilities {

	/**
	 * Convert comma seperated string to an array of values
	 * @param  [string|array] $string : comma seperated string of values
	 * @return [array]          : Array of values
	 */
	public static function string_to_array( $string ) {
		// Convert to an array
		$args = is_array( $string ) ? $string : explode( ',', $string );
		// Trim and Escape values
		foreach( $args as $index => $arg ) {
			$args[$index] = esc_attr( trim( $arg ) );
		}
		return $args;
	}

	/**
	 * Convert comma seperated string to an array of values
	 * @param  [string|bool] $string : String representation of bool, ie "true" for true
	 * @return [bool]           : Bool equivilant of string passed, or false
	 */
	public static function string_to_bool( $string ) {
		// If already bool, we can bail
		if( $string === true || $string === false ) {
			return $string;
		}
		// Parse string
		switch( strtolower( trim( $string ) ) ) {
			case 'false' :
				$bool = false;
				break;
			case '0' :
				$bool = false;
				break;
			case 'true' :
				$bool = true;
				break;
			case '1' :
				$bool = true;
				break;
			default :
				$bool = $string;
				break;
		}
		return $bool;
	}

	/**
	 * Parse terms (categories, tags, etc) from a string
	 * Must be in the format of an array for wp_query
	 * Allow terms to be passed as a string of names instead of proper id's
	 * @return array
	 */
	public static function string_to_term_id( $terms = '', $term_type = 'category' ) {
	    // Make sure we have an array
	    $terms = self::string_to_array( $terms );
	    // Create empty array to hold IDs
	    $term_ids = array();
	    // Get ids for each
	    foreach( $terms as $term ) {
	    	// See if already an ID
	    	if( is_numeric( $term ) ) {
	    		$term_ids[] = intval( $term  );
	    	}
	    	// Check if looking for author
	    	else if( $term_type === 'author' ) {
	    		// Look for author by slug
	    		$user_object = get_user_by( 'slug', trim( $term ) );
	    		// If failed, try login
	    		if( $user_object === false ) {
	    			$user_object = get_user_by( 'login', trim( $term ) );
	    		}
	    		// If failed, try email
	    		if( $user_object === false ) {
	    			$user_object = get_user_by( 'email', trim( $term ) );
	    		}
	    		// Lastly, assign term
	    		if( $user_object ) {
	    			$term_ids[] = $user_object->ID;
	    		}
	    	}
	    	// If looking for any other term
	    	else {
	    		// Look for id by slug
	    		$term_object = get_term_by( 'slug', trim( $term ), $term_type );
	    		// If failed, try name
	    		if( $term_object === false ) {
	    			$term_object = get_term_by( 'name', trim( $term ), $term_type );
	    		}
	    		// Lastly, assign term
	    		if( $term_object ) {
	    			$term_ids[] = $term_object->term_id;
	    		}
	    	}
	    }
	    return $term_ids;
	}

	public static function string_to_ids( $ids ) {
		if( empty( $ids ) ) {
			return array();
		}
		// Make sure we have an array
		$ids = self::string_to_array( $ids );

		return $ids;
	}

	public static function format_slug( $string ) {
		$string = strtolower($string);
		$string = str_replace( ' ', '_', $string );
		return $string;
	}

	public static function tax_query_from_string( $atts = array() ) {
		// Get default attributes
		$defaults = \WPCL\QueryEngine\Classes\Query::get_default_attributes();
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

	public static function format_tax_query_array( $tax_query = array() ) {
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

	public static function get_child_classes( $path = null ) {
		// Try to create path from called class if no path is passed in
		if( empty( $path ) ) {
			// Use ReflectionClass to get the shortname
			$reflection = new \ReflectionClass( get_called_class() );
			// Attempt to construct to path
			$path = \WPCL\QueryEngine\Plugin::path( sprintf( 'includes/classes/%s/', strtolower( $reflection->getShortName() ) ) );

		}
		// Bail if our path is not a directory
		if( !is_dir( $path ) ) {
			return array();
		}
		// Empty array to hold post types
		$classes = array();
		// Get all files in directory
		$files = scandir( $path );

		// If empty, we can bail
		if( !is_array( $files ) || empty( $files ) ) {
			return array();
		}
		// Iterate over all found files
		foreach( $files as $file ) {
			if( strpos( $file, '.php' ) === false ) {
				continue;
			}
			$classes[] = str_replace( '.php', '', $file );
		}
		// Return child classes;
		return $classes;
	}

	public static function get_post_types() {

		$data = array();

		$post_types_raw = get_post_types( array( 'public' => true ), 'objects' );

		foreach( $post_types_raw as $post_type ) {
			$data[$post_type->name] = $post_type->label;
		}

		return $data;
	}

	public static function get_authors() {

		$data = array();

		// Add author choices
		$users = get_users( array( 'fields' => array( 'user_nicename', 'display_name' ) ) );
		foreach( $users as $user ) {
			$data[ $user->user_nicename ] = $user->display_name;
		}

		return $data;
	}

	public static function get_template_names() {
		$templates = \WPCL\QueryEngine\Classes\FrontEnd::get_templates();
		$templates_names = array();
		foreach( $templates as $name => $path ) {
			$template_names[$name] = $name;
		}
		return $template_names;
	}

	public static function get_taxonomies() {
		$data = array();

		// Get Taxonomies
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

		foreach( $taxonomies as $taxonomy ) {
			// Empty choices array
			$choices = array();
			// Get all the terms
			$terms = get_terms( array( 'taxonomy' => $taxonomy->name, 'hide_empty' => false ) );
			// Construct choices array
			foreach( $terms as $term ) {
				$choices[$term->slug] = $term->name;
			}
			$data[ $taxonomy->name ] = array(
				'label'   => $taxonomy->label,
				'choices' => $choices,
			);
		}

		return $data;
	}

} // end class