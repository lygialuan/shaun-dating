<div class="form-group">
    <label class="control-label">{{ __('Default Model') }}</label>
    <input
        class="form-control"
        type="text"
        name="config[model]"
        value="{{ $config['model'] ?? '' }}"
        placeholder="ex: claude-3-opus-20240229, claude-3-sonnet-20240229"
        autocomplete="off">
    <small class="form-text text-muted">{{ __('Used when new keys are created without an explicit model.') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Default Temperature') }}</label>
    <input class="form-control" name="config[temperature]" value="{{ $config['temperature'] ?? '0.7' }}" type="number" step="0.1" min="0" max="1">
    <small class="form-text text-muted">{{ __('Controls randomness (0 = focused, 1 = creative).') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Default Max Tokens') }}</label>
    <input class="form-control" name="config[max_tokens]" value="{{ $config['max_tokens'] ?? '1000' }}" type="number" min="1" max="4096" step="1">
    <small class="form-text text-muted">{{ __('Fallback value applied when key configuration does not specify tokens.') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('System Prompt') }}</label>
    <textarea class="form-control" name="config[system_prompt]" rows="3" placeholder="You are a helpful AI assistant...">{{ $config['system_prompt'] ?? '' }}</textarea>
    <small class="form-text text-muted">{{ __('Default system prompt shared across keys.') }}</small>
</div>
