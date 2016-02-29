<?php if ( $mode != 'overview' ): ?>
    <div id="" class="epl_regis_attendee_wrapper">
        <fieldset class="epl_fieldset">
            <legend>New User</legend>

            <div id="" class="epl_regis_field_wrapper epl_section regis_form">
                <header>
                    <h2><?php epl_e( 'Please fill this information to get access to our members section.' ); ?></h2>
                </header>

                <?php if ( $show_user_login && $mode != 'overview' ): ?>
                    <?php if ( epl_get_element( 'field', $fields['user_login'] ) ): ?>

                        <div>
                            <?php echo epl_get_element( 'label', $fields['user_login'] ); ?>
                            <div>
                                <?php echo epl_get_element( 'field', $fields['user_login'] ); ?>
                                <small> <?php echo epl_get_element( 'description', $fields['user_login'] ); ?></small>
                            </div>    
                        </div>

                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( $show_pass && $mode != 'overview' ): ?>
                    <?php if ( epl_get_element( 'field', $fields['user_pass'] ) ): ?>

                        <div>
                            <?php echo epl_get_element( 'label', $fields['user_pass'] ); ?>
                            <div>
                                <?php echo epl_get_element( 'field', $fields['user_pass'] ); ?>
                                <small> <?php echo epl_get_element( 'description', $fields['user_pass'] ); ?></small>
                            </div>    
                        </div>

                    <?php endif; ?>
                    <?php if ( epl_get_element( 'field', $fields['user_pass_confirm'] ) ): ?>

                        <div>
                            <?php echo epl_get_element( 'label', $fields['user_pass_confirm'] ); ?>
                            <div>
                                <?php echo epl_get_element( 'field', $fields['user_pass_confirm'] ); ?>
                                <small> <?php echo epl_get_element( 'description', $fields['user_pass_confirm'] ); ?></small>
                            </div>    
                        </div>

                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </fieldset>
    </div>
    <script>
        
        jQuery(document).ready(function($){
            $('body').on('change', '#user_login', function(){
                var me = $(this);
                var data = {
                    'epl_action':'username_exists',
                    'epl_controller':'epl_front',
                    'user_login':me.val()
                };
                data = $.param(data);

                events_planner_do_ajax(data, function(r){
                    me.next('small').html(r.html);
                    if (r.username_ok == 0)
                        me.css('background-color','pink');
                    else
                        me.css('background-color','transparent');
                    
                });
               
                return false;
            });
            $('body').on('change', '.user_pass_fields', function(){
            
                
                var p = $('#user_pass').val();
                var pc = $('#user_pass_confirm').val();

                // if((p != '' && pc!='') && (p != pc))
                //   alert ('Passwords do not match');
                
            });
            
        });
        
    </script>
<?php endif; ?>