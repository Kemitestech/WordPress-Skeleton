<!--Sidebar Area-->
<?php if(is_active_sidebar( 'sidebar_primary' )){ ?>
		
		<div class="col-md-4 hc_sidebar">
		<div class="sidebar-section-right">
			<?php dynamic_sidebar( 'sidebar_primary' );	?>
		</div>
		</div>
<?php } ?>
<!--Sidebar Area-->