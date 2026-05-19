var adminCache = function () {
  var initSave = function (storeUrl) {
    $('#driver').change(function () {
      $('.cache-driver').hide();
      $('#content_'+$(this).val()).show();
    });

    $('#cache_form').submit(function (e) {
      e.preventDefault();
      $.ajax({ 
          type: 'POST', 
          url: $('#cache_form').attr('action'), 
          data: $('#cache_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            if (data.status){
              location.reload();
            }
            else{
              $('#cache_form #errors').html('');
              adminCore.setMessageError($('#cache_form #errors'),data.messages);
            }
          }
      });
    });
  }
  return {
    initSave: initSave
  };
}();