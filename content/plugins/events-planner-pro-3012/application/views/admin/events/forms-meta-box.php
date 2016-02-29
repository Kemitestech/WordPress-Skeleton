<div class="epl_info">
    <div class="epl_box_content">

        <?php epl_e("These forms will be presented during the registration process.  If you don't need to collect information from additional attendees,
        don't select any forms in the 'Forms for all attendees' section."); ?>
    </div>
</div>

<div class="epl_box">
    <table class="epl_form_data_table epl_regis_form_choices" cellpadding="0" cellspacing="0">
        <?php

        echo current( $epl_forms_fields );
        ?>
    </table>
</div>

<script>
    
    jQuery(document).ready(function($){
        create_sortable('.epl_subform_table tbody');
    });
    
</script>