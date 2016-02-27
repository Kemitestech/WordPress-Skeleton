<?php
/* Template Name: terms and conditions */
get_header();
?>
<div class="content-section">
	<div class="container">
		<div class="title-section">
			<div class="row">
				<div class="col-md-12">
					<h2><?php echo the_title(); ?></h2>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
        <?php if ( have_posts() ) :
                while ( have_posts() ) :
                  the_post();
                  the_content();
                endwhile;
              else: ?>
          <p>Sorry Terms and Conditions is not available right now.</p>
        <?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
