<?php

namespace WPCL\QueryEngine\Widgets;

class Widget extends \WP_Widget {

	public $widget_id_base;
	public $widget_name;
	public $widget_options;
	public $control_options;

	/**
	 * Constructor, initialize the widget
	 * @param $id_base, $name, $widget_options, $control_options ( ALL optional )
	 * @since 1.0.0
	 */
	public function __construct() {
		// Construct some options
		$this->widget_id_base = 'wpcl_query_engine';
		$this->widget_name    = 'WP Query Engine';
		$this->widget_options = array(
			'classname'   => 'wpcl_query_engine_widget',
			'description' => 'Create custom post queries' );
		// Construct parent
		parent::__construct( $this->widget_id_base, $this->widget_name, $this->widget_options );
	}

	/**
	 * Create back end form for specifying image and content
	 * @param $instance
	 * @see https://codex.wordpress.org/Function_Reference/wp_parse_args
	 * @since 1.0.0
	 */
	public function form( $instance = array() ) {
		$fields = array(
			'title' => array(
				'type'        => 'text',
				'label'       => 'Title',
				'description' => null,
				'value'       => null,
			),
			'hide_title' => array(
				'type'        => 'checkbox',
				'label'       => 'Hide Title?',
				'description' => null,
				'value'       => 'false',
			),
			'posts_per_page' => array(
				'type'        => 'number',
				'label'       => __( 'Posts Per Page', 'wpcl_query_engine' ),
				'description' => __( 'The number of posts to show. Set to -1 to show all.', 'wpcl_query_engine' ),
				'value'       => get_option( 'posts_per_page' ),
			),
			'pagination' => array(
				'type'        => 'checkbox',
				'label'       => 'Use Pagination?',
				'description' => null,
				'value'       => 'true',
			),
			'ignore_sticky_posts' => array(
				'type'        => 'checkbox',
				'label'       => 'Ignore Sticky Posts?',
				'description' => null,
				'value'       => 'false',
			),
			'orderby' => array(
				'type'        => 'select',
				'label'       => 'Order By',
				'description' => null,
				'value'       => null,
				'options'     => array( 'date', 'type', 'name', 'title', 'author', 'ID', 'modified', 'parent', 'comment_count', 'menu_order', 'rand', 'none' ),
			),
			'order' => array(
				'type'        => 'select',
				'label'       => 'Order',
				'description' => null,
				'value'       => null,
				'options'     => array( 'ASC', 'DESC' ),
			),
			'template' => array(
				'type'        => 'select',
				'label'       => 'Order',
				'description' => null,
				'value'       => null,
				'options'     => \WPCL\QueryEngine\Output::get_template_names(),
			),
			'ruleset' => array(
				'type'        => 'template',
				'label'       => 'Rules',
				'description' => null,
				'value'       => array(),
				'template'    => \WPCL\QueryEngine\Common\Plugin::path( 'partials/forms/ruleset.php' ),
			),
		);

		foreach( $fields as $name => $atts ) {
			// Merge values
			$atts['value'] = isset( $instance[$name] ) ? $instance[$name] : $atts['value'];
			// Begin output
			echo '<p>';
			// Print input based on type
			switch( $atts['type'] ) {
				case 'text' :
					printf( '<label for="%s">%s:</label>', $this->get_field_name( $name ), $atts['label'] );
					printf( '<input type="text" class="widefat" id="%s" name="%s" value="%s">', $this->get_field_id( $name ), $this->get_field_name( $name ), $atts['value'] );
					break;
				case 'number' :
					printf( '<label for="%s">%s:</label>', $this->get_field_name( $name ), $atts['label'] );
					printf( '<input type="number" class="widefat" id="%s" name="%s" value="%s">', $this->get_field_id( $name ), $this->get_field_name( $name ), $atts['value'] );
					break;
				case 'select' :
					printf( '<label for="%s">%s:</label>', $this->get_field_name( $name ), $atts['label'] );
					printf( '<select class="widefat" id="%s" name="%s">', $this->get_field_id( $name ), $this->get_field_name( $name ) );
						foreach( $atts['options'] as $option_index => $option ) {
							$option_label = is_int( $option_index ) ? $option : $option_index;
							printf( '<option value="%1$s"%2$s>%3$s</option>', $option, selected( $atts['value'], $option, false ), $option_label );
						}
					echo '</select>';
					break;
				case 'checkbox' :
					printf( '<label for="%1$s"><input type="checkbox" class="widefat" name="%1$s" id="%2$s" value="true" %3$s>&nbsp;<span class="checkbox-label">%4$s</span></label>',
						$this->get_field_name( $name ),
						$this->get_field_id( $name ),
						checked( $atts['value'], 'true', false ),
						$atts['label']
					);
					break;
				case 'template' :
					include $atts['template'];
					break;
				default :
					break;
			}
			if( !empty( $atts['description'] ) ) {
				printf( '<span class="description">%s</span>', $atts['description'] );
			}
			// End output
			echo '</p>';
		}

	}

	/**
	 * Update form values
	 * @param $new_instance, $old_instance
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		// Sanitize / clean values
		$instance = array(
			// Text fields
			'title' => sanitize_text_field( $new_instance['title'] ),
			'posts_per_page' => sanitize_text_field( $new_instance['posts_per_page'] ),
			'orderby' => sanitize_text_field( $new_instance['orderby'] ),
			'order' => sanitize_text_field( $new_instance['order'] ),
			'template' => sanitize_text_field( $new_instance['template'] ),
			// Checkboxes
			'hide_title'           => $new_instance['hide_title'] === 'true' ? 'true' : 'false',
			'pagination'           => $new_instance['pagination'] === 'true' ? 'true' : 'false',
			'ignore_sticky_posts'  => $new_instance['ignore_sticky_posts'] === 'true' ? 'true' : 'false',
		);
		// Ruleset
		foreach( $new_instance['ruleset'] as $rule ) {
			$instance['ruleset'][] = array(
				'type' => sanitize_text_field( $rule['type'] ),
				'bool' => sanitize_text_field( $rule['bool'] ),
				'selector' => sanitize_text_field( $rule['selector'] ),
			);
		}
		// Return values
		return $instance;
	}

	/**
	 * Output widget on the front end
	 * @param $args, $instance
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {
		// Extract the widget arguments ( before_widget, after_widget, description, etc )
		extract( $args );
		// Display before widget args
		echo $before_widget;
		/**
		 * Do widget title output
		 */
		if( !empty( $instance['title'] ) && $instance['hide_title'] !== 'true' ) {
			$instance['title']  = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_id_base );
			// Again check if filters cleared name, in the case of 'dont show titles' filter or something
			$instance['title']  = ( !empty( $instance['title']  ) ) ? $args['before_title'] . $instance['title']  . $args['after_title'] : '';
			// Display Title
			echo $instance['title'];
		}
		/**
		 * Format Query
		 */
		$query = array(
			'posts_per_page'      => intval( $instance['posts_per_page'] ),
			'pagination'          => $instance['pagination'],
			'ignore_sticky_posts' => $instance['ignore_sticky_posts'],
			'orderby'             => $instance['orderby'],
			'order'               => $instance['order'],
			'template'            => $instance['template'],
		);
		foreach( $instance['ruleset'] as $rule ) {
			switch ( $rule['type'] ) {
				case 'post_type':
					$query = $this->format_post_type_rule( $rule, $query );
					break;
				case 'post_tag':
					$query = $this->format_tag_rule( $rule, $query );
					break;
				case 'category':
					$query = $this->format_category_rule( $rule, $query );
					break;
				default:
					$query = $this->format_taxonomy_rule( $rule, $query );
					break;
			}
		}
		/**
		 * Do Output
		 */
		do_action( 'wpcl_query_engine', $query );
		// Display after widgets args
		echo $after_widget;
	} // end widget()

	private function format_category_rule( $rule, $query ) {
		// Get the key
		$key = $rule['bool'] === 'NOT IN' ? 'category__not_in' : 'category__in';
		// Make sure our index is set
		$query[$key] = isset( $query[$key] ) ? $query[$key] : array();
		// Append the rule
		$query[$key][] = $rule['selector'];

		return $query;
	}

	private function format_tag_rule( $rule, $query ) {
		// Get the key
		$key = $rule['bool'] === 'NOT IN' ? 'tag__not_in' : 'tag__in';
		// Make sure our index is set
		$query[$key] = isset( $query[$key] ) ? $query[$key] : array();
		// Append the rule
		$query[$key][] = $rule['selector'];

		return $query;
	}

	private function format_post_type_rule( $rule, $query ) {
		// Make sure our index is set
		$query['post_type'] = isset( $query['post_type'] ) ? $query['post_type'] : array();

		if( $rule['bool'] === 'IN' ) {
			$query['post_type'][] = $rule['selector'];
		}

		return $query;
	}

	private function format_taxonomy_rule( $rule, $query ) {
		// Define what the rule is
		$default_query = array(
			'taxonomy' => $rule['type'],
			'field'    => 'slug',
			'terms'    => array(),
			// 'operator' => 'NOT IN',
			'operator' => $rule['bool'],
		);
		// Make sure our index is set
		$query['tax_query'][$rule['type']] = isset( $query['tax_query'][$rule['type']] ) ? $query['tax_query'][$rule['type']] : $default_query;
		// Merge terms
		$query['tax_query'][$rule['type']]['terms'][] = $rule['selector'];

		return $query;
	}

} // end class