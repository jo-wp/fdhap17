<?php 

/**
 * The template for displaying the container page
 *
 * Template name: Container
 * 
 */

get_header();
?>
<div class="site-main">
<?php 
if ( have_posts() ) :
  while ( have_posts() ) : the_post();
     the_content();
  endwhile;
endif;
?>
</div>
<?php
get_footer();
?>