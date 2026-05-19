<header class="modal-card-head">
    <p class="modal-card-title">{{ $title }}</p>
</header>
<section class="modal-card-body">
    <div class="card-content">
        <form id="ai_provider_key_form" method="post" action="{{ $action }}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $keyItem->id }}" />
            <input type="hidden" name="ai_provider_id" value="{{ $provider->id }}" />

            <div class="form-group">
                <label class="control-label">{{ __('Name') }}</label>
                <input class="form-control" name="name" value="{{ old('name', $keyItem->name) }}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{ __('Description') }}</label>
                <textarea class="form-control" name="description" rows="2">{{ old('description', $keyItem->description) }}</textarea>
                @if ($keyItem->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model', ['ai_provider_keys', 'description', $keyItem->id]) }}">{{ __('Translate') }}</a>
                @else
                    <p class="help">{{ __('*You can add translation language after creating.') }}</p>
                @endif
            </div>

            @php($configValues = $keyItem->config ?? $provider->getDefaultConfig())
            @includeIf('shaun_ai_provider::admin.partial.key.' . $provider->getProviderKey(), ['config' => $configValues])

            <div class="form-group">
                <label class="control-label">{{ __('Active') }}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" name="is_active" type="checkbox" {{ old('is_active', $keyItem->is_active ?? true) ? 'checked' : '' }} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="ai_provider_key_submit">{{ __('Submit') }}</button>
    <button class="btn-filled-white modal-close">{{ __('Cancel') }}</button>
</footer>
<script>
    adminAiProviderKey.initForm();
</script>
