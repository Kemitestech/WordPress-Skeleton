<?php

global $epl_sys_messages;

$epl_sys_messages = array(
    '0' => epl__( 'Sorry but something must have gone wrong.' ),
    '20' => epl__( 'Please select a quantity' ),
    '21' => epl__( 'Please select a date' ),
    '22' => epl__( 'Please select a time' ),
    '23' => epl__( 'Sold Out' ),
    '24' => epl__( 'Registration Closed' ),
    '25' => epl__( 'The quantity selected exceeds the number of available spaces' ),
    '40_5'  => epl__( 'Please select a date.' ),
        
    
    '90' => sprintf( 'Hi there.  If you are seeing this error, you need to enter a Support License Key or your key is invalid.
        If you have already purchased a Support License Key, please go %s (Events Planner > Settings > API Settings tab) and enter it in the API Settings tab.
        If you need to get one, please go to %s.  If at any point you are having issues, please contact Events Planner Helpdesk at help@wpeventsplanner.com.  Thank you. ' ,
            epl_anchor( 'edit.php?post_type=epl_event&page=epl_settings',  "here"  ),
            epl_anchor( 'http://www.wpeventsplanner.com',  "Events Planner Website"  ) )
);

$epl_sys_messages = apply_filters( 'epl_sys_messages', $epl_sys_messages );
?>