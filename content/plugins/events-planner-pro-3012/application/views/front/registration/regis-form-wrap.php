<div id="" class="epl_regis_field_wrapper epl_section regis_form">

    <header>
        <!-- form label -->
        <?php if ( !empty( $form_label ) ): ?>

            <h2><?php echo $form_label; ?></h2>

        <?php endif; ?>
        <!-- selected ticket name -->

        <!-- form description -->
        <?php if ( !empty( $form_descr ) ): ?>

            <div><?php echo $form_descr; ?></div>

        <?php endif; ?>
    </header>

    <!-- registration form -->

    <?php echo $fields; ?>

</div>