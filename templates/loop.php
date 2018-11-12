<?php


do_action( "wp_query_{$template_name}_setup", $template_name, $context, $query, $atts );
/**
 * Begin our main loop
 */
do_action( "wp_query_before_{$template_name}_loop", $template_name, $context, $query, $atts );

if ( $query->have_posts() ) :

	do_action( "wp_query_before_{$template_name}_while", $template_name, $context, $query, $atts );

	while ( $query->have_posts() ) : $query->the_post();

		do_action( "wp_query_{$template_name}_content", $template_name, $context, $query, $atts );

	endwhile;

	do_action( "wp_query_after_{$template_name}_while", $template_name, $context, $query, $atts );

endif;

do_action( "wp_query_after_{$template_name}_loop", $template_name, $context, $query, $atts );
/**
 * End our main loop
 */
do_action( "wp_query_{$template_name}_teardown", $template_name, $context, $query, $atts );