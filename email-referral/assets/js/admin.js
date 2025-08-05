jQuery(function($) {
    // Handle AJAX delete for referral product
    $('.er-table').on('click', '.er-delete-product', function(e){
        e.preventDefault();
        if (!confirm('Delete this product?')) return;
        var product_id = $(this).data('id');
        $.post(ajaxurl, {
            action: 'er_delete_product',
            product_id: product_id,
            nonce: er_admin.nonce
        }, function(res){
            if(res.success) {
                location.reload();
            } else {
                alert(res.data.message || 'Error deleting product');
            }
        });
    });
});