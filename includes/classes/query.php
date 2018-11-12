<?php

/**
 * The plugin file that controls core wp tweaks and configurations
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package mdm_wp_cornerstone
 */

namespace WPCL\QueryEngine\Classes;

use \WPCL\QueryEngine\Classes\Utilities as Utilities;

class Query extends \WPCL\QueryEngine\Plugin {

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
			'offset' => 0,
			'context' => null,
			'post__in' => array(),
			'post__not_in' => array(),
			'tax_query' => array(),
			'meta_query' => array(),
			'page_num' => !empty( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : get_query_var( 'paged', 1 ),
			'options' => array(),
		);
		return $default_atts;
	}

	/**
	 * Normalize all shortcode attributes
	 * Orchastrates parsing of each type of shortcode attribute to ensure good values
	 * @since 1.0.0
	 * @access private
	 */
	private static function normalize_atts( $atts = array() ) {
		$atts_raw = $atts;
		// Define default atts
		$default_atts = self::get_default_attributes();
		// initial merge
		$atts = shortcode_atts( $default_atts, $atts, 'wp_query_engine' );
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
				'terms' => Utilities::string_to_term_id( $tax_query['terms'] , $tax_query['taxonomy'] ),
			);
			$tax_queries[] = $temp;
		}
		// Tack on tax queries
		$atts['tax_query'] = $tax_queries;
		// Do some cleanup
		$atts['posts_per_page']      = intval( $atts['posts_per_page'] );
		$atts['offset']              = intval( $atts['offset'] );
		$atts['ignore_sticky_posts'] = Utilities::string_to_bool( $atts['ignore_sticky_posts'] );
		$atts['pagination']          = Utilities::string_to_bool( $atts['pagination'] );
		$atts['category__in']        = Utilities::string_to_term_id( $atts['category__in'], 'category' );
		$atts['category_not_in']     = Utilities::string_to_term_id( $atts['category_not_in'], 'category' );
		$atts['tag__in']             = Utilities::string_to_term_id( $atts['tag__in'], 'post_tag' );
		$atts['tag__not_in']         = Utilities::string_to_term_id( $atts['tag__not_in'], 'post_tag' );
		$atts['author__in']          = Utilities::string_to_term_id( $atts['author__in'], 'author' );
		$atts['author__not_in']      = Utilities::string_to_term_id( $atts['author__not_in'], 'author' );
		$atts['post_type']           = Utilities::string_to_array( $atts['post_type'] );
		$atts['context']             = Utilities::format_slug( $atts['context'] );
		$atts['post__in']            = Utilities::string_to_ids( $atts['post__in'] );
		$atts['post__not_in']        = Utilities::string_to_ids( $atts['post__not_in'] );

		foreach( $atts_raw as $attribute => $value ) {
			if( isset( $atts[$attribute] ) ) {
				continue;
			}
			$atts['options'][$attribute] = $value;
		}
		// Return
		return $atts;
	}

	private static function get_post_ids( $atts = array() ) {
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
			'post__in' => $atts['post__in'],
			'offset' => $atts['offset'],
		);
		// some conditionalls
		if( isset( $atts['meta_key'] ) && ( $atts['orderby'] === 'meta_value' || $atts['orderby'] === 'meta_value_num' ) ) {
			$query_args['meta_key'] = $atts['meta_key'];
		}
		if( isset( $atts['meta_type'] ) && ( $atts['orderby'] === 'meta_value' || $atts['orderby'] === 'meta_value_num' ) ) {
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
		$query_args['post__in'] = $atts['post__in'];
		$query_args['post__not_in'] = $post__not_in;

		// Run our main query maybe
		$post_ids = get_posts( $query_args );
		// Merge with sticky
		$posts = isset( $sticky_posts ) ? array_merge( $sticky_posts, $post_ids ) : $post_ids;
		return $posts;
	}

	/**
	 * Get query arguments for main query
	 *
	 * Uses post__in from another query that gets all post id's that will be queried
	 * This is necessary to short circuit some of the sticky post functionality if desired.
	 * @param  [array] $atts shortcode/action attributes
	 * @return [array]       complete query arguments
	 */
	private static function get_query_args( $atts ) {
		// Get prefilled post ID's
		$query_args = array(
			'post_status' => array( 'publish' ),
			'nopaging' => false,
			'no_found_rows' => true,
			'post__in' => self::get_post_ids( $atts ),
			'ignore_sticky_posts' => true,
			'post_type' => $atts['post_type'],
			'posts_per_page' => $atts['posts_per_page'],
			'orderby' => 'post__in',
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

	public static function do_query( $atts ) {
		// Allow atts to be prefilterd
		$atts = apply_filters( 'wp_query_engine_args_raw', $atts );
		// Normalize the atts
		$atts = self::normalize_atts( $atts );
		// Get query args
		$query_args = apply_filters( 'wp_query_engine_args', self::get_query_args( $atts ) );
		// Construct query
		$query = new \WP_Query( $query_args );
		// Do before output action
		do_action( 'wp_query_engine_before_output', $query, $atts );
		// Context sensistive before action
		if( !empty( $atts['context'] ) ) {
			do_action( "wp_query_engine_before_{$atts['context']}", $query, $atts );
		}
		// Do output action
		do_action( 'wp_query_engine_output', $atts['template'], $atts['context'], $query, $atts );
		// Context sensistive after action
		if( !empty( $atts['context'] ) ) {
			do_action( "wp_query_engine_after_{$atts['context']}", $query, $atts );
		}
		// Do after output action
		do_action( 'wp_query_engine_after_output', $query, $atts );
		// Restore original Post Data
		wp_reset_postdata();
		// Restore the original query data
		wp_reset_query();
	}
} // end class