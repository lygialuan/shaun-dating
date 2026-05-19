<header class="modal-card-head">
    <p class="modal-card-title">{{__('Upload More Fake Photos')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="create_upload_photos_form" method="POST" action="{{ route('admin.user_page.create_sub_profile.store_upload_photos') }}">
            <div id="errors"></div>
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{ __('Upload additional fake profile photos (maximum 10 per upload)') }}</label>
                <input class="form-control" type="file" id="uploadMulti" name="photos[]" multiple accept="image/*">
                <div id="previewImages" class="mt-2"></div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Gender')}}</label>            
                <select name="gender_id" class="form-select">
                    <option value="">{{__('Prefer not to say')}}</option>
                    @foreach($genders as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>             
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_upload_photos_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script src="{{ asset('admin/js/input_upload.js') }}"></script>
<script>
    adminUserPage.initUploadPhotos();
    adminInputUpload.initMultiUpload('#uploadMulti');
    adminTranslate.add({
        'error_limit_photos' : '{{addslashes(__('You can upload up to 10 photos at a time.'))}}',
    });
</script>