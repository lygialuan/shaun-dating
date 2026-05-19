<div class="form-group">
    <a class="admin_modal_ajax btn-filled-blue" href="javascript:void(0);" data-url="{{route('admin.mail.test')}}">
        {{__('Test send mail')}}
    </a>
</div>

@push('scripts-body')
<script src="{{ asset('admin/js/mail.js') }}"></script>
@endpush