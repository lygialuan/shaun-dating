<div class="link-item">
    @foreach ($items as $item)
        @if ($item['subject']['photo'])
            <a href="{{$item['subject']['url']}}" target="_blank" class="link-item-thumb">
                <img src="{{$static_path}}{{getPathForDownload($item['subject']['photo']['url'])}}">
            </a>
            {{exportDownloadDataFile($download_data_user_id, $item['subject']['photo']['url'])}}
        @endif
        @if ($item['subject']['title'])
            <div class="link-item-content">
                <a href="{{$item['subject']['url']}}" class="link-item-content-title" target="_blank">{{$item['subject']['title']}}</a>
                <div>{{$item['subject']['description']}}</div>
            </div>                    
        @endif        
    @endforeach
</div>