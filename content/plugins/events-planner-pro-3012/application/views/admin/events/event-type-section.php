<div class="epl_box epl_ov_a">


    <ul class="checklist epl_event_type">
        <?php

        foreach ( $epl_event_type as $ev_k => $ev_v ):
            ?>


            <li class="">
                <div class="pinz">
                    <?php echo $ev_v['field']; ?>
                    <?php echo $ev_v['label']; ?>

                </div>
            </li>




        <?php endforeach; ?>
    </ul>
</div>
