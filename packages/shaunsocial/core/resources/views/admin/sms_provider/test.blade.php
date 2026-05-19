
<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="sms_provider_test_form" method="post" action="{{ route('admin.sms_provider.store_test')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Phone number')}}</label>
                <input class="form-control" name="phone_number" value="" type="text" id="phoneNumber">
                <input type="hidden" id="phoneCode">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="sms_provider_test_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminSmsProvider.initTest();
    adminSmsProvider.initCountryPhoneCode('#phoneNumber', '#phoneCode');
</script>