jQuery(document).ready(function($) {
    $('.er-referral-email-form').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        var email = $form.find('input[name="referral_email"]').val();
        var product_id = $form.find('input[name="product_id"]').val();

        $.post(er_ajax.ajax_url, {
            action: 'er_submit_referral',
            nonce: er_ajax.nonce,
            email: email,
            product_id: product_id
        }, function(response){
            if(response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });
});