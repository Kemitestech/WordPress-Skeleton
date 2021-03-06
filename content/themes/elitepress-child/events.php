<?php
/* Template Name: events */
get_header();
?>
<div class="content-section">
	<div class="container">
		<div class="title-section">
			<div class="row">
				<div class="col-md-12">
					<h1><?php echo the_title(); ?></h1>
				</div>
			</div>
			</div>
			<div class="row">
				<div class="col-md-8">
					<?php get_template_part('content',''); ?>
				</div>
					<div class="col-md-3 col-md-offset-1">
			 <?php

				 //Displays sidebar for members with level one access and above(artists to admin level can see the sidebar)
				 if(current_user_can(access_s2member_level1)){
						 if ( is_active_sidebar( 'sidebar_primary' ) ){ //checks if sidebar primary is active then dispolays the sidebar
							dynamic_sidebar( 'sidebar_primary' );
						 }
				 }

				 if ( !bp_is_register_page() && !bp_is_activation_page() && !current_user_can(access_s2member_level1)){//outputs sidebar if current page is not registration or activation

						 if (is_active_sidebar('sidebar_primary_two')){
							 dynamic_sidebar( 'sidebar_primary_two' );
						 }
				 }
			 ?>
		 </div>

			</div>
	</div>
</div>
<?php get_footer(); ?>
