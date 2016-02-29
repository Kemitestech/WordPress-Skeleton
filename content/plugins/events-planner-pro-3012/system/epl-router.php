<?php

class EPL_router {

    private static $routed = false;
    private static $instance;


    function __construct() {

        epl_log( 'init', get_class() . ' initialized' );
        $GLOBALS['epl_ajax'] = false;

        if ( isset( $_POST['epl_ajax'] ) && $_POST['epl_ajax'] == 1 ) {
            $GLOBALS['epl_ajax'] = true;

            if ( !defined( 'EPL_AJAX' ) ) {
                define( "EPL_AJAX", 1 );
            }
        }

        global $epl_is_single;

        $epl_is_single = 0;


        /*
          $this->uri_components = array( );

          $this->uri_components = parse_url( $_SERVER['REQUEST_URI'] );

          if ( array_key_exists( 'query', $this->uri_components ) )
          parse_str( $this->uri_components['query'], $this->uri_segments );
         */
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_router;
        }

        return self::$instance;
    }


    function segment( $segment = null ) {

        if ( array_key_exists( $segment, $this->uri_segments ) )
            return $this->uri_segments[$segment];

        return null;
    }


    function route( $args = null ) {
        //if ( self::$routed )
        //  return;
        //ajax also ends up in admin
        if ( !defined( 'EPL_TIME' ) ) {
            define( "EPL_TIME", strtotime( current_time( 'mysql' ) ) );
            define( 'EPL_DATE', strtotime( '00:00:00', EPL_TIME ) );
        }


        if ( !defined( 'EPL_IS_ADMIN' ) )
            define( 'EPL_IS_ADMIN', is_admin() );

        if ( EPL_IS_ADMIN ) {

            //is_admin is true for ajax requests
            if ( isset( $_REQUEST['epl_action'] ) && epl_get_element( 'epl_controller', $_REQUEST ) == 'epl_front' && $GLOBALS['epl_ajax'] ) {

                $resource = 'epl_front';

                return $this->_route( $resource );
                die();
            }

            $post_type = '';
            $trashing = (strpos( 'trash', epl_get_element( 'action', $_GET ) ) !== false);

            if ( isset( $_GET['post'] ) && ($_GET['action'] == 'edit' || $trashing) ) {

                if ( $trashing && is_array( $_GET['post'] ) )
                    $post_ID = current( $_GET['post'] );
                else
                    $post_ID = $_GET['post'];
                $post_type = get_post_type( intval( $post_ID ) );
            }
            else
                $post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : '';

            $resource = $post_type;

            if ( ('epl_event' == $post_type && isset( $_REQUEST['page'] )) || strpos( epl_get_element( 'page', $_REQUEST, '' ), 'epl_' ) !== false )
                $resource = $_REQUEST['page'];

            if ( isset( $_REQUEST['epl_controller'] ) )
                $resource = $_REQUEST['epl_controller'];

            return $this->_route( $resource );
        } elseif ( !isset( $_REQUEST['epl_action'] ) ) {

            if ( 'single_template' == current_filter() ) {

                epl_is_single( 'set' );

                $resource = 'epl_front';

                return $this->_route( $resource, array( 'epl_action' => 'single_default_template', 'template' => $args ) );
            }
        }
        
        $epl_action = epl_get_element('epl_action', $_REQUEST);
        
        //something new to play with
        if ( !EPL_IS_ADMIN && ('the_content' == current_filter() || current_filter() == '') ) {

            $resource = 'epl_front';
            return $this->_route( $resource );
        }
        elseif ( $epl_action == 'ical' || $epl_action == 'invoice' || $epl_action == 'custom_pdf' || $epl_action == 'custom_html' ) {

            $resource = 'epl_front';
            $this->_route( $resource );
            exit();
        }
    }


    function shortcode_route( $atts ) {

        if ( !EPL_IS_ADMIN && ('the_content' == current_filter()) ) {
            $resource = 'epl_front';

            //global $shortcode_tags;

            return $this->_route( $resource, $atts );
        }
    }


    function _route( $resource = null, $atts = array() ) {

        if ( self::$routed )
            return;

        global $valid_controllers, $post; //When the shortcode is processed, the page id is ready


        if ( !array_key_exists( $resource, $valid_controllers ) )
            return false;


        $controller_location = $valid_controllers[$resource]['location'];

        $controller = EPL_Base::get_instance()->load_controller( $controller_location );

        //self::$routed = true;
        //if ( !EPL_IS_ADMIN && !isset( $_REQUEST['epl_action'] ) ) {
        if ( !EPL_IS_ADMIN || $GLOBALS['epl_ajax'] ) {
            return $controller->run( $atts ); //doing this for the shortcode
        }
    }

}

?>
