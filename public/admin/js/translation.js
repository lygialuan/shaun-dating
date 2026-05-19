var adminTranslation = function () {
    var loading = false
    var initEditModel = function () {
        loading = false
        $('#translation_edit_model_submit').click(function(){
            if (loading) {
                return
            }
            loading = true
            $.ajax({ 
                type: 'POST', 
                url: $('#translation_edit_model_form').attr('action'), 
                data: $('#translation_edit_model_form').serialize(), 
                dataType: 'json',
                success: function (data) {
                  if (data.status){
                    location.reload();
                  }
                  else{
                    $('#translation_edit_model_form #errors').html('');
                    adminCore.setMessageError($('#translation_edit_model_form #errors'),data.messages);
                  }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    return {
        initEditModel: initEditModel
    };
}();