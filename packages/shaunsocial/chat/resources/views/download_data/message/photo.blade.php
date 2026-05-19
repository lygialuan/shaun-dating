<div class="photo-item">
    @foreach ($items as $item)
        <div>
            <img src="{{$static_path}}{{getPathForDownload($item['subject']['url'])}}">
        </div>
        {{exportDownloadDataFile($download_data_user_id,$item['subject']['url'])}}
    @endforeach
</div>