var adminLanguage = function () {
  var loading = false
  var initCreate = function () {
    loading = false
    $('#language_submit').unbind('click');
    $('#language_submit').click(function () {
      if (loading) {
        return
      }
      loading = true

      $.ajax({ 
          type: 'POST', 
          url: $('#language_form').attr('action'), 
          data: $('#language_form').serialize(), 
          dataType: 'json',
          success: function (data) {
            if (data.status){
              location.reload();
            }
            else{
              $('#language_form #errors').html('');
              adminCore.setMessageError($('#language_form #errors'),data.messages);
            }
          }
      }).done(function( data ) {
        loading = false
      });
    })

    $('#is_default').change(function(){
      $('#is_active').attr('onclick', '')
      if ($(this).prop('checked')) {
        $('#is_active').prop('checked', true)
        $('#is_active').attr('onclick', 'return false')
      }
    })

    $('#is_default').trigger('change')
  }
  var initListing = function(storeDefaultUrl) {
  }

  var currentKey = '';
  var md5Key = '';
  var initEditPhrase = function(storeEditPhraseUrl) {
    $('#name').keyup(window.adminDelay(function (e) {
      var value = $(this).val();
      $('.phrases').show();
      if (value.trim() != '') {
        $('.phrases').each(function(e){
          var phrasesKey = $(this).find('.phrases_key').html().trim().toLowerCase();
          var phrasesValue = $(this).find('.phrases_value').html().trim().toLowerCase();
          if (phrasesKey.search(value.toLowerCase()) === -1 && phrasesValue.search(value.toLowerCase()) === -1) {
            $(this).hide();
          }
        });
      }
    }, 500));    
    
    $('.edit_phrases').click(function(){
      adminCore.openModal('modal-edit-phrase')
      currentKey = $(this).parents('.phrases').find('.phrases_key').html().trim();
      md5Key = $(this).parents('.phrases').attr('id');
      $('#modal-edit-phrase #value').val(adminCore.decodeHtml($(this).parents('.phrases').find('.phrases_value').html().trim()));
    });

    $('.modal-action-edit-phrase-save').click(function(){
      if (currentKey != '') {
        value = $('#modal-edit-phrase #value').val().trim();
        $.ajax({ 
            type: 'POST', 
            url: storeEditPhraseUrl, 
            data: {
              key: currentKey,
              value: value
            }, 
            dataType: 'json',
            success: function (data) {
              if (data.status){
                $('#'+ md5Key).find('.phrases_value').html(adminCore.endocdeHtml(value));
                adminCore.hideModal('modal-edit-phrase')
              }
            }
        });
      }
    });
  }
  var initUploadPhrase = function() {
    $('#language_upload_phrase_submit').click(function(){
      var formData = new FormData($('#language_upload_phrase_form')[0]);
      $('#language_upload_phrase_form #errors').html('');
      $.ajax({ 
        type: 'POST', 
        url: $('#language_upload_phrase_form').attr('action'), 
        data: formData, 
        dataType: 'json',
        contentType: false, //this is requireded please see answers above
        processData: false, //this is requireded please see answers above
        success: function (data) {
          if (data.status){
            location.reload();
          }else{            
            adminCore.setMessageError($('#language_upload_phrase_form #errors'),data.messages);
          }
        }
    });
    });
  }
  return {
    initCreate: initCreate,
    initListing: initListing,
    initEditPhrase: initEditPhrase,
    initUploadPhrase: initUploadPhrase
  };
}();