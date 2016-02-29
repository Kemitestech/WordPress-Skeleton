
<p><?php epl_e('Title'); ?> <input class="widefat" name="<?php echo $w->get_field_name( 'title' ); ?>"  type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
<p><?php epl_e('Title URL (optional)'); ?> <input class="widefat" name="<?php echo $w->get_field_name( 'title_url' ); ?>"  type="text" value="<?php echo esc_attr( $title_url ); ?>" /></p>
<p><?php epl_e('Set css class (optional)'); ?> <input class="widefat" name="<?php echo $w->get_field_name( 'css_class' ); ?>"  type="text" value="<?php echo esc_attr( $css_class ); ?>" /></p>

<p><?php epl_e('Include events happening in the next'); ?> <input class="" size="2" name="<?php echo $w->get_field_name( 'days_to_show' ); ?>"  type="text" value="<?php echo esc_attr( $days_to_show ); ?>" /> <?php epl_e('days'); ?>.</p>
<p><?php epl_e('Limit # events'); ?> <input class="" size="2" name="<?php echo $w->get_field_name( 'num_events_to_show' ); ?>"  type="text" value="<?php echo esc_attr( $num_events_to_show ); ?>" /></p>
<p><?php epl_e('Show only:'); ?></p>
<p>
    
<?php echo $tax_filter; ?>


</p>

<p><?php epl_e('Exclude Event IDs (comma separated)'); ?> <input class="widefat" name="<?php echo $w->get_field_name( 'exclude_event_ids' ); ?>"  type="text" value="<?php echo esc_attr( $exclude_event_ids ); ?>" /></p>

<p>
    <?php epl_e('Enable Tooltip?'); ?>
<?php echo $enable_tooltip; ?>


</p>
<p>
    <?php epl_e('Content to show in tooltip'); ?>
<?php echo $content_to_show; ?>


</p>
<p><?php epl_e('# of words in tooltip'); ?> <input class="" size="3" name="<?php echo $w->get_field_name( 'num_words_to_show' ); ?>"  type="text" value="<?php echo esc_attr( $num_words_to_show ); ?>" /></p>
<p>
    <?php epl_e('Class event type display'); ?>
<?php echo $class_display_type; ?>


</p>

<p>
    <?php epl_e('Template'); ?>
<?php echo $template; ?>


</p>
<p>
    <?php epl_e('Thumbnaill Size'); ?>
<?php echo $thumbnail_size; ?>


</p>