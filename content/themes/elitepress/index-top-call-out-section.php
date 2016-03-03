<?php $elitepress_lite_options=theme_data_setup();
		$current_options = wp_parse_args(  get_option( 'elitepress_lite_options', array() ), $elitepress_lite_options );

if($current_options['header_call_out_area_enabled']==true){
?>
<!-- Top Callout Section -->
<div class="top-callout-section">
	<div class="container">
		<div class="row">
			<div class="col-md-9">
				<?php if($current_options['header_call_out_title']){ ?>
				<h2><?php echo $current_options['header_call_out_title']; ?></h2>
				<?php }
				if($current_options['header_call_out_description']){ ?>
				<p><?php echo $current_options['header_call_out_description']; ?></p>
				<?php } ?>
			</div>
			<?php if($current_options['header_call_out_btn_text']){ ?>
			<div class="col-md-3">
			<?php if($current_options['header_call_out_btn_link']){ ?>
				<a href="<?php echo $current_options['header_call_out_btn_link']; ?>" <?php if($current_options['header_call_out_btn_link_target'] ==true) { echo "target='_blank'"; } ?> ><?php echo $current_options['header_call_out_btn_text']; ?></a>
			<?php } else { ?>
			<div class="top_call_out_btn_text"><?php echo $current_options['header_call_out_btn_text']; ?></div><?php } ?>
			</div>
			<?php } ?>
			
		</div>
	</div>
</div>
<!-- /Top Callout Section -->
<?php } ?>