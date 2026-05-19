var adminMail = function () {
    var initCreate = function (url) {
        tinymce.init({
            selector: "textarea.content_textarea",
            plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime media nonbreaking save table directionality",
                "emoticons template paste textcolor"
            ],
            toolbar1: "styleselect | bold italic | bullist numlist outdent indent | forecolor backcolor emoticons | link unlink anchor image | preview fullscreen code",
            image_advtab: true,
            image_dimensions: false,
            height: 500,
            relative_urls : false,
            remove_script_host : true,
            convert_urls : false
        });

        $('#id, #language').change(function () {
            var redirectUrl = url + '/' + $('#id').val();
            if ($('#language').length) {
                redirectUrl += '/' + $('#language').val();
            }
            window.location.href = redirectUrl;
        });
    }

    var initTest = function() {
      $('#mail_test_submit').unbind('click');
      $('#mail_test_submit').click(function () {
        $.ajax({ 
            type: 'POST', 
            url: $('#mail_test_form').attr('action'), 
            data: $('#mail_test_form').serialize(), 
            dataType: 'json',
            success: function (data) {
              console.log(data);
              $('#mail_test_form #errors').html('');
              $('#mail_test_form .message-success').hide();
              
              if (data.status){
                $('#mail_test_form .message-success').show();
                $('#mail_test_form .message-success').html(data.message);
              }
              else{                
                adminCore.setMessageError($('#mail_test_form #errors'),data.messages);
              }
            }
        });
      });
    }

    var initListing = function () {
        $('#search').keyup(function(){
            const search = $(this).val();
            if (search == '') {
              $('.template_list tr').show();
            } else {
              $('.template_list tr').hide();
              $('.template_name').each(function(){
                var text = $(this).html().trim().toLowerCase();
                if (text.search(search.toLowerCase()) !== -1) {
                  $(this).parent().show();
                }
              })
            }
        });
    }

    return {
      initCreate: initCreate,
      initTest: initTest,
      initListing: initListing
    };
}();