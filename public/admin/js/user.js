var adminUser = function () {
    var loading = false
    var initCreate = function () {
      $('#user_form').on( "submit", function( event ) {
          if (loading) {
              event.preventDefault();
          }
          loading = true
      });
    }

    var initChangePassword = function() {
      $('#user_change_password_submit').unbind('click');
      $('#user_change_password_submit').click(function () {
        if (loading) { 
          return;
        }
        loading = true
        $.ajax({ 
            type: 'POST', 
            url: $('#user_change_password_form').attr('action'), 
            data: $('#user_change_password_form').serialize(), 
            dataType: 'json',
            success: function (data) {
              console.log(data);
              if (data.status){
                location.reload();
              }
              else{
                $('#user_change_password_form #errors').html('');
                adminCore.setMessageError($('#user_change_password_form #errors'),data.messages);
              }
            }
        }).done(function( data ) {
          loading = false
        });
      });
    }

    var initListing = function() {
       $('#delete_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {            
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_user'), function(){
            $('#action').val('delete');
            $('#user_form').submit();
          });
       });
       $('#active_button').click(function(e) {
          if ($('.check_item:checked').length == 0) {
            return;
          }

          adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_active_user'), function(){
            $('#action').val('active');
            $('#user_form').submit();
          });
       });
    }

    return {
      initCreate: initCreate,
      initChangePassword: initChangePassword,
      initListing: initListing
    };
  }();