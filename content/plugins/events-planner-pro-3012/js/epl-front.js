
jQuery(document).ready(function ($) {
    /*
     * Please, no funny business.  The ids passed are checked for accuracy, so...
     *
     * This file will be refactored
     */

    _EPL_DOM = {
        'epl_payment_choices_section': $('#epl_payment_choices_wrapper'),
        'epl_cart_sticky_footer': $('#epl_cart_sticky_footer'),
        'epl_cart_totals_wrapper': $('#epl_cart_totals_wrapper'),
        'epl_next_button': $('.epl_button_wrapper :input[name=next]')
    };


    $(document.body).on('click', 'a.open_add_to_waitlist_form', function () {
        var me = $(this);

        var data = {
            'epl_action': 'open_add_to_waitlist_form',
            'epl_controller': 'epl_front',
            'epl_m': '1',
            'epl_just_f': '1',
            'epl_wl_flow': '1',
            'event_id': me.data('event_id')
        }
        data = $.param(data);

        events_planner_do_ajax(data, function (r) {

            epl_modal.open({
                content: r.html,
                width: "700px"
            });

        });

        return false;
    });

    $(document.body).on('submit', '#epl_waitlist_form', function () {

        $('#epl_waitlist_form').removeAttr("novalidate");

        var me = $(this);

        var data = {
            'epl_action': 'add_waitlist_record',
            'epl_controller': 'epl_front',
            'epl_m': '1',
            'epl_just_f': '1',
            'epl_wl_flow': '1'

        }
        data = $.param(data);

        data = data + '&' + $('#epl_waitlist_form').serialize();

        events_planner_do_ajax(data, function (r) {
            //show the message

            var event_id = $('#epl_waitlist_event_id').val();
            //remove the event from the cart
            $('.epl_individual_event_wrapper-' + event_id).slideUp().remove();

            _EPL_DOM.epl_next_button.show();
            _EPL_DOM.epl_payment_choices_section.slideDown();

            calculate_total_due();

            epl_modal.open({
                content: r.html,
                width: "700px",
                height: "100px"
            });

        });

        return false;
    });

    if ($.datepicker instanceof Object) {

        create_datepicker('.datepicker');
    }

    if ($.trim($('div#epl_cart_sticky_footer_content', _EPL_DOM.epl_cart_sticky_footer).html()).length > 0) {
        _EPL_DOM.epl_cart_sticky_footer.fadeIn();
    }
    $('body').on('click', '.epl_date_individual_date_wrapper .epl_delete_date', function () {
        var p = $(this).parents('.epl_date_individual_date_wrapper');
        p.slideUp(400, function () {
            p.remove();
            calculate_total_due();
        });



    });

    setup_select2('select#user_id');

    check_for_remaining();


    $('.load_date_selector_cal').click(function () {
        var par = $(this).closest('table');

        //var data = "epl_action=load_date_selector_cal&epl_controller=epl_front&date_selector=1&event_id=" + <?php echo epl_get_element( 'event_id', $_GET, "" ); ?>;

        events_planner_do_ajax(data, function (r) {

            show_slide_down(r.html);

        });


        return false;

    });
    $('body').on('click', '.epl_register_button', function () {

        if (EPL.sc == 1) {
            var me = $(this);

            if (me.hasClass('epl-no-modal') || me.hasClass('in_cart'))
                return true;
            var url = me.prop('href');

            var event_id = get_query_variable('event_id', url);
            var _date_id = get_query_variable('_date_id', url);
            var _time_id = get_query_variable('_time_id', url);

            var id = me.prop('id'); //wrong approach, use data, or parse url

            var button_cart = me.hasClass('button_cart');

            var data = {
                'epl_action': 'process_cart_action',
                'cart_action': 'add',
                'epl_controller': 'epl_front',
                'epl_m': button_cart ? '2' : '1',
                'event_id': event_id,
                '_date_id': _date_id,
                '_time_id': _time_id
            }
            data = $.param(data);

            events_planner_do_ajax(data, function (r) {

                if (button_cart) {
                    me.addClass('in_cart');
                    me.html(EPL.cart_added_btn_txt);

                    $('div', _EPL_DOM.epl_cart_totals_wrapper).html(r.cart_grand_totals);
                } else {
                    epl_modal.open({
                        content: r.html,
                        width: "700px"
                    });
                }

            });
            return false;
        }

    });

    $('body').on('click', '.epl_list_row', function () {
        var me = $(this);

        //alert (me.prop('id'));


    });
    $('.epl_show_gmap').click(function () {
        var me = $(this);

        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=get_location_map&epl_controller=epl_front&location_id=" + id;

        events_planner_do_ajax(data, function (r) {
            var d = r.html;


            show_slide_down(d);

        });
        return false;

    });
    $('body').on('click', '.epl-adv-calendar .widget_has_data', function () {


        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=get_events_for_day&epl_controller=epl_front&date=" + id;

        events_planner_do_ajax(data, function (r) {
            var d = r.html;
            var sl = $('.calendar_slide');
            $('span.slide_content', sl).html(d);
            sl.slideDown();

        });


        return false;


    });
    $('body').on('click', '.close_calendar_slide', function () {

        $(this).closest('.calendar_slide').slideUp();


    });

    $('a#get_cal_dates').click(function () {


        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=get_cal_dates&epl_controller=epl_front";

        events_planner_do_ajax(data, function (r) {
            var d = r.html; //$('.data', par).val();
            //
            // $('.epl_totals_table').replaceWith(d);
            //    $('#epl_totals_wrapper table').replaceWith(d);
            //console.log(r.html);


            $('#calendar').fullCalendar({
                events: JSON.parse(d),
                color: 'yellow'

            });
            var d = $('#calendar').fullCalendar('getDate');
            //alert("The current date of the calendar is " + d);
        });


        return false;

    });
    $('body').on('click', 'a#calculate_total_due', function () {
        calculate_total_due();
        return false;
    });


    $('body').on('click', 'a.delete_cart_item', function () {


        if (confirm('Are you sure?') !== true)
            return false;

        var me = $(this);
        var id = me.prop('id');

        var caller = me.data('caller');

        var data = "epl_action=process_cart_action&cart_action=delete&epl_controller=epl_front&event_id=" + id + "&caller=" + caller;

        events_planner_do_ajax(data, function (r) {

            if (caller == 'summary') {
                if (r.cart_grand_totals === undefined) {
                    _EPL_DOM.epl_cart_sticky_footer.fadeOut(400, function () {
                        $('div', _EPL_DOM.epl_cart_sticky_footer).html('');
                    });

                } else {

                    $('div', _EPL_DOM.epl_cart_sticky_footer).html(r.cart_grand_totals);

                }
                return false;
            }

            var cont = me.parents('.epl_individual_event_wrapper');
            if (cont.length == 0) {
                cont = me.parents('tr');
                from_modal = true;
            }
            cont.slideUp().remove();
        });
        calculate_total_due();
        return false;

    });

    $('body').on('change', '.epl_att_qty_dd, .epl_time_dd, .epl_cart_dates input,.epl_cart_dates_body input,  #epl_discount_wrapper input, #epl_donation_wrapper input,#epl_cart_totals_table input, .epl_payment_options input', function () {
        calculate_total_due();
        check_for_remaining();
    });

    var prices_table = $('.epl_prices_table');
    var quantities = $('select', prices_table).val();

    if (prices_table.length >= 1 && quantities !== undefined) {
        calculate_total_due();
    }



    $('a.add_to_cart').click(function () {
        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=process_cart_action&cart_action=add&epl_controller=epl_front&event_id=" + id;

        events_planner_do_ajax(data, function (r) {
            var d = r.html; //$('.data', par).val();
            //
            par.html(d);
            //console.log(r.html);
            //alert(d);


        });


        return false;

    });

    /*
     * 
     $('#events_planner_shopping_cart').submit(function() {
     
     return true;
     var me = $(this);
     
     var data = "epl_action=process_cart_action&cart_action=update&epl_controller=epl_front&" + me.serialize();
     
     events_planner_do_ajax(data, function(r) {
     var d = r.html;
     
     par.html(d);
     
     });
     return false;
     
     }); */

    $('body').on('click', 'div.widget_has_data', function () {

        $('.epl_calendar_widget_data').html(Math.random());




    });
    $('body').on('click', 'a.epl_next_prev_link', function () {

        var me = $(this);
        var url = me.prop("href");
        var par = me.parents('.epl_calendar_wrapper');

        var data = url + "&epl_action=widget_cal_next_prev&epl_controller=epl_front";

        events_planner_do_ajax(data, function (r) {
            var d = r.html; //$('.data', par).val();
            //
            par.replaceWith(d);
            //console.log(r.html);
            //alert(d);


        });


        return false;


    });
    $('body').on('click', '.epl_action', function () {

        var me = $(this);
        var my_form = me.parents('form');
        var my_form_id = my_form.prop('id');


        var par = me.closest('li');

        if (par.length == 0)
            par = me.closest('tr');

        if (par.length == 0)
            par = me.parents('div').eq(0);

        if (me.hasClass('epl_delete')) {


            var _ess = get_essential_fields(my_form);
            //invoke this function if confirmed on overlay.
            if (me.hasClass('epl_ajax_delete')) {
                var a = function () {
                    //sending the form also for the referrer and nonces

                    var data = "epl_form_action=delete&_id=" + me.closest('tr').prop('id') + _ess;

                    events_planner_do_ajax(data, function (r) {
                        //console.log(r.code);
                        par.fadeOut().remove();

                    });

                };
            }

            //show the confirmation overlay
            _EPL.delete_element({
                me: me,
                par: par,
                action: a
            });
            return false;
        }
    });

    $.validator.addMethod("password_match", function (value, element) {
        return $('.epl_regis_field_wrapper #user_pass').val() == $('#user_pass_confirm').val()
    }, "* Password fields do not match");

    $("#events_planner_shopping_cart").validate({
        rules: {
            user_login: {
                minlength: 3
            },
            user_pass: {
                minlength: 6
            },
            user_pass_confirm: {
                minlength: 6,
                password_match: true
            }
        },
        /*submitHandler: function(form) { //with this enabled, stripe will not work
         $('input[type=submit]', $("#events_planner_shopping_cart")).prop('disabled', true);
         form.submit();
         }*/
    });

    //not used now.
    $('body').on('submit', 'form#events_planner_shopping_cart1', function () {

        if ($('.epl_prevent_form_submit').length)
            return false;
        var me = $(this);

        me.validate();

        /*        if (!epl_validate(me))
         return false;*/

        return true;
        var main_cont = $('#epl_main_container');
        var ajax_cont = $('#epl_ajax_content');
        var h = main_cont.outerHeight(true);

        main_cont.css('min-height', h + 'px');
        var data = me.serialize() + "&epl_controller=epl_front";

        events_planner_do_ajax(data, function (r) {

            ajax_cont.fadeOut('fast').html(r.html).delay(400).fadeIn('fast');

        });

        return false;
    });



    $(".epl_front_tab1").organicTabs({
        "speed": 0
    });



});

function calculate_total_due() {

    check_for_remaining();

    var me = jQuery(this);
    var par = me.parent();
    var id = me.prop('id');
    //var form = me.parents('form');// jQuery('#events_planner_shopping_cart');
    var form = jQuery('#events_planner_shopping_cart');
    var from_modal = me.hasClass('from_modal') ? 1 : 0;
    var data = form.serialize() + "&epl_action=process_cart_action&cart_action=calculate_total_due&epl_controller=epl_front&from_modal=" + from_modal;

    events_planner_do_ajax(data, function (r) {
        var d = r.html; //jQuery('.data', par).val();

        jQuery.each(d, function (key, val) {
            jQuery('#epl_totals_wrapper_' + key + ' table').replaceWith(val);

        });
        jQuery('.epl_totals_table_' + id).replaceWith(d);

        if (typeof r.cart_grand_totals != 'undefined') {

            if (typeof r.show_footer_total != 'undefined') {
                jQuery('div', _EPL_DOM.epl_cart_sticky_footer).html(r.cart_grand_totals);
                _EPL_DOM.epl_cart_sticky_footer.fadeIn();
            }
            jQuery('div', _EPL_DOM.epl_cart_totals_wrapper).html(r.cart_grand_totals);

        }

        if (from_modal == 0 && typeof r.cart_errors_present != 'undefined') {
            //scroll to error only if not visible
            if (jQuery(".epl_error").offset().top < jQuery(window).scrollTop()) {

                jQuery('html, body').animate({
                    scrollTop: jQuery(".epl_error").offset().top - 200
                }, 400);
            }
        }

        if (r.hide_offline_payment_options == true) {
            toggle_offline_payment_options('hide');

        } else {
            toggle_offline_payment_options('show');
        }
        if (typeof r.is_ok_for_waitlist != 'undefined' || typeof r.hide_payment_choices != 'undefined') {

            if (r.is_ok_for_waitlist == true || r.hide_payment_choices == true) {
                //_EPL_DOM.epl_payment_choices_section.slideUp();
                _EPL_DOM.epl_next_button.prop('disabled', true);
                _EPL_DOM.epl_next_button.addClass('btn_disabled');
                // if (r.is_ok_for_waitlist == true)
                //   _EPL_DOM.epl_next_button.hide();

            }
        } else {
            //_EPL_DOM.epl_next_button.show();
            //_EPL_DOM.epl_payment_choices_section.slideDown();
            _EPL_DOM.epl_next_button.prop('disabled', false);
            _EPL_DOM.epl_next_button.removeClass('btn_disabled');
        }

        if (EPL.empty_cart_redirect_to !== undefined && r.num_events_in_cart == 0)
            window.location.replace(EPL.empty_cart_redirect_to);

    });


    return false;

}
/*
 function calculate_total_due() {
 jQuery('a#calculate_total_due').trigger('click');
 }*/

function check_for_remaining() {


    jQuery('.epl_individual_event_wrapper').each(function () {

        var me = jQuery(this);


        var max = parseInt(jQuery('.epl_registration_max', me).val());

        if (isNaN(max))
            return;

        var total = 0;

        jQuery('select.epl_att_qty_dd', me).each(function () {
            total += Number(jQuery(this).val());
        });

        var remaining = max - total;

        jQuery('select.epl_att_qty_dd', me).each(function () {
            var s = jQuery(this);
            var end = remaining;
            var val = parseInt(s.val());
            if (val > 0)
                end = val + remaining;
            do_options(s, end, s.val());

        });
    });
}

function toggle_offline_payment_options(act) {


    jQuery('.epl_offline_payment').each(function () {
        var me = jQuery(this);

        if (act == 'hide')
            jQuery('input[name^=_epl_payment_method]', me).prop('checked', false).prop('disabled', true);
        //me.fadeOut();
    });


}

function do_options(elem, end, val) {
    elem.find('option').remove().end();

    for (i = 0; i <= end; i++) {
        elem.append(jQuery("<option></option>")
                .attr("value", i)
                .text(i));
    }

    elem.val(val);
}

//from awesome css-tricks.com
(function ($) {

    $.organicTabs = function (el, options) {

        var base = this;
        base.$el = $(el);
        base.$nav = base.$el.find(".nav");

        base.init = function () {

            base.options = $.extend({}, $.organicTabs.defaultOptions, options);

            // Accessible hiding fix
            $(".hide").css({
                "position": "relative",
                "top": 0,
                "left": 0,
                "display": "none"
            });

            base.$nav.delegate("li > a", "click", function () {

                // Figure out current list via CSS class
                var curList = base.$el.find("a.current").attr("href").substring(1),
                        // List moving to
                        $newList = $(this),
                        // Figure out ID of new list
                        listID = $newList.attr("href").substring(1),
                        // Set outer wrapper height to (static) height of current inner list
                        $allListWrap = base.$el.find(".list-wrap"),
                        curListHeight = $allListWrap.height();
                $allListWrap.height(curListHeight);

                if ((listID != curList) && (base.$el.find(":animated").length == 0)) {

                    // Fade out current list
                    base.$el.find("." + curList).fadeOut(base.options.speed, function () {

                        // Fade in new list on callback
                        base.$el.find("." + listID).fadeIn(base.options.speed);

                        // Adjust outer wrapper to fit new list snuggly
                        var newHeight = base.$el.find("." + listID).height();

                        $allListWrap.css({
                            'height': newHeight,
                            'top': '10px'
                        });
                        /*$allListWrap.animate({
                         height: newHeight
                         });*/

                        // Remove highlighting - Add to just-clicked tab
                        base.$el.find(".nav li a").removeClass("current");
                        $newList.addClass("current");

                    });

                }

                // Don't behave like a regular link
                // Stop propegation and bubbling
                return false;
            });

        };
        base.init();
    };

    $.organicTabs.defaultOptions = {
        "speed": 0
    };

    $.fn.organicTabs = function (options) {
        return this.each(function () {
            (new $.organicTabs(this, options));
        });
    };

})(jQuery);
