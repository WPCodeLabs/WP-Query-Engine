<?php

/**
 * Define the query field for v5
 * @link    http://midwestfamilymarketing.com
 * @since   1.0.0
 * @package wpcl_query_engine
 */

namespace WPCL\QueryEngine\Acf;
// Use our query class
use \WPCL\QueryEngine\Query as Query;

class V5 extends \acf_field {

	public function __construct() {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		$this->name = 'wpcl_query_engine';
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		$this->label = __('WP Query', 'wpcl_query_engine');
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		$this->category = 'relational';
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		$this->defaults = array(
			'font_size'	=> 14,
		);

		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/
		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'TEXTDOMAIN'),
		);
		// do not delete!
    	parent::__construct();
	}


	/**
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {

		$values = array(
			'posts_per_page' => get_option( 'posts_per_page' ),
			'category__in' => null,
			'category_not_in' => null,
			'tag__not_in' => null,
			'tag__in' => null,
			'author__in' => null,
			'author__not_in' => null,
			'template' => null,
			'post_type' => null,
			'pagination' => false,
			'ignore_sticky_posts' => true,
			'orderby' => null,
			'order' => null,
			'context' => null,
			'tax_query' => array(),
		);
		// Merge with field values
		$values = wp_parse_args( $field['value'], $values );
		// Transform to search parameters
		$search_parameters = $this->update_search_parameters( $values );
		// Get query option data
		$query_options = Query::get_query_options();
		// Reconstruct array for type choices
		$type_choices = array();

		foreach( $query_options as $type => $data ) {
			$type_choices[$type] = $data['label'];
		}

		echo '<div class="wpcl_query_fields">';

		/**
		 * Include all of our set fields
		 */
		include \WPCL\QueryEngine\Common\Plugin::path( 'includes/acf/inputs/form.php' );

		echo '<span class="label">Query Rules</span>';
		// Open our rule section
		echo '<section class="wpcl_rules_wrapper">';
			// Open the container
			echo '<div class="rule-container">';

			// Include fieldset for each rule
			if( empty( $search_parameters ) ) {
				echo '<p class="notice notice-warning wpcl-notice">No Query Rules Have Been Setup</p>';
			} else {
				echo '<p class="notice notice-warning wpcl-notice hidden">No Query Rules Have Been Setup</p>';
				foreach( $search_parameters as $index => $param ) {
					include \WPCL\QueryEngine\Common\Plugin::path( 'includes/acf/inputs/single_rule.php' );
				}
			}
			// Close the container
			echo '</div>';
			// Add button
			echo '<button class="button button-primary wpcl-button pull-right" data-action="add">Add Query Rule</button>';
			// Include our javascript template
			echo '<script id="wpcl-field-template" type="text/template">';
				$index = '{{INDEX}}';
				$param = array( 'type' => 'post_type', 'operator' => false, 'selection' => array() );
				include \WPCL\QueryEngine\Common\Plugin::path( 'includes/acf/inputs/single_rule.php' );
			echo '</script>';
		// Close our rule section
		echo '</section>';

		echo '</div>';

	}

	function input_admin_enqueue_scripts() {
		// Enqueue our styles
		wp_enqueue_style( 'wpcl_query_engine_acf', plugin_dir_url( WPCL_QUERY_ENGINE_PLUGIN ) . 'styles/dist/admin.min.css' );
		// Enqueue the necessary JS
		wp_enqueue_script( 'wpcl_query_engine_acf', plugin_dir_url( WPCL_QUERY_ENGINE_PLUGIN ) . 'scripts/admin.js', array('jquery') );
		// Localize the script for query option values
		wp_localize_script( 'wpcl_query_engine_acf', 'wpcl_query_engine_acf', array( 'query_options' => Query::get_query_options() ) );
	}

	protected function update_search_parameters( $values ) {
		$search_parameters = array();
		// Set up catgories
		if( !empty( $values['category__in'] ) ) {
			foreach( $values['category__in'] as $category ) {
				$search_parameters[] = array(
					'type' => 'category',
					'operator' => 'IN',
					'selection' => $category,
				);
			}
		}
		if( !empty( $values['category_not_in'] ) ) {
			foreach( $values['category_not_in'] as $category ) {
				$search_parameters[] = array(
					'type' => 'category',
					'operator' => 'NOT IN',
					'selection' => $category,
				);
			}
		}
		// Set up tags
		if( !empty( $values['tag__in'] ) ) {
			foreach( $values['tag__in'] as $tag ) {
				$search_parameters[] = array(
					'type' => 'post_tag',
					'operator' => 'IN',
					'selection' => $tag,
				);
			}
		}
		if( !empty( $values['tag__not_in'] ) ) {
			foreach( $values['tag__not_in'] as $tag ) {
				$search_parameters[] = array(
					'type' => 'post_tag',
					'operator' => 'NOT IN',
					'selection' => $tag,
				);
			}
		}
		// Set up tags
		if( !empty( $values['author__in'] ) ) {
			foreach( $values['author__in'] as $author ) {
				$search_parameters[] = array(
					'type' => 'author',
					'operator' => 'IN',
					'selection' => $author,
				);
			}
		}
		if( !empty( $values['author__not_in'] ) ) {
			foreach( $values['author__not_in'] as $author ) {
				$search_parameters[] = array(
					'type' => 'author',
					'operator' => 'NOT IN',
					'selection' => $author,
				);
			}
		}
		// Set up post types
		if( !empty( $values['post_type'] ) ) {
			foreach( $values['post_type'] as $post_type ) {
				$search_parameters[] = array(
					'type' => 'post_type',
					'operator' => 'IN',
					'selection' => $post_type,
				);
			}
		}
		if( !empty( $values['tax_query'] ) ) {
			foreach( $values['tax_query'] as $tax => $tax_query ) {
				foreach( $tax_query['terms'] as $term ) {
					$search_parameters[] = array(
						'type' => $tax,
						'operator' => $tax_query['operator'],
						'selection' => $term,
					);
				}
			}
		}
		return $search_parameters;
	}

	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	function update_value( $value, $post_id, $field ) {
		// empty set of new values to merge with
		$new_values = array(
			'category__in' => array(),
			'category_not_in' => array(),
			'tag__not_in' => array(),
			'tag__in' => array(),
			'author__in' => array(),
			'author__not_in' => array(),
			'post_type' => array(),
			'tax_query' => array(),
		);
		foreach( $value['search_parameters'] as $parameter ) {
			switch ( $parameter['type'] ) {
				case 'category':
					$operator = $parameter['operator'] === 'IN' ? 'category__in' : 'category_not_in';
					$new_values[$operator][] = $parameter['selection'];
					break;
				case 'post_tag':
					$operator = $parameter['operator'] === 'IN' ? 'tag__in' : 'tag__not_in';
					$new_values[$operator][] = $parameter['selection'];
					break;
				case 'author':
					$operator = $parameter['operator'] === 'IN' ? 'author__in' : 'author__not_in';
					$new_values[$operator][] = $parameter['selection'];
					break;
				case 'post_type':
					$new_values['post_type'][] = $parameter['selection'];
					break;
				default:
					if( !isset( $new_values['tax_query'][$parameter['type']] ) ) {
						$new_values['tax_query'][$parameter['type']] = array( 'operator' => 'IN', 'terms' => array() );
					}
					// Add our values
					$new_values['tax_query'][$parameter['type']]['operator'] = $parameter['operator'];
					$new_values['tax_query'][$parameter['type']]['terms'][] = $parameter['selection'];
					# code...
					break;
			}
		}
		// Unset all empty values
		unset( $value['search_parameters'] );
		// return the values
		return array_filter( array_merge( $value, $new_values ) );

	}

}