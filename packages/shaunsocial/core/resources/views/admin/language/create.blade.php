<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="language_form" method="post" action="{{ route('admin.language.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $language->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($language->id) readonly @endif value="{{$language->getTranslatedAttributeValue('name') }}" type="text">
                @if ($language->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$language->getTable(),'name',$language->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            @if (!$language->id)
                <div class="form-group">
                    <label class="control-label">{{__('Key')}}</label>
                    <input class="form-control" name="key" value="" type="text">
                    <p class="help">
                        <a target="_blank" href="https://www.w3schools.com/tags/ref_language_codes.asp">https://www.w3schools.com/tags/ref_language_codes.asp</a>
                    </p>
                </div>
            @else
                <input type="hidden" name="key" value="{{ $language->key }}" />
            @endif
            <div class="form-group">
                <label class="control-label">{{__('RTL')}}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" name="is_rtl" type="checkbox" {{ $language->is_rtl ? 'checked' : ''}} value="1">
                </div>
            </div>
            @if ($countActive > 1 || ! $language->is_default)
            <div class="form-group">
                <label class="control-label">{{__('Default')}}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" @if ($language->is_default) onclick='return false' @endif name="is_default" id="is_default" type="checkbox" {{ $language->is_default ? 'checked' : ''}} value="1">
                </div>
            </div>
            @endif
            @if ($countActive > 1 || ! $language->is_active)
                <div class="form-group">
                    <label class="control-label">{{__('Active')}}</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" name="is_active" id="is_active" type="checkbox" {{ $language->is_active ? 'checked' : ''}} value="1">
                    </div>
                </div>
            @endif
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="language_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>

<script>
    adminLanguage.initCreate();
</script>