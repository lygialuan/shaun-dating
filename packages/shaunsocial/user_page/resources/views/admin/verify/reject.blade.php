<header class="modal-card-head">
    <p class="modal-card-title">{{__('Please confirm')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="verify_form" method="post" action="{{ route('admin.user_page.verify.store_reject')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $page->id }}" />
            <div class="form-group">
                <textarea class="form-control" placeholder="{{__('Please enter reason to reject')}}" name="reason"></textarea>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="verify_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminVerify.initReject();
</script>