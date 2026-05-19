<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="link_icon_form" method="post" action="{{ route('admin.link_icon.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $icon->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Domain')}}</label>
                <input class="form-control" name="domain" value="{{$icon->domain }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Icon')}}</label>
                <input class="form-control" name="icon" type="file">
                <?php $image = $icon->getIcon(); ?>
                @if ($image) 
                    <img src="{{$image}}" class="mb-1" style="max-width: 50px"/>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>            
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $icon->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="link_icon_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminLinkIcon.initCreate();
</script>