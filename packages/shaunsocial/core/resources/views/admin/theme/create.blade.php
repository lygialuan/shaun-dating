<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="theme_form" method="post" action="{{ route('admin.theme.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $theme->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$theme->name }}" type="text">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="theme_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminTheme.initCreate();
</script>