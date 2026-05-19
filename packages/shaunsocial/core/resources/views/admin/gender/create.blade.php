<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="gender_form" method="post" action="{{ route('admin.gender.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $gender->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($gender->id) readonly @endif value="{{$gender->getTranslatedAttributeValue('name') }}" type="text">
                @if ($gender->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$gender->getTable(),'name',$gender->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>            
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $gender->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="gender_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminGender.initCreate();
</script>