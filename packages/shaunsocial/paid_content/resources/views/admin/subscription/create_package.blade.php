<header class="modal-card-head">
    <p class="modal-card-title">{{ $title }}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="package_form" method="post" enctype="multipart/form-data" action="{{ route('admin.paid_content.subscription.store_package')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $package->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Price'). ' (' .getWalletTokenName(). ')'}}</label>
                <input class="form-control" name="amount" value="{{$package->amount}}" type="number">
            </div>
            <div id="parent_content" class="form-group">
                <label class="control-label">{{__('Type')}}</label>
                <select @if (!$package->canChangeType()) disabled @endif id="type" name="type" class="form-select">
                    @foreach($typeArray as $key => $type)
                        <option @if ($package->type && $package->type->value == $key) selected @endif value="{{$key}}">{{$type}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Order')}}</label>
                <div class="control">
                    <input class="form-control" name="order" value="{{ $package->order }}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" min="0" step="1" type="number">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $package->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            @if ($package->id)
            <p class="help">
                {{__('If you change package information here it only affects new subscribers. Old subscribers will not be affected.')}}
            </p>
            @endif
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="package_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminPaidContent.initCreatePackage();
</script>