var adminTwoFactorProvider = function () {
    var loading = false
    var initEdit = function() {
        loading = false
        $('#two_factor_provider_submit').unbind('click');
        $('#two_factor_provider_submit').click(function () {
        if (loading) {
            return
        }
        loading = true
        $.ajax({ 
            type: 'POST', 
            url: $('#two_factor_provider_form').attr('action'), 
            data: $('#two_factor_provider_form').serialize(), 
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if (data.status){
                location.reload();
                }
                else{
                $('#two_factor_provider_form #errors').html('');
                adminCore.setMessageError($('#two_factor_provider_form #errors'),data.messages);
                }
            }
        }).done(function( data ) {
            loading = false
        });
        });
    }
    return {
        initEdit: initEdit,
    };
}();