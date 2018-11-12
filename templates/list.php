<?php

/**
 * Function that will actually output the content, which can be filtered
 */
if( !function_exists( 'wp_query_list_content' ) ) {
	function wp_query_list_content( $template_name, $context, $query, $atts ) {

		$content = sprintf( '<li><a href="%s">%s</a></li>',
			get_the_permalink(),
			get_the_title()
		);

		echo apply_filters( 'wp_query_list_content_output', $content, $template_name, $context, $wp_query, $atts );
	}
}

/**
 * Wrap the UL tag
 */
if( !function_exists( 'wp_query_list_content_wrap_open' ) ) {
	function wp_query_list_content_wrap_open( $template_name, $context, $query, $atts ) {
		echo '<ul class="wp_query_engine">';
	}
}

/**
 * Close the UL tag
 */
if( !function_exists( 'wp_query_list_content_wrap_close' ) ) {
	function wp_query_list_content_wrap_close( $template_name, $context, $query, $atts ) {
		echo '</ul>';
	}
}