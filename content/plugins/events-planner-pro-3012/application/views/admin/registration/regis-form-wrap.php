<div class="epl_section epl_regis_field_wrapper regis_form">


        <div class="epl_header">

            
            <!-- form label -->
        <?php if ( isset( $form_label ) && $form_label != '' ): ?>

            <h1><?php echo $form_label; ?></h1>

        <?php endif; ?>
            <!-- selected ticket name -->


                <!-- form description -->
        <?php if ( isset( $form_descr ) && $form_descr != '' ): ?>

                    <p><?php echo $form_descr; ?></p>

        <?php endif; ?>


                </div>
                <!-- registration form -->
              

        <?php echo $fields; ?>



</div>
<script>
    
    jQuery(document).ready(function($){
        setup_select2('select[id=user_id]');
    });
    
</script>