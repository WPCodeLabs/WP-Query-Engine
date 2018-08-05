<?php

/**
 * Function that will actually output the content, which can be filtered
 */
if( !function_exists( 'wp_query_default_content' ) ) {
	function wp_query_default_content( $template_name, $context, $wp_query, $atts ) {

		$content = apply_filters( 'the_content', get_the_content() );

		echo apply_filters( 'wp_query_default_content_output', $content, $template_name, $context, $wp_query, $atts );
	}
}
add_action( 'wp_query_default_content', 'wp_query_default_content', 10, 4 );

/**
 * Wrap the content in article tag
 */
if( !function_exists( 'wp_query_default_content_wrap_open' ) ) {
	function wp_query_default_content_wrap_open( $template_name, $context, $wp_query, $atts ) {
		printf( '<article id="post-%s" class="%s">',
			get_the_id(),
			join( ' ', get_post_class( '', get_the_id() ) )
		);
	}
}
add_action( 'wp_query_default_content', 'wp_query_default_content_wrap_open', 5, 4 );

/**
 * Close the article tag
 */
if( !function_exists( 'wp_query_default_content_wrap_close' ) ) {
	function wp_query_default_content_wrap_close( $template_name, $context, $wp_query, $atts ) {
		echo '</article>';
	}
}
add_action( 'wp_query_default_content', 'wp_query_default_content_wrap_close', 15, 4 );

/**
 * Begin our main loop
 */
do_action( 'wp_query_before_default_loop', $template_name, $context, $wp_query, $atts );

if ( $wp_query->have_posts() ) :

	do_action( 'wp_query_before_default_while', $template_name, $context, $wp_query, $atts );

	while ( $wp_query->have_posts() ) : the_post();

		do_action( 'wp_query_default_content', $template_name, $context, $wp_query, $atts );

	endwhile;

	do_action( 'wp_query_after_default_while', $template_name, $context, $wp_query, $atts );

endif;

do_action( 'wp_query_engine_after_default_loop', $template_name, $context, $wp_query, $atts );