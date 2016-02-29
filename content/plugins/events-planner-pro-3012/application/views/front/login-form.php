<?php if ( !is_user_logged_in() && function_exists('login_with_ajax') ) : ?>

        <?php login_with_ajax(); ?>

<?php endif; ?>