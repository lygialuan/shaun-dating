<header class="modal-card-head">
    <p class="modal-card-title">{{__('Create')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="background_form" method="post" enctype="multipart/form-data" action="{{ route('admin.story.background.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            <div class="form-group">
                <label class="control-label">{{__('Photo')}}</label>
                <input class="form-control" name="photo" type="file">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" checked value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="background_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminStory.initCreateBackground();
</script>