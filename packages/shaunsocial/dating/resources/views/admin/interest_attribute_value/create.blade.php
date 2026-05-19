<header class="modal-card-head">
    <p class="modal-card-title">{{__('Create')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="hashtag_form" method="post" action="{{ route('admin.dating.interest_attribute.value.store')}}">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $value->id }}" class="form-control"/>
            <input type="hidden" name="attribute_id" value="{{ $attributeId }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <div class="control">
                    <input class="form-control" name="name" @if ($value->id) readonly @endif value="{{$value->getTranslatedAttributeValue('name') }}" type="text">
                </div>
                @if ($value->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$value->getTable(),'name',$value->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $value->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            @if(!$value->id)
                <div class="form-group">
                    <label class="control-label form-check parent-checkbox">
                        <input class='form-check-input mx-1 my-auto selectOpenAddMore' type="checkbox" name='add_other_attribute'/>{{__('Add to other attribute')}}
                    </label>
                    <div class='more-option' style="display:none">
                        @foreach($moreAttributes as $attribute)
                            <label class='option-box form-check mx-2 my-auto'>
                                <input class="form-check-input option-box-input " type="checkbox" name="more_attribute[]" value="{{$attribute->id}}"/>
                                {{ $attribute->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="hashtag_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>

<script src="{{ asset('admin/js/translation.js') }}"></script>
<script>
    adminHashtag.initCreate();
    adminDating.initCreateAttributeValue();
</script>