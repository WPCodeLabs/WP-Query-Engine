<?php

// Make sure that all default actions are hooked
// add_action( 'genesis_entry_header', 'genesis_do_post_format_image', 4 );
// add_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
// add_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
// add_action( 'genesis_entry_header', 'genesis_do_post_title' );
// add_action( 'genesis_entry_header', 'genesis_post_info', 12 );

// add_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
// add_action( 'genesis_entry_content', 'genesis_do_post_content' );
// add_action( 'genesis_entry_content', 'genesis_do_post_content_nav', 12 );
// add_action( 'genesis_entry_content', 'genesis_do_post_permalink', 14 );

// add_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
// add_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
// add_action( 'genesis_entry_footer', 'genesis_post_meta' );

// add_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 );
// add_action( 'genesis_after_entry', 'genesis_get_comments_template' );

// Do an action to allow loop adjustments

do_action( 'wpcl_query_engine_before_genesis_loop' );

do_action( 'genesis_loop' );

do_action( 'wpcl_query_engine_after_genesis_loop' );

