<div class="video-item">
    @foreach ($items as $item)
        <video controls>
            <source src="{{getPathForDownload($item['subject']['file']['url'])}}" type="video/mp4">                
        </video>
        {{exportDownloadDataFile($download_data_user_id, $item['subject']['file']['url'])}}
    @endforeach
</div>