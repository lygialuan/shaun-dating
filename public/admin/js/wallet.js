var adminWallet = function () {
    var loading = false
    var initPackageListing = function (storeOrderUrl) {
        new Sortable(document.getElementById('package_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
                $.ajax({
                    type: 'POST',
                    url: storeOrderUrl,
                    data: { orders: this.toArray() },
                    dataType: 'json',
                    success: function (data) {

                    }
                });
            }
        });
    }
    var initCreatePackage = function () {
        loading = false
        $('#package_submit').click(function () {
            var form = $('#package_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({
                type: 'POST',
                url: $('#package_form').attr('action'),
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    }
                    else {
                        $('#package_form #errors').html('');
                        adminCore.setMessageError($('#package_form #errors'), data.messages);
                    }
                }
            }).done(function (data) {
                loading = false
            });
        });
    }
    var initWithdrawListing = function () {
        $('#accept_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_accept_wallet'), function () {
                $('#action').val('accept');
                $('#withdraw_form').submit();
            });
        });
        $('#reject_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_reject_wallet'), function () {
                $('#action').val('reject');
                $('#withdraw_form').submit();
            });
        });
        $('#export').click(function(e) {
            e.preventDefault();
            if ($('#withdraw_list td').length < 2) {
                return;
            }
            $('#status_export').val($('#status').val());
            $('#name_export').val($('#name').val());
            $('#type_export').val($('#type').val());
            
            $('#action').val('export');
            $('#withdraw_form').submit();
        })
    }

    var initBillingActivityListing = function() {
        $('#date_type').change(function() {
            $('.wallet_custom').hide();
            if ($(this).val() == 'custom') {
                $('.wallet_custom').show();
            }
        })

        $('#date_type').trigger('change');
    } 

    return {
        initPackageListing: initPackageListing,
        initCreatePackage: initCreatePackage,
        initWithdrawListing: initWithdrawListing,
        initBillingActivityListing: initBillingActivityListing
    };
}();