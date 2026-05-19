<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="gateway_form" method="post" action="{{ route('admin.gateway.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $gateway->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$gateway->name }}" type="text">
            </div>
            @includeIf('shaun_gateway::admin.partial.gateway.'.$gateway->key, ['config' => $gateway->getConfig()])

            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>            
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $gateway->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="gateway_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminGateway.initEdit();
</script>