<?php
/* Template Name: event gallery */
get_header();
?>
<div class="content-section">
	<div class="container">
      <div class="title-section">
  			<div class="row">
  				<div class="col-md-12">
  					<h2>Events Gallery</h2>
  				</div>
  			</div>
  		</div>
      <div class="row">
  			<div class="col-md-12">
  				<?php echo do_shortcode('[mpp-list-gallery component=groups component_id=24 per_page=10 column=2 view="grid"]'); ?>
  			</div>
  		</div>
  </div>
</div>
<?php get_footer(); ?>
