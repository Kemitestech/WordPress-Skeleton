<?php
/**
 * Change BuddyPress default Members landing tab.
 */
define('BP_DEFAULT_COMPONENT', 'profile' );

define( 'BP_GROUPS_DEFAULT_EXTENSION', 'members' );

add_filter( 'bp_after_has_members_parse_args', 'buddydev_exclude_users_by_role' );

function buddydev_exclude_users_by_role( $args ) {
    //do not exclude in admin
    if( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $args;
    }

    $excluded = isset( $args['exclude'] )? $args['exclude'] : array();

    if( !is_array( $excluded ) ) {
        $excluded = explode(',', $excluded );
    }

    //change to the role to be excluded
    $user_ids =  get_users( array( 'role__in' => ['customer', 'subscriber'], 'fields'=>'ID') );


    $excluded = array_merge( $excluded, $user_ids );

    $args['exclude'] = $excluded;

    return $args;
}

add_filter('bp_get_total_member_count','bpdev_members_correct_count');
function bpdev_members_correct_count($total_count){//Get total member count minus users with subscriber and customer roles
	$count_users = count_users(); //WP function returns an array of total users and user counts by roles
	$total_users = $count_users['total_users']; //Gets the total number of users
  $subscriber_role = array('subscriber');
	$customer_role = array('customer');
	$total_subscriber_count = 0; //Initialise total subscriber count to 0
	$total_customer_count = 0; //Initialise total subscriber count to 0

	foreach($count_users['avail_roles'] as $role => $count){
		if( in_array($role, $subscriber_role)) {//checks if the 'subscriber' role is in the $subscriber_role array
			$total_subscriber_count = $count; //sets the total subscriber count
		}
		if( in_array($role, $customer_role)) {//checks if the 'customer' role is in the $subscriber_role array
      $total_customer_count = $count; //sets the total customer count
    }
	}

	$excluded_users_count= $total_subscriber_count + $total_customer_count; //excluded user count which is the subscriber count and customer count
	return $total_count-$excluded_users_count; //Returns the total member count minus the subscriber and customer count
}

add_filter( 'bp_get_group_join_button', 'custom_hide_joingroup_button');
function custom_hide_joingroup_button( $btn) {//Hides join group button from customers and subscribers

	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}

	return $btn;
}

add_filter( 'bp_get_add_friend_button', 'custom_hide_addfriend_button' );
function custom_hide_addfriend_button( $btn ) {//Hides add friend message from customers and subscribers
	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}

	return $btn;
}

add_filter( 'bp_get_send_public_message_button', 'custom_hide_public_message_button' );
function custom_hide_public_message_button( $btn ) {//Hides public message from customers and subscribers
	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}

	return $btn;
}

/**
 * Control if MediaPress is enabled for specific component
 *
 * @param boolean $enabled
 * @param string $component  possible values ( 'members', 'groups', 'sitewide')
 * @param int $component_id user_id if $component is 'members', group_id if component is groups
 * @return boolean true to enable, false to disable
 */
function mpp_custom_restrict( $enabled, $component, $component_id ) {

    if ( $component == 'groups' &&  ! current_user_can('access_s2member_level1' )) {
        $enabled = false;
    }

    return $enabled;
}
add_filter( 'mpp_is_enabled', 'mpp_custom_restrict', 10, 3);

function filter_send_message_btn() {//Hides private message from customers and subscribers
  if ( ! current_user_can('access_s2member_level1' ) ) {
      	$args = array(
      		'id'                => '',
      		'component'         => 'messages',
      		'must_be_logged_in' => true,
      		'block_self'        => false,
      		'wrapper_id'        => '',
      		'link_href'         => '',
      		'link_title'        => __( '', 'buddypress' ),
      		'link_text'         => __( '', 'buddypress' ),
      		'link_class'        => '',
      	);

   }else{
       $args = array(
  			'id'                => 'private_message',
  			'component'         => 'messages',
  			'must_be_logged_in' => true,
  			'block_self'        => true,
  			'wrapper_id'        => 'send-private-message',
  			'link_href'         => bp_get_send_private_message_link(),
  			'link_title'        => __( 'Send a private message to this user.', 'buddypress' ),
   			'link_text'         => __( 'Private Message', 'buddypress' ),
   			'link_class'        => 'send-message',
 		);
  }
    return $args;
 }
add_filter('bp_get_send_message_button_args', 'filter_send_message_btn');

function bp_remove_nav_tabs() {//removes the following tabs for non-members: activity, friends, groups, products
  global $bp;
  if(!current_user_can('access_s2member_level1')){
    bp_core_remove_nav_item( 'activity' );
    bp_core_remove_nav_item( 'friends' );
    bp_core_remove_nav_item( 'groups' );
    bp_core_remove_nav_item( 'products' );
    if(isset($bp->groups->current_group->slug) && $bp->groups->current_group->slug == $bp->current_item) {
        $bp->bp_options_nav[$bp->groups->current_group->slug]['home'] = false;
    }
  }
}
add_action( 'bp_setup_nav', 'bp_remove_nav_tabs', 15 );
?>
