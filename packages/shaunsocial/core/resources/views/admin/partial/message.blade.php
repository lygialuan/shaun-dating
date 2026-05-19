@if (Session::has('admin_message_success'))
    <div class="admin-message message-success" role="alert">
        {{Session::get('admin_message_success')}}
    </div>
@endif

@if (Session::has('admin_message_error'))
    <div class="admin-message message-error" role="alert">
        {{Session::get('admin_message_error')}}
    </div>
@endif

@if (Session::has('admin_message_notice'))
    <div class="admin-message message-notice" role="alert">
        {{Session::get('admin_message_notice')}}
    </div>
@endif