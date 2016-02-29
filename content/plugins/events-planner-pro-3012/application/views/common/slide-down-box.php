<?php if ( !EPL_IS_ADMIN && EPL_DEBUG === true && epl_user_is_admin()): ?>
    <pre id="epl_console" style=""></pre>
<?php endif; ?>
<div class="slide_down_box" id="slide_down_box">
    <div class="display">

    </div>
    <div class="incoming" style="display: none;"></div>
    <a href="#" id="dismiss_loader" class="epl_button_small" style=""><span class=""></span><?php epl_e( 'Close' ); ?></a>
</div>

<div id="epl_overlay" class="">
    <div></div>

</div>

<div id ="epl_loader">
    <img  src ="<?php echo EPL_FULL_URL; ?>images/ajax-loader.gif" alt="loading..." />
</div>
<?php if ( !EPL_IS_ADMIN && (epl_get_setting( 'epl_sc_options', 'epl_sc_footer_subtotal', 0 ) == 10) ): ?>
    <div id="epl_cart_sticky_footer">
        <div id="epl_cart_sticky_footer_content">
            <?php

            global $post;
            $erm = EPL_registration_model::get_instance();
            if ( epl_sc_is_enabled() && !$erm->is_empty_cart() && !isset( $_REQUEST['clear_cart'] ) && epl_get_element( 'epl_action', $_REQUEST ) == '' && $post->post_type != 'epl_registration' )
                echo $erm->get_the_cart_totals( true, true );
            ?>

        </div>

    </div>
<?php endif; ?>
<?php if ( EPL_DEBUG == true ): ?>
    <pre class='debug_message prettyprint'></pre>
<?php endif; ?>

<script>

    jQuery(document).ready(function($) {

        $(".lightbox_login").click(function(e) {

            var redirect_to = this.getAttribute('data-redirect_to');
            var data = "epl_action=login_form&epl_controller=epl_front";
            jQuery('body').data('epl_redirect_to', redirect_to);

            events_planner_do_ajax(data, function(r) {

                epl_modal.open({content: r.html, width: "350px", height: "200px"});

            });

            e.preventDefault();

            return false;
        });
    });

</script>