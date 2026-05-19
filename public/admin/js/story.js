var adminStory = function () {
    var loading = false
    var initBackgroundListing = function(storeActiveUrl, storeOrderUrl) {
        $('.active_action').click(function(e) {
            var active = $(this).data('status');
            if(active == '1'){
                $(this).data('status', '0');                
                $(this).html(adminTranslate.__('active'));
                $('#status-'+$(this).data('id')).html(adminTranslate.__('inactive'));
            }else{
                $(this).data('status', '1');
                $(this).html(adminTranslate.__('deactive'));
                $('#status-'+$(this).data('id')).html(adminTranslate.__('active'));
            }
            $.post(storeActiveUrl, { id: $(this).data('id'), active : active == '0' ? 1 : 0 } );
        });

        new Sortable(document.getElementById('background_list'), {
            animation: 150,
            filter: '.active_action',
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
    var initSongListing = function() {
        $("audio").on("play", function() {
            $("audio").not(this).each(function(index, audio) {
                audio.pause();
            });
        });
    }
    var initCreateBackground = function() {
        loading = false
        $('#background_submit').click(function(){        
            var form = $('#background_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#background_form').attr('action'), 
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
                    $('#background_form #errors').html('');
                        adminCore.setMessageError($('#background_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    var initCreateSong = function (){
        loading = false
        $('#song_submit').click(function(){        
            var form = $('#song_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#song_form').attr('action'), 
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
                    $('#song_form #errors').html('');
                        adminCore.setMessageError($('#song_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    return {
        initBackgroundListing: initBackgroundListing,
        initSongListing: initSongListing,
        initCreateBackground: initCreateBackground,
        initCreateSong: initCreateSong
    };
}();