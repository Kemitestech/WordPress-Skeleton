<?php

if ( isset( $epl_discount_type_dd ) ) {
    echo $epl_discount_type_dd['label'] . ' ' . $epl_discount_type_dd['field'];
}
?>

<div id="epl_discount_data_wrapper">
        <?php echo $discount_fields; ?>

</div>
<?php global $post;
?>

<script>
    
    jQuery(document).ready(function($){
        $('body').on('change', '#autofil_epl_discount_amount', function(){
            
            $('input[name^="_epl_discount_amount"]', $('table#epl_discount_table')).val($(this).val());
            
        });
        $('body').on('change', '#autofil_epl_discount_type', function(){
            
            $('select[name^="_epl_discount_type"]', $('table#epl_discount_table')).val($(this).val());
            
        });
        
        $('#epl_global_discount_type').change(function(){


            var me = $(this);
            var par = me.parent();
            var id = me.prop('id');

            var data = "epl_action=get_discount_fields&epl_controller=epl_global_discount&" + $('form').serialize() ;


            
            events_planner_do_ajax( data, function(r){

                $('#epl_discount_data_wrapper').html(r.html)

            });

             
            return false;

        });
        

 
        var file_frame;
        wp.media.model.settings.post.id = <?php echo $post->ID; ?>; 
        $('body').on('click', '.code_uploader button', function(){

            event.preventDefault();

            if ( file_frame ) {

                // Open frame
                file_frame.open();
                return;
            } else {
                wp.media.model.settings.post.id = <?php echo $post->ID; ?>; 

            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Upload a Living Social file',
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When a file is selected, run a callback.
            file_frame.on( 'select', function() {

                attachment = file_frame.state().get('selection').first().toJSON();

                
                var data =  "epl_action=process_csv&epl_controller=epl_global_discount&post_id="+<?php echo $post->ID; ?> + '&attachment_id=' + attachment.id;

                events_planner_do_ajax( data, function(r){
              
                    $('#epl_discount_data_wrapper').html(r.html)
                    
              
                })
            });


            file_frame.open();
            
        });

        
    });
    
   
   
 
</script>

