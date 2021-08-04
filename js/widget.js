jQuery(document).ready(function ($) {
    $('#epcw-verify-form').on('submit', function (e) {
        e.preventDefault();
        var epcw_purchase_key = $('#epcw_purchase_key').val();
        $.ajax({
        type : "post",  
        url: ajax_object.ajax_url,
        data: {
            'action':'epcw_verify_purchase_code',
            'epcw_purchase_key' : epcw_purchase_key
        },
        success:function(data) {
            $('#epcw-verify-form-output').html(data);
        },
        error: function(errorThrown){
            $('#epcw-verify-form-output').html(errorThrown);
        }
    }); 
    });
});