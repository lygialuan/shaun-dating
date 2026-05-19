<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="city_form" method="post" enctype="multipart/form-data" action="{{ route('admin.country.city.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="country_id" value="{{ $state->country_id }}" class="form-control"/>
            <input type="hidden" name="state_id" value="{{ $state->id }}" class="form-control"/>
            <input type="hidden" name="id" value="{{ $city->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($city->id) readonly @endif value="{{$city->getTranslatedAttributeValue('name') }}" type="text">
                @if ($city->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$city->getTable(),'name',$city->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Order')}}</label>
                <div class="control">
                    <input class="form-control" name="order" value="{{ $city->order }}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" min="0" step="1" type="number">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $city->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_city">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminCountry.initCreateCity();
</script>
