var adminVibb = function () {
    var loading = false
    var initSongListing = function() {
        $("audio").on("play", function() {
            $("audio").not(this).each(function(index, audio) {
                audio.pause();
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
        initSongListing: initSongListing,
        initCreateSong: initCreateSong
    };
}();