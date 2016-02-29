<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<div class="epl_regis_attendee_wrapper">
    <div id="" class="epl_regis_field_wrapper epl_section regis_form">

        <div class="header">

            <legend style="white-space: nowrap;"><?php epl_e( 'Billing Information' ); ?></legend>

        </div>
        <div class="payment-errors epl_font_red"></div>

        <div class="form-row">
            <label for="">Card Number<em>*</em></label>
            <div>
                <input type="text" data-stripe="number" /> 
            </div>
        </div>

        <div class="form-row">
            <label for="">CVC<em>*</em></label>
            <div>
                <input type="text" style="width:60px;" data-stripe="cvc" /> 
            </div>
        </div>

        <div class="form-row">
            <label for="">Expiration (MM/YYYY)<em>*</em></label>
            <div>
                <?php echo $exp_month['field']; ?>
                <span> / </span>
                <?php echo $exp_year['field']; ?>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">



    jQuery(document).ready(function ($) {
        Stripe.setPublishableKey('<?php echo trim($stripe['publishable_key']); ?>');
        var stripeResponseHandler = function (status, response) {
            var $form = $('form#events_planner_shopping_cart');

            if (response.error) {

                $form.find('.payment-errors').text(response.error.message);
                $form.find('input[type=submit]').prop('disabled', false);

            } else {

                var token = response.id;

                $form.append($('<input type="hidden" name="stripeToken" />').val(token));

                $form.get(0).submit();
            }
        };
        
        $('body').on('submit', 'form#events_planner_shopping_cart', function (event) {
            
            var $form = $(this);

            $form.find('input[type=submit]').prop('disabled', true);

            Stripe.card.createToken($form, stripeResponseHandler);

            return false;
        });

    });



</script>

