

    <h3><?php echo $event_title; ?></h3>
    <h3><?php echo $regis_id; ?></h3>
    <h3><?php echo $regis_date; ?></h3>

<table class="epl_plain_table epl_w300" cellspacing="0">
    <thead>
        <tr>
            <th></th>
            <th></th>
        </tr>
    <tbody>
        <?php foreach ( $regis_dates as $_year => $_months ): ?>


        <?php foreach ( $_months as $_month => $_dates ): ?>

        <?php foreach ( $_dates as $_day => $_date ): ?>
                    <tr><td><?php echo epl_formatted_date( $_date ); ?></td><td><input type="button" class="button-secondary" value="Reschedule" /></td></tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endforeach; ?>
        </tbody>
    </table>

    <div>
    <?php echo $regis_dates_cal; ?>
</div>