<div class="epl_info">
    <?php epl_e('If a transaction is successful, this page will automatically refresh.'); ?>
    
</div>

<form id="admin_cc_form">

    <div class="epl_cart_wrapper">
        <div class="epl_section epl_regis_field_wrapper">

            <!-- registration form -->
            <fieldset class="epl_fieldset">

                <?php if ( epl_get_element( 'field', $_f['_epl_cc_first_name'] ) ): ?>
                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_first_name'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_first_name'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_first_name'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_last_name'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_last_name'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_last_name'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_last_name'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_address_num'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_address_num'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_address_num'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_address_num'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_address'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_address'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_address'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_address'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_city'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_city'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_city'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_city'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_state'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_state'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_state'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_state'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_zip'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_zip'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_zip'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_zip'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_country'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_country'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_country'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_country'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_phone'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_phone'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_phone'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_phone'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_email'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_email'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_email'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_email'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_card_type'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_card_type'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_card_type'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_card_type'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_num'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_num'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_num'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_num'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_cvv'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_cvv'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_cvv'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_cvv'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_exp_month'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_exp_month'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_exp_month'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_exp_month'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>
                <?php if ( epl_get_element( 'field', $_f['_epl_cc_exp_year'] ) ): ?>

                    <div  class="row_wrapper clearfix">
                        <?php echo epl_get_element( 'label', $_f['_epl_cc_exp_year'] ); ?>
                        <div class="field_wrapper">
                            <?php echo epl_get_element( 'field', $_f['_epl_cc_exp_year'] ); ?>

                            <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_exp_year'] ); ?></small>
                        </div>
                    </div>

                <?php endif; ?>

            </fieldset>

            <div id="cc_response_message"></div>
            
            <div  class="row_wrapper clearfix">
                <input type="hidden" name="gateway_id" value="<?php echo $gateway_id; ?>" />
                <input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
                <input type="submit" name="submit" value="Process Payment" />
            </div>
        </div>
    </div>
</form>