<?php 

/**
 * Template Name: Search First
 */
get_header();
?>

<?php echo facetwp_display( 'facet', 'classement' ); ?>
<div style="display:none"><?php echo facetwp_display( 'template', 'listing' ); ?></div>
<button class="fwp-submit" data-href="/listing/">Submit</button>

<?php get_footer(); ?>