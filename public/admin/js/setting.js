var adminSetting = function () {
    var init = function (storeUrl) {
        $('.setting_input_text, .setting_textarea, .setting_select').each(function(){
            $(this).blur(function() {
                const formData = new FormData();
                formData.append('key',$(this).attr('name'));
                formData.append($(this).attr('name'),$(this).val());                
                
                store(formData, $(this), storeUrl)
            });
        });

        $('.setting_select').each(function(){
            $(this).change(function() {
                const formData = new FormData();
                formData.append('key',$(this).attr('name'));
                formData.append($(this).attr('name'),$(this).val());                
                
                store(formData, $(this), storeUrl)
            });
        });        

        $('.setting_checkbox').each(function(){
            $(this).change(function(){
                const formData = new FormData();
                formData.append('key',$(this).attr('name'));
                var value = 0;
                if ($(this).is(':checked')) {
                    value = 1;
                }

                formData.append($(this).attr('name'),value);

                store(formData, $(this), storeUrl)
            })
        });

        $('.setting_radio').each(function(){
            $(this).change(function(){
                const formData = new FormData();
                formData.append('key',$(this).attr('name'));
                
                formData.append($(this).attr('name'),$(this).val());

                store(formData, $(this), storeUrl)
            })
        });

        $('.setting_photo').each(function(){
            $(this).change(function(){
                const formData = new FormData();
                formData.append('key',$(this).attr('name'));
                
                var name = $(this).attr('name').replace('.','_');
                if($(this)[0].files[0].size > adminConfig.maxUploadSize * 1024){
                    alert(adminTranslate.__('upload_limit_error'));
                    return
                }
                formData.append(name,$(this)[0].files[0]);

                store(formData, $(this), storeUrl)
            });
        });

        $('.setting_input_color').each(function(){
            $(this).minicolors({
                swatches: ['#ef9a9a','#90caf9','#a5d6a7','#fff59d','#ffcc80','#bcaaa4','#eeeeee','#f44336','#2196f3','#4caf50','#ffeb3b','#ff9800','#795548','transparent'],
                keywords: 'transparent, initial, inherit',
                change: function() {
                    const formData = new FormData();
                    formData.append('key',$(this).attr('name'));
                    formData.append($(this).attr('name'),$(this).val());                
                    
                    store(formData, $(this), storeUrl)
                }
            });
        })
    }

    var store = function(formData, element, storeUrl){
        $.ajax({ 
            type: 'POST', 
            url: storeUrl, 
            data: formData, 
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                if (element.hasClass('setting_photo')) {
                    location.reload()
                } else {
                    element.closest('.form-group').addClass('changed')
                }                
            }
        }).fail(function() {
            location.reload()
        });
    }

    var checkFmpeg = function(checkUrl) {
        $('.ffmpeg_alert').hide();
        $.ajax({ 
            type: 'POST', 
            url: checkUrl, 
            dataType: 'json',
            success: function (data) {
                if (data.status) {
                    $('#ffmpeg_success').show();
                    $('#ffmpeg_success').html(data.message);
                } else {
                    $('#ffmpeg_error').show();
                    $('#ffmpeg_error').html(data.message);
                }
            }
        });
    }

    return {
        init: init,
        checkFmpeg: checkFmpeg
    };
}();