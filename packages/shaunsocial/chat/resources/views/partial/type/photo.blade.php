<?php 
    $items = $message->getItems();
?>
<div class="chat-photos-list">
    @foreach ($items as $item)
        <div class="chat-photos-item" style="background-image: url({{$item->getSubject()->getUrl()}})"></div>
    @endforeach
</div>