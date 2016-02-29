<div class="lookup_field_wrapper">
    <form id="epl_lookup_form" action="" method="post">
        <?php epl_e('Lookup'); ?> <input id="epl_wildcard_lookup" name="lookup_value" placeholder="<?php epl_e('First Name, Last Name, or Email') ;?>" size="50" /><button id="epl_lookup_button"><?php epl_e('Search'); ?></button>
        
        <input type="hidden" name="s_key" value="<?php  echo epl_get_element( 's_key', $_REQUEST );?>" />
        <input type="hidden" name="scope" value="<?php  echo epl_get_element( 'scope', $_REQUEST );?>" />
    </form>
</div>

<div id="lookup_result"></div>
