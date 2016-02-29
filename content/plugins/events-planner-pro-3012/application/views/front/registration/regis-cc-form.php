<div class="epl_regis_attendee_wrapper">
<div id="" class="epl_regis_field_wrapper epl_section regis_form">

        <div class="header">
            <legend style="white-space: nowrap;"><?php epl_e( 'Billing Information' ); ?></legend>
        </div>

            <?php if ( epl_get_element( 'field', $_f['_epl_cc_first_name'] ) ): ?>
                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_first_name'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_first_name'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_first_name'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_last_name'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_last_name'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_last_name'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_last_name'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_address_num'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_address_num'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_address_num'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_address_num'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_address'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_address'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_address'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_address'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_city'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_city'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_city'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_city'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_state'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_state'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_state'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_state'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_zip'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_zip'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_zip'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_zip'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_country'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_country'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_country'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_country'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_phone'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_phone'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_phone'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_phone'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_email'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_email'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_email'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_email'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_card_type'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_card_type'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_card_type'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_card_type'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_num'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_num'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_num'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_num'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_cvv'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_cvv'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_cvv'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_cvv'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_exp_month'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_exp_month'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_exp_month'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_exp_month'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ( epl_get_element( 'field', $_f['_epl_cc_exp_year'] ) ): ?>

                <div>
                    <?php echo epl_get_element( 'label', $_f['_epl_cc_exp_year'] ); ?>
                    <div>
                        <?php echo epl_get_element( 'field', $_f['_epl_cc_exp_year'] ); ?>

                        <small> <?php echo epl_get_element( 'description', $_f['_epl_cc_exp_year'] ); ?></small>
                    </div>
                </div>

            <?php endif; ?>

    </div>
</div>