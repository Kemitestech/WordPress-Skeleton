<?php

class EPL_advanced_cal_widget extends WP_Widget {


    //process the new widget
    function EPL_advanced_cal_widget() {

        $this->epl = EPL_base::get_instance();

        $widget_ops = array(
            'classname' => 'epl_advanced_cal_widget_class',
            'description' => 'Use this widget to put an interactive Events Planner calendar on any page.',
        );

        $this->WP_Widget( 'EPL_advanced_cal_widget', 'Events Planner Advanced Calendar', $widget_ops );
    }


    //build the widget settings form
    function form( $instance ) {
        $defaults = array(
            'title' => epl__( 'Events' ),
            'exclude_past_events' => epl__( 'Exclude Past Events' ),
        );
        $instance = wp_parse_args( ( array ) $instance, $defaults );

        $title = $instance['title'];
        $show_past_events = epl_get_element('show_past_events',$instance);


        $data = array( );

        $data += $instance;

        $data['w'] = $this;

        $this->epl->load_view( 'widgets/advanced-cal/form', $data );
    }


    //save the widget settings
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['exclude_past_events'] = strip_tags( $new_instance['exclude_past_events'] );

        return $instance;
    }


    //display the widget
    function widget( $args, $instance ) {
        extract( $args );

        echo $before_widget;

        $title = apply_filters( 'widget_title', $instance['title'] );

        if ( !empty( $title ) ) {
            echo $before_title . $title . $after_title;
        };

       $data['cal'] = $this->epl->epl_util->get_widget_cal(1, $instance);
        echo $this->epl->load_view('widgets/advanced-cal/front', $data, true);
        echo $after_widget;
    }

}
