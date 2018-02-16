<?php

/**
 * The plugin file that controls the frontend (public) output
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wpcl_query_engine
 */

namespace WPCL\QueryEngine;

class Query extends \WPCL\QueryEngine\Common\Plugin {

	public static function get_default_attributes() {
		$default_atts = array(
			'posts_per_page' => get_option( 'posts_per_page' ),
			'category__in' => null,
			'category_not_in' => null,
			'tag__not_in' => null,
			'tag__in' => null,
			'author__in' => null,
			'author__not_in' => null,
			'template' => null,
			'post_type' => array( 'post' ),
			'pagination' => false,
			'ignore_sticky_posts' => true,
			'orderby' => null,
			'order' => null,
			'context' => null,
			'post__not_in' => array(),
			'tax_query' => array(),
			'meta_query' => null,
			'page_num' => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1
		);
		return $default_atts;
	}

	/**
	 * Normalize all shortcode attributes
	 * Orchastrates parsing of each type of shortcode attribute to ensure good values
	 * @since 1.0.0
	 * @access private
	 */
	private function normalize_atts( $atts = array() ) {
		// Define default atts
		$default_atts = self::get_default_attributes();
		// initial merge
		$atts = shortcode_atts( $default_atts, $atts, 'wpcl_query_engine' );
		// Make sure our tax_query is an array
		$atts['tax_query'] = is_array( $atts['tax_query'] ) ? $atts['tax_query'] : array();
		// Sanatize each tax query argument
		$tax_queries = array();
		// Loop over and sanitize each
		foreach( $atts['tax_query'] as $tax_query ) {
			// Bail if required fields aren't set
			if( !isset( $tax_query['taxonomy'] ) || !isset( $tax_query['terms'] ) ) {
				continue;
			}
			$temp = array(
				'taxonomy' => sanitize_text_field( $tax_query['taxonomy'] ),
				'field' => 'term_id',
				'operator' => isset( $tax_query['operator'] ) ? sanitize_text_field( $tax_query['operator'] ) : 'IN',
				'terms' => $this->string_to_term_id( $tax_query['terms'] , $tax_query['taxonomy'] ),
			);
			$tax_queries[] = $temp;
		}
		// Tack on tax queries
		$atts['tax_query'] = $tax_queries;
		// Do some cleanup
		$atts['posts_per_page'] = intval( $atts['posts_per_page'] );
		$atts['ignore_sticky_posts'] = $this->string_to_bool_value( $atts['ignore_sticky_posts'] );
		$atts['pagination'] = $this->string_to_bool_value( $atts['pagination'] );
		$atts['category__in'] = $this->string_to_term_id( $atts['category__in'], 'category' );
		$atts['category_not_in'] = $this->string_to_term_id( $atts['category_not_in'], 'category' );
		$atts['tag__in'] = $this->string_to_term_id( $atts['tag__in'], 'post_tag' );
		$atts['tag__not_in'] = $this->string_to_term_id( $atts['tag__not_in'], 'post_tag' );
		$atts['author__in'] = $this->string_to_term_id( $atts['author__in'], 'author' );
		$atts['author__not_in'] = $this->string_to_term_id( $atts['author__not_in'], 'author' );
		$atts['post_type'] = $this->string_to_array_values( $atts['post_type'] );
		$atts['context'] = $this->format_slug( $atts['context'] );
		// Return
		return $atts;
	}

	private function format_slug( $string ) {
		$string = strtolower($string);
		$string = str_replace( ' ', '_', $string );
		return $string;
	}

	private function string_to_array_values( $string ) {
		// make sure we have an array
		$args = is_array( $string ) ? $string : explode( ',', $string );
		// Trim and Escape values
		foreach( $args as $index => $arg ) {
			$args[$index] = esc_attr( trim( $arg ) );
		}
		return $args;
	}

	private function string_to_bool_value( $string ) {
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
	private function string_to_term_id( $terms = '', $term_type = 'category' ) {
	    // Make sure we have an array
	    $terms = $this->string_to_array_values( $terms );

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
	    			$term_ids[] = $user_object->id;
	    		}
	    	}
	    	// If looking for any other term
	    	else {
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

	private function get_post_ids( $atts = array() ) {
		// Get sticky posts
		$sticky_post_ids = $atts['ignore_sticky_posts'] === false ? get_option( 'sticky_posts', array() ) : array();
		// Adjust posts NOT to get
		$post__not_in = wp_parse_args( $sticky_post_ids, $atts['post__not_in'] );
		// Get regular posts w/ args
		$query_args = array(
			'post_status' => array( 'publish' ),
			'cache_results' => true,
			'posts_per_page' => $atts['posts_per_page'],
			'nopaging' => false,
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'post_type' => $atts['post_type'],
			'fields' => 'ids', // ONLY GET POST ID's, VERY LEAN QUERY
			'orderby' => $atts['orderby'],
			'order' => $atts['order'],
			'tax_query' => $atts['tax_query'],
			'meta_query' => $atts['meta_query'],
		);

		// some conditionalls
		if( isset( $atts['meta_key'] ) && $atts['orderby'] === 'meta_value' ) {
			$query_args['meta_key'] = $atts['meta_key'];
		}
		if( isset( $atts['meta_type'] ) && $atts['orderby'] === 'meta_value' ) {
			$query_args['meta_type'] = $atts['meta_type'];
		}

		$query_args['nopaging'] = $atts['pagination'] === true ? true : false;

		// Flag to get all posts
		if( $atts['posts_per_page'] === -1 ) {
			$query_args['posts_per_page'] = 0;
			$query_args['nopaging'] = true;
		}
		// Tack on additional arguments
		foreach( array( 'category__in', 'category__not_in', 'tag__in', 'tag__not_in', 'author__in', 'author__not_in' ) as $arg ) {
			if( !empty( $atts[$arg] ) ) {
				$query_args[$arg] = $atts[$arg];
			}
		}
		// Empty array to hold our sticky posts
		$sticky_posts = array();
		// Do sticky query
		if( !empty( $sticky_post_ids ) ) {
			$query_args['post__in'] = $sticky_post_ids;
			$sticky_posts = get_posts( $query_args );
		}

		// See how many we have remaining, assuming we aren't getting all
		if( $atts['pagination'] === false && $atts['posts_per_page'] > -1 ) {
			$remaining = $atts['posts_per_page'] - count( $sticky_posts );
			$query_args['posts_per_page'] = $atts['posts_per_page'] > 0 && $remaining > 0 ? $remaining : 'false';

		}
		$query_args['post__in'] = null;
		$query_args['post__not_in'] = $sticky_posts;
		// Run our main query maybe
		$post_ids = get_posts( $query_args );
		// Merge with sticky
		return isset( $sticky_posts ) ? array_merge( $sticky_posts, $post_ids ) : $post_ids;
	}



	private function get_query_args( $atts ) {
		// Get prefilled post ID's
		$query_args = array(
			'post_status' => array( 'publish' ),
			'nopaging' => false,
			'no_found_rows' => true,
			'post__in' => $this->get_post_ids( $atts ),
			'ignore_sticky_posts' => true,
			'post_type' => $atts['post_type'],
			'posts_per_page' => $atts['posts_per_page'],
			// 'orderby' => 'post__in',
		);
		// Force no found posts if no found ID's
		if( empty( $query_args['post__in'] ) ) {
			$query_args['post__in'] = array( 0 );
		}
		// Set Pagination
		if( isset( $atts['pagination'] ) && $atts['pagination'] === true ) {
			$query_args['paged'] = $atts['page_num'];
			$query_args['no_found_rows'] = false;

		}
		if( $atts['posts_per_page'] === -1 ) {
			$query_args['posts_per_page'] = 0;
			$query_args['nopaging'] = true;
		}
		// Send back args
		return $query_args;
	}

	public function do_query( $atts ) {
		// Normalize the atts
		$atts = $this->normalize_atts( $atts );
		// Get the global query
		global $wp_query;
		// Set temporary variable to the global for restoration when we are done
		$original_query = $wp_query;
		// Null the global
		$wp_query = null;
		// Allow atts to be prefilterd
		$atts = apply_filters( 'wpcl_query_engine_args_raw', $atts );
		// Get query args
		$query_args = apply_filters( 'wpcl_query_engine_args', $this->get_query_args( $atts ) );
		// Construct query
		$wp_query = new \WP_Query( $query_args );
		// Do before output action
		do_action( 'wpcl_query_engine_before_output', $wp_query, $atts );
		// Context sensistive before action
		do_action( "wpcl_query_engine_before_{$atts['context']}", $wp_query, $atts );
		// Do output action
		do_action( 'wpcl_query_engine_output', $atts['template'], $atts['context'], $wp_query, $atts );
		// Context sensistive after action
		do_action( "wpcl_query_engine_after_{$atts['context']}", $wp_query, $atts );
		// Do after output action
		do_action( 'wpcl_query_engine_after_output', $wp_query, $atts );
		// Reset Query
		$wp_query = $original_query;
		// Restore original Post Data
		wp_reset_postdata();
	}

	/**
	 * Get query data
	 *
	 * Get all of the data necessary to select query options from a GUI
	 * @return array $data : all query options
	 */
	public static function get_query_options() {
		// Construct initial choices
		$data = array(
			'post_type' => array(
				'label'   => 'Post Type',
				'choices' => array(),
			),
			'author' => array(
				'label'   => 'Author',
				'choices' => array(),
			),
		);
		// Add post type options
		$post_types_raw = get_post_types( array( 'public' => true ), 'objects' );
		foreach( $post_types_raw as $post_type ) {
			$data[ 'post_type' ]['choices'][$post_type->name] = $post_type->label;
		}
		// Add author choices
		$users = get_users( array( 'fields' => array( 'user_nicename', 'display_name' ) ) );
		foreach( $users as $user ) {
			$data[ 'author' ]['choices'][ $user->user_nicename ] = $user->display_name;
		}
		// Add taxonomy choices;
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