<li class="item-box">
    <div class="item-flex">
        <img class="avatar" src="{{$user['avatar']}}"/>
        <a href="{{$user['href']}}">{{$user['name']}}</a>        
    </div>
    <div class="item-date">
        {{$created_at_full}}
    </div>
    <div class="item-content">
        {!! nl2br(e($content)) !!}
    </div>
    <div class="item-items">
        @includeIf('shaun_chat::download_data.message.'.$type, ['items' => $items])
    </div>
</li>