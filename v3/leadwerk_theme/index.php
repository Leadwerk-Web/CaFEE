<?php
/**
 * Fallback Template
 *
 * @package Leadwerk_Theme
 */
get_header();
?>
<main id="main" class="wp-block-group">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php
get_footer();
