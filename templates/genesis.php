<?php

add_filter( 'wp_query_include_loop', '__return_false' );

do_action( 'wp_query_engine_before_genesis_loop' );

do_action( 'genesis_loop' );

do_action( 'wp_query_engine_after_genesis_loop' );