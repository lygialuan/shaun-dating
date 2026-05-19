<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="warning_content_category_form" method="post" action="{{ route('admin.content_warning.store_category')}}" onsubmit="return false;">
            <div id="errors">
            </div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $category->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($category->id) readonly @endif value="{{$category->getTranslatedAttributeValue('name') }}" type="text">
                @if ($category->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$category->getTable(),'name',$category->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            @if ($category->canDelete())
                <div class="form-group">
                    <label class="control-label">{{__('Active')}}</label>
                    <div class="field-body">
                        <div class="field">
                            <label class="switch">
                                <input class="form-check-input" name="is_active" type="checkbox" {{ $category->is_active ? 'checked' : ''}} value="1">
                            </label>
                        </div>
                    </div>
                </div>
            @endif
            
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="warning_content_category_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminWarningContent.initCreateCategory();
</script>