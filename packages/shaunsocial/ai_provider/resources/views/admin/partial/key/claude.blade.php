<div class="form-group">
    <label class="control-label">{{ __('API Key') }}</label>
    <input class="form-control" name="config[api_key]" value="{{ $config['api_key'] ?? '' }}" type="password" placeholder="sk-ant-...">
    <small class="form-text text-muted">{{ __('Enter your Anthropic API key') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Model') }}</label>
    <input
        class="form-control"
        type="text"
        name="config[model]"
        value="{{ $config['model'] ?? '' }}"
        placeholder="ex: claude-3-opus-20240229, claude-3-sonnet-20240229"
        autocomplete="off">
    <small class="form-text text-muted">{{ __('Enter the exact model name according to the provider’s documentation.') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Temperature') }}</label>
    <input class="form-control" name="config[temperature]" value="{{ $config['temperature'] ?? '0.7' }}" type="number" step="0.1" min="0" max="1">
    <small class="form-text text-muted">{{ __('Controls randomness (0 = focused, 1 = creative)') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Max Tokens') }}</label>
    <input class="form-control" name="config[max_tokens]" value="{{ $config['max_tokens'] ?? '1000' }}" type="number" min="1" max="4096" step="1">
    <small class="form-text text-muted">{{ __('Maximum number of tokens in response') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('System Prompt') }}</label>
    <textarea class="form-control" name="config[system_prompt]" rows="3" placeholder="You are a helpful AI assistant...">{{ $config['system_prompt'] ?? '' }}</textarea>
    <small class="form-text text-muted">{{ __('Default system prompt for the AI') }}</small>
</div>
