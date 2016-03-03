<?php
get_header();
get_template_part('index', 'banner');
?>
<!-- Blog Full Width Section -->
<div class="blog-section">
	<div class="container">
		<div class="row">
			<!--Blog Area-->
					<div class="<?php elitepress_post_layout_class(); ?>" >
					<?php 	
					while(have_posts()):the_post();
					global $more;
					$more = 0;
					get_template_part('content',''); 
					endwhile; ?>
					<div class="blog-pagination">
					<?php previous_posts_link( __('Previous','elitepress') ); ?>
					<?php next_posts_link( __('Next','elitepress') ); ?>
					</div>
				</div>
			<!--/Blog Area-->
			<?php get_sidebar(); ?>
		</div>	
	</div>
</div>
<?php get_footer(); ?>
<!-- /Blog Full Width Section -->