var adminReport = function () {
  var loading = false
  var initCreateCategory = function () {
    loading = false
    $('#report_category_submit').unbind('click');
    $('#report_category_submit').click(function () {
      if (loading) {
        return
      }
      loading = true
      $.ajax({ 
          type: 'POST', 
          url: $('#report_category_form').attr('action'), 
          data: $('#report_category_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            console.log(data);
            if (data.status){
              location.reload();
            }
            else{
              $('#report_category_form #errors').html('');
              adminCore.setMessageError($('#report_category_form #errors'),data.messages);
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

      adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_report'), function(){
        $('#report_form').submit();
      });
   });
  }
  var initCategoriesListing = function(orderCategoriesListUrl) {
      new Sortable(document.getElementById("categories_list"), {
        animation: 150,
        filter: ".admin_modal_ajax",
        onUpdate: function (e) {
            $.ajax({
                type: "POST",
                url: orderCategoriesListUrl,
                data: { orders: this.toArray() },
                dataType: "json",
            });
        },
      });
  }
  return {
    initCreateCategory: initCreateCategory,
    initCategoriesListing: initCategoriesListing,
    initListing: initListing
  };
}();