<script>
    jQuery(document).ready(function($){


        $('.epl_show_tooltip').mouseover(function(){

            var me = $(this);
            var c = $('.event_details_hidden', me).html();

            //no tooltip if empty description
            if(c=='')
                return;

            $('body').append('<div id="ue_tooltip"><div class="tip_body">' + c + '</div></div>');

            var tt = $('#ue_tooltip');
            var elOffset= $(this).offset();
            tt.css('top', elOffset.top + 0 ).css('left', elOffset.left -320 ).fadeIn('500');
        }).mouseout(function(){

            $('#ue_tooltip').remove();

        });
    });




</script>