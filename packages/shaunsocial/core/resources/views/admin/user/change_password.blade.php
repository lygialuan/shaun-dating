<header class="modal-card-head">
    <p class="modal-card-title">{{__('Change password')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="user_change_password_form" method="post" action="{{ route('admin.user.store_change_password', $id)}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('New password')}}</label>
                <input class="form-control" name="password" type="password">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Confirm password')}}</label>
                <input class="form-control" name="password_confirmed" type="password">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Notify to user')}}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" name="notify" type="checkbox" value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="user_change_password_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminUser.initChangePassword();
</script>