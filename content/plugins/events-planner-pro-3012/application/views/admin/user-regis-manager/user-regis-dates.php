

<table class="epl_plain_table epl_w300" cellspacing="0">
    <thead>
        <tr>
            <th>Dates</th>
            <th></th>
        </tr>
    <tbody>
        <?php foreach ( $regis_dates as $_date_key => $_date ): ?>
    
            <tr><td><?php echo epl_formatted_date( $_date ); ?></td><td></td></tr>

        <?php endforeach; ?>
    </tbody>
</table>
