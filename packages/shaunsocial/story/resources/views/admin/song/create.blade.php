<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="song_form" method="post" enctype="multipart/form-data" action="{{ route('admin.story.song.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $song->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{ $song->name }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('File')}}</label>
                <input class="form-control" name="file" type="file">
                <?php $file = $song->getFile('file_id'); ?>
                @if ($file) 
                    <p class="help">
                        {{$file->name}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input  name="is_active" class="form-check-input" type="checkbox"  {{ $song->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Default')}}</label>        
                <div class="form-check form-switch">
                    <input  name="is_default" class="form-check-input" type="checkbox"  {{ $song->is_default ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="song_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminStory.initCreateSong();
</script>