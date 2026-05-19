var adminRole = function () {
    var loading = false
    var initCreate = function () {
        loading = false
        $('#role_submit').unbind('click');
        $('#role_submit').click(function () {
          if (loading) {
            return
          }
          loading = true
          $.ajax({ 
              type: 'POST', 
              url: $('#role_form').attr('action'), 
              data: $('#role_form').serialize(), 
              dataType: 'json',
              success: function (data) {
                if (data.status){
                  location.reload();
                }
                else{
                  $('#role_form #errors').html('');
                  adminCore.setMessageError($('#role_form #errors'),data.messages);
                }
              }
          }).done(function( data ) {
            loading = false
          });
        })
    }

    var initListing = function(storeDefaultUrl) {
      $('.is_default').click(function(e) {
        if (!$(this).is(':checked')) {
          e.preventDefault();
          e.stopImmediatePropagation();
          return false;
        }
        $('.is_default').prop("checked", false);
        $(this).prop("checked", true);
  
        $.post(storeDefaultUrl, { id: $(this).data('id') } );
      });
    }

    return {
        initCreate: initCreate,
        initListing: initListing
    };
}();
  