var adminLinkIcon = function () {
  var loading = false
  var initCreate = function () {
    loading = false
    $('#link_icon_submit').unbind('click');
    $('#link_icon_submit').click(function () {
      if (loading) {
        return
      }
      loading = true
      var form = $('#link_icon_form');
      var formData = new FormData(form[0]);
      $.ajax({ 
          type: 'POST', 
          url: $('#link_icon_form').attr('action'), 
          data: formData, 
          dataType: 'json',
          contentType: false,
          processData: false,
          cache: false,
          success: function (data) {
            console.log(data);
            if (data.status){
              location.reload();
            }
            else{
              $('#link_icon_form #errors').html('');
              adminCore.setMessageError($('#link_icon_form #errors'),data.messages);
            }
          }
      }).done(function( data ) {
        loading = false
      });
    });
  }
  return {
    initCreate: initCreate,
  };
}();