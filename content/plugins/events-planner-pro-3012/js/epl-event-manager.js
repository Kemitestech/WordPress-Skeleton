

jQuery(document).ready(function ($) {

    /*
     * MAJOR CLEANUP AND REFACTORING IN ONE OF THE UPCOMING VERSIONS
     **/

    $('#epl_dates_section').resizable({
        resize: function (event, ui) {
            ui.size.width = ui.originalSize.width;
        }
    });


    $('body').on('click', '.epl_admin_del_attendee', function () {

        if (confirm('Are you sure?') === false)
            return false;

        var me = $(this);
        var par = me.parents('.epl_regis_attendee_wrapper');
        var event_id = me.data('event_id');
        var price_id = me.data('price_id');

        //decrease the quantity for this ticket by one
        var att_dd = $('select[id^=_att_quantity-' + event_id + '-' + price_id + '-]');

        if (att_dd.val() == 0) {
            return false;
        }

        var new_qty = att_dd.val() - 1;
        att_dd.val(new_qty);

        var deleted_ticket_no = $('input.epl_ticket_no-' + event_id + '-' + price_id, par).val();

        par.remove();

        $('.epl_regis_attendee_wrapper').each(function () {

            var sect = $(this);
            var current_ticket_no = $('.epl_ticket_no-' + event_id + '-' + price_id, sect);
            if(current_ticket_no.length == 0)
                return;
            if (current_ticket_no.val() > deleted_ticket_no) {
                var new_ticket_no = current_ticket_no.val()- 1;
                $('input[name*="[' + event_id + '][' + price_id + ']"],select[name*="[' + event_id + '][' + price_id + ']"]', sect).each(function () {

                    var field = $(this);

                    var new_count = new_ticket_no;
                    var new_name = field.prop('name').replace(/\[\w+\]\[\w+\]\[.+\]/, '[' + event_id + '][' + price_id + '][' + new_count + ']');
                    field.prop('name', new_name);
                    field.attr('data-ticket_no', new_count); //setting .data will not work

                });
                
                var legend = $('legend', sect);
                legend.text(legend.text().replace(/(\d{1,3}:)/, new_ticket_no +':'));
                current_ticket_no.val(new_ticket_no);
            }

        });
        $('a#admin_calc_total').trigger('click');
        return false;

    });

    //refactor
    $('body').on('click', '.epl_cb_selector', function () {
        var me = $(this);
        var targ = $('table.epl_email_to_list');

        if (me.hasClass('epl_select_all_email'))
            $('input[type=checkbox]', targ).prop('checked', true);

        if (me.hasClass('epl_select_status')) {
            var status = me.data('status');
            $('input[type=checkbox]', targ).prop('checked', false);
            $('input.regis_status_' + status, targ).prop('checked', true);
        }

        return false;
    });


    toggle_recurrence_fields();

    $('select[name=event_id]').css('width', '350px').select2();
    $('select[name=add_event_id]').css('width', '550px').select2();
    $('select.chzn-select').css('width', '200px').select2();


    $('.help_tooltip_trigger').tipsy({
        gravity: 's',
        offset: 10,
        opacity: 1.0,
        html: true
    });


    $('.epl_form_data_table tr').mouseover(function () {
        $('span.description', $(this)).css({
            'color': '#000'
        });
    }).mouseout(function () {
        $('span.description', $(this)).css({
            'color': '#ddd'
        });
    });

    $('#event_list_discount_import_dd').change(function () {
        var me = $(this);


        var data = "epl_action=import_discount&epl_controller=epl_event_manager&event_list_discount_import_dd=" + me.val()
                + "&discount_import_action=" + $('#discount_import_action').val()
                + "&post_ID=" + $('#post_ID').val();

        events_planner_do_ajax(data, function (r) {

            var wrapper = $('#epl_discount_data_wrapper');
            wrapper.slideUp(function () {
                wrapper.html(r.html).slideDown();
            });

        });
        me.val('');

        return false;

    });

    $('.epl_discount_usage_view').click(function () {

        var me = $(this);
        var id = me.prop('id');

        var data = "epl_action=get_discount_usage_report&epl_controller=epl_global_discount&post_ID=" + id;


        events_planner_do_ajax(data, function (r) {


            epl_modal.open({
                content: r.html,
                width: "700px",
                height: "500px"
            });

        });


        return false;
    });
    //will switch to .on down the road

    function price_specific_dd_populate() {

        var avail_prices = $('.epl_prices_row_table input[name^="_epl_price_name"]');

        $('.price_specific_selector')
                .html($('<option>', {
                    value: ''
                })
                        .text('Specific Price?'));

        //date_specific_selector

        $(avail_prices).each(function () {
            var me = $(this);
            var patrn = /\[\w+\]|\[\]/g;
            var tmp_name = me.attr('name');

            var new_name = patrn.exec(tmp_name);

            $('.price_specific_selector')
                    .append($('<option>', {
                        value: new_name
                    })
                            .text(me.val()));

        });

    }
    ;



    $('body').on('change', '.price_specific_selector', function () {
        var me = $(this);

        var cont = me.parents('tr');
        var patrn = /\[\w+\]|\[\]/g;
        var v = me.val();

        var par_id = $('input[name^="_epl_discount_code"]', cont).prop('name');
        var par_id = patrn.exec(par_id);
        var ins = '<input type="text" style="display:inlline;" readonly="readonly" name="_epl_price_specific_discount' + par_id + v + '" value="' + $('option:selected', me).text() + '"/>';
        $('td.price_specific_discount', cont).append(ins);
        me.val('');
    });


    $('body').on('click', '.price_specific_selector', function () {
        price_specific_dd_populate();
    });
    $('body').on('dblclick', 'input[name^="_epl_price_specific_discount"]', function () {
        $(this).remove();
    });


    $('body').on('click', '.epl_view_attendee_list_table', function () {
        var me = $(this);

        var url = me.prop('href');

        url = url.split('?')[1];

        events_planner_do_ajax(url, function (r) {
            //show_slide_down(r.html);
            epl_modal.open({
                content: r.html,
                width: "1100px",
                height: "600px"
            });
        });

        return false;
    });
    $('body').on('click', '.epl_send_email_form_link', function () {


        //var data =  $(this).prop('href') + "&post_ID=" + this.getAttribute('data-post_ID') + "&event_id=" + this.getAttribute('data-event_id') ;
        var me = $(this);

        var url = me.prop('href');

        url = url.split('?')[1];

        events_planner_do_ajax(url, function (r) {
            //show_slide_down(r.html);
            epl_modal.open({
                content: r.html,
                width: "750px",
                height: "600px"
            });
        });

        return false;
    });

    $('body').on('click', '#epl_send_email_button', function () {

        tinyMCE.triggerSave();

        var data = "epl_action=send_email&epl_controller=epl_registration&" + $('form#epl_email_form').serialize();


        events_planner_do_ajax(data, function (r) {

            alert(r.html);

        });

        return false;
    });

    $('body').on('change', '.epl_notification_dd', function () {
        var me = $(this);
        if (me == '') {
            alert('Please select a template.')
            return false;
        }


        var data = "epl_action=get_the_email_form_editor&epl_controller=epl_registration&notif_id=" + me.val();


        events_planner_do_ajax(data, function (r) {

            $('#epl_email_editor_wrapper').html(r.html);


            tinyMCE.init({
                mode: "exact",
                elements: "email_body",
                //theme : "advanced",
                relative_urls: false,
                remove_script_host: false,
                remove_linebreaks: false,
                //mode : "textareas",
                //editor_selector :"email_body",
                width: "600",
                height: "480",
                //plugins : "inlinepopups,spellchecker,tabfocus,paste,media,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs",
                theme_advanced_toolbar_location: "top",
                theme_advanced_toolbar_align: "left",
                theme_advanced_statusbar_location: "bottom",
                theme_advanced_resizing: true
            });

        });

    });
    $('body').on('click', '.send_waitlist_approval_email', function () {
        var me = $(this);

        var data = "epl_action=send_waitlist_approval_email&epl_controller=epl_registration&post_ID=" + this.getAttribute('data-post_ID') + "&event_id=" + this.getAttribute('data-event_id');

        events_planner_do_ajax(data, function (r) {

            show_slide_down(r.html);

        });


        return false;

    });

    $('.load_fullcalendar').click(function () {
        var par = $(this).closest('table');

        var data = "epl_action=load_fullcalendar&epl_controller=epl_event_manager&parent=" + par.prop('id');

        events_planner_do_ajax(data, function (r) {

            show_slide_down(r.html);

        });


        return false;

    });

    $('a#add_event_to_cart').click(function () {

        var event_id = $('#add_event_id').val();
        var admin_cart_section = $('#epl_regis_cart_data');

        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = {
            'epl_action': 'process_cart_action',
            'cart_action': 'add_event',
            'epl_controller': 'epl_registration',
            'add_event_id': event_id,
            '_date_id': get_query_variable('_date_id', ''),
            '_time_id': get_query_variable('_time_id', '')
        }

        data = $.param(data) + "&" + $('form').serialize();

        events_planner_do_ajax(data, function (r) {
            if (EPL.sc == 1)
                admin_cart_section.append(r.html);
            else
                admin_cart_section.html(r.html);
        });

        return false;

    });
    $('body').on('click', 'a#admin_calc_total', function () {

        var event_id = $('#event_list_id').val();
        var admin_totals_section = $('#admin_totals_section');


        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=process_cart_action&cart_action=calc_total&epl_controller=epl_registration&event_list_id=" + event_id + "&" + $('form').serialize();


        events_planner_do_ajax(data, function (r) {
            admin_totals_section.html(r.html);

            $('input[name="_epl_grand_total"]').val(r.total_due);
            update_balance_due();

        });


        return false;

    });

    $('body').on('click', 'a.delete_cart_item', function () {

        if (confirm('Are you sure?  After deletion, please use the Calculate Total and Get Registration Forms buttons to update this registration info.  Also, please hit Update/Publish to finalize changes.') === false)
            return false;

        var me = $(this);
        var par = me.parents('.admin_cart_section');
        var quantities = $('select[name^=_att_quantity]', par);

        $.each(quantities, function () {
            var _me = $(this);
            if (_me.val() > 0) {
                $('<input type="hidden" name="deleted_event' + _me.prop('name').replace('_att_quantity', '') + '" value="' + _me.val() + '" />').appendTo($('form#post'));
            }
        });


        par.slideUp(400, function () {
            par.remove()
        });

        return false;

    });
    $('body').on('click', 'a#admin_get_regis_form', function () {

        var event_id = $('#event_list_id').val();
        var admin_regis_section = $('#admin_regis_section');


        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=process_cart_action&cart_action=regis_form&epl_controller=epl_registration&event_list_id=" + event_id + "&" + $('form').serialize();


        events_planner_do_ajax(data, function (r) {
            var d = r.html; //$('.data', par).val();
            //

            admin_regis_section.html(d);
            //console.log(r.html);
            //alert(d);


        });


        return false;

    });
    $('body').on('click', 'a.epl_event_snapshot, a.epl_regis_snapshot, a.epl_payment_snapshot', function (e) {

        e.preventDefault();
        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');
        var epl_action = me.prop('class');


        if (me.hasClass('epl_event_snapshot_active')) {
            me.removeClass('epl_event_snapshot_active');
            me.parents('tr').next('tr').slideUp(1000).remove();
            return false;
        }

        var data = "epl_action=" + epl_action + "&epl_controller=epl_registration&post_ID=" + this.getAttribute('data-post_ID') + "&event_id=" + this.getAttribute('data-event_id');


        events_planner_do_ajax(data, function (r) {

            //load the event snapshot
            if (me.hasClass('epl_event_snapshot')) {
                me.addClass('epl_event_snapshot_active');
                var parent_tr = me.parents('tr');
                var colspan = $('>th, >td', parent_tr).size();//may be th or td in there

                parent_tr.after('<tr><td colspan ="' + colspan + '"  class="epl_regis_snapshot_td">' + r.html);

            } else {


                epl_modal.open({
                    content: r.html,
                    width: "910px",
                    height: "500px"
                });

            }

            create_datepicker('.datepicker');


        });


        return false;

    });


    $('body').on('submit', 'form.epl_regis_payment_meta_box_form', function () {
        var me = $(this);
        post_ID = $('input[name="post_ID"]', me).val();

        var data = "epl_action=update_payment_details&epl_controller=epl_registration&" + me.serialize();

        events_planner_do_ajax(data, function (r) {

            hide_slide_down();
            $('.epl_regis_list_payment_info_wrapper_' + post_ID).hide().html(r.html).fadeIn();

        });

        return false;
    });

    $('a.epl_get_help, a.epl_send_email').click(function () {

        var me = $(this);
        var data = '';

        if (me.hasClass('epl_send_email')) {

            data = "epl_load_feedback_form=1&epl_controller=epl_event_manager&section=" + me.prop('id');
            events_planner_do_ajax(data, function (r) {

                show_slide_down(r.html);


            });
            //show_slide_down('THE FEEDBACK FORM');
            return false;
        }

        data = "epl_get_help=1&epl_controller=epl_event_manager&section=" + me.prop('id');

        events_planner_do_ajax(data, function (r) {


            show_slide_down(r.html);


        });
        return false;
    });
    $('body').on('submit', '#epl_feedback_form', function () {
        var me = $(this);

        if (!epl_validate(me))
            return false;

        var data = "epl_send_feedback=1&epl_controller=epl_event_manager&" + me.serialize();
        epl_loader('show');
        events_planner_do_ajax(data, function (r) {

            show_slide_down(r.html);


        });
        return false;


    });

    $('#epl_pay_type').change(function () {


        var me = $(this);
        var par = me.parent();
        var id = me.prop('id');

        var data = "epl_action=get_pay_profile_fields&epl_controller=epl_pay_profile&" + $('form').serialize();
        //alert (me.val());
        //alert (data);

        events_planner_do_ajax(data, function (r) {
            var d = r.html; //$('.data', par).val();
            //

            $('#epl_pay_profile_fields_wrapper').html(r.html)

        });


        return false;

    });

    $('select[name="_epl_pricing_type"]').change(function () {
        var data = "epl_action=epl_pricing_type&epl_controller=epl_event_manager&" + $('form').serialize();

        //$("#container").html('<img src="' + EPL.plugin_url + 'images/ajax-loader.gif">').delay(5000);
        events_planner_do_ajax(data, function (r) {

            $('#epl_time_price_section').html(r.html);


        });

        create_datepicker('.datepicker');
        create_timepicker('.timepicker');
        //hide_loader();
        return false;



    });
    $(".checklist :radio").parents('li').removeClass("selected");
    $(".checklist :radio:checked").parents('li').addClass("selected");

    $(".checklist li").click(
            function (event) {
                // event.preventDefault();
                $(".checklist :radio").parents('li').removeClass("selected");
                $(this).addClass("selected");
                $(this).find(":radio").attr("checked", "checked");

                toggle_recurrence_fields();
            }
    );

    $(".checklist .checkbox-deselect").click(
            function (event) {
                event.preventDefault();
                $(this).parent().removeClass("selected");
                $(this).parent().find(":radio").removeAttr("checked");
            }
    );

    $('input[name="epl_event_type"]').change(function () {


        toggle_recurrence_fields();


    });

    function toggle_recurrence_fields() {
        var v = $(':input[name="_epl_event_type"]:checked').val();

        var nfc = $(".not_for_class");
        var fc = $(".for_class");
        switch (v) {
            case "10":
                fc.show();
                nfc.hide();
                break;

            default:
                fc.hide();
                nfc.show();
                $.each(nfc, function () {
                    $(this).show();

                })


        }


    }


    $("a#recurrence_process, a#recurrence_preview").click(function () {


        var act = $(this).prop('id');

        var data = "epl_action=" + act + "&epl_controller=epl_event_manager&" + $('form').serialize();
        //$("#container").html('<img src="' + EPL.plugin_url + 'images/ajax-loader.gif">').delay(5000);
        events_planner_do_ajax(data, function (r) {

            if (act == 'recurrence_preview') {
                $("#slide_down_box div.display").html(r.html);
                show_slide_down();
            } else {
                var dates_section = $('#epl_dates_section');
                dates_section.slideUp(function () {
                    dates_section.html(r.html).slideDown();
                    create_datepicker('.datepicker');
                });
            }
        });
        //hide_loader();
        return false;

    });


    $('body').on('click', 'a.add_time_block', function () {


        //the last time box
        var box = $('div.time-box:last');

        //clone and inster after the last one
        $(box.clone()).insertAfter('div.time-box:last');

        //find the last inserted time box
        var ins = $.find('div.time-box:last'); //inserted element

        //$(ins).css('background-color','red');
        //the time section inside the newest inserted box
        var time_section = $('table:first', ins);

        //the price section inside the newest inserted box
        var price_section = $('.price-box', ins);


        //inside the new time section, remove the index
        //var new_time_index = $(time_section).find('input[name^="epl_start_time"]').attr('name').replace(/(\D)+/g,"");

        //new_time_index = (new_time_index === '')?0:new_time_index; //if [], it will be an empty string
        //new_time_index++;

        var new_time_index = get_random_string();
        //console.log(new_time_index);
        jQuery(':input', time_section).each(function () {

            var me = jQuery(this);
            //me.css('background-color','red');
            //me.val('');
            if (me.hasClass('hasTimepicker'))
                me.removeClass('hasTimepicker');

            var tmp_name = me.attr('name');
            var new_name = tmp_name.replace(/\[\w+\]|\[\]/g, '[' + new_time_index + ']');
            //console.log(new_name);
            me.attr('name', new_name);


        });

        //var new_time_index = $(ins).find('input[name^="epl_start_time"]').attr('name').replace(/(\D)+/g,"");
        //var new_price_key = Date.now();

        jQuery('tr', price_section).each(function () {
            var new_price_key = get_random_string();
            jQuery(':input', jQuery(this)).each(function () {

                var me = jQuery(this);

                //me.val('');

                var tmp_name = me.prop('name');

                me.prop('name', tmp_name.replace(/\[\w+\]|\[\]/, '[' + new_price_key + ']'));


                if (tmp_name.search('_epl_price_parent_time_id') !== -1)
                {
                    me.val(new_time_index);
                    //tmp_name =  tmp_name.replace(/\[\d+\]\[/g, '[' + new_time_index + '][');
                    //me.attr('name',  tmp_name.replace(/\]\[\d+/,"]["));

                }




            });
        });

        create_timepicker($('.timepicker', time_section));
        return false;


    });

    $('a.check_all, a.uncheck_all').click(function () {

        var cont = $(":input[name^='_epl_recurrence_weekdays']");
        epl_checkbox_state(cont, $(this).prop('class'));

        return false;
    });


    create_sortable(jQuery("#epl_dates_table > tbody"));
    create_sortable(jQuery("#epl_prices_table > tbody"));
    //create_sortable(jQuery("#epl_time_price_section"));
    create_sortable(jQuery("#epl_class_session_table > tbody"));
    create_sortable(jQuery("#epl_discount_table > tbody"));
    create_sortable(jQuery(".epl_sortable"));
    create_datepicker('.datepicker');
    create_timepicker('.timepicker');

});
