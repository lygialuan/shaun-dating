<?php 
    $items = $message->getItems();
?>
<div class="chat-files-list">
    @foreach ($items as $item)
        <div class="chat-files-item">
            <a href="{{$item->getSubject()->getUrl()}}" download>{{ $item->getSubject()->name }}</a>
        </div>
    @endforeach
</div>