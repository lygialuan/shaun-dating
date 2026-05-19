var adminHashtag = function () {
    var loading = false
    var initCreate = function () {
        loading = false
        $('#hashtag_submit').unbind('click');
        $('#hashtag_submit').click(function () {
            if (loading) {
                return
            }
            loading = true
            $.ajax({ 
                type: 'POST', 
                url: $('#hashtag_form').attr('action'), 
                data: $('#hashtag_form').serialize(), 
                dataType: 'json',
                success: function (data) {
                    if (data.status){
                        location.reload();
                    }
                    else{
                    $('#hashtag_form #errors').html('');
                        adminCore.setMessageError($('#hashtag_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }

    var initListing = function(storeActiveUrl, listingUrl) {
        $('.is_active').click(function(e) {
            var active = $(this).prop("checked");
            if(active){
                $('#status-action-'+$(this).data('id')).html(adminTranslate.__('deactive'));
                $('#status-'+$(this).data('id')).html(adminTranslate.__('active'));
            }else{
                $('#status-action-'+$(this).data('id')).html(adminTranslate.__('active'));
                $('#status-'+$(this).data('id')).html(adminTranslate.__('inactive'));
            }
            $.post(storeActiveUrl, { id: $(this).data('id'), active : active ? 1 : 0 } );
        });

        $('#name').keyup(function(e){
            if(e.which == 13){
                window.location.href = listingUrl + '/' + $('#name').val();
            }
        });
    }
    return {
      initCreate: initCreate,
      initListing: initListing
    };
  }();