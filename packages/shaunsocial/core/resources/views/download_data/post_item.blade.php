<li class="item-box">
    <div class="item-content">
        {!! nl2br(e($content)) !!}
    </div>
    <div class="item-date">
        {{$created_at_full}}
    </div>
    <div class="item-items">
        @includeIf('shaun_core::download_data.posts.'.$type, ['items' => $items])
    </div>
</li>