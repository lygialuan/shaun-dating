<header class="modal-card-head">
    <p class="modal-card-title">{{__('Edit Two-Factor Provider')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="two_factor_provider_form" method="post" action="{{ route('admin.two_factor_provider.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $provider->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" readonly value="{{$provider->getTranslatedAttributeValue('name') }}" type="text">
                <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$provider->getTable(),'name',$provider->id])}}">{{__('Translate')}}</a>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Description')}}</label>
                <textarea class="form-control" name="description" readonly>{{$provider->getTranslatedAttributeValue('description') }}</textarea>
                <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$provider->getTable(),'description',$provider->id])}}">{{__('Translate')}}</a>
            </div>
            @includeIf('shaun_core::admin.partial.two_factor_provider.provider_'.$provider->type,['config' => $provider->getConfig()])
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>           
                <div class="form-check form-switch">
                    <input id="is_active" class="form-check-input" name="is_active" @if ($provider->is_active) @endif type="checkbox" {{$provider->is_active ? 'checked' : ''}} value="1">
                </div>  
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="two_factor_provider_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminTwoFactorProvider.initEdit();
</script>