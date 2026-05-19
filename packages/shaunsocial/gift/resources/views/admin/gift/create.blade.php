<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="create_gift_form" method="post" action="{{ route('admin.gift.store')}}">
            <div id="errors">
            </div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $gift->id }}">
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <div class="control">
                    {{-- <input id="clone_from" type="hidden" name="clone_from" value=""> --}}
                    <input class="form-control" name="name" @if ($gift->id) readonly @endif value="{{$gift->getTranslatedAttributeValue('name') }}" type="text">
                </div>
                @if ($gift->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$gift->getTable(),'name',$gift->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Price')}}</label>
                <div class="control">
                    <input class="form-control" name="price" value="{{ $gift->price }}" type="number">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Icon')}}</label>
                <input class="form-control" name="icon" type="file" id="uploadLogo">
                <?php $image = $gift->getIconUrl(); ?>
                <img id="uploadedImage" src="{{$image}}" class="mb-1 icon_image"/>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $gift->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_gift_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script src="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.css')}}">
<script src="{{ asset('admin/js/input_upload.js') }}"></script>
<script>
    adminGift.initCreate();
    adminInputUpload.initInputUpload('#uploadLogo');
</script>