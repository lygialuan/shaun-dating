var adminVerify = function () {   
    var loading = false
    var initListing = function() {
       $('#verify_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {            
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_verify_user'), function(){
            $('#action').val('verify');
            $('#user_form').submit();
          });
       });
       $('#reject_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_reject_user'), function(){
            $('#action').val('reject');
            $('#user_form').submit();
          });
       });
    }

    var initReject = function() {
      loading = false
      $('#verify_submit').unbind('click');
      $('#verify_submit').click(function () {
        if (loading) {
          return
        }
        loading = true
        $.ajax({ 
            type: 'POST', 
            url: $('#verify_form').attr('action'), 
            data: $('#verify_form').serialize(), 
            dataType: 'json',
            success: function (data) {
              if (data.status){
                location.reload();
              }
              else{
                $('#verify_form #errors').html('');
                adminCore.setMessageError($('#verify_form #errors'),data.messages);
              }
            }
        }).done(function( data ) {
          loading = false
        });
      });
    }
    return {
      initListing: initListing,
      initReject: initReject
    };
}();