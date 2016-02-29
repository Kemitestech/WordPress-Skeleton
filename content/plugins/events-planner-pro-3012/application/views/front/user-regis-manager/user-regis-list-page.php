<style>

    #epl_user_schedule_wrapper {
        /*background-color: #eee;*/
    }

    #epl_user_schedule_wrapper .epl_user_schedule_ind_wrapper {
        /*background-color: #e9e9e9;*/
        margin:10px;
        border:1px solid #eee;
        padding:10px 20px;
        overflow: hidden;
}




</style>

<div id="epl_user_schedule_wrapper">

    <form action="<?php echo epl_get_url(); ?>" method="post">



            <?php foreach ( $list as $regis_info ): ?>
                <div class="epl_user_schedule_ind_wrapper">


                <?php

                echo $regis_info;
                ?>
            </div>
                <?php endforeach; ?>



    </form>
</div>