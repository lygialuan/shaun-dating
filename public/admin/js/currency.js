var adminCurrency = function () {
  var initCreate = function () {
    $('#currency_submit').unbind('click');
    $('#currency_submit').click(function () {
      $.ajax({ 
          type: 'POST', 
          url: $('#currency_form').attr('action'), 
          data: $('#currency_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            if (data.status){
              location.reload();
            }
            else{
              $('#currency_form #errors').html('');
              adminCore.setMessageError($('#currency_form #errors'),data.messages);
            }
          }
      });
    });
  }
  var initListing = function(storeDefaultUrl) {
    $('.is_default').click(function(e) {
      if (!$(this).is(':checked')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
      $('.currency_delete').show();
      $('.is_default').prop("checked", false);
      $(this).prop("checked", true);
      $(this).parent().parent().parent().find('.currency_delete').hide();

      $.post(storeDefaultUrl, { id: $(this).data('id') } );
    });
  }
  return {
    initCreate: initCreate,
    initListing: initListing
  };
}();