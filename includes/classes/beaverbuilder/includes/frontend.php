<?php

if( isset( $settings->atts ) ) {
	do_action( 'wp_query', $settings->atts );
}