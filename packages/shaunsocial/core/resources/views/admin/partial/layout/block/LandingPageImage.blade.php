<input id="photo_id" name="photo_id" type="hidden" value="">
<div class="form-group">
    <label class="control-label">{{__('Image/Video')}}</label>
    <input id="image" class="form-control mb-2" name="image" type="file">
    <div class="admin-indeterminate-progress-bar mb-2" style="display: none"></div>
    <div id="content_image" style="display: none;">
        <img id="image_preview" width="200px" />
        <video id="video_preview" width="200px"></video>
    </div>
</div>

<script>
    function getLandingPageImage()
    {
        return {
            'photo_id': $('#modal-ajax #photo_id').val()
        }
    }
    function setLandingPageImage(data)
    {
        photo_id = '';
        if (typeof data.photo_id !== "undefined") {
            photo_id = data.photo_id;
        }

        $.ajax({ 
            type: 'POST', 
            url: '{{route('admin.layout.get_data_block')}}', 
            data: {'photo_id' : photo_id, 'component': 'LandingPageImage'}, 
            dataType: 'json',
            success: function (data) {
                if (data.status){
                    $('#modal-ajax #content_image').show()
                    $('#modal-ajax #image_preview').hide();
                    $('#modal-ajax #video_preview').hide();
                    if (data.data.extension == 'mp4') {
                        $('#modal-ajax #video_preview').show();
                        $('#modal-ajax #video_preview').attr('src', data.data.file_url)
                    } else {
                        $('#modal-ajax #image_preview').show();
                        $('#modal-ajax #image_preview').attr('src', data.data.file_url)
                    }
                }
            }
        });

        $('#modal-ajax #image').change(function(){
            const formData = new FormData();

            formData.append('file',$(this)[0].files[0]);
            formData.append('extension', '{{config('shaun_core.validation.photo').',mp4'}}');
            $('.admin-indeterminate-progress-bar').show();

            $.ajax({ 
                type: 'POST', 
                url: '{{route('admin.layout.upload_file_block')}}', 
                data: formData, 
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status){
                        $('#modal-ajax #photo_id').val(data.data.file_id)
                        $('#modal-ajax #content_image').show()
                        $('#modal-ajax #image_preview').hide();
                        $('#modal-ajax #video_preview').hide();
                        if (data.data.extension == 'mp4') {
                            $('#modal-ajax #video_preview').show();
                            $('#modal-ajax #video_preview').attr('src', data.data.file_url)
                        } else {
                            $('#modal-ajax #image_preview').show();
                            $('#modal-ajax #image_preview').attr('src', data.data.file_url)
                        }
                    } else {
                        alert(data.messages[0])
                    }
                    $('.admin-indeterminate-progress-bar').hide();
                },
                error: function (request, status, error) {
                    alert(error);
                    $('.admin-indeterminate-progress-bar').hide();
                }
            });
        });
        
        $('#modal-ajax #photo_id').val(photo_id)
        
    }
</script>