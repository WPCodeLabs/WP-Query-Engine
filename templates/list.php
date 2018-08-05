<?php

/**
 * Function that will actually output the content, which can be filtered
 */
if( !function_exists( 'wp_query_list_content' ) ) {
	function wp_query_list_content( $template_name, $context, $wp_query, $atts ) {

		$content = sprintf( '<li><a href="%s">%s</a></li>',
			get_the_permalink(),
			get_the_title()
		);

		echo apply_filters( 'wp_query_list_content_output', $content, $template_name, $context, $wp_query, $atts );
	}
}
add_action( 'wp_query_list_content', 'wp_query_list_content', 10, 4 );

/**
 * Wrap the UL tag
 */
if( !function_exists( 'wp_query_list_content_wrap_open' ) ) {
	function wp_query_list_content_wrap_open( $template_name, $context, $wp_query, $atts ) {
		echo '<ul class="wp_query_engine">';
	}
}
add_action( 'wp_query_before_list_while', 'wp_query_list_content_wrap_open', 10, 4 );

/**
 * Close the UL tag
 */
if( !function_exists( 'wp_query_list_content_wrap_close' ) ) {
	function wp_query_list_content_wrap_close( $template_name, $context, $wp_query, $atts ) {
		echo '</ul>';
	}
}
add_action( 'wp_query_after_list_while', 'wp_query_list_content_wrap_close', 10, 4 );

/**
 * Begin our main loop
 */
do_action( 'wp_query_before_list_loop', $template_name, $context, $wp_query, $atts );

if ( $wp_query->have_posts() ) :

	do_action( 'wp_query_before_list_while', $template_name, $context, $wp_query, $atts );

	while ( $wp_query->have_posts() ) : the_post();

		do_action( 'wp_query_list_content', $template_name, $context, $wp_query, $atts );

	endwhile;

	do_action( 'wp_query_after_list_while', $template_name, $context, $wp_query, $atts );

endif;

do_action( 'wp_query_engine_after_list_loop', $template_name, $context, $wp_query, $atts );