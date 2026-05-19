<?php 
    $items = $message->getItems();
?>
<div class="chat-files-list">
    @foreach ($items as $item)
        <div class="chat-files-item">
            <audio controls muted>
                <source src="{{$item->getSubject()->getFile('file_id')->getUrl()}}" type="audio/mpeg">
            </audio>
        </div>
    @endforeach
</div>