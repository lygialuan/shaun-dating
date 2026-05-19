@extends('shaun_core::admin.layouts.master')
@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">
            {{ $title }}
        </h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form method="post" action="{{ route('admin.user_page.ai_config.store', $page->id) }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('Enable AI')}}</label>
                <div class="form-check form-switch">
                    <input name="enabled" class="form-check-input" type="checkbox" value="1" {{ old('enabled', $config->enabled !== null ? $config->enabled : null) ? 'checked' : '' }}>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Chat Tone')}}</label>
                <select name="tone" class="form-select">
                    @foreach ($tones as $t)
                        <option value="{{ $t->value }}" {{ old('tone', $config->tone?->value ?? 'friendly') === $t->value ? 'selected' : '' }}>
                            {{ ucfirst($t->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Intent')}}</label>
                <select name="intent" class="form-select">
                    @foreach ($intents as $i)
                        <option value="{{ $i->value }}" {{ old('intent', $config->intent?->value ?? 'casual') === $i->value ? 'selected' : '' }}>
                            {{ ucfirst($i->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @foreach ([
                ['trait_playfulness', 'Playfulness'],
                ['trait_warmth', 'Warmth'],
                ['trait_assertiveness', 'Assertiveness'],
            ] as [$field, $label])
            <div class="form-group">
                <label class="control-label">{{__($label)}} (0–100)</label>
                <input type="number" name="{{ $field }}" class="form-control" min="0" max="100"
                    value="{{ old($field, $config->$field ?? 50) }}">
            </div>
            @endforeach
            <div class="form-group">
                <label class="control-label">{{__('Message Length')}}</label>
                <select name="message_length" class="form-select">
                    @foreach ($lengths as $l)
                        <option value="{{ $l->value }}" {{ old('message_length', $config->message_length?->value ?? 'medium') === $l->value ? 'selected' : '' }}>
                            {{ ucfirst($l->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Max messages per day')}}</label>
                <input type="number" name="max_messages_per_day" class="form-control" min="1" max="1000" value="{{ old('max_messages_per_day', $config->max_messages_per_day ?? 50) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{ __('Minimum reply delay (seconds)') }}</label>
                <input type="number" name="reply_delay_min_sec" class="form-control" min="1" max="1000" value="{{ old('reply_delay_min_sec', $config->reply_delay_min_sec ?? 10) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{ __('Maximum reply delay (seconds)') }}</label>
                <input type="number" name="reply_delay_max_sec" class="form-control" min="1" max="1000" value="{{ old('reply_delay_max_sec', $config->reply_delay_max_sec ?? 120) }}">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Custom System Prompt')}} <small class="text-muted">({{__('overrides auto-generated prompt')}})</small></label>
                <textarea name="custom_system_prompt" class="form-control" rows="5">{{ old('custom_system_prompt', $config->custom_system_prompt) }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-filled-blue">{{__('Save')}}</button>
                <a href="{{ route('admin.user_page.index') }}" class="btn-outline-blue">{{__('Back')}}</a>
            </div>
        </form>
    </div>
</div>
@stop
