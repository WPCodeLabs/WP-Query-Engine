<?php
// Get post types
$post_types = get_post_types( array( 'public' => true ) );
// Get Taxonomies
$taxonomies = get_taxonomies();
// Create type array
$rule_type = array_merge( array( 'post_type' => 'post_type' ), $taxonomies );

$atts['value'] = array_values( $atts['value'] );

// var_dump($rule_type);

echo '<div class="wpcl_ruleset_wrapper">';

echo '<style scoped>.wpcl_ruleset_wrapper .ruleset {display: flex; margin-bottom: 1em;} .wpcl_ruleset_wrapper .ruleset .rule {flex:1;}</style>';

printf( '<label for="%s[]">%s:</label>', $this->get_field_name( $name ), $atts['label'] );

printf( '<div class="rulesets" data-ruleset-index="%d">', count( $atts['value'] ) );

foreach( $atts['value'] as $index => $rule ) {

	// Parse rule for defaults
	$default_rule = array(
		'type' => null,
		'bool' => null,
		'selector' => null,
	);
	// Merge single rule with defaults
	$rule = wp_parse_args( $rule, $default_rule );

	echo '<div class="ruleset">';
		// Print Rule type select
		echo '<div class="rule">';
			printf( '<select class="widefat rule_type" id="%2$s[%1$d][type]" name="%3$s[%1$d][type]">', $index, $this->get_field_id( $name ), $this->get_field_name( $name ) );
				foreach( $rule_type as $type => $value ) {
					printf( '<option value="%1$s"%2$s>%1$s</option>', $value, selected( $rule['type'], $value, false ) );
				}
			echo '</select>';
		echo '</div>';
		// Print bool select
		echo '<div class="rule">';
			printf( '<select class="widefat" id="%2$s[%1$d][bool]" name="%3$s[%1$d][bool]">', $index, $this->get_field_id( $name ), $this->get_field_name( $name ) );
				printf( '<option value="IN"%s>IS</option>', selected( $rule['bool'], 'IN', false ) );
				printf( '<option value="NOT IN"%s>IS NOT</option>', selected( $rule['bool'], 'NOT IN', false ) );
			echo '</select>';
		echo '</div>';
		// Print selector select
		$rule_selectors = $rule['type'] === 'post_type' ? $post_types : get_terms( array( 'taxonomy' => $rule['type'], 'hide_empty' => false, 'fields' => 'id=>slug' ) );
		// get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'id=>slug' ) )
		echo '<div class="rule">';
			printf( '<select class="widefat rule_selector" id="%2$s[%1$d][selector]" name="%3$s[%1$d][selector]">', $index, $this->get_field_id( $name ), $this->get_field_name( $name ) );
				foreach( $rule_selectors as $rule_selector => $value ) {
					printf( '<option value="%1$s"%2$s>%1$s</option>', $value, selected( $rule['selector'], $value, false ) );
				}
			echo '</select>';
		echo '</div>';
		echo '<div class="action">';
			echo '<button class="button button-default remove_rule"> - </button>';
		echo '</div>';
	echo '</div>';
}
echo '</div>';

echo '<div class="wpcl_ruleset_footer">';
	echo '<button class="button button-default alignright add_rule">Add Rule</button>';
echo '</div>';

// include template
echo '<script type="text/x-template" id="ruleset_template">';
	echo '<div class="ruleset">';
	// Print Rule type select
	echo '<div class="rule">';
		printf( '<select class="widefat rule_type" id="%s[{{INDEX}}][type]" name="%s[{{INDEX}}][type]">', $this->get_field_id( $name ), $this->get_field_name( $name ) );
			foreach( $rule_type as $type => $value ) {
				printf( '<option value="%1$s">%1$s</option>', $value );
			}
		echo '</select>';
	echo '</div>';
	// Print bool select
	echo '<div class="rule">';
		printf( '<select class="widefat" id="%s[{{INDEX}}][bool]" name="%s[{{INDEX}}][bool]">', $this->get_field_id( $name ), $this->get_field_name( $name ) );
			echo '<option value="IN">IS</option>';
			echo '<option value="NOT IN">IS NOT</option>';
		echo '</select>';
	echo '</div>';
	// Print selector select
	echo '<div class="rule">';
		printf( '<select class="widefat rule_selector" id="%s[{{INDEX}}][selector]" name="%s[{{INDEX}}][selector]">', $this->get_field_id( $name ), $this->get_field_name( $name ) );
			foreach( $post_types as $post_type => $value ) {
				printf( '<option value="%1$s">%1$s</option>', $value );
			}
		echo '</select>';
	echo '</div>';
	echo '<div class="action">';
		echo '<button class="button button-default remove_rule"> - </button>';
	echo '</div>';
	echo '</div>';
echo '</script>';

echo '</div>';