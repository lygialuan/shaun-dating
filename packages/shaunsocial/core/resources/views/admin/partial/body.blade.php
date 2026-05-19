<div style="display:none;" id="message-error">
    <div class="admin-message message-error" role="alert"></div>
</div>

<div id="modal-delete" class="modal">
    <div class="modal-background modal-close"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">{{__('Please confirm')}}</p>
        </header>
        <section class="modal-card-body">        
            <p></p>
        </section>
        <footer class="modal-card-foot">
            <button class="button btn-filled-blue modal-delete-confirm">{{__('Confirm')}}</button>
            <button class="button btn-filled-white modal-close">{{__('Cancel')}}</button>
        </footer>
    </div>
</div>

<div id="modal-confirm" class="modal">
    <div class="modal-background modal-close"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title"></p>
        </header>
        <section class="modal-card-body">        
            <p></p>
        </section>
        <footer class="modal-card-foot">
            <button class="button btn-filled-blue modal-action-confirm">{{__('Confirm')}}</button>
            <button class="button btn-filled-white modal-close">{{__('Cancel')}}</button>
        </footer>
    </div>
</div>

<div id="modal-loading" style="display:none;">
    <div class="modal-background modal-close"></div>
    <div class="modal-card">
        <div class="flex items-center justify-center">
            <div class="modal-spin"></div>
        </div>
    </div>
</div>

<div id="modal-ajax" class="modal">
    
</div>
@isModerator
<div id="modal-language" class="modal">
    <div class="modal-background modal-close"></div>
    <div class="modal-card modal-card-sm">
        <header class="modal-card-head">
            <p class="modal-card-title">{{__('Change Language')}}</p>
        </header>
        <section class="modal-card-body">
            @foreach ($languagesGlobal as $key => $name)   
                <p>
                    <a class="@if($key == $languageCurrent) fw-bold @endif" href="{{route('admin.dashboard.change_language',$key)}}">{{$name}}</a>
                </p>
            @endforeach
        </section>
    </div>
</div>
@endIsModerator