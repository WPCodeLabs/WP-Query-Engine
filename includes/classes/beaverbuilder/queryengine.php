<?php

namespace WPCL\QueryEngine\Classes\BeaverBuilder;

use \WPCL\QueryEngine\Classes\Utilities as Utilities;

/**
 * @class QueryEngine
 */
class QueryEngine extends \FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct(array(
			'name'          	=> __( 'WP Query', 'cornerstone' ),
			'description'   	=> __( 'Perform a custom WP_Query', 'cornerstone' ),
			'category'      	=> __( 'Actions', 'cornerstone' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
		));
	}

	/**
	 * Update data before saving
	 *
	 * Formats the settings passed from beaver builder, into a usable settings
	 * array for our query class
	 * @param  [object] $settings : all settings passed from beaver builder
	 * @since version 1.0.0
	 */
	public function update( $settings ) {
	    $settings->atts = array(
	    	'post_type'      => isset( $settings->post_type ) ? $settings->post_type : null,
	    	'template'       => isset( $settings->template ) ? $settings->template : null,
	    	'order'          => isset( $settings->order ) ? $settings->order : null,
	    	'orderby'        => isset( $settings->orderby ) ? $settings->orderby : null,
	    	'offset'         => isset( $settings->offset ) ? $settings->offset : null,
	    	'posts_per_page' => isset( $settings->posts_per_page ) ? $settings->posts_per_page : null,
	    	'pagination'     => isset( $settings->pagination ) ? $settings->pagination : null,
	    	'ignore_sticky_posts' => isset( $settings->ignore_sticky_posts ) ? $settings->ignore_sticky_posts : null,
	    );

	    // /**
	    //  * Set individual posts to either retrieve or not retrieve
	    //  */
	    if( isset( $settings->post_type ) && $settings->post_type !== 'any' ) {
	    	if( isset( $settings->{"match__{$settings->post_type}"} ) && !empty( $settings->{"match__{$settings->post_type}"} ) ) {

	    		if( intval( $settings->{"match__{$settings->post_type}_matching"} ) === 0 ) {
	    			$settings->atts['post__not_in'] = $settings->{"match__{$settings->post_type}"};
	    		}

	    		else if( intval( $settings->{"match__{$settings->post_type}_matching"} ) === 1 ) {
	    			$settings->atts['post__in'] = $settings->{"match__{$settings->post_type}"};
	    		}
	    	}
	    }
	    // /**
	    //  * Special handing for the "all" type
	    //  */
	    else {

	    	$settings->atts['post__in'] = array();

	    	foreach ( \FLBuilderLoop::post_types() as $slug => $type ) {
	    		if( isset( $settings->{"match__{$slug}"} ) && isset( $settings->{"match__{$slug}_matching"} ) ) {

	    			if( intval( $settings->{"match__{$slug}_matching"} ) === 0 ) {
	    				// Get array of excluded id's
	    				$excluded = !empty( $settings->{"match__{$slug}"} ) ? Utilities::string_to_term_id( $settings->{"match__{$slug}"} ) : array();
	    				// Get all content blocks
	    				$posts = get_posts( array(
	    					'numberposts' => -1,
	    					'post_type' => $slug,
	    					'suppress_filters' => true,
	    					'status' => 'publish',
	    					'fields' => 'ids',
	    					'post__not_in' => $excluded,
	    				) );
	    				$settings->atts['post__in'] = array_merge( $settings->atts['post__in'], $posts );
	    			}

	    			else if( intval( $settings->{"match__{$slug}_matching"} ) === 1 && !empty( $settings->{"match__{$slug}"} )  ) {
	    				$settings->atts['post__in'] = array_merge( $settings->atts['post__in'], Utilities::string_to_term_id( $settings->{"match__{$slug}"} ) );
	    			}
	    		}
	    	}
	    }

	    // /**
	    //  * Set taxonomy matching
	    //  */
	    $taxonomies = isset( $settings->post_type ) ? get_object_taxonomies( $settings->post_type ) : array();

	    foreach( $taxonomies as $tax ) {
	    	if( isset( $settings->{"tax__{$tax}"} ) && isset( $settings->{"tax__{$tax}_matching"} ) ) {
	    		// Set to match terms
	    		if( $settings->{"tax__{$tax}_matching"} === 1 && !empty( $settings->{"tax__{$tax}"} ) ) {
	    			$settings->atts["{$tax}__in"] = $settings->{"tax__{$tax}"};
	    		}
	    		// Set NOT to match
	    		else if( $settings->{"tax__{$tax}_matching"} === 0  && !empty( $settings->{"tax__{$tax}"} ) ) {
	    			$settings->atts["{$tax}__not_in"] = $settings->{"tax__{$tax}"};
	    		}
	    		// Set to related
	    		else if( $settings->{"tax__{$tax}_matching"} === 'related' ) {
	    			$settings->atts["{$tax}__in"] = $this->get_related_terms( $settings->{"tax__{$tax}"}, $tax );
	    		}
	    	}
	    }

	    // /**
	    //  * Set Author Matching
	    //  */
	    if( isset( $settings->author ) && isset( $settings->author_matching ) ) {
	    	// Set to match terms
	    	if( intval( $settings->author_matching ) === 1 ) {
	    		$settings->atts['author__in'] = $settings->author;
	    	}
	    	// Set NOT to match
	    	else if( intval( $settings->author_matching ) === 0 ) {
	    		$settings->atts['author__not_in'] = $settings->author;
	    	}
	    }
	    /**
	     * Append our new atts attribute to the settings
	     */
	    return $settings;
	}

	/**
	 * Get Related Terms
	 *
	 * Gets terms related to the current post the query is being performed on
	 * @param  [string] $tax_list comma deliminated list of term ID's to exclude
	 * @param  [string] $tax_slug [description]
	 * @return [array]           array of term ID's to include
	 */
	public function get_related_terms( $tax_list, $tax_slug ) {
		global $post;
		// List of all the {$term} for this post
		$terms 	 = wp_get_post_terms( $post->ID, $tax_slug );
		// Get our string to an array
		$tax_list = !empty( $tax_list ) ? Utilities::string_to_term_id( $tax_list ) : array();
		// Empty array to hold our related ID's
		$related = array();
		// Add each, checking if they are in the list of excluded terms
		foreach ( $terms as $term ) {
			if ( !in_array( $term->term_id, $tax_list ) ) {
				$related[] = $term->term_id;
			}
		}
		return $related;
	}

	/**
	 * Get fields required by filter section
	 * Get all post types and add single post option
	 * Get all taxonomies for each post type and add suggest field
	 * Add all toggle fields for each post type
	 * @return [array] $fields : formatted fields array
	 */
	public function get_filter_fields() {

		$fields = array();

		$post_types = \FLBuilderLoop::post_types();

		// Add post type selection
		$fields['post_type'] = array(
			'type'          => 'select',
			'label'         => __( 'Post Type', 'fl-builder'),
			'default'       => 'any',
			'options'       => array(
				'any' => __( 'Any', 'wpcl_query_engine' ),
			),
			'toggle'        => array(
				'any' => array(
					'fields' => array(),
				),
			),
			'preview'       => array(
				'type'          => 'refresh',
			),
		);

		// Add toggle field to post type selector
		foreach ( $post_types as $slug => $type ) {
			// Add post type as option
			$fields['post_type']['options'][$slug] = $type;
			// Add single post select toggle
			$fields['post_type']['toggle'][$slug] = array(
				'fields' => array( "match__{$slug}" ),
			);
			// Expand all for 'any' post type
			$fields['post_type']['toggle']['any']['fields'][] = "match__{$slug}";
			// Add supported taxonomies
			foreach( get_object_taxonomies( $slug ) as $tax ) {
				$fields['post_type']['toggle'][$slug]['fields'][] = "tax__{$tax}";
				// Expand all for 'any' post type
				$fields['post_type']['toggle']['any']['fields'][] = "tax__{$tax}";
			}

		}

		// Add single post selections
		foreach ( $post_types as $slug => $type ) {
			$fields["match__{$slug}"] = array(
				'type'          => 'suggest',
				'action'        => 'fl_as_posts',
				'data'          => $slug,
				'label'         => $type->label,
				'help'          => sprintf( __( 'Enter a list of %1$s.', 'fl-builder' ), $type->label ),
				'matching'      => true,
				'preview'       => array(
					'type'          => 'refresh',
				),
			);
		}
		// Add taxonomies
		foreach ( $post_types as $slug => $type ) {
			foreach ( \FLBuilderLoop::taxonomies( $slug ) as $tax_slug => $tax ) {
				$fields["tax__{$tax_slug}"] = array(
					'type'          => 'suggest',
					'action'        => 'fl_as_terms',
					'data'          => $tax_slug,
					'label'         => $tax->label,
					'help'          => sprintf( __( 'Enter a list of %1$s.', 'fl-builder' ), $tax->label ),
					'matching'      => true,
					'preview'       => array(
						'type'          => 'refresh',
					),
				);
			}
		}
		// Add authors
		$fields['author'] = array(
			'type'          => 'suggest',
			'action'        => 'fl_as_users',
			'label'         => __( 'Authors', 'fl-builder' ),
			'help'          => __( 'Enter a list of authors usernames.', 'fl-builder' ),
			'matching'      => true,
			'preview'       => array(
				'type'          => 'refresh',
			),
		);
		return $fields;
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		\FLBuilder::register_module( __CLASS__, array(
			'general'       => array( // Tab
			    'title'         => __('General', 'fl-builder'), // Tab title
			    'sections'      => array( // Tab Sections
			        'general'       => array( // Section
			            'title'         => __('General Options', 'fl-builder'), // Section Title
			            'fields'        => array( // Section Fields
			            	'template'   => array(
			            	    'type'          => 'select',
			            	    'label'         => __('Template', 'fl-builder'),
			            	    'default'       => '',
			            	    'options'       => Utilities::get_template_names(),
			            	    'preview'       => array(
			            	    	'type'          => 'refresh',
			            	    ),
			            	),
			            	'posts_per_page'       => array(
			            	    'type'          => 'text',
			            	    'label'         => __( 'Posts Per Page', 'fl-builder' ),
			            	    'default'       => get_option( 'posts_per_page' ),
			            	    'maxlength'     => '4',
			            	    'size'          => '5',
			            	    'description'   => 'Number of posts to display',
			            	    'help'          => 'The number of posts to display. If using pagination, the number of posts to display on each page. Use <em>-1</em> to display all.',
			            	    'preview'       => array(
			            	    	'type'          => 'refresh',
			            	    ),
			            	),
			            	'order' => array(
		        				'type'          => 'select',
		        				'label'         => __( 'Order', 'fl-builder' ),
		        				'default'       => 'DESC',
		        				'options'       => array(
		        					'DESC'          => __( 'Descending', 'fl-builder' ),
		        					'ASC'           => __( 'Ascending', 'fl-builder' ),
		        				),
		        				'preview'       => array(
		        					'type'          => 'refresh',
		        				),
		        			),
		            		'orderby' => array(
		            			'type'          => 'select',
		            			'default'       => 'date',
		            			'label'         => __( 'Order By', 'fl-builder' ),
		            			'options'       => array(
		            				'date'           => __( 'Date', 'fl-builder' ),
		            				'author'         => __( 'Author', 'fl-builder' ),
		            				'comment_count'  => __( 'Comment Count', 'fl-builder' ),
		            				'modified'       => __( 'Date Last Modified', 'fl-builder' ),
		            				'ID'             => __( 'ID', 'fl-builder' ),
		            				'menu_order'     => __( 'Menu Order', 'fl-builder' ),
		            				'meta_value'     => __( 'Meta Value (Alphabetical)', 'fl-builder' ),
		            				'meta_value_num' => __( 'Meta Value (Numeric)', 'fl-builder' ),
		            				'rand'        	 => __( 'Random', 'fl-builder' ),
		            				'title'          => __( 'Title', 'fl-builder' ),
		            			),
		            			'preview'       => array(
		            				'type'          => 'refresh',
		            			),
		            			'toggle'		=> array(
		            				'meta_value' 	=> array(
		            					'fields'		=> array( 'meta_key' ),
		            				),
		            				'meta_value_num' => array(
		            					'fields'		=> array( 'meta_key' ),
		            				),
		            			),
			            	),
			            	'meta_key' => array(
		        				'type'          => 'text',
		        				'label'         => __( 'Meta Key', 'fl-builder' ),
		        				'preview'       => array(
		        					'type'          => 'refresh',
		        				),
		        			),
		        			'offset' => array(
								'type'          => 'text',
								'label'         => _x( 'Offset', 'How many posts to skip.', 'fl-builder' ),
								'default'       => '0',
								'size'          => '4',
								'help'          => __( 'Skip this many posts that match the specified criteria.', 'fl-builder' ),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
			            	'pagination'   => array(
			            	    'type'          => 'select',
			            	    'label'         => __('Use Pagination', 'fl-builder'),
			            	    'default'       => 'false',
			            	    'help'          => __( 'Use with caution, pagination will not work correctly in many cases', 'fl-builder' ),
			            	    'options'       => array(
			            	        'false'      => __('Disable', 'fl-builder'),
			            	        'true'      => __('Enable', 'fl-builder'),
			            	    ),
			            	    'preview'       => array(
			            	    	'type'          => 'refresh',
			            	    ),
			            	),
			            	'ignore_sticky_posts'   => array(
			            	    'type'          => 'select',
			            	    'label'         => __('Ignore Sticky Posts', 'fl-builder'),
			            	    'default'       => 'true',
			            	    'options'       => array(
			            	        'true'      => __('Enable', 'fl-builder'),
			            	        'false'      => __('Disable', 'fl-builder'),
			            	    ),
			            	    'preview'       => array(
			            	    	'type'          => 'refresh',
			            	    ),
			            	),
			            )
			        ),
			        'filter'       => array( // Section
			            'title'         => __('Filters', 'fl-builder'), // Section Title
			            'fields'        => $this->get_filter_fields(),
			        ),
			    )
			)
		));
	}
}
