<header class="modal-card-head">
    <p class="modal-card-title">{{__('Create')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="hashtag_form" method="post" action="{{ route('admin.hashtag.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $hashtag->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <div class="control">
                    <input class="form-control" name="name" value="{{$hashtag->name }}" type="text">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $hashtag->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="hashtag_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminHashtag.initCreate();
</script>