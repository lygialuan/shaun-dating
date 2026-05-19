<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="create_category_form" method="post" action="{{ route('admin.dating.interest_attribute.store')}}">
            <div id="errors">
            </div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $attribute->id }}">
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <div class="control">
                    {{-- <input id="clone_from" type="hidden" name="clone_from" value=""> --}}
                    <input class="form-control" name="name" @if ($attribute->id) readonly @endif value="{{$attribute->getTranslatedAttributeValue('name') }}" type="text">
                </div>
                @if ($attribute->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$attribute->getTable(),'name',$attribute->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>

            @if(!$attribute->id)
                <div class="form-group">
                    <label class="control-label">{{__('Import values from existing attributes')}}</label>
                    <select id="clone_from" name="clone_from" class="form-select">
                        <option value="0"></option>
                        @foreach($otherAttributes as $otherAttribute)
                            <option value="{{$otherAttribute->id}}">{{$otherAttribute->getTranslatedAttributeValue('name')}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Icon')}}</label>
                <input class="form-control" name="icon" type="file" id="uploadLogo">
                <?php $image = $attribute->getIconUrl(); ?>
                <img id="uploadedImage" src="{{$image}}" class="mb-1 icon_image"/>
            </div>

            <div class="form-group">
                <div class="radio-box">
                    <label class="radio-block">
                        <input id="single_choice" type="checkbox" name="allow_multiple"  class="form-radio" data-group="allow_multiple" value="0" @if(!$attribute->allow_multiple) checked @endif>
                        <span class="radio-visual"></span> 
                        {{ __('Single choice') }}
                    </label>
                    <label class="radio-block">
                        <input id="multiple_choice" type="checkbox" name="allow_multiple"  class="form-radio" data-group="allow_multiple" value="1" @if($attribute->allow_multiple) checked @endif>
                        <span class="radio-visual"></span> 
                        {{ __('Multiple choice') }}
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $attribute->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_category_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script src="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.css')}}">
<script src="{{ asset('admin/js/input_upload.js') }}"></script>
<script>
    adminDating.initCreateAttribute('{{ route("admin.dating.search")."?keyword=abc" }}');
    adminDating.initRadio();
    adminInputUpload.initInputUpload('#uploadLogo');
</script>