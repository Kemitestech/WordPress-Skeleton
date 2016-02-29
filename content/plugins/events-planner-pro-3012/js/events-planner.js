jQuery(document).ready(function($) {

    /*$('.help_tip').tipsy({
        opacity: 1
    });*/

    $.ajaxSetup({
        cache: false,
        xhr: function()
        {
            if ($.browser.msie)
            {
                return new ActiveXObject("Microsoft.XMLHTTP");
            }
            else
            {
                return new XMLHttpRequest();
            }
        },
        type: "POST",
        url:  EPL.ajaxurl
    });
    $('body').on('click', '#dismiss_loader', function(){
        hide_slide_down();
        return false;
    });
    $('body').on('click', '.ttp_close', function(){

        $(this).closest('#epl_tooltip').remove();


    });
    
    $('#epl_console').dblclick(function(){
        $(this).html('');
    })
    //$('#slide_down_box').draggable();
    $('body').on('click', '.open_lookup_form', function(){

        $('body').data('lookup_caller_section', $(this).parents('.epl_regis_attendee_wrapper').prop('id'));

        var vars = {
            'epl_action':'wildcard_lookup_form',
            'epl_controller':'epl_user_regis_manager',
            'scope':'regis_forms'
                                
        };

                    
        var data = $.param(vars);

        events_planner_do_ajax( data, function(r){
            
            epl_modal.open({
                content: r.html,
                width: "900px",
                height: "600px"
            });
            $('#epl_wildcard_lookup').focus();
        });
        return false; 
        
    });

    $('body').on('change','select[id=user_id]', function(e){
        return false;
        var user_id = e.val;
        if(user_id == "")
            return false;
       
        var data = {
            'epl_action':'get_last_regis_form_data',
            'epl_controller':'epl_user_regis_manager',
            'user_id':user_id
        }
        data = $.param(data);

        events_planner_do_ajax( data, function(r){
           lookup_result_select(r);

        });
        return false;
    });
    
    $('body').on('submit', 'form#epl_lookup_form', function(){

        var par = $(this);
        var lookup = $('#epl_wildcard_lookup', par).val();

        if (lookup=='')
            return false;
            

        var vars = {
            'epl_action':'wildcard_lookup',
            'epl_controller':'epl_user_regis_manager',
            'lookup': lookup
            
                                
        };

                    
        var data = $.param(vars);
        data += '&' + par.serialize();

        events_planner_do_ajax( data, function(r){
            
            var d = r.html;//.toString();
            $('#lookup_result').html(d);
        /* epl_modal.open({
                    content: d,
                    width: "900px"
                });*/

        });
        return false;
           
    });
        
    $('body').on('click', 'a.epl_lookup_row_select', function(){

        var me = $(this);
        var data = {};
        
        lookup_result_select({
            'form_data':me.next('span.form_data').html()
        });
        epl_modal.close();

        return false;
       
    });
    
 
    $('body').on('click', '.epl_delete_element, .epl_delete_element_no_conf, .delete_element', function(){

        _EPL.delete_element({
            me: $(this)
        });
        return false;

    });



    $(".toggle_container").slideUp();

    $('body').on('click', '.expand_trigger', function(){
        $(this).toggleClass("expand_active").next().slideToggle("normal");
        return false;
    });
    $('body').on('click', '.epl_tt_close', function(){
        $('#epl_tooltip').remove()
    });


    $('body').on('click', 'a.epl_copy_from', function(){
        var cont = '';
        me = $(this);
        var par = me.parents('div.epl_regis_attendee_wrapper');
        var par_id = par.prop('id');

        $('div.epl_regis_attendee_wrapper').not(par).not('#epl_attendee_lookup_wrapper').each(function(){

            var _me = $(this);

            var title = $('legend',_me).html();
            
            if (!title)
                title = $('h1',_me).html();

            cont += '<a href="#" class="epl_copy_from_source" id="' + par_id +'__'+ _me.prop('id')+'">' + title + '</a><br />';

        });

        epl_add_tooltip(me, cont)

        return false;
    });
    $('body').on('click', 'a.epl_copy_from_source', function(){
        var me = $(this);

        var id = me.prop('id');

        id = id.split('__');

        var to = id[0].split('--')[1];
        var from =id[1].split('--')[1];


        epl_form_to_form(from, to);
        return false;



    });

    function epl_form_to_form(from_id, to_id) {

        var form_a = $('#epl_form_section--' + from_id);
        var form_b = $('#epl_form_section--' + to_id);

        $(':input', form_b).each(function() {
            var _me = $(this);
            var _val = _me.val();
            var tmp_name = _me.attr('name');
            //tmp_name = tmp_name.replace(/(?=.*)\[\d\]+/,'[' + to_id + ']');
            tmp_name = tmp_name.split('[',1);
            tmp_name = tmp_name[0];


            var _type = _me.prop('type');

            var _index = _me.index();
            var _orig = $('[name^="' + tmp_name +'"]', form_a);


            switch (_type)
            {
                case 'text':
                case 'textarea':
                    _me.val(_orig.val());
                    break;
                case 'radio':
                case 'checkbox':
                    _orig = $('[name^="' + tmp_name +'"][value="' + _val +'"]',form_a);

                    if (_orig.is(":checked")  )
                        _me.prop("checked", true);
                    else
                        _me.prop("checked", false);

                    break;
                default:
                    _me.val(_orig.val());
            }

            
        //console.log(_me.attr('name') + ' > ' + tmp_name + ' >orig ' + _val + ' >> dest ' + _dest.val() + ' >> index ' + _index);


        });
        $('#epl_tooltip').remove();
    }



    function epl_add_tooltip(elem, cont, loc ){

        $('#epl_tooltip').remove();

        loc  = 'tt_' + pick(loc, 'bottom');

        $('body').append('<div id="epl_tooltip" class="' + loc + '"><img class="epl_tt_close" src="' +EPL.plugin_url + 'images/cross.png" alt="Close"/><div class="tip_body">' + cont + '</div></div>');

        var ttp =  $('#epl_tooltip');
        var ttp_h = ttp.height();

        var el_offset= elem.offset();

        ttp.css('top', el_offset.top + 35 ).css('left', el_offset.left - 130 ).delay(300).fadeIn(200, function(){
            var new_height = $('#epl_tooltip').height();
            //alert(ttp_h);
            //alert(new_height);
            if(new_height != ttp_h){

                $('#epl_tooltip').animate({

                    top: '-=' + (new_height - ttp_h)
                },200);

            };
        });

    }



    function pick(arg, def) {
        return (typeof arg == 'undefined' ? def : arg);
    }



});

var _EPL = {
    

    progress: function(container){

        $(container).html('<img src="' + EPL.plugin_url + 'images/ajax-loader2.gif">');

    },

    add_table_row: function(params){

        var row =jQuery(params.table).find('tbody tr.copy_:last').html();//can use .clone() but will have to use cloned.appendTo

        var ins = params.table.append('<tr>' + row + '</tr>');//.find('tr:last');

        ins = jQuery(params.table).find(' > tbody > tr:last');//very specific, the new row may have another table in it
        
        ins.find('img.ui-datepicker-trigger').remove(); //need to remove datepicker icon so when datepicker re-created, there aren't two calendar icons
        
        var new_key = get_random_string();

        //for each one of the elements in the inserted row, remove the value and get rid of the index in the name key
        jQuery(':input',ins).each(function(){

            var me = jQuery(this);

            var tmp_name = me.attr('name');
            if (typeof tmp_name != 'undefined'){
                me.attr('name',  tmp_name.replace(/\[\w+\]|\[\]/,'[' + new_key + ']'));

                if (me.hasClass('hasDatepicker')){
                
                    me.removeClass('hasDatepicker').removeAttr('id');
                    //me.datepicker('destroy');
                    create_datepicker(me);
                
                };

                if (me.hasClass('hasTimepicker')){

                    me.removeClass('hasTimepicker').removeAttr('id');
                    //me.datepicker('destroy');
                    create_timepicker(me);

                };
                
                if(me.hasClass('reset_val'))
                    me.val('');
            /*if (me.attr('type') != 'hidden')
                me.val('');*/
            }

        });

        //find if there is another table inside the row.  If so, remove the header.
        if(jQuery("> tbody > tr", params.table).size() > 1){

        //jQuery('thead', ins).hide();

        }

        create_datepicker(jQuery('.datepicker', ins));
        create_timepicker(jQuery('.timepicker', ins));

        return ins;

        return false;

    },
    delete_table_row: function(params){

        var par = params.me.closest('table');


        if(jQuery("tbody >tr", par).size() == 1)
        {
            alert ("At least one row is required.");
            return false;
        }

        var par_row = params.me.closest('tr');

        var rel = params.me.attr('rel');

        if (typeof rel == 'undefined'){

            jQuery('body').data('epl_del_elem', par_row);


            var conf = '<a href="#" class="delete_table_row"  rel="yes">Confirm</a>';
            var cancel = ' <a href="#" class="delete_table_row" rel="no">Cancel</a>';
            _EPL.do_overlay({
                'elem':par_row,
                'content':conf + cancel
            });

        }
        else if (rel == 'no'){

            _EPL.hide_overlay();
            
        }
        else if (rel == 'yes'){

            jQuery('body').data('epl_del_elem').slideUp().remove();

            jQuery('body').removeData('epl_del_elem');
            _EPL.hide_overlay();
        }



        return false;


    },
    delete_element: function(params){

        /*
 *  How this works.
 *  - an element is clicked to delete another element
 *      - In the first click, the .data stores the element to be deleted
 *      - If a function needs to run after confirmation, the function is stored in data as well
 *      - an overlay is displayed over the element that needs to be deleted, with confirm and cancel links
 *   - if cancel, remove overlay, empty .data
 *   - if confirm, remove element, run function, if any();
 */

        //var par = params.par;

        var par = params.me.closest('li');

        if (par.length == 0){
            par = params.me.closest('tr');

            if(!params.force && jQuery("> tbody >tr", par.closest('table') ).size() == 1)
            {
                alert ("At least one row is required.");
                return false;
            }

        }

        if (par.length == 0)
            par = params.me.parents('div').eq(0);

        var rel = params.me.attr('rel');
        
        if (rel == 'no_conf'){
            par.slideUp().slideUp(300).remove();
            return false;
        }

        if (typeof rel == 'undefined'){

            jQuery('body').data('epl_del_elem', par);
            jQuery('body').data('epl_del_elem_action', params.action);


            var conf = '<a href="#" class="delete_element " rel="yes">Confirm</a>';
            var cancel = ' <a href="#" class="delete_element " rel="no">Cancel</a>';
            var _ol_id = _EPL.do_overlay({
                'elem':par,
                'content':conf + cancel
            });

            jQuery('body').data('epl_del_elem_ol_id', _ol_id);
        }
        else if (rel == 'no'){

            _EPL.hide_overlay({
                '_ol_id': jQuery('body').data('epl_del_elem_ol_id')
            });
            jQuery('body').removeData('epl_del_elem');
            jQuery('body').removeData('epl_del_elem_action');
            jQuery('body').removeData('epl_del_elem_ol_id');
            
        }
        else if (rel == 'yes'){
           
            var r = null;
            if (typeof jQuery('body').data('epl_del_elem_action') != 'undefined'){
                //invoke the function
                r = jQuery('body').data('epl_del_elem_action')();

            } else {
                 
                r = jQuery('body').data('epl_del_elem').slideUp().slideUp(300).remove();
            }

            _EPL.hide_overlay({
                '_ol_id': jQuery('body').data('epl_del_elem_ol_id')
            });
            jQuery('body').removeData('epl_del_elem');
            jQuery('body').removeData('epl_del_elem_action');
            jQuery('body').removeData('epl_del_elem_action');

            return r;

        }



        return false;


    },
    replace_element: function(params){

        var elem = params.elem;

        _EPL.hide_overlay();

        //elem.slideUp();//.replaceWith(params.content).slideDown();
        elem.replaceWith(params.content);

        return false;


    },
    assign_input_value: function(params){

        var val = params.value;
        var _parent_form = params.parent_form;
        var _input = jQuery(":input[name='" + params.input_name + "']", _parent_form);
       
        if (_input.length === 0) //if the input was not found, chances are it's an array
            _input = jQuery(":input[name='" + params.input_name + "[]']", _parent_form);

           
        switch (_input.prop('type'))
        {
            case 'text':
            case 'hidden':
            case 'select-one':
            case 'textarea':

                _input.val(val);
                break;
            case 'radio':
            case 'checkbox':
                // val comes in as 0,1,2
                //jQuery still accepts these values and determines which one to select, YAY :)
                //Could have kept with the above group but put them here just in case

                _input.val(val);

                // if the above doesn't work, try
                var arr = jQuery.makeArray(val);

                jQuery(_input).each(function(){

                    if (jQuery.inArray(jQuery(this).val(), arr) > -1) //-1 = not found
                        jQuery(this).prop("checked", "checked");

                });
                break;

        }


    },
    do_overlay: function(params){

        var elem = params.elem;

        var _new_id = 'AAA' + get_random_string(); //Date.now() returns a formatted date;
        
        var  div_overlay = jQuery('#epl_overlay').clone(true).prop('id', _new_id).addClass('epl_overlay').hide();
        var _offsets = elem.offset();

        div_overlay.css({
            top: _offsets.top,
            left: _offsets.left,
            width: elem.outerWidth(),
            height: elem.outerHeight(true)
        });
        jQuery('div',div_overlay).html(params.content);

        div_overlay.appendTo('body').slideDown('normal');
        return _new_id;
    },
    hide_overlay: function(params){
        if (params !== undefined)
            jQuery('div#' + params._ol_id).fadeOut(400).remove();
        else
            jQuery('.epl_overlay').fadeOut(400).remove();
    },
    hide_overlay_all: function(){

        jQuery('.epl_overlay').fadeOut(400).remove();
    },
    populate_dd: function(el, num_options){

        var temp_val =0;
        temp_val = el.val(); // in case there is a selected value, remember


        if (temp_val > 0)
            num_options = parseInt(temp_val) + parseInt(num_options);

        //remove all the <options> and reconstruct
        el.children().remove();
        //Reconstruch the dd based on avaiable spaces left
        for (var i=0;i<=num_options;i++){
            jQuery(el).append(
                jQuery('<option></option>').val(i).html(i)
                );


        }
        //assign the previously selected value to the newly modified dd
        el.val(temp_val);

    }




}

/*
 * Global Functions
 *
 **/

function lookup_result_select(data){
    //var response_form_data = jQuery.parseJSON(me.next('span.form_data').html());
    
    var caller_section_id = jQuery('body').data('lookup_caller_section');
    if(!caller_section_id)
        caller_section_id = 'epl_form_section--0';
    
    var caller_section = jQuery("#" + caller_section_id);

    var response_form_data = jQuery.parseJSON(data['form_data']);

    clear_form(caller_section);
   
    var _form = jQuery('input,select',caller_section);

    jQuery.each(_form, function(){
        var _me = jQuery(this);
        var input_name = _me.prop('name').replace(/(\[.+)/,'');

        if(!response_form_data.hasOwnProperty(input_name))
            return true;
                
        var val = response_form_data[input_name];
                
        if(val === false) return true;
                
        var _type = _me.prop('type');
                

        switch (_type)
        {
            case 'text':
            case 'textarea':
            case 'select':
            case 'select-one':
                _me.val(val);
                break;
            case 'radio':
            case 'checkbox':
 
                var this_cb = jQuery(this);
                this_cb.prop("checked", false);
                        
                val = val.split(',');
                if( jQuery.inArray(this_cb.val(), val) != -1)
                    this_cb.prop("checked", true);
   
                break;

        /* default:
                        _me.val(val);*/
        }
                

    });

    if(typeof data['user_id'] !== undefined && caller_section_id.slice(-3) == '--0'){

        jQuery('select#user_id').select2("val", response_form_data['user_id']);

    }
    return false;
}
    
function epl_date_now(){
    if (!Date.now) {
        Date.now = function() {
            return new Date().valueOf();
        }
    }
    return Date.now();
}

function events_planner_do_ajax(data, callback){
    data += "&epl_ajax=1&action=events_planner_form&_rand=" + Math.random();
    jQuery.ajax({
        data: data,
        dataType: "json",
        beforeSend: function(){
            epl_loader('show');
        },
        success: function(response, textStatus){

            events_planner_process_response(response, callback);

        },
        error: function(response) {
            events_planner_process_response(response, callback);//alert("Error.");
        },
        complete:function(){
            epl_loader('hide');
        }
    });

}

function events_planner_process_response(from_server, callback)
{
    if (from_server == null){
        return false;
    }

    if (from_server.debug_message != null && EPL.debug == 1){
        jQuery.each(from_server.debug_message, function( index, value ) {
            epl_console( index + ' >> ' + value );
        });
    }

    if (from_server.is_error ==1){
           
        alert(from_server.error_text);
            

    }
    else
    {
        callback(from_server);
    }

    return true;
}

function epl_loader(act){

    var loader = jQuery('#epl_loader');

    if (act == 'show'){
        loader.show();
 
    }
    else {
        loader.hide();
    }
}


function show_loader_image(container){

    jQuery('#'.container).html('<img src="' + EPL.plugin_url + 'images/ajax-loader.gif" alt="loading..." />');

}

function show_slide_down(cont){

    show_loader_image('slide_down_box div.display');

    if(cont !== '')
        jQuery("#slide_down_box div.display").html(cont);

    jQuery('#slide_down_box').animate({
        'top':'0px'
    },300);

    return true;
}

function hide_slide_down(){

    var height = Number(jQuery('#slide_down_box').outerHeight(true) + 10);
    epl_static_var(true);
    jQuery('#slide_down_box').animate({
        'top':'-' + height + 'px'
    },300,function(){
                        
        });
};

function create_datepicker(elem){

    jQuery( elem ).datepicker({
        dateFormat: EPL.date_format,
        showOn: "button",
        buttonImage: EPL.plugin_url + "images/calendar_1.png",
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        firstDay: EPL.firstDay,        
        yearRange: EPL.yearRange
    });

}
function create_sortable(elem){
    jQuery( elem ).sortable();

}

function create_lightbox(){
    jQuery( '.lightbox' ).lightbox();

}
function create_timepicker(elem){
    var _t_f =  (EPL.time_format == "H:i")?false:true;
    var opt = {
        showPeriod: _t_f,
        showLeadingZero: !_t_f,
        showPeriodLabels: _t_f
    };

    jQuery(elem).timepicker(opt);

}

function destroy_datepicker(elem){
    jQuery( elem ).datepicker('destroy');

}

function clear_form(form){

    jQuery(':input',form)
    .not(':button, :submit, :reset, :hidden, .no_clear')
    .val('')
    .removeAttr('checked')
    .removeAttr('selected');
}

//get the nonce fields, scope
//this is used for ajax calls when we don't need to send the whole form data
//on the admin side
function get_essential_fields(_form){

    return "&form_scope=" + jQuery(":input[name='form_scope']",_form).val() + "&_epl_nonce=" + jQuery(":input[name='_epl_nonce']",_form).val()
    + "&_wp_http_referer=" + jQuery(":input[name='_wp_http_referer']",_form).val()
    + "&epl_controller=" + jQuery(":input[name='epl_controller']",_form).val();


}

function epl_checkbox_state(control, state){
    state = (state == 'check_all'?true:false);
    jQuery(control).prop("checked",state);

}

function get_random_string() {
    var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
    var string_length = 6;
    var random_string = '';
    for (var i=0; i<string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        random_string += chars.substring(rnum,rnum+1);
    }
    return random_string;
}

/*
 * JS field validations.  Will only check for required or email, for now.
 * Planning on adding min, max, alpha, numeric, custom regexp, tooltip
 */

function epl_validate(form){

    epl_valid = true;
    jQuery.each (jQuery('input, textarea',form), function (){

        var field = jQuery(this);

        if (field.prop('type') != 'submit' && field.prop('type') != 'hidden'){
            
            if (!epl_validate_field(field))
                epl_valid = false;

        }
    });


    return epl_valid;
}

function epl_validate_field(field){

    //http://www.regular-expressions.info/email.html should get 99.99%
    var email_regexp = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;



    if (field.hasClass('required') && jQuery.trim(field.val()) == ''){

        field.css('background-color','pink');
        /*par.animate({
            backgroundColor: 'pink'
        }, 300);*/
        return false;
        
    }

    if (field.hasClass('email') && !validate_regex(field.val(), email_regexp)){

        
        field.css('background-color','pink');
        return false;
    } else {
 
        field.css('background-color','#fff');
        return true;
    }
}


function get_query_variable(variable, query) {
    if(query !='')
        query =query.substring(1);
    else    
        query = location.search;
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    return '';

}



function validate_regex(FValue, VRegExp){
    if(VRegExp){
        var re=new RegExp(VRegExp);
        if (re.test(FValue)) {
            return true;
        } else {
            return false;
        }
    }
    else
    {
        return "empty";
    }



}

function setup_select2(elem){
    if(jQuery().select2)
        jQuery(elem).css('width', 'auto').select2();
}

function epl_block(elem){
    if (jQuery.type(elem) != 'object')
        return null;
    elem.block({
        message: '<img src="'+ EPL.plugin_url + '/images/ajax-loader5.gif" />',
        overlayCSS: { 
            backgroundColor:'#fff',
            opacity: 0.7
        },
        css: {
            border:'0px solid #fff'
        }
    });
    
}

function epl_console(content){

    jQuery("<div />").text(content).appendTo("#epl_console");

}

function epl_static_var (v, reset){

    //var _v  = jQuery('body').data('_static_'+v)


    /*if ( typeof epl_static_var.c == 'undefined') {
        // It has not... perform the initilization
        epl_static_var.c = 0;
    } else {
        epl_static_var.c++;
    }*/
    return

}
/*
jQuery(function($){
	$.datepicker.regional['de'] = {
		closeText: 'schlieÃŸen',
		prevText: '&#x3c;zurÃ¼ck',
		nextText: 'Vor&#x3e;',
		currentText: 'heute',
		monthNames: ['Januar','Februar','MÃ¤rz','April','Mai','Juni',
		'Juli','August','September','Oktober','November','Dezember'],
		monthNamesShort: ['Jan','Feb','MÃ¤r','Apr','Mai','Jun',
		'Jul','Aug','Sep','Okt','Nov','Dez'],
		dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
		dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		weekHeader: 'Wo',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['de']);
});*/

var epl_modal = (function($){
    var 
    method = {},
    $overlay,
    $modal,
    $content,
    $close;

    // Center the modal in the viewport
    method.center = function () {
        var top, left;

        top = Math.max($(window).height() - $modal.outerHeight(true), 0) / 2;
        left = Math.max($(window).width() - $modal.outerWidth(true), 0) / 2;

        $modal.css({
            top:top + $(window).scrollTop(), 
            left:left + $(window).scrollLeft()
        });
    };

    // Open the modal
    method.open = function (settings) {
        $content.empty().append(settings.content);
        //$('body').css('overflow','hidden');
        $modal.css({
            width: settings.width || 'auto', 
            height: settings.height || 'auto'
        });

        method.center();
        $(window).bind('resize.modal', method.center);
        $modal.show();
        $overlay.show();

    };

    // Close the modal
    method.close = function () {
        $modal.hide();
        $overlay.hide();
        $content.empty();
        $(window).unbind('resize.modal');
    // $('body').css('overflow','visible');

    };

    // Generate the HTML and add it to the document
    $overlay = $('<div id="epl_modal_overlay"></div>');
    $modal = $('<div id="epl_modal"></div>');
    $content = $('<div id="epl_modal_content"></div>');
    $close = $('<a id="epl_modal_close" href="#">Close</a>');

    $modal.hide();
    $overlay.hide();
    $modal.append($content, $close);

    $(document).ready(function(){
        $('body').append($overlay, $modal);
    //$modal.draggable();
    });

    $close.click(function(e){
        e.preventDefault();
        method.close();
    });
    $overlay.click(function(e){
        e.preventDefault();
        method.close();
    });

    return method;
}(jQuery));
