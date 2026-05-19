<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="country_form" method="post" enctype="multipart/form-data" action="{{ route('admin.country.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $country->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('ISO')}}</label>
                <input class="form-control" name="country_iso" value="{{ $country->country_iso }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($country->id) readonly @endif value="{{$country->getTranslatedAttributeValue('name') }}" type="text">
                @if ($country->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$country->getTable(),'name',$country->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Order')}}</label>
                <div class="control">
                    <input class="form-control" name="order" value="{{ $country->order }}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" min="0" step="1" type="number">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $country->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_country">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminCountry.initCreateCountry();
</script>
