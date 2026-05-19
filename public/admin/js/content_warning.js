var adminWarningContent = function () {
  var loading = false
  var initCreateCategory = function () {
    loading = false
    $('#warning_content_category_submit').unbind('click');
    $('#warning_content_category_submit').click(function () {
      if (loading) {
        return
      }
      loading = true
      $.ajax({ 
          type: 'POST', 
          url: $('#warning_content_category_form').attr('action'), 
          data: $('#warning_content_category_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            console.log(data);
            if (data.status){
              location.reload();
            }
            else{
              $('#warning_content_category_form #errors').html('');
              adminCore.setMessageError($('#warning_content_category_form #errors'),data.messages);
            }
          }
      }).done(function( data ) {
        loading = false
      });
    });
  }

  var initCategoriesListing = function(storeOrderUrl) {
    new Sortable(document.getElementById('content_warnings_list'), {
        animation: 150,
        onUpdate: function () {
          $.ajax({ 
              type: 'POST', 
              url: storeOrderUrl, 
              data: {orders: this.toArray()}, 
              dataType: 'json'
          });
        }
    });
}
  
  return {
    initCreateCategory: initCreateCategory,
    initCategoriesListing: initCategoriesListing
  };
}();