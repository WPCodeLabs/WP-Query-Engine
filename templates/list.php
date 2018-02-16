<?php



if ( have_posts() ) :
	echo '<ul class="wpcl_query_engine">';
	while ( have_posts() ) : the_post();
		printf( '<li><a href="%s">%s</a></li>', get_the_permalink(), get_the_title() );
	endwhile;
	echo '</ul>';
endif;

the_posts_pagination();