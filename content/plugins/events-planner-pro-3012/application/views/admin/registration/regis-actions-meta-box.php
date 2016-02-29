<?php

//This view is used for the Payment Info meta box and
//the ajax payment info modification
//If called by ajax, a form wrapper is introduced, along with nonce, regis post_ID


wp_nonce_field( 'epl_form_nonce', '_epl_nonce' );

if ( $edit_mode ):
    ?>


<?php endif; ?>
<table class="epl_form_data_table epl_regis_payment_meta_box" cellspacing="0">


    <?php

    //Print the fields
    echo current( $epl_regis_payment_fields );
    ?>


</table>


<?php echo epl_get_send_email_button( $post_ID, $event_id ); ?>
<div><br />
    <?php echo $send_waitlist_approval_email; ?>
    <br /><br />
    <?php echo $waitlist_email_time; ?>
</div>
<?php if ( $regis_notes ): ?>
    <div>

        <?php

        foreach ( $regis_notes as $note ):
            $note = maybe_unserialize( $note->meta_value );
            ?>
            <div style="display: block; background-color: #efefef;padding:2px;margin-bottom: 3px;">
                <?php echo $note['action']; ?> 
                <p style="display: block;margin:0;text-align: right;color:#333;font-size: 0.8em;"><?php echo epl_formatted_date( $note['timestamp'], 'Y-m-d H:i' ); ?></p>
            </div>
        <?php endforeach; ?>

    </div>
<?php endif; ?>


