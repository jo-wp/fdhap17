<?php

/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */
if (! is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area"><h1>test</h1>
  <?php dynamic_sidebar('sidebar-2'); ?>
</aside><!-- #secondary -->
