<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="package_form" method="post" action="{{ route('admin.user_page.feature_package.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $package->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$package->name }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Amount'). ' (' .getWalletTokenName().')'}}</label>
                <input class="form-control" name="amount" value="{{$package->amount }}" type="number">
            </div>
            <div id="parent_content" class="form-group">
                <label class="control-label">{{__('Type')}}</label>
                <select id="type" name="type" class="form-select">
                    @foreach($types as $key => $type)
                        <option @if ($package->type && $package->type->value == $key) selected @endif value="{{$key}}">{{$type}}</option>
                    @endforeach
                </select>
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
    adminUserPage.initCreateFeaturePackage();
</script>