var adminTheme = function () {
  var loading = false
  var initCreate = function () {
    loading = false
    $('#theme_submit').unbind('click');
    $('#theme_submit').click(function () {
      if (loading) {
        return
      }
      loading = true
      $.ajax({ 
          type: 'POST', 
          url: $('#theme_form').attr('action'), 
          data: $('#theme_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            console.log(data);
            if (data.status){
              location.reload();
            }
            else{
              $('#theme_form #errors').html('');
              adminCore.setMessageError($('#theme_form #errors'),data.messages);
            }
          }
      }).done(function( data ) {
        loading = false
      });
    });
  }
  var initListing = function(storeActiveUrl) {
    $('.is_active').click(function(e) {
      if (!$(this).is(':checked')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
      $('.is_active').prop("checked", false);
      $(this).prop("checked", true);

      $.post(storeActiveUrl, { id: $(this).data('id') } );
    });
  }
  return {
    initCreate: initCreate,
    initListing: initListing
  };
}();