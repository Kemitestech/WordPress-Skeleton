<?php
/**
 * Change BuddyPress default Members landing tab.
 */
define('BP_DEFAULT_COMPONENT', 'profile' );

define( 'BP_GROUPS_DEFAULT_EXTENSION', 'members' );
//Stops users with subscriber and customer roles from showing up as buddypress member
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
//Restricts group members from uploading to other group members gallery
add_filter( 'mpp_user_can_upload', 'mpp_custom_restrict_group_upload', 11, 4 );
function mpp_custom_restrict_group_upload( $can_do, $component, $component_id, $gallery  ) {

	if ( $component != 'groups' ) {
		return $can_do;
	}
	//we only care about group upload
	$gallery = mpp_get_gallery( $gallery );

	if ( ! $gallery || $gallery->user_id != get_current_user_id() ) {
		return false;//do not allow if gallery is not given
	}

	return true;//the user had created this gallery

}

//disables media and gallery commenting for non-artist members
add_filter('mpp_settings', 'custom_comment_restrict');
function custom_comment_restrict($keys){
  if(!current_user_can('access_s2member_level1')){
    $keys['enable_gallery_comment'] = false;
    $keys['enable_media_comment'] = false;
  }
  return $keys;
}
//disables gallery component for non-artist members
add_filter( 'mpp_is_enabled', 'mpp_custom_restrict', 10, 3);
function mpp_custom_restrict( $enabled, $component, $component_id ) {

    if ( $component == 'groups' &&  ! current_user_can('access_s2member_level1' )) {
        $enabled = false;
    }

    return $enabled;
}
//Get total member count minus users with subscriber and customer roles
add_filter('bp_get_total_member_count','bpdev_members_correct_count');
function bpdev_members_correct_count($total_count){
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
//Hides join group button from users who are not artist members
function custom_hide_joingroup_button( $btn) {

	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}

	return $btn;
}

add_filter( 'bp_get_add_friend_button', 'custom_hide_addfriend_button' );
//Hides add friend button from users who are not artist members
function custom_hide_addfriend_button( $btn ) {
	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}

	return $btn;
}

add_filter( 'bp_get_send_public_message_button', 'custom_hide_public_message_button' );
//Hides public message button from users who are not artist members
function custom_hide_public_message_button( $btn ) {
	if ( ! current_user_can('access_s2member_level1' ) ) {
		unset( $btn['id'] );//unsetting id will force BP_Button to not generate any content
	}
	return $btn;
}
//Hides private message button from users who are not artist members
add_filter('bp_get_send_message_button_args', 'filter_send_message_btn');
function filter_send_message_btn() {
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
//removes the following tabs for non-members: activity, friends, groups, products, home(in group section)
add_action( 'bp_setup_nav', 'bp_remove_nav_tabs', 15 );
function bp_remove_nav_tabs() {
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
//shortcode for returning the url of an artist members profile
add_shortcode('bpProfile','bpProfile');
function bpProfile( $atts=null, $content=null ) {
$user_ID = get_current_user_id();
if ( is_user_logged_in() && current_user_can('access_s2member_level1' )) {
  return '<a href='.bp_core_get_user_domain( $user_ID ).'profile/>Back to my profile</a>';
} else {
  return "";
}
}


?>
