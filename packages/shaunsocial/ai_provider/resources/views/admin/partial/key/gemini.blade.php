<div class="form-group">
    <label class="control-label">{{ __('API Key') }}</label>
    <input class="form-control" name="config[api_key]" value="{{ $config['api_key'] ?? '' }}" type="password" placeholder="AIza...">
    <small class="form-text text-muted">{{ __('Enter your Google AI API key') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Model') }}</label>
    <input
        class="form-control"
        type="text"
        name="config[model]"
        value="{{ $config['model'] ?? '' }}"
        placeholder="ex: gemini-1.5-pro, gemini-1.5-flash"
        autocomplete="off">
    <small class="form-text text-muted">{{ __('Enter the exact model name according to the provider’s documentation.') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Temperature') }}</label>
    <input class="form-control" name="config[temperature]" value="{{ $config['temperature'] ?? '0.7' }}" type="number" step="0.1" min="0" max="2">
    <small class="form-text text-muted">{{ __('Controls randomness (0 = focused, 2 = creative)') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Max Output Tokens') }}</label>
    <input class="form-control" name="config[max_output_tokens]" value="{{ $config['max_output_tokens'] ?? '1000' }}" type="number" min="1" max="8192" step="1">
    <small class="form-text text-muted">{{ __('Maximum number of tokens in response') }}</small>
</div>

<div class="form-group">
    <label class="control-label">{{ __('System Prompt') }}</label>
    <textarea class="form-control" name="config[system_prompt]" rows="3" placeholder="You are a helpful AI assistant...">{{ $config['system_prompt'] ?? '' }}</textarea>
    <small class="form-text text-muted">{{ __('Default system prompt for the AI') }}</small>
</div>
