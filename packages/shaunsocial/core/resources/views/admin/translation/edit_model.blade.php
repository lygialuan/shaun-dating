<header class="modal-card-head">
    <p class="modal-card-title">{{__('Translate')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="translation_edit_model_form" method="post" action="{{ route('admin.translation.store_model')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="model" value="{{ $model }}" />
            <input type="hidden" name="column" value="{{ $column }}" />
            <input type="hidden" name="id" value="{{ $id }}" />
            <input type="hidden" name="empty" value="{{ $empty }}" />
            @foreach ($languages as $key=>$name)
                <div class="form-group">
                    <label class="control-label">{{ $name }}</label>
                    <textarea class="form-control" rows="3" name="language[{{ $key }}]">{{ $values[$key]->value }}</textarea>
                </div>
            @endforeach
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="translation_edit_model_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminTranslation.initEditModel();
</script>