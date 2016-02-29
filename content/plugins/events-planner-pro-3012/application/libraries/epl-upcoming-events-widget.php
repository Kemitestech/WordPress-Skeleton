<?php

class EPL_upcoming_events_widget extends WP_Widget {


    //process the new widget
    function EPL_upcoming_events_widget() {

        $this->epl = EPL_base::get_instance();

        $widget_ops = array(
            'classname' => 'epl_upcoming_events_widget',
            'description' => 'Use this widget to put a list of upcoming events in the sidebar.',
        );

        $this->WP_Widget( 'EPL_upcoming_events_widget', 'Events Planner Upcoming Events', $widget_ops );
    }


    //build the widget settings form
    function form( $instance ) {
        $defaults = array(
            'title' => epl__( 'Events' ),
        );

        $instance = wp_parse_args( ( array ) $instance, $defaults );

        $title = $instance['title'];
        $title_url = $instance['title_url'];
        $css_class = $instance['css_class'];
        $num_events_to_show = $instance['num_events_to_show'];
        $exclude_event_ids = $instance['exclude_event_ids'];
        $enable_tooltip = $instance['enable_tooltip'];
        $template = $instance['template'];
        $thumbnail_size = $instance['thumbnail_size'];
        $class_display_type = epl_get_element( 'class_display_type', $instance );
        $num_words_to_show = epl_get_element( 'num_words_to_show', $instance );
        $content_to_show = epl_get_element( 'content_to_show', $instance );

        $days_to_show = $instance['days_to_show'];

        $tax_filter = $instance['tax_filter'];
        $tax_filter_field = $this->get_field_name( 'tax_filter' );

        //send name and value
        $args =array (
            'name'=> $tax_filter_field . '[]',
            'value'=>$tax_filter
        );
        $r = $this->epl->epl_util->epl_terms_field( $args );

        $data = array( );

        //send the checkbox array as a separate var
        $data['tax_filter'] = $r['field'];

        $_f = array(
            'input_type' => 'select',
            'input_name' => $this->get_field_name( 'enable_tooltip' ),
            'options' => epl_yes_no(),
            'value' => $enable_tooltip );

        $r = $this->epl->epl_util->create_element( $_f );
        $data['enable_tooltip'] = $r['field'];

        $_f = array(
            'input_type' => 'select',
            'input_name' => $this->get_field_name( 'class_display_type' ),
            'options' => array( 1 => epl__( 'First Date Only' ), 2 => epl__( 'Each Date Individually' ) ),
            'value' => $class_display_type );

        $r = $this->epl->epl_util->create_element( $_f );
        $data['class_display_type'] = $r['field'];

        $template_options = apply_filters( 'epl_ue_available_templates', array( 
            'template-1' => epl__( 'Template 1' ), 
            'template-2' => epl__( 'Template 2' ), 
            'template-1-loc' => epl__( 'Template 1 + location' ),
            'template-2-loc' => epl__( 'Template 2 + location' ),
            ) );

        $_f = array(
            'input_type' => 'select',
            'input_name' => $this->get_field_name( 'template' ),
            'options' => $template_options,
            'value' => $template );

        $r = $this->epl->epl_util->create_element( $_f );
        $data['template'] = $r['field'];

        $_f = array(
            'input_type' => 'select',
            'input_name' => $this->get_field_name( 'thumbnail_size' ),
            'options' => get_intermediate_image_sizes(),
            'value' => $thumbnail_size );

        $r = $this->epl->epl_util->create_element( $_f );
        $data['thumbnail_size'] = $r['field'];

        $_f = array(
            'input_type' => 'select',
            'input_name' => $this->get_field_name( 'content_to_show' ),
            'options' => array('content' => epl__('Content'),'excerpt' => epl__('Excerpt')),
            'value' => $content_to_show );

        $r = $this->epl->epl_util->create_element( $_f );
        $data['content_to_show'] = $r['field'];

        $data += $instance;

        $data['w'] = $this;

        $this->epl->load_view( 'widgets/upcoming-events/form', $data );
    }


    //save the widget settings
    function update( $new_instance, $old_instance ) {
        epl_log( "debug", "<pre>" . __LINE__ . '> ' . print_r( $new_instance, true ) . "</pre>" );

        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['title_url'] = strip_tags( $new_instance['title_url'] );
        $instance['css_class'] = strip_tags( $new_instance['css_class'] );
        $instance['num_events_to_show'] = strip_tags( $new_instance['num_events_to_show'] );
        $instance['exclude_event_ids'] = strip_tags( $new_instance['exclude_event_ids'] );
        $instance['enable_tooltip'] = strip_tags( $new_instance['enable_tooltip'] );
        $instance['template'] = strip_tags( $new_instance['template'] );
        $instance['thumbnail_size'] = strip_tags( $new_instance['thumbnail_size'] );
        $instance['class_display_type'] = strip_tags( $new_instance['class_display_type'] );
        $instance['content_to_show'] = strip_tags( $new_instance['content_to_show'] );
        $instance['num_words_to_show'] = strip_tags( $new_instance['num_words_to_show'] );

        $instance['days_to_show'] = strip_tags( $new_instance['days_to_show'] );
        $instance['tax_filter'] = $new_instance['tax_filter'];


        return $instance;
    }


    //display the widget
    function widget( $args, $instance ) {

        extract( $args );

        static $common_loaded = false;

        echo $before_widget;

        $title = apply_filters( 'widget_title', $instance['title'] );

        if ( $instance['title_url'] != '' ) {
            $title = '<a href="' . $instance['title_url'] . '">' . $title . '</a>';
        }
        if ( !empty( $title ) ) {
            $data['title'] = $before_title . $title . $after_title;
        }

        $data['instance'] = $instance;

        $data['events'] = $this->epl->epl_util->get_days_for_widget( 0, $instance );

        $data['enable_tooltip'] = (epl_get_element( 'enable_tooltip', $instance, 0 ) == 10) ? 'epl_show_tooltip' : '';
        $data['css_class'] = epl_get_element( 'css_class', $instance, 'ue_template1' );
        $thumbnail_size = epl_get_element( 'thumbnail_size', $instance, null );
        $data['thumbnail_size'] = epl_get_element( $thumbnail_size, get_intermediate_image_sizes(), null );

        $template = epl_get_element( 'template', $instance, 'template-1' );


        echo $this->epl->load_view( 'widgets/upcoming-events/' . $template, $data, true );

        if ( !$common_loaded ) {
            $this->epl->load_view( 'widgets/upcoming-events/ue-widget-common' );
            $common_loaded = true;
        }

        echo $after_widget;
    }

}
