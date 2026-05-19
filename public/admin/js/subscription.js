var adminSubscription = function () {
    var initListing = function() {
        $('#date_type').change(function() {
            $('.subscription_custom').hide();
            if ($(this).val() == 'custom') {
                $('.subscription_custom').show();
            }
        })

        $('#date_type').trigger('change');
    } 

    return {
        initListing: initListing
    };
}();