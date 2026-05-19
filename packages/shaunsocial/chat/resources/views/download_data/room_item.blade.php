<li class="item-box">
    <div class="item-flex">
        @foreach ($members as $member)
            @if ($member['id'] != $user_id)
                <img class="avatar" src="{{$member['avatar']}}"/>
            @endif
        @endforeach
        <a href="room/{{$id}}.html">
            {{$name}}
        </a>    
    </div>
</li>