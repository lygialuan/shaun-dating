var adminMenu = function () {
    var loading = false
    var changeType = function() {
      $('#url_help').hide();
      if ($('#type').val() == 'internal') {
        $('#url_help').show();
        changeUrl();
      }
    }
    var changeUrl = function() {
      $('#url_prefix').html($('#url').val())
    }
    var initCreateItem = function () {
      loading = false
      $('#type').change(function(){
        changeType();
      });
      $('#url').keyup(function(){
        changeUrl();
      });
      $('#is_header').change(function(){
        $('#url_content').show();
        $('#parent_content').show();
        $('#type_content').show();
        $('#new_tab_content').show();
        if ($(this).prop('checked')) {
          $('#url_content').hide();
          $('#parent_content').hide();
          $('#type_content').hide();
          $('#new_tab_content').hide();
          $('#parent').val(0);
          $('#url').val('');
        }
      });
      $('#menu_item_submit').click(function(){ 
        if (loading) {
          return
        }
        loading = true
        var form = $('#menu_item_form');
        var formData = new FormData(form[0]);

        $.ajax({ 
            type: 'POST', 
            url: $('#menu_item_form').attr('action'), 
            data: formData, 
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            success: function (data) {
              if (data.status){
                location.reload();
              }
              else{
                loading = false
                $('#menu_item_form #errors').html('');
                adminCore.setMessageError($('#menu_item_form #errors'),data.messages);
              }
            }
        })
      });
    }
    var initListing = function(storeOrderUrl) {
      $('.menu-list-core').each(function(){
        new Sortable(document.getElementById($(this).attr('id')), {
          animation: 150,
          filter: '.admin_modal_ajax',
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
      });
      
      $('.menu_child').each(function(){
        new Sortable(document.getElementById($(this).attr('id')), {
          animation: 150,
          filter: '.admin_modal_ajax',
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
      });
    }
    return {
      initCreateItem: initCreateItem,
      initListing: initListing
    };
  }();