<div class="date"><?php echo $date; ?></div>

<?php

if ( empty( $events ) ) {
    echo epl__('No events on this day.');
    return;
}
foreach ( $events as $event_id => $d ):
?>
<dl>
    <dt><a href="<?php echo $d['regis_link']; ?>"><?php echo $d['title']; ?></a></dt>
    <dd>&nbsp;</dd>
</dl>


<?php endforeach; ?>
