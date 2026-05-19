<div class="photo-item">
    @foreach ($items as $item)
        <img src="{{$static_path}}{{getPathForDownload($item['subject']['url'])}}">
        {{exportDownloadDataFile($download_data_user_id,$item['subject']['url'])}}
    @endforeach
</div>