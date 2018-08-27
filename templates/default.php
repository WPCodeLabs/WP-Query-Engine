<?php

/**
 * Function that will actually output the content, which can be filtered
 */
if( !function_exists( 'wp_query_default_content' ) ) {
	function wp_query_default_content( $template_name, $context, $wp_query, $atts ) {

		ob_start();

		printf( '<header class="entry-header"><h2 class="entry-title"><a href="%s">%s</a></h2></header>',
			get_the_permalink(),
			get_the_title()
			);

		echo '<div class="entry-content">';

		the_content();

		echo '</div>';

		echo apply_filters( 'wp_query_default_content_output', ob_get_clean(), $template_name, $context, $wp_query, $atts );
	}
}

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

/**
 * Close the article tag
 */
if( !function_exists( 'wp_query_default_content_wrap_close' ) ) {
	function wp_query_default_content_wrap_close( $template_name, $context, $wp_query, $atts ) {
		echo '</article>';
	}
}

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