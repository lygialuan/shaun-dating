<header class="modal-card-head">
    <p class="modal-card-title">{{__('Upload translate')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="language_upload_phrase_form" enctype="multipart/form-data" method="post" action="{{ route('admin.language.store_upload_phrase', $language->id)}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('File Json')}}</label>
                <input class="form-control" name="file" type="file">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="language_upload_phrase_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminLanguage.initUploadPhrase();
</script>