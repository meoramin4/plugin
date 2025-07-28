jQuery(function($) {
    // Normal send for new referrals and for change email
    $('.er-dashboard-products').on('click', '.er-referral-slot-submit', function(e) {
        e.preventDefault();

        var btn = $(this);
        var row = btn.closest('.er-referral-slot-row');
        var card = btn.closest('.er-referral-product-box');
        var product_id = card.data('product-id');
        var slot = row.data('slot');
        var email_input = row.find('.er-referral-slot-email');
        var email = email_input.val().trim();
        var status_span = row.find('.er-status');
        var referral_id = row.data('referral-id');

        // If input is not readonly and referral_id exists, trigger change AJAX
        if (referral_id && !email_input.prop('readonly')) {
            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                status_span.text('Please enter a valid email.')
                    .removeClass().addClass('er-status failed').show();
                return;
            }
            btn.prop('disabled', true);
            status_span.text('Updating...')
                .removeClass().addClass('er-status pending').show();
            $.post((typeof erData !== 'undefined' && erData.ajaxUrl) ? erData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                action: 'er_change_referral_email',
                referral_id: referral_id,
                product_id: product_id,
                email: email,
                nonce: (typeof erData !== 'undefined' && erData.nonce) ? erData.nonce : ''
            }, function(res){
                btn.prop('disabled', false);
                if (res.success) {
                    email_input.val(email).prop('readonly', true);
                    btn.hide();
                    row.find('.er-change-email-btn').show();
                    status_span.text('Pending')
                        .removeClass().addClass('er-status pending').show();
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : 'Error';
                    status_span.text(msg)
                        .removeClass().addClass('er-status failed').show();
                }
            });
            return;
        }

        // Otherwise, normal create AJAX for empty slot
        if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
            status_span.text('Please enter a valid email.')
                .removeClass().addClass('er-status failed').show();
            return;
        }
        btn.prop('disabled', true);
        status_span.text('Sending...')
            .removeClass().addClass('er-status pending').show();

        $.post((typeof erData !== 'undefined' && erData.ajaxUrl) ? erData.ajaxUrl : '/wp-admin/admin-ajax.php', {
            action: 'er_submit_referral_email',
            product_id: product_id,
            slot: slot,
            email: email,
            nonce: (typeof erData !== 'undefined' && erData.nonce) ? erData.nonce : ''
        }, function(res) {
            btn.prop('disabled', false);
            if (res.success) {
                email_input.val(email).prop('readonly', true);
                btn.hide();
                status_span.text('Pending')
                    .removeClass().addClass('er-status pending').show();
            } else {
                var msg = (res.data && res.data.message) ? res.data.message : 'Error';
                status_span.text(msg)
                    .removeClass().addClass('er-status failed').show();
            }
        });
    });

    // Change email handler for pending referrals
    $('.er-dashboard-products').on('click', '.er-change-email-btn', function(e){
        e.preventDefault();
        var btn = $(this);
        var row = btn.closest('.er-referral-slot-row');
        var email_input = row.find('.er-referral-slot-email');
        var status_span = row.find('.er-status');
        email_input.prop('readonly', false).val('').focus();
        btn.hide();
        status_span.text('').hide();
        row.find('.er-referral-slot-submit').show();
    });
});