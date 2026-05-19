<header class="modal-card-head">
    <p class="modal-card-title">{{__('Test email')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="mail_test_form" method="post" action="{{ route('admin.mail.store_test')}}" onsubmit="return false;">
            <div id="errors"></div>
            <div style="display: none;" class="admin-message message-success" role="alert"></div>
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('Email')}}</label>
                <input class="form-control" name="email" type="text">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="mail_test_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminMail.initTest();
</script>