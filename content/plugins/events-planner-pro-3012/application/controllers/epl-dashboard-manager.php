<?php

if ( !class_exists( 'EPL_Dashboard_Manager' ) ) {

    class EPL_Dashboard_Manager extends EPL_Controller {


        function __construct() {

            parent::__construct();
            global $epl_fields;

            $this->fields = $epl_fields;
            add_action( 'admin_notices', array( $this, 'dashboard' ) );
        }


        function dashboard() {

            $this->epl->load_view( 'admin/dashboard/dashboard' );
        }

    }

}