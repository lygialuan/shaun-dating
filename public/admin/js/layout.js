var adminLayout = function () {
    var self = this;
    var initEdit = function (listingUrl, editBlockUrl, storeUrl) {
      self.editBlockUrl = editBlockUrl;
      $('#page_id').change(function(){
        window.location.href = listingUrl + '/' + $(this).val();
      });

      Sortable.create(document.getElementById('blockContent'), {
        group: {
          name: 'content',
          put: false,
          pull: 'clone'
        },        
        sort: false,
        handle: '.drag'
      });

      $('.viewType').each(function(){
        var key = $(this).data('view');
        createSort(document.getElementById('centerContent_' + key), key);        
        if (key != 'header' && key != 'footer') {          
          createSort(document.getElementById('topContent_' + key), key);
          createSort(document.getElementById('rightContent_' + key), key);
          createSort(document.getElementById('bottomContent_' + key), key);
        }
      });

      $('body').on('click', '.content_edit', function (e) {
        e.preventDefault();
        loadBlock($(this));
      });

      $('body').on('click', '.content_remove', function (e) {
        e.preventDefault();
        if ($(this).data('id') > 0) {
          var ids = $('#block_remove_ids').val();
          $('#block_remove_ids').val(ids + $(this).data('id') + ',');
        }
        $(this).parents('.drag').remove();
      });

      $('#save_change').click(function(){
        var data = {
          'blocks' : {},
          'block_remove_ids' : $('#block_remove_ids').val(),
          'page_id' : $('#page_id').val(),
        };
        $('.viewType').each(function(){
          var key = $(this).data('view');
          data.blocks[key] = [];
          $(this).find('.content_edit').each(function(){
            dataContent = $(this).data();
            if (typeof dataContent.role_access == 'object') {
              dataContent.role_access = JSON.stringify(dataContent.role_access)
            }
  
            if (typeof dataContent.params == 'object') {
              dataContent.params = JSON.stringify(dataContent.params);
            }
            data.blocks[key].push(dataContent);
          });
        });

        $.ajax({ 
            type: 'POST', 
            url: storeUrl, 
            data: data, 
            dataType: 'json',
            success: function (data) {
              location.reload();
            }
        });
      });

      $('#name').keyup(function(){
        const search = $(this).val();
        if (search == '') {
          $('.available_widgets_list .available_widgets_list_item').show();
        } else {
          $('.available_widgets_list .available_widgets_list_item').hide();
          $('.available_widgets_list .available_widgets_list_item').each(function(){
            var text = $(this).find('.title').html().trim().toLowerCase();
            if (text.search(search.toLowerCase()) !== -1) {
              $(this).show();
            }
          })
        }
      });
    }

    var createSort = function (element, key) {
      Sortable.create(element, {
        group: {
          name: key,
          put: ['content', key]
        },
        sort: true,
        handle: '.drag',
        onAdd: function (e) {
          var item = $(e.item);
          if ($(e.from).attr('id') == 'blockContent') {
            item.find('.layout-block-col-item-actions').show();
            loadBlock(item.find('.content_edit'));
          }
          setPositionBlock(item.find('.content_edit'),$(e.target));
        }
      });
    }

    var loadBlock = function(item) {
      $.get( self.editBlockUrl + '/' + item.data('component') + '/' + item.data('id'), function( html ) {
        $('#modal-ajax').html($('#modal-loading').html());
        adminCore.openModal('modal-ajax');
        $('#modal-ajax .modal-card').html(html);
        setDataEditBlock(item);
        item.parents('.action').show();
        $('#modal-ajax #block_edit_submit').click(function(){
          item.data('title',$('#block_edit_form #title').val());
          item.data('content',$('#block_edit_form #content').val());
          item.parents('.drag').find('.title').html($('#block_edit_form #title').val());

          item.data('enable_title',$('#block_edit_form #enable_title').prop('checked') ? '1' : '0');
          var roleAccess = [];
          $('#block_edit_form .allow_role_access').each(function(){
              if ($(this).prop('checked')) {
                roleAccess.push($(this).val());
              }
          });
          item.data('role_access',JSON.stringify(roleAccess));
          var params = {};
          try {
            var nameFunction = 'get' + item.data('component');
            params = window[nameFunction]();
          } catch (error) {
            
          }
          item.data('params',JSON.stringify(params));

          $('#modal-ajax .modal-close').trigger('click');
        });
      });
    }

    var setDataEditBlock = function (element) {
      var data = element.data();
      $('#block_edit_form #title').val(data.title);
      if (data.enable_title != 1) {
        $('#block_edit_form #enable_title').prop('checked', false);
      } else {
        $('#block_edit_form #enable_title').prop('checked', true);
      }
      
      var roleAccess = [];
      if (typeof data.role_access == 'string')
      {
        try {
          roleAccess = JSON.parse(data.role_access);  
        } catch (error) {
          
        }
      } else {
        roleAccess = data.role_access;
      }

      if (roleAccess.length > 0) {        
        $('#block_edit_form .allow_role_access').each(function(){
            if (roleAccess.includes($(this).val())) {
              $(this).prop('checked', true);
            } else {
              $(this).prop('checked', false);
            }
        });

        adminCore.initAllowAccess('role_access');        
      }
      $('#block_edit_form #content').val(data.content);

      var params = {}
      if (typeof data.params == 'string')
      {
        try {
          params = JSON.parse(data.params);  
        } catch (error) {
          
        }
      } else {
        params = data.params;
      }

      try {
        var nameFunction = 'set' + data.component;
        
        window[nameFunction](params);
      } catch (error) {
        
      }
    }

    var setPositionBlock = function (element,target) {
      var postion = target.attr('class');
      element.data('position',postion);
    }

    var initEditPage = function() {
      $('#layout_page_submit').unbind('click');
      $('#layout_page_submit').click(function () {
        $.ajax({ 
            type: 'POST', 
            url: $('#layout_page_form').attr('action'), 
            data: $('#layout_page_form').serialize(), 
            dataType: 'json',
            success: function (data) {
              if (data.status){
                location.reload();
              }
              else{
                $('#layout_page_form #errors').html('');
                adminCore.setMessageError($('#layout_page_form #errors'),data.messages);
              }
            }
        });
      });
    }

    return {
      initEdit: initEdit,
      initEditPage: initEditPage
    };
  }();