<div class="form-group">
    <div id="ffmpeg_success" style="display: none;" class="ffmpeg_alert alert alert-success" role="alert">
    </div>
    <div id="ffmpeg_error" style="display: none;" class="ffmpeg_alert alert alert-danger" role="alert">
    </div>
</div>
<div class="form-group">
    <a class="btn-filled-blue" id="check_ffmpeg" href="javascript:void(0);">
        {{__('Check FFMPEG')}}
    </a>
</div>

@push('scripts-body')
<script>
    $('#check_ffmpeg').click(function(){
        adminSetting.checkFmpeg('{{route('admin.setting.check_fmpeg')}}')
    });   
</script>
@endpush