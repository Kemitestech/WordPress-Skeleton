<?php
/* Template Name: subscription-plan */
get_header();
?>
<div class="content-section">
	<div class="container">
		<div class="title-section">
			<div class="row">
				<div class="col-md-12">
					<h3>My Subscription Plan</h3>
				</div>
			</div>
		 </div>
		 <div class="row">
			<div class="col-md-8">
		 <?php if(current_user_is(s2member_level1)){?>
				<p>Hi there <?php echo do_shortcode('[s2Get constant="S2MEMBER_CURRENT_USER_DISPLAY_NAME" /]'); ?>, you are on a <?php echo do_shortcode('[s2Get constant="S2MEMBER_CURRENT_USER_ACCESS_LABEL" /]'); ?>ship plan.</p>
			    <p>This plan is based on a yearly subscription of £30.</p>
				<h4>Membership Expiration Date</h4>
				<?php echo do_shortcode('[s2Eot date_format="M jS, Y" /]'); ?>
				<h4>I want to unsubscribe</h4>
				<p>Clicking the unsubscribe button handles refunds and cancellations.</p>
				<?php echo do_shortcode('[s2Member-PayPal-Button cancel="1" image="default" output="anchor" /]'); ?>
				<hr>
				<span><?php echo do_shortcode('[bpProfile /]'); ?></span>
		 <?php } ?>
				<?php if(!current_user_is(s2member_level1)){ ?>
					<p>You have no subscription plan</p>
				<?php }?>
			</div>
			<div class="col-md-3 col-md-offset-1">
			<?php
				//Displays sidebar for members with level one access and above(artists to admin level can see the sidebar)
			  if(current_user_can(access_s2member_level1)){
				if ( is_active_sidebar( 'sidebar_primary' ) ) //checks if sidebar primary is active then dispolays the sidebar
				{ dynamic_sidebar( 'sidebar_primary' );	}
			  }
			?>
			</div>
		 </div>
	</div>
</div>
<?php
get_footer();
?>
