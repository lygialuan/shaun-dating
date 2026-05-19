var adminGender = function () {
  var loading = false
  var initCreate = function () {
    loading = false
    $('#gender_submit').unbind('click');
    $('#gender_submit').click(function () {
      if (loading) {
        return
      }
      loading = true
      $.ajax({ 
          type: 'POST', 
          url: $('#gender_form').attr('action'), 
          data: $('#gender_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            console.log(data);
            if (data.status){
              location.reload();
            }
            else{
              $('#gender_form #errors').html('');
              adminCore.setMessageError($('#gender_form #errors'),data.messages);
            }
          }
      }).done(function( data ) {
        loading = false
      });
    });
  }
  var initListing = function(orderUrl) {
    new Sortable(document.getElementById("genders_list"), {
        animation: 150,
        filter: ".admin_modal_ajax",
        onUpdate: function (e) {
            $.ajax({
                type: "POST",
                url: orderUrl,
                data: { orders: this.toArray() },
                dataType: "json",
            });
        },
    });
  }
  return {
    initCreate: initCreate,
    initListing: initListing
  };
}();