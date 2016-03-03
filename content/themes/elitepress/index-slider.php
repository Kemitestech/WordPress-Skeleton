<?php
$elitepress_lite_options=theme_data_setup(); 
$current_options = wp_parse_args(  get_option( 'elitepress_lite_options', array() ), $elitepress_lite_options );
$settings= array();
$settings=array('animation'=>$current_options['animation'],'animationSpeed'=>$current_options['animationSpeed'],'slide_direction'=>$current_options['slide_direction'],'slideshowSpeed' =>$current_options['slideshowSpeed']);
 
wp_register_script('elitepress-slider',get_template_directory_uri().'/js/front-page/slider.js',array('jquery'));
wp_localize_script('elitepress-slider','slider_settings',$current_options);
wp_enqueue_script('elitepress-slider');
?>
<?php if($current_options['home_banner_enabled'] == true) { ?>
<!-- Slider -->
<div class="homepage-mycarousel">
	
		<?php
			if($current_options['slider_radio']=='demo')
			{
			$query_args =''; ?>
        <div class="flexslider">
			<div class="flex-viewport">
			<?php 
				$slider_default_title = array('elitepress by Webriti Themes', 'Clean & Fresh Design', 'elitepress by Webriti Themes', 'Clean & Fresh Design', 'elitepress by Webriti Themes', 'Clean & Fresh Design'); ?>
			

			<ul class="slides">
				<?php for($i=1; $i<=3; $i++) {  ?>
				<li>
					<img class="img-responsive" src="<?php echo WEBRITI_TEMPLATE_DIR_URI; ?>/images/slide/slide<?php echo $i; ?>.jpg">
					<div class="flex-slider-center">
						<div class="slide-text-bg1"><h1><?php echo $slider_default_title[$i-1]; ?></h1></div>
						<div class="slide-text-bg2"><h3><?php _e('Create Fresh Website fast with us!!', 'elitepress'); ?></h3></div>
						<div class="flex-btn-div"><a class="btn1 flex-btn" href="#"><?php _e('Know More', 'elitepress'); ?></a></div>
                    </div>
				</li>
				<?php } ?>
			</ul>
		</div>
		</div>
		<?php }
		else if($current_options['slider_radio']=='category')
		{
			$query_args =array( 'category__in' =>$current_options['slider_select_category'],'ignore_sticky_posts' => 1 );
			}
			$the_query = new WP_Query($query_args);
			?>
		<div class="flexslider">
		 <div class="flex-viewport">
			<ul class="slides">
			<?php
			if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
			 ?>
			<li>
					<?php $default_arg =array('class' => "img-responsive"); 
					if(has_post_thumbnail()):  the_post_thumbnail('', $default_arg);  
					endif; ?>
					<div class="flex-slider-center">
						<div class="slide-text-bg1"><h1><?php the_title();?></h1></div>
						<?php echo get_the_excerpt(); ?>					
                    </div>
			</li>	
				<?php } wp_reset_postdata(); } ?>
			</ul>
		</div>
		</div>
</div>
<?php } ?>
<!-- /Slider -->