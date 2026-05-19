var adminOpenProvider = function () {
  var initCreateOpenProvider = function (url, deleteUrl) {
    $('#app_name').on( "keypress", function() {
        $('#return_url').val(url.replace('123', $(this).val()))
        $('#return_app_url').val(url.replace('123', $(this).val()) + '/1')
        $('#delete_user_url').val(deleteUrl.replace('123', $(this).val()))
    });
    
    $('#app_name').trigger('keypress')
  }
  var initListing = function(storeOrderUrl) {
    new Sortable(document.getElementById('provider_list'), {
        animation: 150,
        filter: 'a',
        onUpdate: function (e) {
          $.ajax({ 
              type: 'POST', 
              url: storeOrderUrl, 
              data: {orders: this.toArray()}, 
              dataType: 'json',
              success: function (data) {

              }
          });
        }
    });
  }
  return {
    initCreateOpenProvider: initCreateOpenProvider,
    initListing: initListing
  };
}();