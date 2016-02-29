<?php if ( isset( $event_time ) ): ?>

    <div class="epl_times_wrapper <?php echo ($time_optional)?'epl_d_n':'' ;?>">


        <span><?php epl_e('Time'); ?>: </span>

    <?php echo $event_time; ?>

</div>

<?php endif; ?>