<div>
    @foreach ($items as $item)
        <div>
            <a href="{{$static_path}}{{getPathForDownload($item['subject']['url'])}}">{{$item['subject']['name']}}</a>
        </div>
        {{exportDownloadDataFile($download_data_user_id,$item['subject']['url'])}}
    @endforeach
</div>