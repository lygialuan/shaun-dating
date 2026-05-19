var adminPaidContent = function () {
    var loading = false
    var initCreatePackage = function()
    {
        loading = false
        $('#package_submit').click(function(){        
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
                    if (data.status){
                        location.reload();
                    }
                    else{
                    $('#package_form #errors').html('');
                        adminCore.setMessageError($('#package_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    var initTipPackageListing = function (storeOrderUrl) {
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
    var initCreateTipPackage = function () {
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
    return {
        initCreatePackage: initCreatePackage,
        initCreateTipPackage: initCreateTipPackage,
        initTipPackageListing: initTipPackageListing
    };
  }();