

<div class="fc_template1">

    <div class="event_title"><?php echo $_event['title']; ?></div>

    <div class="event_details">

        <div class="event_date">
            <?php echo date_i18n( get_option( 'date_format' ), epl_get_date_timestamp( $_event['date'] ) ); ?>
        </div>


        <?php foreach ( $_event['times']['start'] as $k => $_v ) {
                $_e = ($_v != $_event['times']['end'][$k]) ? ' - ' . $_event['times']['end'][$k] : ''; ?>
                <div class="event_time"><?php echo $_v . $_e; ?></div>
        <?php }; ?>


            <div style="" class="event_description"><?php echo $_event['description']; ?></div>
    </div>

</div>

