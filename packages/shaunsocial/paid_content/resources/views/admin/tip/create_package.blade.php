<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="package_form" method="post" action="{{ route('admin.paid_content.tip.store_package')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $package->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Amount'). ' (' .getWalletTokenName().')'}}</label>
                <input class="form-control" name="amount" value="{{$package->amount }}" type="number">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Google product id')}}</label>
                <input class="form-control" name="google_price_id" value="{{$package->google_price_id }}" type="text">
                <p class="help">
                    {{__('This is for in-app purchase on mobile apps.')}}
                </p>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Apple product Id')}}</label>
                <input class="form-control" name="apple_price_id" value="{{$package->apple_price_id }}" type="text">
                <p class="help">
                    {{__('This is for in-app purchase on mobile apps.')}}
                </p>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>            
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox" {{ $package->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="package_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminPaidContent.initCreateTipPackage();
</script>