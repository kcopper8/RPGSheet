<?php get_header(); ?>

	<div id="primary">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php $format = get_post_format(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				    <?php get_template_part( 'content', 'header' ); ?>

				    <div class="entry-content">
					    <?php
						if ( empty( $format ) && ( ! is_single() || is_search() || is_archive() ) ) {
							if( has_post_thumbnail() ) {
								global $wp_query;
								$size = ( 0 == $wp_query->current_post ) ? 'large' : 'medium';
								$class = ( 0 == $wp_query->current_post ) ? 'alignnone' : 'alignleft';
								echo '<a href="' . get_permalink() . '" class="image-anchor">';
								the_post_thumbnail( $size, array( 'class' => $class . ' img-thumbnail' ) );
								echo '</a>';
							}
							the_excerpt();
						} else {
							the_content( __( 'Read more &rarr;', 'carton' ) );
						}
						?>
				    </div><!-- .entry-content -->
				    <?php
						$rs_type = rs_common_get_post_rs_type();
						$rs_sheet_data = rs_common_get_sheet_data($rs_type);
						$rs_sheet_css_url = RS_PLUGIN_SHEETS_URL . "/$rs_type/" . $rs_sheet_data->css;
						$rs_sheet_html_path = RS_PLUGIN_SHEETS_PATH . "/$rs_type/" . $rs_sheet_data->html;
				    ?>
				    <link rel="stylesheet" type="text/css" href="<?=RS_PLUGIN_ROOT_URL?>styles/style.css">
				    <link rel="stylesheet" type="text/css" href="<?=$rs_sheet_css_url?>">
				    <script type="text/javascript">
						window.rsData = <?php
							$rs_data = get_post_custom_values("rs_data");
							echo(html_entity_decode($rs_data[0]));
						?>;
					</script>
					<div id="rs_sheet">
						<?php 
							include($rs_sheet_html_path); 
						?>
					</div>
				    <?php get_template_part( 'content', 'footer' ); ?>
				</article><!-- #post-<?php the_ID(); ?> -->
			

			<div id="posts-pagination">
				<h3 class="screen-reader-text"><?php _e( 'Post navigation', 'carton' ); ?></h3>
				<?php if ( 'attachment' == get_post_type( get_the_ID() ) ) { ?>
					<div class="previous fl"><?php previous_image_link( false, __( '&larr; Previous Image', 'carton' ) ); ?></div>
					<div class="next fr"><?php next_image_link( false, __( 'Next Image &rarr;', 'carton' ) ); ?></div>
				<?php } else { ?>
					<div class="previous fl"><?php previous_post_link( '%link', __( '&larr; %title', 'carton' ) ); ?></div>
					<div class="next fr"><?php next_post_link( '%link', __( '%title &rarr;', 'carton' ) ); ?></div>
				<?php } ?>
			</div><!-- #posts-pagination -->

			<?php comments_template( '', true ); ?>

		<?php endwhile; // end of the loop. ?>

	</div><!-- #primary.c8 -->

<?php get_footer(); ?>