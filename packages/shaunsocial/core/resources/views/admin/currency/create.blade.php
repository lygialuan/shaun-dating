<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="currency_form" method="post" action="{{ route('admin.currency.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $currency->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$currency->name }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Code')}}</label>
                <input class="form-control" name="code" value="{{$currency->code }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Symbol')}}</label>
                <input class="form-control" name="symbol" value="{{$currency->symbol }}" type="text">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="currency_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminCurrency.initCreate();
</script>