<?php

if ( isset( $epl_pay_types ) ) {
    echo $epl_pay_types['label'];
    echo $epl_pay_types['field'];
}
?>

<div id="epl_pay_profile_fields_wrapper" class="meta_box_content rounded_corners">
    <?php if ( isset( $epl_pay_profile_fields ) ): ?>
        <?php echo current( ( array ) $epl_pay_profile_fields ); ?>
    <?php endif; ?>

</div>
<?php

global $post;



?>
<script>
    
    jQuery(document).ready(function($){
        
        var file_frame;
        $('body').on('click', '.epl_file_upload_trigger', function(){


            event.preventDefault();

            var me = $(this);
            if ( file_frame ) {

                // Open frame
                file_frame.open();
                return;
            } 

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'PEM File Upload',
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When a file is selected, run a callback.
            file_frame.on( 'select', function() {

                attachment = file_frame.state().get('selection').first().toJSON();
                me.val(attachment.id);

            });


            file_frame.open();
            
        });
        
    });
    
</script>