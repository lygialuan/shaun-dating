var adminGateway = function () {
    var loading = false
    var initListing = function(storeOrderUrl) {
        new Sortable(document.getElementById('gateway_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
              $.ajax({ 
                  type: 'POST', 
                  url: storeOrderUrl, 
                  data: {orders: this.toArray()}, 
                  dataType: 'json',
                  success: function (data) {
    
                  }
              });
            }
        });
    }
    var initEdit = function() {
        loading = false
        $('#gateway_submit').click(function(){        
            var form = $('#gateway_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#gateway_form').attr('action'), 
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
                    $('#gateway_form #errors').html('');
                        adminCore.setMessageError($('#gateway_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    return {
        initListing: initListing,
        initEdit: initEdit
    };
}();